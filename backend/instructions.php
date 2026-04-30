<?php
$lang = $_GET['lang'] ?? 'pt';
$file = __DIR__ . "/lang/$lang.json";
if (!file_exists($file)) $file = __DIR__ . "/lang/pt.json";
$strings = json_decode(file_get_contents($file), true);
function t($path) { global $strings; $parts = explode('.', $path); $ref=$strings; foreach($parts as $p){ if(isset($ref[$p])) $ref=$ref[$p]; else return $path; } return $ref; }
?><!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head><meta charset="UTF-8"><title><?php echo t('menu.instructions'); ?></title></head>
<body>
<nav>
  <a href="index.php?lang=<?php echo $lang; ?>"><?php echo t('menu.home'); ?></a>
</nav>
<h1><?php echo t('menu.instructions'); ?></h1>
<p>Conteúdo da página instructions...</p>
</body></html>
