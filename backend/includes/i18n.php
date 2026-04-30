<?php
// includes/i18n.php
// Simple JSON-based i18n loader. Use ?lang=pt|en|es|it
$lang = $_GET['lang'] ?? ($_COOKIE['lang'] ?? 'pt');
$supported = ['pt','en','es','it'];
if (!in_array($lang, $supported)) { $lang = 'pt'; }

// Persist language in a cookie (30 days)
setcookie('lang', $lang, [
    'expires' => time() + 60*60*24*30,
    'path' => '/',
    'secure' => false,
    'httponly' => false,
    'samesite' => 'Lax'
]);

$file = __DIR__ . "/../lang/{$lang}.json";
if (!file_exists($file)) {
    $file = __DIR__ . "/../lang/pt.json";
}
$strings = json_decode(file_get_contents($file), true);

/**
 * Get a nested string using dot notation, e.g., t('menu.features')
 */
function t($path, $default = '') {
    global $strings;
    $parts = explode('.', $path);
    $ref = $strings;
    foreach ($parts as $p) {
        if (is_array($ref) && array_key_exists($p, $ref)) {
            $ref = $ref[$p];
        } else {
            return $default ?: $path;
        }
    }
    return is_string($ref) ? $ref : (is_array($ref) ? $ref : $default);
}

/**
 * Build a URL preserving current lang (adds ?lang=xx if not present)
 */
function with_lang($url) {
    global $lang;
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'lang=' . urlencode($lang);
}
