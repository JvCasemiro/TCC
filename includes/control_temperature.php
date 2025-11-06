<?php
require_once __DIR__ . '/../config/database.php';

class TemperatureController {
    private $conn;
    private $statusFile;
    private $numTemperatures = 4;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->statusFile = __DIR__ . '/../temperature_status.txt';
        $this->initializeStatusFile();
    }

    private function initializeStatusFile() {
        if (!file_exists($this->statusFile)) {
            $initialStatus = str_repeat('0', $this->numTemperatures);
            file_put_contents($this->statusFile, $initialStatus);
        } else {
            $currentStatus = trim(file_get_contents($this->statusFile));
            if (strlen($currentStatus) != $this->numTemperatures) {
                $currentStatus = str_pad($currentStatus, $this->numTemperatures, '0', STR_PAD_RIGHT);
                file_put_contents($this->statusFile, $currentStatus);
            }
        }
        @chmod($this->statusFile, 0666);
    }

    public function updateTemperatureStatus($temperatureId, $status) {
        // Busca o índice da temperatura no banco de dados
        $index = $this->getTemperatureIndex($temperatureId);
        if ($index === false) {
            throw new Exception("Temperatura com ID {$temperatureId} não encontrada");
        }

        $status = strtoupper($status) === 'ON' ? '1' : '0';

        $currentStatus = str_split(trim(file_get_contents($this->statusFile)));
        
        // Garante que o array tenha tamanho suficiente
        while (count($currentStatus) <= $index) {
            $currentStatus[] = '0';
        }
        
        if (isset($currentStatus[$index]) && $currentStatus[$index] !== $status) {
            $currentStatus[$index] = $status;
            file_put_contents($this->statusFile, implode('', $currentStatus));
            
            // Atualiza o banco de dados
            $this->updateDatabase($temperatureId, $status === '1' ? 'ON' : 'OFF');
            
            return true;
        }
        
        return false;
    }
    
    private function getTemperatureIndex($temperatureId) {
        try {
            $stmt = $this->conn->query("SELECT ID_Temperatura FROM Temperaturas ORDER BY ID_Temperatura");
            $temperatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($temperatures as $index => $temp) {
                if ($temp['ID_Temperatura'] == $temperatureId) {
                    return $index;
                }
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao buscar índice da temperatura: " . $e->getMessage());
            return false;
        }
    }

    private function updateDatabase($temperatureId, $status) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE Temperaturas SET `Status` = :status, Data_Atualizacao = NOW() WHERE ID_Temperatura = :id"
            );
            $result = $stmt->execute([
                ':status' => $status,
                ':id' => $temperatureId
            ]);
            
            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Erro ao executar a atualização: " . ($error[2] ?? 'Erro desconhecido'));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar banco de dados: " . $e->getMessage());
            throw new Exception("Erro ao atualizar status da temperatura no banco de dados: " . $e->getMessage());
        }
    }

    public function getStatus() {
        $status = trim(file_get_contents($this->statusFile));
        
        try {
            $stmt = $this->conn->query("SELECT ID_Temperatura, Status FROM Temperaturas ORDER BY ID_Temperatura");
            $dbTemperatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $statusArray = str_split($status);
            $updated = false;
            
            // Garante que o array tenha tamanho suficiente
            while (count($statusArray) < count($dbTemperatures)) {
                $statusArray[] = '0';
            }
            
            foreach ($dbTemperatures as $index => $temp) {
                $dbStatus = $temp['Status'] === 'ON' ? '1' : '0';
                
                if (isset($statusArray[$index]) && $statusArray[$index] !== $dbStatus) {
                    $statusArray[$index] = $dbStatus;
                    $updated = true;
                }
            }
            
            if ($updated) {
                $newStatus = implode('', $statusArray);
                file_put_contents($this->statusFile, $newStatus);
                $status = $newStatus;
            }
            
        } catch (Exception $e) {
            error_log("Erro ao sincronizar status com o banco de dados: " . $e->getMessage());
        }
        
        $result = [
            'status' => $status,
            'porcentagem' => $this->calculatePercentage($status)
        ];
        
        return $result;
    }

    private function calculatePercentage($status) {
        $totalCount = strlen($status);
        if ($totalCount == 0) return 0;
        
        $onCount = substr_count($status, '1');
        return round(($onCount / $totalCount) * 100);
    }

    public function sendToArduino($temperatureId, $status) {
        $command = "TEMP" . $temperatureId . ":" . $status . "\n";
        
        // Log do comando enviado para o Arduino
        error_log("Comando enviado para o Arduino: " . trim($command));
        
        // Atualiza o status
        $this->updateTemperatureStatus($temperatureId, $status);
        
        return true;
    }
}
