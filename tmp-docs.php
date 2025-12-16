<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
foreach ($db->query('select idDocument,idTache,nom,chemin from document order by idDocument desc limit 5') as $r) {
    echo json_encode($r, JSON_UNESCAPED_SLASHES), PHP_EOL;
}
