# Fonctionnalites interactives

Ce document decrit les fonctionnalites interactives deja prototypees dans le front Vue.
Il sert de reference pour comprendre leur fonctionnement actuel, leurs fichiers, leurs limites et leur evolution future.

Pour le moment, seules deux fonctionnalites sont documentees :

- prototype swipe type Tinder ;
- prototype conversation SMS gamifiee.

## Principes generaux

- Les prototypes actuels sont des experiences front-end.
- Ils sont affiches sur la page d'accueil publique pendant la phase de test.
- Ils ne dependent pas encore de la base de donnees.
- Les contenus de test sont stockes en dur cote front.
- Les futures versions pourront etre reliees aux pages co-brandees, aux campagnes et aux donnees admin quand l'UML et le modele de donnees seront valides.

## Prototype swipe type Tinder

### Objectif

Le prototype swipe sert a tester une interaction ludique de type cartes a balayer.
L'intention metier future est de l'utiliser comme premiere etape d'un parcours d'eligibilite ou d'orientation.

Dans le userflow cible, ce module peut jouer le role d'un premier filtre :

```text
Test ludique -> match / pas match -> suite du parcours conversationnel
```

Pour le moment, le prototype n'est pas encore connecte au chat SMS.

### Fichiers concernes

```text
resources/js/pages/public/HomePage.vue
resources/js/components/tinder-cards/TinderCard.vue
resources/js/components/tinder-cards/TinderActions.vue
```

### Librairie utilisee

Le swipe repose sur la librairie :

```text
vue3-flashcards
```

Elle est utilisee dans `HomePage.vue` via le composant `FlashCards`.

### Donnees actuelles

Les cartes sont definies directement dans `HomePage.vue` sous forme de tableau reactive `items`.

Chaque carte contient actuellement :

```text
id
text
description
image
```

Ces donnees sont temporaires et ne representent pas encore le contenu final lie au don du sang.

### Comportement actuel

Le composant `FlashCards` recoit :

- la liste des cartes ;
- les directions autorisees : gauche, droite, haut ;
- un seuil de swipe ;
- les parametres d'empilement visuel.

Les directions actuelles sont :

```text
left  -> refus / non
right -> validation / oui
top   -> super like
```

Les callbacks appellent `handleSwipe(item, direction)`.
Pour le moment, cette fonction ecrit simplement le resultat dans la console.

### UI

`TinderCard.vue` gere l'affichage d'une carte :

- image en fond ;
- titre ;
- description ;
- degrade sombre en bas pour la lisibilite ;
- arrondi et ombre.

`TinderActions.vue` gere les boutons d'action :

- retour ;
- non ;
- top ;
- oui ;
- passer.

Ces boutons pilotent les actions exposees par `vue3-flashcards`.

### Limites actuelles

- Les contenus sont encore des placeholders.
- Aucun score n'est calcule.
- Aucun resultat n'est stocke.
- Le module ne declenche pas encore le chat SMS.
- Les criteres d'eligibilite reels ne sont pas encore modelises.

### Evolution prevue

Quand le modele de donnees sera valide, le module pourra evoluer vers :

- des cartes liees a des criteres ou situations ;
- un calcul de compatibilite ;
- une redirection conditionnelle vers le chat SMS ;
- une integration dans une page co-brandee ;
- un suivi statistique anonyme par campagne.

## Prototype conversation SMS gamifiee

### Objectif

Le prototype SMS sert a tester une deuxieme etape de parcours sous forme de conversation.
Il fonctionne comme un mini-jeu de questions / reponses, avec des choix fermes.

L'objectif est de simuler une discussion simple et rassurante autour du don du sang :

- freins ;
- motivation ;
- premiere fois ;
- retour au don ;
- disponibilites ;
- collecte en entreprise ;
- passage vers une prise de rendez-vous.

Le champ libre est volontairement exclu pour le moment.
Sans LLM ni validation metier avancee, les choix fermes sont plus fiables et plus faciles a tester.

### Fichiers concernes

```text
resources/js/components/sms-chat/SmsConversationPrototype.vue
resources/js/data/sms-scenarios.json
resources/js/types/sms-chat.ts
public/img/sanguy/
```

Le composant est affiche dans :

```text
resources/js/pages/public/HomePage.vue
```

### UI et DaisyUI

Le prototype utilise les composants CSS DaisyUI :

```text
chat
chat-start
chat-end
chat-header
chat-image
chat-bubble
avatar
avatar-placeholder
btn
loading
```

DaisyUI n'est pas une librairie React ou Vue.
C'est une couche de classes CSS basee sur Tailwind CSS.
Elle est donc utilisee directement dans les templates Vue.

### Structure de la section

La section SMS occupe un viewport complet :

```text
height: 100svh
width: 100vw
```

