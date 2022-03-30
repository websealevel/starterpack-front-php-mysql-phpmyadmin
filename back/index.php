<?php

// A retirer, juste pour tester le lancement du pack

// Test connexion à la base de données
$mysqli = new mysqli("db", "root", "root", "information_schema");
$db_response = $mysqli->ping();
$mysqli->close();


//Retourne une réponse au front
$data = array("Hello World ! " . match ($db_response) {
    false => 'La connexion à la base de données a échoué...',
    true => 'La connexion à la base de données a réussi !'
});

header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
echo json_encode($data);
exit;