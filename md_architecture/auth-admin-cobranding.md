# Authentification admin et acces co-brandes

Ce document conserve les decisions prises pour l'authentification, le back-office admin et les futures pages co-brandees.

## Decisions principales

- Le projet reste sur un seul domaine applicatif, sans sous-domaines.
- Le back-office sera accessible plus tard via un chemin du type `/admin`.
- Les pages co-brandees seront gerees via des slugs, pas via des sous-domaines.
- Laravel Sanctum n'est pas utilise pour l'admin actuel.
- L'admin utilise l'authentification Laravel classique par session/cookie.
- Aucun formulaire, template ou route admin visible ne doit etre cree avant validation explicite.

## Pourquoi pas de sous-domaines

L'equipe a choisi d'eviter les sous-domaines pour reduire la complexite :

- configuration locale plus simple pour tous les membres de l'equipe ;
- moins de problemes avec MAMP, hosts locaux, virtual hosts et certificats ;
- deploiement Infomaniak plus direct ;
- cookies et sessions Laravel plus simples a gerer ;
- pas de difference importante entre local et production.

Le projet ne doit donc pas partir sur une architecture du type :

```text
admin.example.ch
entreprise.example.ch
```

Sauf decision future explicite de l'equipe.

## Structure d'URL cible

Structure recommandee :

```text
/                         site public principal
/admin                    futur back-office admin
/c/{slug}                 future page co-brandee
```

Le slug co-brande ne doit pas etre considere comme une authentification forte. Il sert a identifier une entreprise, une campagne ou une experience personnalisee.

## Admin

L'admin utilise la table `users` existante.

Roles initiaux :

```text
superadmin
admin
user
```

Le role est stocke en base dans `users.role` et encadre cote PHP par l'enum `App\Enums\UserRole`.

Les routes admin futures devront utiliser :

```php
->middleware('auth')
->middleware('role:superadmin,admin')
```

Pour une route reservee au niveau maximum :

```php
->middleware('role:superadmin')
```

Important : ne pas considerer qu'un utilisateur connecte a automatiquement acces a l'admin. L'authentification et l'autorisation sont deux etapes separees.

## Routes actuelles

Routes deja mises en place :

```text
/                Accueil public du site Trophee, affiche via Vue.
/admin/login     Formulaire login admin minimal.
/admin           Dashboard admin minimal, protege par auth + role:superadmin,admin.
/admin/logout    Deconnexion admin.
```

L'accueil public affiche encore le prototype Tinder existant. Il est maintenant isole dans une page Vue dediee pour pouvoir le remplacer ou l'adapter quand l'UI finale sera disponible.

Le formulaire admin est volontairement minimal :

- champ email ;
- champ password ;
- soumission vers Laravel ;
- erreurs serveur affichees simplement.

Le login admin utilise `Auth::attempt()` avec l'authentification Laravel classique par session/cookie. Apres connexion, seuls les roles `superadmin` et `admin` peuvent acceder a `/admin`.

Route non mise en place pour le moment :

```text
/collecte/{companySlug}/{token}
```

Elle sera ajoutee quand les tables liees aux entreprises et campagnes/collectes seront validees.

## Middleware de roles

Le middleware `role` est generique et extensible.

Exemples :

```php
role:superadmin
role:superadmin,admin
role:admin
```

Comportement attendu :

- utilisateur non connecte : gere par le middleware `auth` quand les routes admin existeront ;
- utilisateur connecte avec mauvais role : reponse `403 Forbidden` ;
- utilisateur connecte avec bon role : acces autorise.

L'interface admin future pourra masquer les liens non autorises selon le role, mais le back-end doit toujours proteger les routes et actions sensibles.

## Seeder superadmin

Le seeder cree un compte de test :

```text
Email: superadmin@example.com
Mot de passe: password
Role: superadmin
```

Ces identifiants sont uniquement destines au developpement local et aux donnees de test. Ils sont aussi documentes dans le `README.md`.

## Co-branding

Les pages co-brandees ne sont pas implementees pour le moment, mais les decisions d'architecture sont :

- pas de sous-domaine entreprise ;
- acces via une URL avec slug ;
- logique metier a isoler plus tard dans une couche dediee, par exemple un resolver de campagne ou d'entreprise ;
- ne pas eparpiller la lecture de l'URL directement dans les composants Vue ou les controllers.

Exemple futur possible :

```text
/c/rolex
/c/campagne-rolex-2026
```

Le slug peut etre public ou difficile a deviner selon le besoin, mais il ne doit pas remplacer une vraie verification si la page expose des informations sensibles.

## Acces co-brande par email professionnel

Pour une version plus securisee, l'equipe a evoque un acces par email professionnel :

1. L'utilisateur arrive sur une page co-brandee.
2. Il saisit son email professionnel.
3. Le back-end verifie que le domaine email est autorise pour l'entreprise.
4. Un lien de validation est envoye par email.
5. L'utilisateur accede a la page apres validation.

Preference technique :

- stocker des domaines autorises, par exemple `rolex.com` ou `ch.rolex.com` ;
- eviter les regex libres saisies en admin si ce n'est pas necessaire ;
- si des patterns avances deviennent necessaires, les encadrer et les tester.

Cette authentification co-brandee doit rester separee de l'authentification admin. Les comptes `users` servent a l'admin et aux futurs comptes applicatifs, pas forcement aux collaborateurs anonymes des entreprises partenaires.

## Evolution future des roles

Les roles initiaux restent volontairement simples. Quand les vrais besoins admin seront connus, l'equipe pourra ajouter des roles plus precis :

```text
content_manager
partner_manager
stats_viewer
cts_admin
```

Avant d'ajouter un role, verifier :

- quelles routes il peut consulter ;
- quelles actions il peut effectuer ;
- quelles donnees il peut modifier ;
- quels tests doivent etre ajoutes.

## Etat actuel du code

Mis en place :

- enum `App\Enums\UserRole` ;
- migration `users.role` ;
- cast du role dans `App\Models\User` ;
- middleware `App\Http\Middleware\EnsureUserHasRole` ;
- alias middleware `role` ;
- seeder superadmin ;
- tests backend du role, du middleware et du seeder.
- routes `/`, `/admin/login`, `/admin`, `/admin/logout` ;
- controllers publics/admin minimaux ;
- pages Vue temporaires pour l'accueil, le login admin et le dashboard admin ;
- tests backend du login admin, de la protection admin et du logout.

Non mis en place pour le moment :

- pages co-brandees ;
- magic links email ;
- gestion avancee des permissions.
