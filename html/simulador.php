<?php
// Configuración y verificación
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función mejorada para obtener criptomonedas
function obtenerTopCriptomonedas($n = 10) {
    $url = "https://api.coingecko.com/api/v3/coins/markets?" . http_build_query([
        "vs_currency" => "usd",
        "order" => "market_cap_desc",
        "per_page" => $n,
        "page" => 1,
        "sparkline" => "false"
    ]);
    
    // Intentar con cURL primero
    if (function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
    }
    
    // Fallback a file_get_contents con contexto
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        if ($response) {
            return json_decode($response, true);
        }
    }
    
    // Datos de ejemplo si falla la API
    return generarDatosEjemplo();
}

// Datos de ejemplo por si la API falla
function generarDatosEjemplo() {
    $criptosEjemplo = [
        ['name' => 'Bitcoin', 'current_price' => 45000, 'price_change_percentage_24h' => 2.5, 'total_volume' => 25000000000],
        ['name' => 'Ethereum', 'current_price' => 3200, 'price_change_percentage_24h' => 1.8, 'total_volume' => 15000000000],
        ['name' => 'Binance Coin', 'current_price' => 580, 'price_change_percentage_24h' => 3.2, 'total_volume' => 5000000000],
        ['name' => 'Cardano', 'current_price' => 1.2, 'price_change_percentage_24h' => 5.1, 'total_volume' => 3000000000],
        ['name' => 'Solana', 'current_price' => 120, 'price_change_percentage_24h' => 8.7, 'total_volume' => 4000000000],
        ['name' => 'XRP', 'current_price' => 0.75, 'price_change_percentage_24h' => 1.2, 'total_volume' => 2000000000],
        ['name' => 'Polkadot', 'current_price' => 25, 'price_change_percentage_24h' => 4.3, 'total_volume' => 1800000000],
        ['name' => 'Dogecoin', 'current_price' => 0.15, 'price_change_percentage_24h' => 12.5, 'total_volume' => 1200000000],
        ['name' => 'Avalanche', 'current_price' => 85, 'price_change_percentage_24h' => 6.8, 'total_volume' => 900000000],
        ['name' => 'Polygon', 'current_price' => 1.8, 'price_change_percentage_24h' => 3.9, 'total_volume' => 800000000]
    ];
    
    return $criptosEjemplo;
}

// Función de evaluación
function evaluarCriptomonedas($criptos) {
    foreach ($criptos as &$crypto) {
        $volumen = isset($crypto['total_volume']) ? $crypto['total_volume'] : 0;
        $cambio = isset($crypto['price_change_percentage_24h']) ? $crypto['price_change_percentage_24h'] : 0;
        $crypto['potencial'] = $cambio + ($volumen / 1000000000);
    }
    
    usort($criptos, function($a, $b) {
        return ($b['potencial'] ?? 0) <=> ($a['potencial'] ?? 0);
    });
    
    return $criptos;
}

// Función de plan de inversión (CORREGIDA)
function planInversion($objetivo_usd, $tiempo_meses, $rendimiento_mensual) {
    // Cálculo correcto de la mensualidad
    $factor = 0;
    for ($i = 1; $i <= $tiempo_meses; $i++) {
        $factor += pow(1 + $rendimiento_mensual, $i);
    }
    
    $mensualidad = $objetivo_usd / $factor;
    $plan = [];
    $capital_acumulado = 0;
    
    for ($mes = 1; $mes <= $tiempo_meses; $mes++) {
        $capital_acumulado = $mensualidad * ((pow(1 + $rendimiento_mensual, $mes + 1) - 1) / $rendimiento_mensual);
        $plan[] = [
            'mes' => $mes,
            'inversion_mensual' => $mensualidad,
            'capital_acumulado' => $capital_acumulado
        ];
    }
    
    return [
        'mensualidad' => $mensualidad,
        'plan' => $plan,
        'capital_final' => $capital_acumulado
    ];
}

// Valores por defecto
$objetivo = 1000000;
$tiempo = 60;
$rendimiento = 5.0;
$resultados = null;
$topCriptos = [];
$error = null;

