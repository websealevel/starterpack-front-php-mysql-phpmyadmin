<?php

// A retirer, juste pour tester le lancement du pack


//Configuration accès à la base de donnée
$db = "mydb";
$dbhost = "db";
$dbport = 3306;
$dbuser = "user";
$dbpasswd = "password";


//CORS policy
header("Access-Control-Allow-Origin: http://front.PROJECT_NAME.test");
header("Content-Type: application/json");

//Ping la base de données
try {
    $pdo = new PDO('mysql:host=' . $dbhost . ';port=' . $dbport . ';dbname=' . $db . '', $dbuser, $dbpasswd);
    echo json_encode(array('La connexion à la base de données a réussi !'));
} catch (PDOException $e) {
    echo json_encode(array('La connexion a la base de données a échoué :('));
}

exit;
