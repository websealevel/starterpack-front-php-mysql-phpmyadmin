# Starter pack stack client/php/mysql

Un projet starter pour expérimenter et développer avec PHP et Mysql. Le tout dockerisé et derrière un proxy pour avoir des jolis noms de domaine et assurer un worklow plus sympas pour des projets en parallèle.

## Services

Le projet contient 4 serveurs isolés dans des services avec Docker

- serveur front statique. Il sert le dossier `front`. On y mettra donc notre code html
- serveur web php. Il sert le dossier `back`. On y mettra donc notre code php pour le backend
- serveur de la base de données mysql
- serveur lançant phpmyadmin pour donner une interface à l'accès à la base

## Lancer le projet

Consulter [l'article ici](./doc/article1-starterpack.md) pour plus de détails.

## Avec Traeffik

Utiliser les labels (commentés) dans le fichier `docker-compose.yml`

## Sans Traefik

Utiliser les ports dans le fichier `docker-compose.yml`

## Servir le projet

Lancer le projet à la racine avec

`docker-compose up -d`

## Liens utiles :

[https://blog.silarhi.fr/image-docker-php-apache-parfaite/]