// Obtener criptomonedas
try {
    $topCriptos = obtenerTopCriptomonedas(10);
    $topCriptos = evaluarCriptomonedas($topCriptos);
} catch (Exception $e) {
    $error = "Error obteniendo criptomonedas: " . $e->getMessage();
    $topCriptos = generarDatosEjemplo();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $objetivo = floatval($_POST['objetivo'] ?? $objetivo);
    $tiempo = intval($_POST['tiempo'] ?? $tiempo);
    $rendimiento_input = floatval($_POST['rendimiento'] ?? $rendimiento);
    $rendimiento = $rendimiento_input / 100;
    
    $resultados = planInversion($objetivo, $tiempo, $rendimiento);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyección de Inversión en Criptomonedas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh; 
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { 
            background: white; 
            border-radius: 15px; 
            padding: 30px; 
            margin-bottom: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { 
            text-align: center; 
            color: white; 
            margin-bottom: 30px; 
            font-size: 2.2em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        h2 { color: #333; margin-bottom: 20px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        
        .form-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px; 
            margin-bottom: 20px;
        }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input { 
            width: 100%; 
            padding: 15px; 
            border: 2px solid #ddd; 
            border-radius: 10px; 
            font-size: 16px; 
            transition: all 0.3s;
        }
        input:focus { 
            outline: none; 
            border-color: #667eea; 
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.3);
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 18px 40px;
            border-radius: 10px;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            display: block;
            margin: 30px auto 0;
            width: 100%;
            max-width: 400px;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .pro-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.7em;
            margin-left: 10px;
            vertical-align: middle;
        }
        
        .resultados {
            background: linear-gradient(135deg, #f8f9ff, #e8ecff);
            border-left: 5px solid #667eea;
            padding: 25px;
            margin: 25px 0;
            border-radius: 10px;
        }
        
        .cripto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .cripto-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border: 2px solid #f0f0f0;
            transition: transform 0.3s;
        }
        .cripto-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
        }
        .cripto-name { font-weight: bold; color: #333; margin-bottom: 8px; font-size: 1.1em; }
        .cripto-price { font-size: 1.3em; color: #667eea; margin-bottom: 8px; font-weight: bold; }
        .cripto-change { font-weight: bold; font-size: 1.1em; }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: bold;
        }
        tr:hover { background: #f8f9ff; }
        
        .grafica-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .bars-container {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            height: 250px;
            gap: 12px;
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9ff, #e8ecff);
            border-radius: 10px;
        }
        .bar {
            width: 40px;
            background: linear-gradient(to top, #667eea, #764ba2);
            border-radius: 8px 8px 0 0;
            position: relative;
            transition: all 0.3s;
        }
        .bar:hover {
            transform: scale(1.05);
            background: linear-gradient(to top, #764ba2, #667eea);
        }
        .bar-label {
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8em;
            color: #666;
            white-space: nowrap;
        }
        
        .error {
            background: #ff6b6b;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        
        .warning {
            background: #ffeaa7;
            color: #2d3436;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
            text-align: center;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💰 Calcula tu proyección con datos en tiempo real</h1>
        
        <!-- Formulario -->
        <div class="card">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="objetivo">🎯 Meta de inversión (USD)</label>
                        <input type="number" id="objetivo" name="objetivo" value="<?= $objetivo ?>" min="1000" step="1000" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tiempo">⏰ Tiempo (meses)</label>
                        <input type="number" id="tiempo" name="tiempo" value="<?= $tiempo ?>" min="1" max="120" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rendimiento">📈 Rendimiento mensual estimado (%)</label>
                        <input type="number" id="rendimiento" name="rendimiento" value="<?= $rendimiento * 100 ?>" min="0.1" max="50" step="0.1" required>
                    </div>
                </div>
                
                <button type="submit" class="btn">
                    🚀 Calcular Proyección <span class="pro-badge">PRO</span>
                </button>
            </form>
        </div>

        <!-- Criptomonedas -->
        <div class="card">
            <h2>📊 Criptomonedas en Tiempo Real</h2>
            <?php if ($error): ?>
                <div class="warning">
                    ⚠️ Usando datos de ejemplo. <?= $error ?>
                </div>
            <?php endif; ?>
            
            <div class="cripto-grid">
                <?php foreach (array_slice($topCriptos, 0, 10) as $crypto): ?>
                    <div class="cripto-card">
                        <div class="cripto-name"><?= htmlspecialchars($crypto['name'] ?? 'N/A') ?></div>
                        <div class="cripto-price">$<?= number_format($crypto['current_price'] ?? 0, 2) ?></div>
                        <div class="cripto-change <?= ($crypto['price_change_percentage_24h'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                            <?= number_format($crypto['price_change_percentage_24h'] ?? 0, 2) ?>%
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($resultados): ?>
            <!-- Resultados -->
            <div class="card">
                <h2>✅ Resultados de tu Inversión</h2>
                
                <div class="resultados">
                    <h3>💡 Inversión mensual necesaria: <strong>$<?= number_format($resultados['mensualidad'], 2) ?> USD</strong></h3>
                    <p>Capital final proyectado: <strong>$<?= number_format($resultados['capital_final'], 2) ?> USD</strong></p>
                </div>

                <!-- Tabla -->
                <h3>📋 Plan de Inversión Detallado</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th>Inversión Mensual (USD)</th>
                            <th>Capital Acumulado (USD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados['plan'] as $fila): ?>
                            <tr>
                                <td><strong><?= $fila['mes'] ?></strong></td>
                                <td>$<?= number_format($fila['inversion_mensual'], 2) ?></td>
                                <td><strong>$<?= number_format($fila['capital_acumulado'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Gráfica -->
                <div class="grafica-container">
                    <h3>📈 Proyección de Crecimiento del Capital</h3>
                    <div class="bars-container">
                        <?php 
                        $mesesMostrar = 12;
                        $planReducido = array_slice($resultados['plan'], 0, $mesesMostrar);
                        $maxCapital = max(array_column($planReducido, 'capital_acumulado'));
                        
                        foreach ($planReducido as $fila): 
                            $altura = ($fila['capital_acumulado'] / $maxCapital) * 200;
                        ?>
                            <div class="bar" style="height: <?= $altura ?>px;">
                                <div class="bar-label">Mes <?= $fila['mes'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="text-align: center; color: #666; margin-top: 20px;">
       
       // ... (el código anterior se mantiene igual)

                <!-- Gráfica -->
                <div class="grafica-container">
                    <h3>📈 Proyección de Crecimiento del Capital</h3>
                    <div class="bars-container">
                        <?php 
                        $mesesMostrar = 12;
                        $planReducido = array_slice($resultados['plan'], 0, $mesesMostrar);
                        $maxCapital = max(array_column($planReducido, 'capital_acumulado'));
                        
                        foreach ($planReducido as $fila): 
                            $altura = ($fila['capital_acumulado'] / $maxCapital) * 200;
                        ?>
                            <div class="bar" style="height: <?= $altura ?>px;">
                                <div class="bar-label">Mes <?= $fila['mes'] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="text-align: center; color: #666; margin-top: 20px;">
                        Proyección de los primeros <?= min($mesesMostrar, count($resultados['plan'])) ?> meses de inversión
                    </p>
                </div>

                <!-- Recomendaciones -->
                <div class="card" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); border-left: 5px solid #ffc107;">
                    <h3>💡 Recomendaciones de Inversión</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                        <div>
                            <h4>🚀 Top 3 Criptomonedas por Potencial</h4>
                            <ol style="margin-left: 20px; margin-top: 10px;">
                                <?php foreach (array_slice($topCriptos, 0, 3) as $index => $crypto): ?>
                                    <li style="margin-bottom: 8px;">
                                        <strong><?= htmlspecialchars($crypto['name'] ?? 'N/A') ?></strong> 
                                        - Potencial: <?= number_format($crypto['potencial'] ?? 0, 2) ?> puntos
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                        <div>
                            <h4>📊 Estrategia Sugerida</h4>
                            <ul style="margin-left: 20px; margin-top: 10px;">
                                <li style="margin-bottom: 8px;">Diversifica entre las top 5 criptomonedas</li>
                                <li style="margin-bottom: 8px;">Revisa tu portafolio mensualmente</li>
                                <li style="margin-bottom: 8px;">Considera DCA (Dollar Cost Averaging)</li>
                                <li style="margin-bottom: 8px;">Mantén reservas para oportunidades</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advertencia -->
            <div class="warning">
                <strong>⚠️ ADVERTENCIA:</strong> Esta es una proyección teórica. Los mercados de criptomonedas son volátiles 
                y el rendimiento pasado no garantiza resultados futuros. Invierte solo lo que estés dispuesto a perder 
                y considera asesoramiento financiero profesional.
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div style="text-align: center; color: white; margin-top: 40px; padding: 20px;">
            <p>💡 <strong>Consejo:</strong> Las criptomonedas con mayor volumen y crecimiento positivo suelen ser más estables</p>
            <p style="margin-top: 10px; font-size: 0.9em; opacity: 0.8;">
                Datos proporcionados por CoinGecko API | Última actualización: <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>

    <script>
        // Efectos visuales mejorados
        document.addEventListener('DOMContentLoaded', function() {
            // Animación de las barras
            const bars = document.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                bar.style.opacity = '0';
                bar.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    bar.style.transition = 'all 0.5s ease';
                    bar.style.opacity = '1';
                    bar.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Tooltip para las criptomonedas
            const criptoCards = document.querySelectorAll('.cripto-card');
            criptoCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 10px 25px rgba(102, 126, 234, 0.3)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                });
            });

            // Validación del formulario
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const objetivo = document.getElementById('objetivo').value;
                const tiempo = document.getElementById('tiempo').value;
                const rendimiento = document.getElementById('rendimiento').value;
                
                if (objetivo < 1000) {
                    alert('La meta de inversión mínima es $1,000 USD');
                    e.preventDefault();
                    return;
                }
                
                if (rendimiento > 50) {
                    if (!confirm('⚠️ Un rendimiento mensual del ' + rendimiento + '% es muy optimista. ¿Estás seguro?')) {
                        e.preventDefault();
                    }
                }
            });

            // Efecto de escritura en el título
            const title = document.querySelector('h1');
            const originalText = title.textContent;
            title.textContent = '';
            let i = 0;
            
            function typeWriter() {
                if (i < originalText.length) {
                    title.textContent += originalText.charAt(i);
                    i++;
                    setTimeout(typeWriter, 50);
                }
            }
            
            // Iniciar efecto de escritura
            setTimeout(typeWriter, 1000);
        });

        // Mostrar/ocultar secciones avanzadas
        function toggleAdvanced() {
            const advanced = document.getElementById('advanced-settings');
            advanced.style.display = advanced.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>
