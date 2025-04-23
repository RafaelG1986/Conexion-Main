<link rel="stylesheet" href="../css/styles_estadisticas.css">

<div class="stats-container">
    <div class="stats-header">
        <h2><i class="fas fa-chart-bar"></i> Estadísticas Generales</h2>
        <div class="date-range"><i class="fas fa-calendar-alt"></i> Últimos 30 días</div>
    </div>
    
    <div class="stats-cards">
        <div class="stat-card primary">
            <div class="stat-title"><i class="fas fa-users"></i> Total de Registros</div>
            <div class="stat-value">247</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 12% desde el mes pasado
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-title"><i class="fas fa-user-check"></i> Conectados</div>
            <div class="stat-value">156</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 8% desde el mes pasado
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-title"><i class="fas fa-utensils"></i> Confirmados a Desayuno</div>
            <div class="stat-value">89</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i> 5% desde el mes pasado
            </div>
        </div>
        
        <div class="stat-card danger">
            <div class="stat-title"><i class="fas fa-user-times"></i> No interesados</div>
            <div class="stat-value">12</div>
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i> 3% desde el mes pasado
            </div>
        </div>
    </div>
    
    <div class="chart-container">
        <div class="chart-header">
            <div class="chart-title">Registros por Estado</div>
            <div class="chart-actions">
                <button class="chart-action-btn active">Semanal</button>
                <button class="chart-action-btn">Mensual</button>
                <button class="chart-action-btn">Anual</button>
            </div>
        </div>
        <!-- Aquí iría el gráfico usando Chart.js o similar -->
        <canvas id="estadosChart" width="400" height="200"></canvas>
    </div>
    
    <div class="chart-container">
        <div class="chart-header">
            <div class="chart-title">Tendencia de Registros</div>
            <div class="chart-actions">
                <button class="chart-action-btn active">Línea</button>
                <button class="chart-action-btn">Barras</button>
            </div>
        </div>
        <!-- Aquí iría otro gráfico -->
        <canvas id="tendenciaChart" width="400" height="200"></canvas>
    </div>
    
    <div class="table-responsive">
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Porcentaje</th>
                    <th>Cambio</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Primer contacto</td>
                    <td>45</td>
                    <td>18.2%</td>
                    <td class="positive">+5.2%</td>
                </tr>
                <tr>
                    <td>Conectado</td>
                    <td>156</td>
                    <td>63.2%</td>
                    <td class="positive">+8.1%</td>
                </tr>
                <tr>
                    <td>Confirmado a Desayuno</td>
                    <td>89</td>
                    <td>36.0%</td>
                    <td class="positive">+4.7%</td>
                </tr>
                <tr>
                    <td>Desayuno Asistido</td>
                    <td>62</td>
                    <td>25.1%</td>
                    <td class="positive">+3.2%</td>
                </tr>
                <tr>
                    <td>No interesado</td>
                    <td>12</td>
                    <td>4.9%</td>
                    <td class="negative">-3.1%</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>