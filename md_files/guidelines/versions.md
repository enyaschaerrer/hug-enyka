# Versions imposees

Ce projet utilise les versions et outils suivants. Toute modification du code doit etre ecrite pour cette stack precise.

Il ne suffit pas que le code soit "compatible". Le code doit suivre les conventions, syntaxes, APIs et fichiers attendus par les versions declarees dans ce projet.

## Backend

- PHP : `^8.3`
- Laravel : `^13.8`
- Laravel Tinker : `^3.0`
- PHPUnit : `^12.5.12`

## Frontend

- Vite : `^8.0.0`
- Vue : `^3.5.34`
- Tailwind CSS : `^4.0.0`
- DaisyUI : `^5.5.20`
- Laravel Vite Plugin : `^3.1`
- Plugin Vue pour Vite : `^6.0.7`
- GSAP : `^3.15.0`

## Outils de developpement

- Composer gere les dependances PHP avec `composer.json` et `composer.lock`.
- npm gere les dependances JavaScript avec `package.json` et `package-lock.json`.
- Vite compile les assets frontend.
- PHPUnit sert aux tests PHP.

## Regles pour le code genere ou propose par IA

Il est proscrit d'utiliser du code inadapte aux versions ci-dessus.

Une IA ne doit jamais proposer du code base sur une ancienne version du framework ou des outils, meme si ce code pourrait encore fonctionner partiellement.

Exemples interdits :

- code, structure ou conventions Laravel 8, 9, 10, 11 ou 12 ;
- configuration Tailwind CSS v2 ou v3 ;
- code Vue 2 ;
- Laravel Mix, Webpack ou anciennes configurations frontend Laravel ;
- anciennes conventions de factories, seeders, routes, middleware, bootstrap ou tests si elles ne correspondent pas a Laravel `^13.8` ;
- commandes ou fichiers qui ne correspondent pas a la structure actuelle du projet.

Avant d'ajouter ou modifier du code, toute IA doit verifier que les syntaxes, API, commandes et exemples proposes correspondent aux versions exactes de ce projet. En particulier :

- ecrire le code Laravel pour Laravel `^13.8`, pas pour Laravel 8 ou une autre version ;
- ne pas utiliser de syntaxe PHP incompatible avec PHP `8.3` ;
- ecrire la configuration Tailwind pour Tailwind CSS `^4.0.0`, pas Tailwind CSS v3 ;
- ecrire le frontend pour Vue `^3.5.34`, pas Vue 2 ;
- ne pas remplacer Vite par Laravel Mix ou Webpack ;
- ne pas ajouter de dependance sans verifier son utilite et sa compatibilite ;
- ne pas inventer de fichiers, commandes ou conventions qui ne correspondent pas a ce projet.

Si une IA n'est pas certaine qu'une API ou une convention appartient bien a ces versions, elle doit s'arreter et verifier les fichiers du projet ou la documentation officielle avant de proposer du code.

En cas de doute, il faut verifier les fichiers de reference du projet avant de coder :

- `composer.json`
- `composer.lock`
- `package.json`
- `package-lock.json`
- `vite.config.js`
- `phpunit.xml`
