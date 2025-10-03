<?php
// TEST BÁSICO PHP Y API
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>🔧 DEBUG TEST - TOKENLAB</h3>";
echo "✅ PHP está funcionando correctamente<br>";
echo "✅ Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido') . "<br>";
echo "✅ PHP Version: " . phpversion() . "<br>";

// Probar la API de CoinGecko
echo "<h4>📡 Probando API CoinGecko...</h4>";

$url = "https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&per_page=3";
$context = stream_context_create([
    'http' => ['timeout' => 10],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
]);

$response = @file_get_contents($url, false, $context);

if ($response) {
    $data = json_decode($response, true);
    echo "✅ API CoinGecko FUNCIONA<br>";
    echo "✅ Criptos obtenidas: " . count($data) . "<br>";
    foreach ($data as $i => $coin) {
        echo "&nbsp;&nbsp;• " . $coin['name'] . " ($" . $coin['current_price'] . ")<br>";
    }
} else {
    echo "❌ ERROR: No se pudo conectar a la API CoinGecko<br>";
    echo "❌ Posible problema: allow_url_fopen deshabilitado<br>";
}

// Probar POST
echo "<h4>📝 Probando formulario POST...</h4>";
if ($_POST) {
    echo "✅ POST funciona. Datos: " . print_r($_POST, true) . "<br>";
} else {
    echo "ℹ️ No hay datos POST<br>";
}
?>