Le contenu interne est scrollable.
Quand un nouveau message ou de nouveaux choix apparaissent, le composant force le scroll vers le bas pour garder le fil actif visible.

### Donnees du scenario

Le scenario est stocke en JSON dans :

```text
resources/js/data/sms-scenarios.json
```

Il contient :

- un noeud de depart `start` ;
- une collection de `nodes` ;
- des questions ;
- des reponses ;
- des noeuds finaux avec CTA.

Exemple de structure :

```json
{
    "start": "welcome",
    "nodes": {
        "welcome": {
            "id": "welcome",
            "type": "question",
            "speaker": "bot",
            "text": "Je vais te poser quelques questions rapides...",
            "answers": [
                {
                    "id": "start",
                    "label": "C'est parti",
                    "next": "already_donated",
                    "sanguyEmotion": "happy"
                }
            ]
        }
    }
}
```

### Typage TypeScript

Les types sont definis dans :

```text
resources/js/types/sms-chat.ts
```

Les principaux types sont :

```text
SmsScenario
SmsScenarioNode
SmsQuestionNode
SmsAppointmentNode
SmsAnswer
SmsMessage
SanguyEmotion
```

Les emotions Sanguy actuellement supportees sont :

```text
happy
angry
alt-happy
alt-angry
```

### Fonctionnement du parcours

Au chargement du composant :

1. Le scenario JSON est importe.
2. Le noeud de depart est lu.
3. Le premier message Sanguy est ajoute a l'historique.

Quand l'utilisateur choisit une reponse :

1. La reponse est ajoutee comme message utilisateur.
2. Les choix disparaissent.
3. Un indicateur de chargement apparait.
4. Apres un court delai, le prochain noeud est ajoute.
5. L'emotion de Sanguy est mise a jour selon la reponse choisie.
6. Le scroll descend vers le bas.

### Gestion des messages

L'historique est garde dans un tableau `messages`.

Chaque message contient :

```text
id
speaker
text
nodeType
cta
```

Les messages deja envoyes restent visibles.
Les avatars ne sont affiches que sur l'echange actif, pour eviter de repeter Sanguy et LB a chaque message.

### Avatars et personnage Sanguy

Les images de Sanguy sont stockees dans :

```text
public/img/sanguy/
```

Fichiers actuels :

```text
sanguy-angry.png
sanguy-alt-angry.png
sanguy_happy.png
sanguy-alt-happy.png
```

Le composant mappe chaque emotion vers une image publique :

```text
happy     -> /img/sanguy/sanguy_happy.png
angry     -> /img/sanguy/sanguy-angry.png
alt-happy -> /img/sanguy/sanguy-alt-happy.png
alt-angry -> /img/sanguy/sanguy-alt-angry.png
```

L'avatar utilisateur est encore en dur :

```text
LB
```

Il pourra plus tard etre derive de l'email ou du profil utilisateur.

### Choix de reponse

Les choix ne sont pas des `chat-bubble` classiques.
Ils sont affiches comme des rectangles arrondis alignes a droite, pour les distinguer des messages deja envoyes.

Quand un choix est clique, il devient ensuite un vrai message utilisateur dans l'historique.

### Noeud final et CTA

Les fins de scenario utilisent un noeud de type :

```text
appointment
```

Ces noeuds affichent une bulle speciale avec un bouton :

```text
Prendre RDV
```

Le lien est actuellement un placeholder :

```text
#
```

### Limites actuelles

- Le scenario est en JSON local.
- Il n'y a pas encore de base de donnees.
- Il n'y a pas encore de validation runtime du JSON.
- Le chat n'est pas encore connecte au resultat du swipe Tinder.
- Le bouton de prise de rendez-vous est un placeholder.
- Les textes ne sont pas encore administribles.
- Les choix ne sont pas encore lies a des criteres medicaux reels.

### Evolution prevue

Quand l'UML et la DB seront valides, ce prototype pourra evoluer vers :

- des scenarios stockes en base ;
- une edition admin des questions et reponses ;
- un lien entre resultat du swipe et scenario de chat ;
- des contenus differents selon campagne ou entreprise ;
- des statistiques anonymes de parcours ;
- un CTA de rendez-vous fourni par la campagne co-brandee.

## Integration actuelle dans la page d'accueil

Les deux prototypes sont affiches dans :

```text
resources/js/pages/public/HomePage.vue
```

Ordre actuel :

```text
1. Navigation temporaire
2. Prototype Tinder
3. Prototype SMS
```

Cette integration est temporaire.
A terme, l'outil d'eligibilite doit vivre dans le site co-brande, en amont du lien de prise de rendez-vous.

## Tests et verification

Pour verifier les modifications front-end :

```bash
npm run type-check
npm run build
```

Ces commandes ont ete utilisees pendant le developpement du prototype SMS.
