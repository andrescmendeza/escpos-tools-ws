<?php
header('Content-Type: text/html; charset=utf-8');

if (!isset($_POST['esc'])) {
    http_response_code(400);
    echo base64_encode("Missing 'esc' parameter.");
    exit;
}

$rawData = base64_decode($_POST['esc']);
if ($rawData === false) {
    http_response_code(400);
    echo base64_encode("Invalid base64 data.");
    exit;
}

$width = isset($_POST['width']) ? intval($_POST['width']) : null;

$tmpFile = tempnam(sys_get_temp_dir(), 'esc_');
file_put_contents($tmpFile, $rawData);

$phpBinary = PHP_BINARY;
$cmd = escapeshellcmd($phpBinary) . ' ' . escapeshellarg(__DIR__ . '/esc2html.php') . ' ' . escapeshellarg($tmpFile);

$output = shell_exec($cmd);

unlink($tmpFile);

if ($output !== null) {
    echo base64_encode($output);
} else {
    http_response_code(500);
    echo base64_encode("Error processing ESC/POS data.");
}
