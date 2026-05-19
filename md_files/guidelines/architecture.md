# Architecture du projet

L'architecture doit rester coherente, modulaire et non redondante. C'est une demande explicite du professeur dans `md_files/guidelines/consigne.md`.

## Structure generale

- `app/` contient le code Laravel back-end.
- `app/Http/Controllers/` contient les controllers.
- `app/Http/Requests/` doit contenir les Form Requests quand une validation devient importante ou reutilisable.
- `app/Models/` contient les models Eloquent.
- `routes/web.php` contient les routes web.
- `database/migrations/` contient les migrations.
- `database/seeders/` contient les seeders, dont le jeu de donnees de test demande par le professeur.
- `database/factories/` contient les factories.
- `resources/js/` contient le front-end Vue.
- `resources/js/pages/` contient les pages Vue.
- `resources/js/services/` contient les appels API ou la logique de communication front-end.
- `resources/css/app.css` contient le CSS global.
- `tests/` contient les tests automatises.

## Regles Laravel

- Un controller doit rester centre sur la coordination de la requete.
- La validation doit etre explicite.
- La logique metier importante ne doit pas etre cachee dans une vue.
- Les models Eloquent doivent representer les donnees et leurs relations.
- Les routes doivent rester lisibles et nommees quand cela aide la navigation.
- Ne pas creer de dossier ou pattern complexe si le besoin est simple.

## Regles Vue

- Les vues doivent etre structurees avec une approche par composants.
- Les composants doivent avoir une responsabilite claire.
- Ne pas mettre toute l'interface dans un seul gros fichier si elle contient plusieurs blocs reutilisables.
- La logique reactive doit rester dans Vue.
- Les appels reseau ou acces API doivent etre isoles dans `resources/js/services/` quand ils deviennent reutilisables.

## Regles CSS et UI

- Utiliser DaisyUI et Tailwind CSS selon `md_files/guidelines/daisyui.md`.
- Eviter le CSS global inutile.
- Ne pas dupliquer des styles si une classe DaisyUI ou Tailwind existe deja.
- Garder une interface coherente entre les pages.

## Interdictions

- Ne pas melanger du code back-end Laravel dans les composants Vue.
- Ne pas melanger de la logique Vue complexe dans Blade si un composant Vue est plus adapte.
- Ne pas creer une architecture differente sans raison claire.
- Ne pas ajouter une bibliotheque UI concurrente a DaisyUI sans demande explicite.
