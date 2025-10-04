<?php
require_once __DIR__ . '/../config/database.php';

class LightController {
    private $conn;
    private $statusFile;
    private $numLights = 12;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->statusFile = __DIR__ . '/../light_status.txt';
        $this->initializeStatusFile();
    }

    private function initializeStatusFile() {
        if (!file_exists($this->statusFile)) {
            $initialStatus = str_repeat('0', $this->numLights);
            file_put_contents($this->statusFile, $initialStatus);
        } else {
            $currentStatus = trim(file_get_contents($this->statusFile));
            if (strlen($currentStatus) != $this->numLights) {
                $currentStatus = str_pad($currentStatus, $this->numLights, '0', STR_PAD_RIGHT);
                file_put_contents($this->statusFile, $currentStatus);
            }
        }
        @chmod($this->statusFile, 0666);
    }

    public function updateLightStatus($lightId, $status) {
        if ($lightId < 1 || $lightId > $this->numLights) {
            throw new Exception("ID da lâmpada inválido. Deve ser entre 1 e {$this->numLights}");
        }

        $status = strtoupper($status) === 'ON' ? '1' : '0';
        $index = $lightId - 1;

        $currentStatus = str_split(trim(file_get_contents($this->statusFile)));
        if (isset($currentStatus[$index]) && $currentStatus[$index] !== $status) {
            $currentStatus[$index] = $status;
            file_put_contents($this->statusFile, implode('', $currentStatus));
            
            
            $this->updateDatabase($lightId, $status === '1' ? 'ON' : 'OFF');
            
            return true;
        }
        
        return false;
    }

    private function updateDatabase($lightId, $status) {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE Lampadas SET `Status` = :status, Data_Atualizacao = NOW() WHERE ID_Lampada = :id"
            );
            $result = $stmt->execute([
                ':status' => $status,
                ':id' => $lightId
            ]);
            
            if ($result === false) {
                $error = $stmt->errorInfo();
                throw new Exception("Erro ao executar a atualização: " . ($error[2] ?? 'Erro desconhecido'));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar banco de dados: " . $e->getMessage());
            throw new Exception("Erro ao atualizar status da lâmpada no banco de dados: " . $e->getMessage());
        }
    }

    public function getStatus() {
        $status = trim(file_get_contents($this->statusFile));
        $status = str_pad($status, $this->numLights, '0', STR_PAD_RIGHT);
        
        try {
            $stmt = $this->conn->query("SELECT ID_Lampada, Status FROM Lampadas ORDER BY ID_Lampada");
            $dbLights = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $statusArray = str_split($status);
            $updated = false;
            
            foreach ($dbLights as $light) {
                $lightId = (int)$light['ID_Lampada'];
                $dbStatus = $light['Status'] === 'ON' ? '1' : '0';
                
                $index = $lightId - 1;
                
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
        $onCount = substr_count($status, '1');
        return round(($onCount / $this->numLights) * 100);
    }

    public function sendToArduino($lightId, $status) {
        $command = "LED" . $lightId . ":" . $status . "\n";
        
        
        
        error_log("Comando enviado para o Arduino: " . trim($command));
        
        
        $this->updateLightStatus($lightId, $status);
        
        return true;
    }
}


?>
