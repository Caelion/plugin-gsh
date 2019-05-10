# Description

Ce plugin permet de :

- utiliser l'implementation native de Google pour la gestion des objets connectés
- utiliser les interactions de jeedom en utilisant "Parler a ..."

Il peut fonctionner suivant 2 manières :

- Standalone : vous créer les applications en mode dev coté Google puis connectez votre Jeedom au service Google
- Cloud : en passant par un service cloud mis à disposition par Jeedom (non disponible pour le moment car en cours de validation chez Google)

# Mode cloud

> **IMPORTANT**
>
> Il est important de faire les étapes suivantes dans l'ordre indiqué !!!

Après l'installation du plugin, il vous suffit d'aller sur la configuration du plugin, de selectionner le mode Cloud puis de cliquer sur "Envoyer" (cela va envoyer les informations de connexion à l'api du plugin sur le market)

Ensuite sur le market dans votre profil partie "Mes Services" puis bouton "Configuration" des assistants vocaux cocher "Activer Google Smarthome" et sauvegarder.

Il vous faut maintenant attendre 24h le temps que votre demande soit prise en compte.

> **IMPORTANT**
>
> Suite à l'activation et/ou modification des informations pour Google Smarthome il faut attendre 24h pour que cela soit prise en compte

# Mode standalone

> **IMPORTANT**
>
> Le mode standalone est une "arnaque" car cela vous fait créer une application chez Google en mode développement qui ne permet la synchronisation que pendant 3 jours après activation du mode test. Il faut donc lors de l'ajout ou modification des équipements réactiver le mode test tous les 3 jours pour pouvoir faire une synchronisation. Attention on parle bien d'une modification de la configuration si vous n'ajoutez pas ou ne modifiez pas d'équipements il n'y a rien a faire l'application marchera sans limite de temps

> **IMPORTANT**
>
> Etant donné que l'application est une application de dev coté Google ils peuvent à tout moment la couper, la supprimer ou changer les régles. Dans ce cas Jeedom ne pourra aucunement être tenu responsable en cas de mauvais fonctionnement.

> **IMPORTANT**
>
> Pour que la ou les applications marchent il faut absolument que votre jeedom soit en https avec un certificat valide (si vous avez un service pack l'accès DNS est la pour ca)

Pour rappel le plugin permet de :

- utiliser l'implementation native de Google pour la gestion des objets connectés
- utiliser les interactions de jeedom en utilisant "Parler a ..."

Il y a donc 2 applications coté Google : une pour la partie Google Smarthome et une pour la partie intéraction (vous pouvez bien sur n'en faire que une des 2)

## Application Google Smarthome

Allez [ici](https://console.actions.google.com) puis cliquez sur "Add/import project"

![gsh](../images/gsh7.png)

Donnez un nom au projet changez les langue et region par defaut et validez :

![gsh](../images/gsh8.png)

Sélectionnez "Home control" :

![gsh](../images/gsh9.png)

Cliquez sur "Smarthome" :

![gsh](../images/gsh10.png)

Cliquez sur "Name your Smart Home action" :

![gsh](../images/gsh11.png)

Mettez "test smarthome" et validez (bouton Save en haut a droite) :

![gsh](../images/gsh12.png)

Ensuite à gauche cliquez sur "Actions" :

![gsh](../images/gsh13.png)

Puis "Add your first action" :

![gsh](../images/gsh14.png)

Il va falloir donner ici l'url d'arriver de Jeedom. Pour l'avoir c'est très simple, il faut dans Jeedom aller sur la page de gestion du plugin, bien choisir le mode "Standalone" (et enregistrer au passage), l'url est celle qui s'appelle "Fulfillment URL"

![gsh](../images/gsh15.png)

Ensuite allez dans "Account linking" (menu de gauche) :

![gsh](../images/gsh13.png)

Selectionnez "No, I only want to allow account creation on my website" et faite "Next" :

![gsh](../images/gsh16.png)


Selectionnez "OAuth" puis "Autorization code" et faite "Next" :

![gsh](../images/gsh17.png)

Remplissez ensuite les 4 champs en fonction de la page de configuration du plugin, puis faite "Next" :

![gsh](../images/gsh18.png)

> **NOTE**
>
> Les champs sont :
> - le client ID en premier
> - puis le client secret
> - puis "Authorization URL" (attention à bien prendre celle de la partie "Smarthome")
> - puis "Token URL" (attention à bien prendre celle de la partie "Smarthome")

Il n'y a rien a faire la simplement "Next" :

![gsh](../images/gsh19.png)

Ecrivez "Toto" puis faite "Save" :

![gsh](../images/gsh20.png)

Il faut maintenant configuré la clef API homegraph, pour cela allez [ici](https://console.developers.google.com/apis/dashboard), puis à droite cliquez sur "Bibliothèque" :

![gsh](../images/gsh21.png)

> **NOTE**
>
> Si vous n'avez pas de projet il vous faut en créer un

Et cherchez "Homegraph" :

![gsh](../images/gsh22.png)

Cliquez sur homegraphapi puis activez l'api et cliquez sur gerer :

![gsh](../images/gsh23.png)

Cliquez sur identifiant à gauche :

![gsh](../images/gsh24.png)

Puis sur "Identifiants des API et services" :

![gsh](../images/gsh25.png)

Cliquez sur "Créer des identifiants" puis sur "Clé API" :

![gsh](../images/gsh26.png)

Copiez la clef API générées et collez la sur la page de gestion du plugin Google Smarthome dans "Homegraph API Google"

Derniere étape à faire donner l'id projet à Jeedom pour la gestion de la connexion, allez [ici](https://console.actions.google.com) puis cliquez sur votre projet. Recuperez l'url de la forme "https://console.actions.google.com/u/0/project/monprojet-31023/overview", l'id du projet est entre project/ et /overview, dans l'exemple ici c'est "monprojet-31023", copiez cet id sur la page de gestion du plugin Google Smarthome dans "ID du projet Smarthome".

Voila vous pouvez maintenant cliquez à gauche sur "Action" :

![gsh](../images/gsh27.png)

Puis sur test :

![gsh](../images/gsh28.png)

Il vous faut ensuite sur un smartphone **android**(obligatoirement ne marche pas sur un iphone) vous connecter a votre Jeedom par**l'url externe** de celui-ci. Il faut ensuite aller dans l'application home puis "configurer ou ajouter" puis "Configurer un appareil" et enfin cliquer sur "Fonctionne avec Google" et la ajouter votre service crée plus haut (il commence par [test])

## Configuration jwt

JWT permet de remonter automatiquement tout changement d'état d'un équipement transmis à Google, cela permet donc d'avoir plus rapidement l'état lors d'une demande à Google Home ou sur l'application Google Home. Il n'est pas obligatoire de le configurer par contre si vous cochez la case "Remonter l'état" il faut absolument l'avoir fait pour que ca marche.

Pour faire la configuration il faut

* aller [ici](https://console.developers.google.com/iam-admin/serviceaccounts), si Google vous le demande il faut choisir le projet (celui créé juste au dessus).
* Cliquer sur créer un compte de service
  * Lui donner un nom (vous pouvez mettre ce que vous voulez, évitez )
  * Récuperer le mail généré juste en dessous pour le copier dans la configuration du plugin sur Jeedom (champs : Mail client (JWT))
  * Cliquez sur créer
  * Cliquez sur continuer sans rien modifier
  * Cliquez sur  "Créer une clé"
    * Laissez JSON et cliquez sur OK
  * Cliquez sur OK
  * Ouvrez le fichier téléchargé et copier la partie 'private_key' (commence par "-----BEGIN PRIVATE KEY-----", inclus et fini par "-----END PRIVATE KEY-----\n", inclus sans les ") dans la configuration du plugin sur Jeedom (champs : Clef privé (JWT))

## Application Intéraction

A venir

# Plugin configuration

Sur votre Jeedom, allez sur Plugin -> Communication -> Google Smarthome et dans la partie équipement sélectionnez les équipements à transmettre à Google ainsi que le type de l'équipement.

![gsh](../images/gsh2.png)

> **IMPORTANT**
>
> Le plugin se base sur les types génériques de Jeedom des commandes pour piloter votre domotique. Il est donc très important de configurer ceux-ci correctement.

> **NOTE**
>
> Pour le type "caméra" vous devez absolument configurer l'URL du flux (nous avons testé seulement le RTSP) pour que cela marche.
> A noter que le support des caméras est pour le moment en beta et consomme enormement de ressources

## Device

Sur les équipements vous pouvez configurer :

* Options :
  * Transmettre : envoi l'équipement au Google Home pour qu'il puisse le piloter (attention il faut que la configuration des génériques type soient OK)
  * Remonter l'état : envoi toute informations de changement d'état à Google directement (cela évite que lors d'une demande d'information Google Home interroge Jeedom). Voir partie "Configuration JWT"
  * Challenge [Aucun,Code] : si en mode code alors Google vous demandera un Code (celui indiqué juste en dessous) pour chaque action sur l'équipement
* Status : indique si la transmission est OK, si c'est NOK alors cela vient des Générique type
* Type : indique le type d'équipements
* Pseudo : pseudo de l'équipement, si vide alors c'est le nom de l'équipement qui est utilisé
* Action :
  * Permet la configuration avancé de l'équipement pour Google Home (dépend du type utilisé)
  * Configuration avancée de l'équipements, permet d'accéder à la configuration avancée des commandes et donc de modifier les types générique
  * Permet d'aller directement sur la page de configuration de l'équipement

### Type

Les types d'équipements sont important cela permet à Google de réagir en fonction des phrases que vous dite.

> **IMPORTANT**
>
> Jeedom support des types "beta" non encore documenté chez Google donc qui peuvent marcher ou non en fonction des mises à jour chez google

* Lumière : supporte l'allumage/l'arret, le changement de couleur, le dimming....
* Thermostat : support le changement de consigne et les modes (de maniere limité seul certain nom de mode sont autorisé par Google, attention a bien faire la configuration avancée)
* Prise : marche/arret
* Caméra : en beta, peut afficher le flux sur une télé Android sur le réseaux local
* Store : permet d'ouvrir/fermer un store (vous pouvez inverser le sens dans la configuration avancée)
* Volet : permet d'ouvrir/fermer un volet (vous pouvez inverser le sens dans la configuration avancée)
* Fenêtre [beta] : supporte normalement le status ouvert/fermé d'une fenetre (pour information il n'y a que si je pose la question en anglais que ca marche)
* Porte [beta] : supporte normalement le status ouvert/fermé d'une porte (pour information il n'y a que si je pose la question en anglais que ca marche)
* Alarme [beta] : permet d'armer/désarmer l'alarme (attention il faut employer le terme arme/désarme pour que Google fasse l'action)
* Verrou [beta] : permet de fermer/ouvrir un équipement
* TV [beta] : permet de changer le volume et les chaines
* Enceinte [beta] : permet de changer le volume et de faire stop,pause,suivant,precedent,reprendre

## Scénario

Vous pouvez aussi créer des scènes dans l'onglet scène, avec des actions d'entrée et de sortie.

![gsh](../images/gsh3.png)

> **NOTE**
>
> Lors de la sauvegarde Jeedom va automatiquement demander une synchronisation avec Google. Attention en mode standalone si vous avez une erreur (type "Requested entity was not found") essayez de réactiver le test de l'application ([ici](https://console.actions.google.com)) puis de recliquez sur le bouton de connection sur la page la page de configuration du plugin. Vérifiez aussi la configuration (surtout la partie oauth) de votre application google (desfois les champs sont effacés par Google)

Il ne vous reste plus qu'a faire l'affectation des équipements aux pieces dans l'application Google Home

# FAQ

>**Quelles sont les commandes possibles ?**
>
>Les commandes vocales (ainsi que les retours) sont gérés uniquement pas Google, voila la [documentation](https://support.google.com/googlehome/answer/7073578?hl=fr)

>**L'assistant me demande d'affecter les pieces mais je ne sais pas à quoi correspond l'équipement.**
>
>Oui l'assisant n'affiche pas le nom réel de l'équipement, juste le pseudo. Il faut donc quitter l'assistant et revenir sur la page d'acceuil du controle de la maison. La en cliquant sur l'équipement vous allez avoir son nom vous pourrez ensuite l'affecter à une piece

>**Pourquoi faut-il affecter des pièces à chaque équipement ?**
>
>Car Google ne permet pas de le faire par l'API vous devez donc absolument le faire manuellement.

>**J'ai le message d'erreur "OpenSSL unable to sign data"**
>
>Vous avez du cocher la case "Remonter l'état" sans faire la configuration JWT

>**J'ai le message d'erreur "Cant find ressource 404" lors de la sauvegarde**
>
>Alors pas de soucis tout est bien sauvé, c'est juste la synchronisation automatique qui n'est pas faites. Pour la faire dites "Synchroniser mes appareils" à votre assistant. Pour tenter de corriger ce soucis :
> allez sur la page suivante [ici](https://console.cloud.google.com/cloud-resource-manager). Sur cette page vous verrez la liste de tous les projets même ceux invisibles sur la page google actions. Supprimer tous les projets inutilisés.
> Puis refaites le tuto à partir de l'étape configurer la clef api Homegraph (activation, génération de clé, puis dissocier jeedom de votre compte google et refaites le lien)
