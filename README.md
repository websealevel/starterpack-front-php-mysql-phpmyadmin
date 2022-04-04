# Fabriquer et utiliser son starterpack

- [Fabriquer et utiliser son starterpack](#fabriquer-et-utiliser-son-starterpack)
  - [Pas le temps ou l'envie, je suis pressé de l'utiliser](#pas-le-temps-ou-lenvie-je-suis-pressé-de-lutiliser)
  - [Docker au lieu de LAMP, être machine indépendant](#docker-au-lieu-de-lamp-être-machine-indépendant)
  - [Docker-compose](#docker-compose)
  - [Nos services Docker ou _conteneurs_](#nos-services-docker-ou-conteneurs)
    - [Services : de quoi a-t-on besoin ?](#services--de-quoi-a-t-on-besoin-)
      - [Un backend](#un-backend)
      - [Une base de données MySQL](#une-base-de-données-mysql)
      - [Adminer](#adminer)
      - [Un serveur front](#un-serveur-front)
    - [Communication _entre_ nos services](#communication-entre-nos-services)
    - [Communication _avec_ nos services](#communication-avec-nos-services)
  - [Le starterpack en action](#le-starterpack-en-action)
    - [Lançons le projet](#lançons-le-projet)
    - [Testons le projet](#testons-le-projet)
    - [Arrêtons le projet](#arrêtons-le-projet)
  - [Aller un peu plus loin, acceder à nos conteneurs via un nom de domaine](#aller-un-peu-plus-loin-acceder-à-nos-conteneurs-via-un-nom-de-domaine)
    - [Issues](#issues)
    - [Apparté: automatiser vos tâches répétitives et fastidieuses](#apparté-automatiser-vos-tâches-répétitives-et-fastidieuses)
    - [Résoudre tous ces problèmes: dns local et reverse-proxy](#résoudre-tous-ces-problèmes-dns-local-et-reverse-proxy)
      - [Mise en place d'un dns local avec `dnsmasq`](#mise-en-place-dun-dns-local-avec-dnsmasq)
      - [Nouveau dépôt](#nouveau-dépôt)
      - [Configuration de Traefik](#configuration-de-traefik)
        - [Lancement du conteneur Traefik](#lancement-du-conteneur-traefik)
        - [Intercepter uniquement les reqûetes vers nos conteneurs Docker](#intercepter-uniquement-les-reqûetes-vers-nos-conteneurs-docker)
        - [Application à notre starterpack](#application-à-notre-starterpack)
        - [Ajout du nom de domaine dans le `.env`](#ajout-du-nom-de-domaine-dans-le-env)
      - [En résumé](#en-résumé)
        - [Labeliser vos conteneurs Docker](#labeliser-vos-conteneurs-docker)
  - [Mode pragmatique : utiliser le projet directement](#mode-pragmatique--utiliser-le-projet-directement)
    - [Description courte](#description-courte)
    - [Prérequis](#prérequis)
    - [Instructions](#instructions)
    - [Démonter le projet](#démonter-le-projet)
    - [Gestion multiprojets](#gestion-multiprojets)
  - [Aller plus loin](#aller-plus-loin)
  - [Références](#références)
    - [Images officielles et leur documentation](#images-officielles-et-leur-documentation)
    - [Autres](#autres)

Un starterpack c'est un projet à l'état initial où les cables sont tirés. C'est pratique car on peut le dupliquer pour commencer rapidement un nouveau projet. On galère une fois à mettre l'environnement en place et puis après on est tranquille. On peut le faire évoluer ensuite. Pour cela je recommande de faire un dépot qui contient ce starterpack. A chaque fois qu'on relance un projet on le duplique et on fait un dépôt pour ce projet. Et voilà un petit workflow sympathique. On documente bien aussi le starterpack, comme ça si on revient dans 1 mois on peut se souvenir de ce qu'on a fait et pourquoi on a fait les choses comme ça. Soyons sympas envers nous même, et les autres.

## Pas le temps ou l'envie, je suis pressé de l'utiliser

Si vous voulez directement utiliser ce starterpack sans vous soucier des détails de son fonctionnement, pas de problèmes. Peut être que ça vous intéresse pas vraiment ou que vous le ferez plus tard. Rendez-vous dans ce cas directement à la [section suivante](#mode-pragmatique--utiliser-le-projet-directement).

## Docker au lieu de LAMP, être machine indépendant

LAMP c'est bien mais c'est machine-dépendant, c'est galère. On doit installer et configurer des choses directement sur notre machine locale. J'aime pas trop bidouiller ma machine locale pour faire marcher un projet. Qui dit que je ne devrais pas la rebidouiller pour un autre et que ces changements ne casseront pas la config du projet précédent ? Pour ces raisons, on va utiliser [Docker](https://docs.docker.com/). Rien ne sera installé sur notre machine (seulement une petite config qui passera inaperçue): on reste clean et en plus notre projet _est garanti_ (normalement) de marcher sur toute machine capable de faire tourner Docker.

## Docker-compose

Comme on aura plusieurs conteneurs à gérer on va se servir de [docker-compose ou Compose](https://docs.docker.com/compose/), ce qui va nous faciliter la tâche. On va définir tous nos conteneurs dans un seul fichier de configuration. Une fois qu'on aura cablé tout ça ce sera opérationnel, et on aura notre starterpack.

On crée un fichier `.env` et un fichier `docker-compose.yml` (car c'est en yaml). Le `.env` est automatiquement détecté par `docker-compose` et permet de stocker des variables d'environnement pour Docker, qu'on pourra ensuite utiliser directement dans le `docker-compose.yml` sous la forme `${ma_variable_d_environnement}`.

## Nos services Docker ou _conteneurs_

Définissons nos _services_, c'est à dire nos conteneurs Docker ainsi que les relations entre eux.

### Services : de quoi a-t-on besoin ?

#### Un backend

Tout d'abord on a besoin d'un serveur backend avec PHP d'installé dessus. C'est le but du service `back`. J'ai choisi une image apache avec PHP 8. Sur Apache, le programme serveur sert par défaut les sources présentes dans le dossier `/var/www/html`. Nous on veut servir le contenu du dossier `back` à la racine du projet. Donc on utilise les `volumes` de Docker pour rewrite le path et le faire correspondre à notre dossier avec

    volumes:
      - ./back/:/var/www/html/:rw

Sous Linux, par défaut le contenu dockerisé (ici notre dossier `back`) appartient en écriture à l'utilisateur `root`. On veut éviter cela. Et pour cela il y a une ligne de configuration importante c'est

    user: "${UID}:${GID}"

`UID` et `GID` sont des variables d'environnement définies dans le `.env`. Par défaut notre utilisateur est `1000` et son groupe est `1000`. Pour vous en assurer taper la commande `id` dans votre terminal. De cette manière on donne les droits d'écriture à notre utilisateur sur le volume monté par Docker et on aura plus de soucis pour éditer les sources servies.

La ligne `restart:always` dit qu'on veut redémarrer le conteneur automatiquement s'il est stoppé de manière non explicite (intentionnelle).

Enfin, notre backend va communiquer avec notre service hebergeant notre base de données. On veut donc que la base soit montée et configurée **avant** notre service backend. Pour cela on utilise la variable `depends_on: -db` de Compose. Comme ça on est sûrs que le service `db` sera monté avant le service `back`.

Rien de plus à dire pour le moment, passons à la base de données.

#### Une base de données MySQL

Le service `db` est un conteneur MySQL. On renseigne ici les valeurs des variables d'environnement [mises à disposition par l'image officielle](https://hub.docker.com/_/mysql). On doit également dire au conteneur où stocker sur notre machine hôte le système de fichiers du SGBD. On le fait avec la ligne


~~~yaml
    volumes: - ./mysql-data:/var/lib/mysql
~~~

#### Adminer

Un monte un service `adminer` pour se faciliter la vie lorsque l'on voudra travailler sur la base de données. Pas envie de faire ça via la CLI, du moins pas pour le moment. Rien de spécial ici.

Ce service dépend du service `db` donc on l'explicite également.

#### Un serveur front

On peut se rajouter un service `front` qui simulera un client sur un autre domaine que notre backend. Ce service servira du HTML de manière statique. On se sert de [l'image officielle httpd](https://hub.docker.com/_/httpd), on sert le contenu du dossier `front` en faisant correspondre le dossier `front` au [`DocumentRoot`](https://httpd.apache.org/docs/2.4/fr/urlmapping.html) d'Apache

~~~yaml
    volumes: - ./front/:/usr/local/apache2/htdocs/:rw
~~~

Le `DocumentRoot` d'Apache définit par défaut quel fichier sera servi par une reqûete. Après tout, **le web consiste à accéder à des fichiers sur des machines connectées au réseau**. Prenons l'exemple d'une reqûete qui arrive sur votre serveur faite depuis un navigateur de la forme

    http://www.exemple.com/animal/tardigrade.html

Apache extrait le chemin de la requête en écartant le protocole, le nom de domaine et le port, et le concatène à la valeur de `DocumentRoot`.
Si la directive `DocumentRoot` vaut `/usr/local/apache2/htdocs`, alors le fichier retourné au client par le serveur sera `/usr/local/apache2/htdocs/animal/tardigrade.html` (s'il existe).

Sur cette image il vaut par défaut `/usr/local/apache2/htdocs` et non `/var/www/html` comme précédemment.

### Communication _entre_ nos services

Bien, maintenant que nos services sont individuellement prêts et tous sur le même réseau il ne reste qu'à s'assurer qu'ils pussent communiquer entre eux. Listons les relations entre services ici :

- `back` doit acceder à `db`
- `adminer` doit acceder à `db`
- `front` doit acceder à `back`

On va mettre tous nos conteneurs sur le réseau `web` qui a été crée pour que le conteneur Traefik puisse communiquer avec tous nos conteneurs. Nous verrons cela à la section [suivante](#résoudre-tous-ces-problèmes-dns-local-et-reverse-proxy).


~~~yaml
networks:
  project_php:
  web:
    external: true
~~~

On indique que le réseau `web` est `external` ce qui veut dire qu'il existe déjà. On ne veut pas en recréer un autre. On ajoute égalempent un réseau propre au projet `project_php`. Docker va aitomatiquement créer un réseau de la forme `${COMPOSE_PROJECT_NAME}_project_php`, où `${COMPOSE_PROJECT_NAME}` est le nom du dossier dans lequel se trouve votre projet par défaut. On a donc un réseau unique pour chaque projet.

On veut à présent exposer tous les conteneurs sur le réseau `web` que l'on peut se représenter comme le réseau public, accessible au monde exterieur, sauf la base de données. Aucun raison qu'on puisse y accèder depuis le monde exterieur. Non recommandé. C'est le service `back` qui va communiquer avec la base de données. Donc on met tout le monde sous `web` et `project_php`, sauf `db` que l'on met que sous `project_php`. Cela donne


~~~yaml
services:
  #Le serveur front (html static)
  front:
    ...
    networks:
      - project_php
      - web

  #Le serveur php
  back:
    ...
    networks:
      - project_php
      - web

  #Le serveur de la base de données mysql
  db:
    ...
    networks:
    - project_php

  #Le serveur de adminer
  adminer:
    ...
    networks:
      - web
      - project_php

...

networks:
  project_php:
  web:
    external: true
~~~


Sous la clef `networks` on dit à nos conteneurs de [rejoindre un réseau web pré existant](https://docs.docker.com/compose/networking/#use-a-pre-existing-network) et à Docker qu'on déclare un réseau par défaut `project_php`.

Comme on l'a vu précédemment, tous les conteneurs appartenant au même réseau peuvent communiquer via leur nom d'hôte ou nom de conteneur. Donc normalement on est bon. 

### Communication _avec_ nos services

Pour communiquer, nous, depuis notre machine, avec nos conteneurs Docker il faut leur prêter un port de notre machine hôte (l'ordinateur sur lequel vous travaillez). C'est ce que l'on fait avec la directive

    ports:
      - "9000:80"

Un port c'est une entité logique (et non matérielle) qui agit comme un identifiant pour chaque processus sur notre machine. Cet identifiant permet également de communiquer avec ce processus depuis un autre processus. Les ports vont de `0` à `65535` et si je comprends bien ceux entre 1024 et 49151 sont disponibles et non utilisés par des processus importants.

Ici, on associe le port `9000` de notre machine au port `80` de notre conteneur, le port par défaut pour le protocole HTTP. Pourquoi ai-je choisi le port `9000` ? Aucune idée, il fait juste partie des ports disponibles.

Les services `back`, `adminer` et `front` sont tous des serveurs HTTP qui communiquent via le port `80`, donc pour chacun d'entre eux je map un port de ma machine hôte à leur port 80. Et pour le service `db` ? Par défaut, MySQL utilise le port `3306`.

## Le starterpack en action

### Lançons le projet

Maintenant que tout est bien configuré, lançons le projet avec un


~~~bash
    docker-compose up -d
~~~
### Testons le projet

Si c'est la première fois que vous lancez la commande Docker va construire les conteneurs à partir des images Docker, et ensuite il va les instancier. Vérifions deux ou trois petites choses.

Tapez la commande

~~~bash
    docker ps -a
~~~

Elle vous listera tous les conteneurs en activité à la racine du projet, avec différentes informations.

Ouvrez 3 onglets dans votre navigateur favori et demandez `localhost:9000` (front), `localhost:9001` (back), `localhost:90002`(adminer). Vous devriez visitez le front, le back et arriver sur adminer. Logez vous avec l'utilisateur `root` (mot de passe `root`).

### Arrêtons le projet

Pour stoper tous les conteneurs du projet faites un

    docker-compose down

Enfin si les choses vous ont échappées, pas de panique, vous pouvez arrêter **tous** les conteneurs avec la commande

    docker rm -f $(docker ps -a -q)

Félicitations ! Notre starterpack commence à ressembler à quelque-chose. On va pouvoir s'en servir de base pour nos projets.

## Aller un peu plus loin, acceder à nos conteneurs via un nom de domaine

### Issues

So far, so good. Vous pouvez vous arrêtez là si vous le souhaitez (je le recommande pas car la suite vaut le détour) mais on peut aller un peu plus loin pour améliorer notre starterpack.

Vous avez remarqué qu'accéder à nos conteneurs via un obscure nom de domaine comme `localhost:9000` c'est pas fou ? Déjà on ne sait plus ce qui se cache derrière comme service. Et pire, si demain on monte un autre projet avec notre starterpack il faudra

- soit choisir de nouveaux ports car _un port sur notre machine ne peut être mapé qu'à un seul port à la fois_. Fastidieux, `error prone`, galère
- soit down les autres projets pour libérer les ports. Fastidieux, `error prone`, galère

Pas terrible, on a connu mieux comme workflow. Je regrette presque LAMP...

### Apparté: automatiser vos tâches répétitives et fastidieuses

Une chose à garder en tête et valable peu importe les situations: **si vous répétez plusieurs fois la même tâche manuellement, essayez de trouver un moyen de l'automatiser ou de dépenser le moins d'énergie possible pour la réaliser**.

Si c'est une tâche que vous faites deux fois par an peut-être que ça vaut pas le coup, mais peut-être que non. La limite de cette habitude est une question presque philosophique. Cela dit, par experience, je sais que lorsque je me retrouve à faire plusieurs fois la même chose à la main, même deux fois par mois, ça me fatigue car ça crée de la charge mentale inutile. Et plus on persiste à le faire quand même à la main en sachant qu'on devrait pas le faire, moins on se respecte. C'est presque une question d'hygiène mentale.

Un conseil que je peux donner c'est que si vous vous retrouvez à faire des choses manuellement souvent et que ça necessite de taper un peu trop de texte, de manipuler des fichiers, de cliquer plusieurs fois à différents endroits de votre écran, notez cette tâche quelque part. Sur un cahier, un fichier texte peu importe. Faites vous une liste. Et de temps en temps, essayez d'automatiser certaines de ces tâches, ou apprenez les raccourcis pour les rendre moins fastidieuses. On a pas toujours le temps de se pencher là-dessus. C'est pourquoi de les noter et de le faire quand on a un moment je pense que c'est une bonne idée.

Le DRY, comme on dit, ce n'est pas que dans le code.

### Résoudre tous ces problèmes: dns local et reverse-proxy

Nous l'avons dit dans la [section précédente](#aller-un-peu-plus-loin-acceder-à-nos-conteneurs-via-un-nom-de-domaine) notre starterpack est bien mais on peut faire mieux dans le cas où l'on souhaite travailler sur plusieurs projets en même temps sans avoir à toucher de la config et maintenir des états.

Pour régler ce problème on va utiliser un autre outil, le service [Traefik](https://doc.traefik.io/traefik/). On va s'en servir comme [reverse proxy](https://fr.wikipedia.org/wiki/Proxy_inverse). Il servira d'intermédiaire pour accéder à nos conteneurs Docker.

Illustrons concrètement ce que l'on cherche à faire: je démarre un projet `foobar` avec mon starterpack. J'ai déjà deux autres projets sur lesquels je travaille issus de mon pack. Je m'en soucie pas. J'accède par exemple à mon service `back` depuis mon navigateur en requêtant `back.foobar.test`. Le domaine `.test` [est recommandé](https://fr.wikipedia.org/wiki/.test) car il a été reservé pour offrir un domaine qui ne rentre pas en conflit avec des domaines réels d'Internet. Pour accéder à adminer de mon projet je tape `adminer.foobar.test`. Etc... Pratique non ? D'une part je n'ai plus besoin de savoir quels ports sont déjà réservés sur tel ou tel projet ni de les gérer. Enfin `back.foobar.test` est plus explicite que `localhost:90001`. Si je me donne une règle de syntaxe je peux retrouver n'importe quel conteneur de n'importe quel projet facilement six mois plus tard.

Pour y parvenir, on va se servir d'un serveur dns local et d'un reverse-proxy. On va mettre en place un service qui va essayer de résoudre le nom de domaine à partir d'une configuration sur votre machine avant d'interroger un vrai serveur dns d'internet. Quand on tapera l'url `back.foobar.test`, notre système de dns va donc regarder s'il trouve un pattern, ici le domaine `.test` et tous les [sous-domaines associés](https://fr.wikipedia.org/wiki/Nom_de_domaine), par exemple `back.foobar.test`. S'il le trouve, il va rediriger la requête faite depuis notre navigateur vers notre machine au lieu d'aller requêter l'Internet. C'est là que notre reverse proxy rentre en jeu: il va recevoir la requete, et s'il est bien configuré, va résoudre le nom de domaine pour nous servir le conteneur Docker de notre projet. Voilà le plan:

- utiliser le domaine reservé `.test` pour capter tous les sous-domaines (aka tous nos projets de dev) et renvoyer les reqûetes vers notre machine. C'est le job de notre service dns local
- intercepter les requêtes entrant sur notre machine pour les résoudre et les rediriger vers le bon conteneur Docker, par exemple le adminer d'un de nos projets. C'est le job du [reverse-proxy](https://fr.wikipedia.org/wiki/Proxy_inverse), il agit comme un portique par lequel les requêtes entrantes vont devoir passer pour être traitées selon nos besoins.

Pour mettre en place ce système on va avoir besoin de conteneurs Docker car on va conteneurisé le reverse proxy (et oui, encore, le minimum sur notre machine). Pour cela, on va créer un nouveau dépôt en dehors de notre starterpack. Un projet, un dépôt, c'est la règle. Ce projet vivra sa vie de manière indépendante sur votre machine et pourra servir à tous vos projets en local et non seulement à ceux réalisés avec votre starterpack. Quand on l'aura cablé, on le lancera une fois pour toute et vous n'y retoucherez plus jamais.

Créer donc un autre dépôt sur votre machine, par exemple `local-env-docker` et allons-y.

#### Mise en place d'un dns local avec `dnsmasq`

Mettons en place ce système. Je le fais sur Linux, si vous êtes sur un autre OS il faudra trouver des façons équivalentes de faire la même chose. L'idée restera la même.

Dans tous les cas, pour notre dns local on va utiliser [dnsmasq](https://www.linuxtricks.fr/wiki/dnsmasq-le-serveur-dns-et-dhcp-facile-sous-linux) (disponible sous Linux/MacOS, pour Windows un équivalent semble être [Acrylic](http://mayakron.altervista.org/support/acrylic/Home.htm)).

Sur Linux, en tout cas sur Ubuntu, la configuration réseau est gérée par le processus `systemd`. Celui-ci définit `NetworkManager` comme application réseau par défaut. `NetworkManager` gère donc les DNS et le DHCP de votre machine. NetworkManager connait mais n'utilise pas `dnsmasq` par défaut donc il va falloir lui dire. On édite le fichier de configuration `/etc/NetworkManager/NetworkManager.conf` et on ajoute une nouvelle ligne `dns=dnsmasq` dans la section `[main]`. On enregistre la modification.

En principe, la résolution d'URL est gérée par `systemd-resolver`, mais, on va laisser `NetworkManager` s'en occuper afin de permettre à `dnsmasq` d'attraper les URLs qui nous concernent, celles en `.test`, en exécutant la commande suivante :

`sudo rm /etc/resolv.conf ; sudo ln -s /var/run/NetworkManager/resolv.conf /etc/resolv.conf`

Ici on supprime le fichier de configuration par défaut du resolver d'URL et on le remplace par le fichier de configuration de `NetworkManager` via un lien symbolique.

On crée ensuite un fichier de configuration dnsmasq `test-tld` dans le dossier `/etc/NetworkManager/dnsmasq.d/`, en ajoutant le pattern `.test` recherché

`echo 'address=/test/127.0.0.1' | sudo tee /etc/NetworkManager/dnsmasq.d/test-tld`

Ici on map le pattern `.test` à l'ip de notre machine pour qu'une requête comme `foobar.test` revienne vers nous. On redémarre `NetworkManager` pour prendre en compte les modifications

`sudo service NetworkManager reload`

A présent toutes les requêtes émises par notre machine vers les sous-domaines de `.test` devraient être interceptées et redirigées sur elle même (et non vers l'Internet). Testons cela

```
ping foobar.test
ping back.example.test
ping front.projet.test
```

Vous devriez voir que le ping vers n'importe quel sous-domaine de `.test` est bien redirigé vers notre machine, à savoir vers l'ip `127.0.0.1`, `localhost` en somme. Parfait, c'est exactement ce que nous voulions !

Donc dorénavant toutes vos requêtes depuis votre navigateur vers un sous domaine de `.test` n'ira jamais vers l'Internet. Mais si un site web est en `.test` ? Justement non et c'est tout l'intérêt d'utiliser ce nom de domaine: il est reservé pour le test et le développement. Vous êtes donc garantis par les standards de ne jamais perdre accès à un site en `.test` puisqu'il y'en aura jamais.

#### Nouveau dépôt

Notre seule config sur notre machine locale est terminée. A présent nous allons pouvoir mettre en place notre reverse proxy.

Dans notre dépôt dédié `local-env-docker` (et complètement indépendant de notre starterpack et de son dépôt) on va crée un fichier `docker-compose.yml` et un `.env` pour _dockeriser_ `Traefik`.

#### Configuration de Traefik

Je ne suis pas un expert en reverse proxy mais voici en quelques mots à quoi va nous servir [Traefik](https://doc.traefik.io/traefik/getting-started/concepts/). `Traefik` est un service puissant et nous allons seulement en utiliser quelques fonctionnalités. Libre à vous d'explorer ce programme pour aller plus loin dans son usage.

En gros `Traefik` va intercepter les requêtes en `.test` qui retombent sur notre machine et les rediriger vers le bon conteneur. Mais ce qui est top avec [Traefik] c'est qu'il va détecter les nouveaux conteneurs automatiquement et créer les routes pour y accèder (en gros associer une URL à l'IP d'un conteneur Docker). Pas besoin de configurer des routes à chaque fois qu'on démarre un nouveau projet (ou qu'on en supprime un), [Traefik] gère ça pour nous !

##### Lancement du conteneur Traefik

Je recommande déjà de suivre la page [Quick Start](https://doc.traefik.io/traefik/getting-started/quick-start/) de la doc de Traefik, vous aurez une base pour votre `docker-compose.yml` et une meilleure idée de son fonctionnement et de ses capacités.

Commençons donc à configurer. Ouvrez la page [Traefik & Docker](https://doc.traefik.io/traefik/providers/docker/), tout ce dont on aura besoin y est, vous pourrez vous y référer si c'est pas clair ce que j'écris. Si vous suivez le guide [Quick Start](https://doc.traefik.io/traefik/getting-started/quick-start/) et vous rendez à l'adresse `http://localhost:8080/api/rawdata` vous verez vos conteneurs docker reconnus par Traefik ainsi que leurs configurations (notamment leur IP). Si vous montez ou fermez des conteneurs vous les verrez ajoutés ou retirés de la liste. Plutôt cool.

Maintenant qu'on est convaincus que Traefik voit nos conteneurs et les prends en charge dynamiquement la question c'est : comment rediriger notre requête par exemple `back.foobar.test` vers le conteneur(service Compose) `back` du projet `foobar` monté sur notre machine locale ?

La première ligne de la page dit "_Attach labels to your containers and let Traefik do the rest!_". Voilà, donc faut qu'on comprenne comment arriver à ça.

Regardons aussi [cette page](https://doc.traefik.io/traefik/getting-started/configuration-overview/) qui donne un aperçu de la configuration de Traefik et comment la _magie opère_.

Déjà on ne veut pas que Traefik intercepte toutes les requêtes entrantes, seulement les requêtes HTTP (nos requêtes en `.test`). Pour cela on va utiliser la directive
`entryPoints` directement dans notre docker-compose.yml, c'est de la [configuration dynamique](https://doc.traefik.io/traefik/getting-started/configuration-overview/#the-dynamic-configuration) car elle va s'adapter à chaque situation. Voici notre service reverse-proxy, en s'inspirant directement de l'[exemple donné dans la doc](https://doc.traefik.io/traefik/user-guides/docker-compose/basic-example/)

```
services:
  reverse-proxy:
    # The official v2 Traefik docker image
    image: traefik:v2.6
    ports:
      # The HTTP port
      - "80:80"
      # The Web UI (enabled by --api.insecure=true)
      - "8080:8080"
    volumes:
      # So that Traefik can listen to the Docker events
      - /var/run/docker.sock:/var/run/docker.sock

    command:
      #- "--log.level=DEBUG"
      - "--api.insecure=true"
      - "--providers.docker=true"
      - "--providers.docker.exposedbydefault=false"
      - "--entrypoints.web.address=:80"
```

On map les ports 80 pour que Traefik écoute toutes les requêtes http entrantes sur notre machine. Le port 8080 est utilisé pour nous donner accès à des UI de Traefik (comme `http://localhost:8080/api/rawdata` que nous avons inspecté juste avant). La partie qui nous intéresse pour la configuration dynamique est sous la clef `command`. Ici on dit

- `--api.insecure=true`, on active l'API de Traefik pour exposer tout un tas d'UI et d'informations. Très utile pour le dev, à désactiver en prod (c'est le cas par défaut)
- `--providers.docker=true`, pas sûr de comprendre exactement mais en gros on dit à Traefik que Docker est utilisé. Donc Traefik va pouvoir requêter l'API de Docker pour pouvoir fonctionner correctement avec Docker
- `providers.docker.exposedbydefault=false`, on dit à Traefik que si un conteneur ne déclare pas explicitement (on le verra après) son envie d'être scanné pour mettre en place le routing automatiquement alors ignore le. Parfait
- `--entrypoints.web.address=:80`, très important. Les [entryPoints](https://doc.traefik.io/traefik/routing/entrypoints/) permettent de maper les ports de notre machine à Traefik pour qu'il se branche dessus. Ici on dit qu'on crée un `entryPoint` appelé `web` et que Traefik écoute le port `80` seulement.

Les `entryPoints` permettent à Traefik de récupérer les requêtes. Maintenant il faut lui dire vers où les diriger. Traefik crée pour chaque conteneur detecté un [routeur](https://doc.traefik.io/traefik/routing/routers/) et un [service](https://doc.traefik.io/traefik/routing/services/). Un _router_ est en charge de rediriger les requêtes entrantes vers le service Traefik qui peut les gérer. C'est un câble tiré entre l'`entryPoint` et le `service Traefik`. Oui il y a un peu de terminologie mais la documentation est vraiment bien faite et accompagnée de schémas en couleur. Un _service Traefik_, à ne pas confondre avec notre service Compose, est quand à lui responsable de définir _comment_ accéder rééelement à nos conteneurs. On verra ça juste après. Pour l'instant on met en place la partie interface entre Traefik et notre machine.

##### Intercepter uniquement les reqûetes vers nos conteneurs Docker

Donc là, on a dit de récuperer les requêtes HTTP mais on veut être encore plus restrictif et ne pas interférer avec le trafic sur notre machine, on veut récupérer seulement les requêtes en `.test`.

Ajoutons les configurations suivantes

```
      - "--providers.docker.network=web"
      - "--providers.docker.defaultrule=HostRegexp(`{subdomain:[a-z]+}.test`)"
```

A présent on dit d'utiliser le réseau Docker `web` par défaut (car pourquoi pas). Et surtout on filtre les requêtes en fonction de l'hôte demandé (via la clef `defaultrule`) avec une regex pour ne capter que les noms de domaine en `.test`.

Relancez le projet avec `docker-compose up -d`. Si vous retournez sur `http://localhost:8080/api/rawdata` où sont exposées des infos de Traefik sur l'état des services, vous verrez que le service `whoami` n'est plus visible ! C'est bien ce qu'on voulait. D'ailleurs aucun de nos conteneurs ne sera visible car on a dit que par défaut Traefik ne devait pas les prendre en compte.

Comment ré-intégrer notre service whoami à Traefik ? Pour cela on va ajouter un peu de config sur notre service `whoami` sous la clef `labels`. Labeliser nos conteneurs permet à Traefik de retrouver sa configuration de routing, et donc au final le conteneur ciblé en retrouvant son adresse ip.

```
whoami:
  labels:
    # Explicitly tell Traefik to expose this container
    - "traefik.enable=true"
    # The domain the service will respond to
    - "traefik.http.routers.whoami.rule=Host(`whoami.test`)"
    # Allow request only from the predefined entry point named "web"
    - "traefik.http.routers.whoami.entrypoints=web"
```

Les commentaires sont assez clairs ici. Sur la première ligne on expose le conteneur de manière explicite. La ligne importante est `traefik.http.routers.whoami.rule=Host(`whoami.test`)`. Il attribue un nom de domaine au service de Traefik.

Visitez à présent `whoami.test` depuis votre navigateur favori. Vous devriez tomber sur la réponse du service comme attendu.

Enfin un point très important, que j'ai découvert en passant des heures à m'arracher les cheveux à comprendre pourquoi Traefik ne me renvoyait des 404 lorsque j'essayais de faire tourner plusieurs conteneurs en parallèle. Lorsque l'on va travailler sur plusieurs projets dockerisés de manière parallèle il faudra s'assurer que chaque conteneur accessible dispose de son propre router.

Le `router` ici est appelé `whoami`, comme notre service. Chaque router est défini fondamentalement par deux paramètres:

- `entrypoints`: est ce que la requête entrante doit être écoutée pour accéder à ce service ?
- `rule` : est ce que la requete entrante concerne mon service ?
  
Donc lorsque vous voulez monter à la chaine plusieurs projets dans ce setup, il faudra bien labeliser vos services comme suit

##### Application à notre starterpack

C'est top, tout fonctionne comme prévu. Il ne nous reste plus qu'à rajouter quelques petites choses pour nous simplifier la vie et en finir une fois pour toute avec ces histoires pour avoir notre starterpack. Après cela, on pourra consacrer notre temps à ce qui nous intéresse le plus, coder nos projets.

##### Ajout du nom de domaine dans le `.env`

Créer un fichier `.env` dans votre dépôt `local-env-docker` et renseigner juste cette ligne

```
TRAEFIK_DOMAIN=test
```

Puis dans le docker-compose.yml on va modifier légèrement notre règle précédente pour filtrer les requêtes entrantes

```
      - "--providers.docker.defaultrule=HostRegexp(`{subdomain:[a-z0-9]+}.${TRAEFIK_DOMAIN`)"
```

Voilà, c'est pas grand chose mais on y voit plus clair. Il sera plus facile de changer le domaine .test si on le souhaite, pour [un autre nom de domaine de premier niveau reservé(https://fr.wikipedia.org/wiki/Domaine_de_premier_niveau#Domaine_de_premier_niveau_r%C3%A9serv%C3%A9)] comme `.example`.

#### En résumé

Donc pour résumer quand je taperai `whoami.test` dans mon navigateur:

- ma configuration dns locale va repérer le `.test` et rediriger la reqûete vers ma machine, sur le port `80`
- Traefik, qui écoute sur le port 80, va regarder si cette requête finit en `.test`. Si c'est le cas on continue, sinon Traefik ignore
- La requete `whoami.test` passe dans Traefik. Traefik regarde si il a un service labelisé `whoami.test`. Si c'est le cas, il retrouve sa configuration de routing et renvoie la requête vers l'adresse ip du conteneur.
- Le conteneur nous répond et on récupère le résultat dans le navigateur

Magnifique !

##### Labeliser vos conteneurs Docker

Ajouter dans le .env du dépôt du starterpack ces lignes

```
# Nom de domaine filtré par Traefik (à synchroniser avec le TRAEFIK_DOMAIN du .env du dépot local-docker-env)
TRAEFIK_DOMAIN=test

#Nom du projet
PROJECT_NAME=foo
```

`TRAEFIK_DOMAIN` doit avoir la même valeur que celle définie dans le dépôt local-docker-env. Et `PROJECT_NAME` sera le nom unique de votre projet.

A présent on peut labeliser nos services Docker sous la clef `labels` de notre starterpack (à savoir `back`, `front`, `adminer`) comme on l'a fait avec le service `whoami`. Par exemple pour le service front

```
  #Le serveur front (html static)
  front:
    image: httpd:latest
    restart: always
    volumes:
      - ./front/:/usr/local/apache2/htdocs/:rw
    container_name: minimal-front
    networks:
      - web
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.${PROJECT_NAME}-front.rule=Host(`front.${PROJECT_NAME}.${TRAEFIK_DOMAIN}`)"
      - "traefik.http.routers.${PROJECT_NAME}-front.entrypoints=web"
```


Adapter le nom de domaine selon vos préférences. On fait la même chose pour les autres services. On relance le projet avec `docker-compose up -d` et si on visite `front.foo.test`, normalement, on est servis !

Vous pouvez y tester, via un petit formulaire, la communication entre le front et le back. Le back teste la connexion à la base de données et retourne la réponse. Si tout se passe bien vous devriez obtenir la réponse `Hello World ! La connexion à la base de données a réussi !`. Si c'est le cas, bravo ! Si c'est pas le cas, courage. Moi aussi j'ai galéré à monter ce pack... Ou alors j'ai oublié de mentionner une configuration et n'hésitez pas à ouvrir une issue sur le dépôt !

Et voilà, c'est fini ! Enfin, tout peut commencer. A présent vous pouvez dupliquer ce starterpack autant que vous le souhaitez, d'ailleurs changer même les services et changer complètement de stack. Vous avez toutes les clefs pour monter votre stack préféré sur des conteneurs accessibles via un nom de domaine facile à retenir et ne rentrant pas en conflit avec tous vos autres projets.

On remarquera qu'on ne précise plus de ports pour les conteneurs. En effet, c'est bien le conteneur Traefik qui est mappé au port 80 et qui écoute. Les autres conteneurs se sont vus attribués des ports aléatoires et nous n'avons plus à nous en soucier !

Merci pour votre lecture.

## Mode pragmatique : utiliser le projet directement

### Description courte

Voici les instructions à suivre pour vous appropriez le starterpack.

Il est composé de deux projets (chacun sur son dépôt):

- le [starterpack](https://github.com/websealevel/starterpack-front-php-mysql-phpmyadmin) à proprement dit, avec nos services Docker. Le starterpack est composé des services suivants
  - `front` : un serveur qui sert du contenu HTML statique
  - `back` : un serveur apache/php pour le backend
  - `adminer`: pour administrer la base de données
  - `db` : une base de données MySql
- le [reverse-proxy](https://github.com/websealevel/local-env-docker), pour faciliter notre workflow et la gestion de nos projets

### Prérequis

- Docker
- Docker-compose
- dnsmasq ou autre utilitaire de dns local

Pas de questions, pas d'explications. On va droit au but.

### Instructions

1. Cloner le dépôt [local-docker-env](https://github.com/websealevel/local-env-docker)
2. Configurer le dns local en suivant les instructions de cette [section](#mise-en-place-dun-dns-local-avec-dnsmasq). A faire qu'une fois pour tous vos projets
3. Lancer le projet à la racine avec `docker-compose up -d`. A faire qu'une fois pour tous vos projets. Laissez tourner le conteneur `traefik` pour tous vos projets
4. Cloner ce dépot [starter-pack-front-php-mysql](https://github.com/websealevel/starterpack-front-php-mysql-phpmyadmin)
   1. Changer la valeur de `PROJECT_NAME` et donner lui le nom de votre projet (lettres minuscules de préférence)
   2. Créer un dossier `mysql-data`
   3. Lancer le projet à la racine avec `docker-compose up -d`
   4. Accéder à vos services :
      1. `front.${PROJECT_NAME}.test` pour acceder au backend
      2. `back.${PROJECT_NAME}.test` pour acceder au frontend
      3. `adminer.${PROJECT_NAME}.test` pour acceder à adminer et à la base de données. Logger vous avec l'utilisateur `root` (mot de passe `root`)

### Démonter le projet

Démontez le projet avec `docker-compose down` à la racine du dépôt.

### Gestion multiprojets

Montez un autre projet en dupliquant/clonant le starterpack. Ne répétez pas les étapes 1 à 3. Reprenez à l'étape 4.

## Aller plus loin

Voilà, en espérant que cette config vous apporte satisfaction pour vos projets de développement. La première fois c'est galère mais après c'est la tranquilité totale pour gérer plusieurs projets sur notre machine locale.

On pourrait enrichir ce starterpack de beaucoup de manières, mais ce sera le sujet peut être d'un autre article. On pourrait commencer par automatiser les dernières petites taches via un script. Je pense également à l'installation de modules pour PHP, ou alors rajouter un service pour intercepter les mails etc... Affaire à suivre.

Have fun.

## Références

Ne pas hésiter à consulter la documentation de Docker, Compose et Traefik. Elles sont bien faites.

### Images officielles et leur documentation

- [Mariadb](https://hub.docker.com/_/mariadb)
- 

### Autres

- [Les volumes de Docker](https://docs.docker.com/storage/volumes/)
- [Get started with Docker Compose](https://docs.docker.com/compose/gettingstarted/)
- [Commandes utiles de Docker et Compose](https://www.padok.fr/blog/docker-docker-compose-commandes-connaitre)
- [Networking in Compose](https://docs.docker.com/compose/networking/)
- [Networking with standalone containers](https://docs.docker.com/network/network-tutorial-standalone/)
- [Les concepts pour comprendre Traefik](https://doc.traefik.io/traefik/getting-started/concepts/)
- [Docker Tip - How to use the host's IP Address inside a Docker container on macOS, Windows, and Linux](https://dev.to/natterstefan/docker-tip-how-to-get-host-s-ip-address-inside-a-docker-container-5anh)
