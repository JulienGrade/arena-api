# Starter Symfony avec Docker

Ce dépôt contient l'environnement de développement nécessaire pour créer un projet Symfony : **PHP 8.5.12**, **Symfony CLI**, **Composer** et **PostgreSQL 17**. Aucun de ces outils n'est à installer sur l'ordinateur : Docker Desktop et Git suffisent. L'application Symfony n'est pas préinstallée ; vous la créez en suivant les commandes officielles.

Le dossier du projet Symfony s'appelle `app/`. Le dépôt Git est à la racine de ce starter ; l'option `--no-git` évite la création d'un second dépôt dans `app/`.

## 1. Cloner et configurer

```bash
git clone https://github.com/JulienGrade/starter-symfony.git
cd starter-symfony
```

L'URL ci-dessus est un modèle à remplacer par celle du dépôt publié.

Créez le fichier `.env` **à la racine du starter**, puis renseignez au minimum `DB_NAME`, `DB_USER` et `DB_PASSWORD` :

| Terminal | Commande |
| --- | --- |
| macOS / Linux | `cp .env.example .env` |
| Windows PowerShell | `Copy-Item .env.example .env` |

Les valeurs proposées servent uniquement au développement local. Choisissez pour les identifiants des lettres, des chiffres et des `_` ; si votre mot de passe contient des caractères spéciaux, encodez-les pour une URL PostgreSQL avant de le mettre dans `DATABASE_URL`. Le `.env` à la racine est lu par **Docker Compose** ; l'application Symfony possède ensuite son propre `app/.env`.

## 2. Démarrer les outils

```bash
docker compose up -d --build
docker compose exec dev sh
```

Le conteneur `dev` s'ouvre dans `/workspace`. Vérifiez éventuellement les outils :

```bash
php -v
composer --version
symfony -V
```

## 3. Créer votre projet

Dans le shell du conteneur :

```bash
symfony new app --webapp --no-git
cd app
symfony console about
```

`symfony new ... --webapp` installe la dernière version stable du framework et ses paquets pour une application web. La création exige une connexion Internet. Pour un projet API plus léger, exécutez `symfony new app --no-git` à la place. Pour voir les commandes disponibles : `symfony console list`. Pour le générateur Maker, utilisez par exemple `symfony console make:controller` ; si une commande `make:*` manque, installez le bundle depuis ce même répertoire avec `composer require --dev symfony/maker-bundle`.

Le projet est maintenant présent sur votre ordinateur dans `app/`. Les variables `DATABASE_URL` définies dans Compose sont disponibles dans `dev` et `web` et prennent le pas sur la valeur d'exemple du `app/.env` Symfony.

## 4. Afficher le projet

Sortez du shell (`exit`), puis démarrez le service web :

```bash
docker compose --profile web up -d web
```

Ouvrez <http://localhost:8000> (ou le port défini dans `WEB_PORT`). Le service web attend la création de `app/public/index.php`, d'où son profil séparé. Il utilise le serveur intégré PHP pour le développement local.

Pour revenir aux commandes Symfony à tout moment :

```bash
docker compose exec dev sh
cd app
symfony console make:controller
```

Sous Windows, ces mêmes commandes `docker compose` fonctionnent dans PowerShell ; `sh` désigne le shell Linux du conteneur.

## 5. Vérifier PostgreSQL

Dans le shell du conteneur, après installation de Doctrine dans votre projet :

```bash
cd app
composer require symfony/orm-pack
symfony console doctrine:query:sql 'SELECT version()'
```

Une autre vérification sans Doctrine, à exécuter depuis l'hôte, est `docker compose exec db psql -U atelier -d atelier -c 'SELECT current_database();'` si vous avez conservé les valeurs d'exemple. Sinon remplacez `atelier` par les valeurs de votre `.env`.

## Mise à jour de sécurité PHP

Les services `dev` et `web` sont construits à partir du même Dockerfile. La version PHP de l'image de base est fixée à **8.5.12**, qui corrige les vulnérabilités CVE-2026-109618 et CVE-2026-109619 publiées le 21 octobre 2026. Si vous avez construit le starter avant cette date, une simple relance avec `docker compose up -d` conserve potentiellement l'ancienne image. Depuis la racine du dépôt, reconstruisez et recréez les deux services :

```bash
docker compose --profile web build --pull --no-cache dev web
docker compose --profile web up -d --force-recreate dev web
docker compose exec dev php -v
docker compose --profile web exec web php -v
```

Les deux commandes `php -v` doivent afficher **PHP 8.5.12 ou une version corrigée plus récente**. Si le projet Symfony n'est pas encore créé, le service `web` peut s'arrêter : créez d'abord `app/`, puis relancez `docker compose --profile web up -d web` avant de vérifier sa version. Le numéro fixé dans le Dockerfile doit être revu lors des futures mises à jour PHP ; remplacer le Dockerfile sans reconstruire les images ne met pas à jour les conteneurs existants. Aucun effacement du volume PostgreSQL n'est nécessaire pour cette mise à jour.

Références : <https://www.php.net/archive/2026.php> et <https://github.com/docker-library/official-images/blob/master/library/php>.

## Arrêt et données

```bash
docker compose --profile web down
```

Le volume PostgreSQL garde les données après cet arrêt. Changer `DB_NAME`, `DB_USER` ou `DB_PASSWORD` dans `.env` ne reconfigure **pas** un volume déjà initialisé. Pour repartir volontairement avec une base vide, `docker compose --profile web down -v` supprime le volume et toutes ses données.

## Documentation officielle

| Sujet | Lien |
| --- | --- |
| Installation Symfony et `symfony new` | <https://symfony.com/doc/current/setup.html> |
| Symfony CLI | <https://symfony.com/doc/current/setup/symfony_cli.html> |
| Docker Compose et `.env` | <https://docs.docker.com/compose/how-tos/environment-variables/variable-interpolation/> |
| Variables de l'image PostgreSQL | <https://docs.docker.com/guides/postgresql/> |

Propriété de Julien Grade.
