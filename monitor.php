<?php
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-tachometer-alt me-2"></i>Monitoramento de Lâmpadas</h4>
                    <div>
                        <span class="badge bg-primary" id="last-updated">Atualizando...</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Total de Lâmpadas</h6>
                                    <h2 class="display-4" id="total-lights">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Lâmpadas Acesas</h6>
                                    <h2 class="display-4" id="lights-on">0</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-subtitle mb-2 text-muted">Porcentagem Acesa</h6>
                                    <h2 class="display-4" id="percentage-on">0%</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Status</th>
                                    <th>Última Atualização</th>
                                    <th>Tempo Ligada</th>
                                </tr>
                            </thead>
                            <tbody id="lights-table-body">
                                <tr>
                                    <td colspan="5" class="text-center">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateMonitorData() {
        fetch('includes/monitor_lights.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('total-lights').textContent = data.total_lights;
                    document.getElementById('lights-on').textContent = data.lights_on;
                    document.getElementById('percentage-on').textContent = data.percentage_on + '%';
                    document.getElementById('last-updated').textContent = 'Atualizado em: ' + new Date().toLocaleTimeString();
                    
                    const tbody = document.getElementById('lights-table-body');
                    tbody.innerHTML = '';
                    
                    data.lights.forEach(light => {
                        const row = document.createElement('tr');
                        const statusClass = light.Status.toLowerCase() === 'on' ? 'text-success' : 'text-danger';
                        const statusText = light.Status.toLowerCase() === 'on' ? 'Ligada' : 'Desligada';
                        
                        row.innerHTML = `
                            <td>${light.ID_Lampada}</td>
                            <td>${light.Nome}</td>
                            <td><span class="badge ${statusClass}">${statusText}</span></td>
                            <td>${light.last_status_change}</td>
                            <td>${light.uptime_formatted}</td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar monitoramento:', error);
                document.getElementById('last-updated').textContent = 'Erro ao atualizar';
            });
    }
    
    updateMonitorData();
    setInterval(updateMonitorData, 5000);
});
</script>

<?php
require_once 'includes/footer.php';
?>
