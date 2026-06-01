# Authentification des campagnes co-brandees

Ce document decrit le systeme d'authentification ajoute pour proteger les pages de campagnes co-brandees.

## Objectif

Les pages co-brandees sont accessibles via un lien unique :

```text
/collecte/{brand}/{token}
```

Le lien identifie la campagne, mais il ne donne pas directement acces au contenu pour les collaborateurs.
Un collaborateur doit prouver qu'il possede une adresse email appartenant a un domaine autorise pour l'entreprise.

Les administrateurs CTS gardent un acces direct a toutes les campagnes co-brandees pour pouvoir les tester.

## Flux utilisateur

1. L'utilisateur clique sur le lien interne de campagne.
2. Laravel verifie que le couple `brand + token` correspond a une collecte active.
3. Si l'utilisateur n'est pas autorise, la SPA affiche une page d'authentification a deux onglets :
   - `Recevoir mon code d'acces`
   - `Se connecter`
4. L'utilisateur saisit son adresse email professionnelle.
5. Le back-end verifie que le domaine de l'email est autorise par `companies.allowed_email_domains`.
6. Si le domaine est valide :
   - un utilisateur `users` est cree ou mis a jour ;
   - son role est `user` ;
   - son mot de passe est regenere puis stocke hashe ;
   - l'utilisateur est associe a la collecte via `collections_users` ;
   - un email contenant le mot de passe est envoye.
7. L'utilisateur se connecte avec son email et son mot de passe.
8. Si son compte est lie a la bonne entreprise et a la bonne collecte, le site co-brande est affiche.

## Regles d'acces

### Admin

Les roles suivants peuvent acceder a toutes les pages co-brandees sans auth employe :

```text
superadmin
admin
```

Cette exception sert a tester les campagnes depuis le back-office.

### Collaborateur

Un collaborateur doit respecter toutes ces conditions :

- `users.role = user`
- `users.company_id = collections.company_id`
- une ligne existe dans `collections_users` pour le couple `collection_id + user_id`

Sinon la page d'authentification reste affichee.

## Domaines email autorises

Le champ utilise est :

```text
companies.allowed_email_domains
```

Il est gere dans les formulaires admin de creation et edition de campagne.
Le format attendu est une liste separee par des virgules :

```text
rolex.com, ch.rolex.com
```

Le champ est obligatoire cote client et cote serveur.
La validation serveur verifie qu'au moins un domaine exploitable est fourni.

Le matching est volontairement strict :

```text
prenom@rolex.com      accepte pour rolex.com
prenom@fake-rolex.com refuse pour rolex.com
```

## Mot de passe

Le mot de passe est un code personnel de 6 caracteres, compose de lettres majuscules et de chiffres faciles a saisir.

Il n'est pas temporaire au sens d'une expiration automatique.
En revanche, si l'utilisateur redemande un code d'acces ou utilise le flux `Mot de passe oublie`, un nouveau mot de passe remplace l'ancien.

Le mot de passe est stocke hashe grace au cast Laravel existant sur `users.password`.
Il n'est jamais stocke en clair en base.

## Email

Le projet utilise le systeme mail Laravel deja present :

```php
Mail::send(new CoBrandedAccessCodeMail(...));
```

Le template email est sobre :

- logo HUG ;
- logo entreprise si disponible ;
- couleurs issues du co-branding ;
- email utilisateur ;
- mot de passe ;
- bouton vers la campagne.

Le QR code n'est pas integre dans l'email pour eviter une dependance serveur supplementaire et des problemes de compatibilite client mail.
Le passage mobile reste gere par la popup QR deja presente cote front.

## Securite

Mesures appliquees :

- validation Laravel des emails ;
- validation stricte des domaines autorises ;
- mot de passe hashe en base ;
- association explicite utilisateur/collecte via `collections_users` ;
- role employe limite a `user` ;
- routes admin toujours protegees par `auth` + `role:superadmin,admin` ;
- rate limit Laravel :
  - 3 demandes de code par minute par IP + email ;
  - 5 tentatives de login par minute par IP + email ;
- `appointmentUrl` n'est injecte au front que si la session est autorisee.

## Test local des emails

Mailpit est utilise comme SMTP local.

Configuration locale `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

Interface Mailpit :

```text
http://localhost:8025
```

Commandes utiles :

```bash
brew services start mailpit
brew services stop mailpit
```

## Fichiers crees

```text
app/Http/Controllers/CoBrandedAuthController.php
app/Mail/CoBrandedAccessCodeMail.php
app/Rules/EmailDomainListRule.php
app/Support/EmailDomainList.php
resources/js/components/public/CoBrandedAuthGate.vue
resources/views/emails/cobranded-access-code.blade.php
md_architecture/auth-cobranded-campaigns.md
```

## Fichiers modifies

```text
app/Http/Controllers/CoBrandedCollecteController.php
app/Http/Requests/Admin/StoreCompanyRequest.php
app/Http/Requests/Admin/UpdateCompanyRequest.php
app/Models/Collection.php
app/Models/User.php
resources/js/pages/admin/CompanyCreatePage.vue
resources/js/pages/admin/CompanyEditPage.vue
resources/js/pages/coBranded/CoBrandedCollectePage.vue
routes/web.php
tests/Feature/CoBrandedCollecteAccessTest.php
```

## Verification effectuee

```bash
php artisan test
npm run type-check
npm run build
```
