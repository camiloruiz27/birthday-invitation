<?php
$cases = ['la-final-sin-tablero','la-caja-de-donaciones','la-boya-naranja','modo-demostracion','el-bingo-del-jueves','bajo-la-cabeza-del-dragon'];
foreach ($cases as $slug) {
    $c = require __DIR__ . '/app/Modules/Immersion/Cases/' . $slug . '/case.php';
    echo "========================================\n";
    echo "CASE: {$slug}\n";
    echo "Title: " . ($c['name'] ?? '') . "\n";
    echo "Duration: " . ($c['catalog']['duration_minutes'] ?? '') . " min\n";
    echo "Cover: " . ($c['catalog']['cover'] ?? '') . "\n";
    echo "Victim: " . ($c['victim']['name'] ?? '') . " | photo: " . ($c['victim']['photo'] ?? '') . "\n";
    echo "Suspects:\n";
    foreach ($c['suspects'] ?? [] as $k => $s) {
        echo "  - [{$k}] {$s['name']} ({$s['role']}) -> {$s['photo']}\n";
    }
    echo "Gallery:\n";
    foreach ($c['gallery'] ?? [] as $k => $g) {
        echo "  - [{$k}] {$g['title']} -> {$g['file']}\n";
    }
    echo "Timeline:\n";
    foreach ($c['timeline'] ?? [] as $t) {
        $ev = implode(', ', $t['evidence'] ?? []);
        $aud = $t['audio'] ?? 'none';
        echo "  - Min {$t['minute']} [{$t['type']}]: {$t['title']} | Ev: {$ev} | Aud: {$aud}\n";
    }
    echo "Confession script: " . (isset($c['solution']['confession_script']) ? 'YES' : 'NO') . "\n";
    if (isset($c['solution']['confession_script'])) {
        echo "Confession excerpt: " . substr($c['solution']['confession_script'], 0, 100) . "...\n";
    }
}

