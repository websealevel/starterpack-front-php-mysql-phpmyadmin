<?php

// A retirer, juste pour tester le lancement du pack
//CORS policy
header("Access-Control-Allow-Origin: http://front.PROJECT_NAME.test");
header("Content-Type: application/json");

$dsn = 'mysql:dbname=information_schema;host=mysql';

try {
    $pdo = new PDO($dsn, 'root', 'root');
    echo json_encode(array('La connexion à la base de données a réussi !'));
} catch (PDOException $e) {
    echo json_encode(array('La connexion a la base de données a échoué :('));
}

exit;
