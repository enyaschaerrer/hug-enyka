# Seeder global local

Ce document explique comment reproduire l'etat final de la base locale a partir d'un dump SQL, sans supprimer ni modifier les seeders existants du projet.

## Objectif

Conserver un snapshot rejouable de la base locale de fin de developpement dans un seeder dedie :

```text
database/seeders/EnykaLocalSeeder.php
```

Ce seeder ne remplace pas les autres seeders.
Il sert uniquement a restaurer un etat global realiste de la base locale.

## Source utilisee

Le fichier source etait :

```text
/Users/tanguyvaucher/Desktop/hug_enyka_local.sql
```

Ce dump correspond a l'etat reel de la base locale a la fin du developpement.

## Principe retenu

Au lieu de convertir manuellement chaque table en grands tableaux PHP, la methode retenue a ete :

1. lire le dump SQL local ;
2. extraire uniquement les `INSERT INTO` utiles ;
3. ignorer les tables volatiles ou specifiques a l'instance ;
4. compresser ces `INSERT` dans le seeder ;
5. decompresser puis executer le SQL au runtime du seeder.

Le resultat est un seeder autonome, plus compact et plus fiable qu'une retranscription manuelle de centaines de lignes.

## Tables conservees

Le seeder restaure uniquement les tables metier utiles :

```text
companies
users
trophy_editions
collections
forms
prizes
collections_users
page_visits
```

## Tables volontairement exclues

Ces tables n'ont pas ete restaurees dans le seeder :

```text
migrations
sessions
cache
cache_locks
```

Raison :

- `migrations` appartient au cycle Laravel et est reconstruite par `migrate`;
- `sessions` est volatile et propre a une instance locale ;
- `cache` et `cache_locks` sont temporaires et ne representent pas l'etat metier de l'application.

## Structure du seeder

Le fichier cree est :

```text
database/seeders/EnykaLocalSeeder.php
```

Son fonctionnement :

1. desactive temporairement les contraintes de cles etrangeres ;
2. vide les tables cibles ;
3. decode un payload SQL compresse ;
4. execute les `INSERT` du dump ;
5. reactive les contraintes.

Le payload SQL est stocke dans une variable :

```php
$compressedSql = '...';
```

Ce contenu est :

- compresse en `gzip` ;
- encode en `base64` ;
- decode a l'execution avec `gzdecode(base64_decode(...))`.

Cela permet de garder un fichier unique sans embarquer tout le dump en clair.

## Commandes utiles

### Rejouer uniquement le seeder global sur une base deja migree

```bash
php artisan db:seed --class=Database\\Seeders\\EnykaLocalSeeder
```

### Repartir d'une base vide puis rejouer le seeder global

```bash
php artisan migrate:fresh
php artisan db:seed --class=Database\\Seeders\\EnykaLocalSeeder
```

## Procedure pour refaire la meme chose plus tard

Si la base locale evolue et qu'un nouveau snapshot complet doit etre regenere :

1. exporter la base locale dans un nouveau fichier `.sql` ;
2. verifier quelles tables doivent etre conservees ;
3. exclure les tables volatiles (`sessions`, `cache`, etc.) ;
4. regenerer `EnykaLocalSeeder.php` a partir des `INSERT INTO` utiles ;
5. tester avec :

```bash
php artisan migrate:fresh
php artisan db:seed --class=Database\\Seeders\\EnykaLocalSeeder
```

## Point d'attention important

Cette methode suppose que :

- les migrations actuelles restent compatibles avec le contenu du dump ;
- les colonnes presentes dans le dump existent toujours apres `migrate:fresh`.

Si le schema Laravel change fortement plus tard, il faudra :

- soit regenerer le seeder depuis un dump plus recent ;
- soit adapter le contenu restaure aux nouvelles colonnes.

## Pourquoi cette methode a ete choisie

Cette approche a ete retenue parce qu'elle est :

- rapide a produire ;
- fidele au dump reel ;
- plus stable qu'une conversion manuelle longue ;
- facile a rejouer avec une simple commande Artisan.
