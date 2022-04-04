#!/bin/bash

# SCRIPT D'INITITIALISATION DU PROJET

# Permet d'initialiser le nom du projet
# Cree la base de données et lance le projet

if [ $# -eq 0 ]; then
  echo "Donnez un nom au projet"
  exit 1
fi

echo "Nom du projet: $1"

# Création du fichier .env à partir de .env.dist

FILE_ENV_DIST=.env.dist
FILE_ENV=.env

if [ ! -f "$FILE_ENV_DIST" ]; then
  echo "Le fichier $FILE_ENV_DIST n'existe pas"
  exit 1
fi

echo "Création du fichier .env"
cp "$FILE_ENV_DIST" "$FILE_ENV"

# Ecriture du nom du projet pour Docker

DOCKER_VARIABLES=(
  "PROJECT_NAME"
)

echo "Configuration du fichier .env"

for variable in ${DOCKER_VARIABLES[@]}; do
  sed -i "s/$variable=[a-zA-Z]*/$variable=$1/" $FILE_ENV
done

echo "Configuration du front"
sed -i "s/PROJECT_NAME/$1/" front/index.html

echo "Configuration du back"
sed -i "s/PROJECT_NAME/$1/" back/index.php

# Lancement des conteneurs
echo "Lancement du projet"
docker-compose up --build -d

echo "Projet $1 configuré, have fun :)"
