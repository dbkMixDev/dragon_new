<?php
require_once 'config.php'; // Ambil key dari config

// Encode base64 menjadi URL-safe
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Decode base64 dari URL-safe
function base64url_decode($data) {
    $padding = strlen($data) % 4;
    if ($padding > 0) {
        $data .= str_repeat('=', 4 - $padding);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function encrypt($data) {
    $key = ENCRYPTION_KEY;
    $iv = openssl_random_pseudo_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64url_encode($iv . $encrypted); // Tidak perlu urlencode
}

function decrypt($data) {
    $key = ENCRYPTION_KEY;
    $decoded = base64url_decode($data); // Tidak perlu urldecode
    if (!$decoded || strlen($decoded) <= 16) return false;
    $iv = substr($decoded, 0, 16);
    $encrypted = substr($decoded, 16);
    return openssl_decrypt($encrypted, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
}
?>
