<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
$f = __DIR__ . '/lb.json';
$d = file_exists($f) ? json_decode(file_get_contents($f), true) : [];
if (!is_array($d)) $d = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $n = trim(mb_substr(strip_tags($_POST['n'] ?? ''), 0, 16));
  $s = (int)($_POST['s'] ?? 0);
  if ($n !== '' && $s > 0 && $s <= 500000000) {
    $found = false;
    foreach ($d as &$r) { if ($r['n'] === $n) { if ($s > $r['s']) { $r['s'] = $s; $r['t'] = time(); } $found = true; } }
    unset($r);
    if (!$found) $d[] = ['n' => $n, 's' => $s, 't' => time()];
    usort($d, function($a, $b) { return $b['s'] <=> $a['s']; });
    $d = array_slice($d, 0, 200);
    file_put_contents($f, json_encode($d, JSON_UNESCAPED_UNICODE), LOCK_EX);
  }
}
echo json_encode(array_slice($d, 0, 10), JSON_UNESCAPED_UNICODE);
