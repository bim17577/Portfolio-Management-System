<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_GET['data'])) {
    header('Content-Type: text/plain');
    echo 'No data provided';
    exit;
}

$data = $_GET['data'];
$hash = md5($data); // unique name based on portfolio URL
$cacheFile = __DIR__ . "/../../cache/qr_$hash.png";

// ✅ If already generated, just serve cached file
if (file_exists($cacheFile)) {
    header('Content-Type: image/png');
    readfile($cacheFile);
    exit;
}

// ❌ If not cached, generate once
$result = Builder::create()
    ->writer(new PngWriter())
    ->data($data)
    ->size(300)
    ->build();

file_put_contents($cacheFile, $result->getString());

header('Content-Type: image/png');
echo $result->getString();
exit;
