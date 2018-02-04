# Description

Ce plugin permet de faire le lien entre votre jeedom, un service Google Smarthome et Google Smarthome.

Il peut fonctionner suivant 2 manieres : 

- hebergé chez vous avec une app que vous ajouter coté Google (cette méthode ne sera pas décrite ici)
- en passant par un service cloud mise à disposition par Jeedom

# Mise en place de la connexion vers Google Smarthome

> **IMPORTANT**
>
> Il est important de faire les étapes suivantes dans l'ordre indiqué !!!!

## Configuration Market

Après installation du plugin il vous suffit d'aller dans l'administration de Jeedom puis la partie API est de recuperer la clef API du plugin.

Ensuite sur le market dans votre profils partie "Mes Jeedoms" il faut que vous completiez les champs "Google Smarthome" : 

- Activer le service google Smarthome
- Mettez la clef API précedement récuperée

Validez ensuite la configuration. 

Il vous faut maintenant attendre 24h le temps que votre demande soit prise en compte

> **IMPORTANT**
>
> Toute modification d'URL de votre Jeedom ou de clef API necessite d'attendre 24h avant la prise en compte

> **NOTE**
>
> Pour savoir si votre modification est prise en compte il suffit de regarder le status, si c'est actif alors votre configuration est prise en compte

## Configuration du plugin

Allez sur Plugin -> Communication -> Google Smarthome et dans la partie équipement selectionnez les équipements à transmettre à Google ainsi que le type de l'équipement.

> **IMPORTANT**
>
> Le plugin se base sur les types générique des commandes pour piloter votre domotique il est donc très important de configurer ceux-ci correctement

> **NOTE**
>
> Pour le type "caméra" vous devez absoluement configurer l'URL du flux (nous avons testé seulement le RTSP) pour que cela marche.
> A noter que le support des caméras est pour le moment en beta.

Vous pouvez aussi creer des scènes dans l'onglet scène, avec des actions d'entrée et de sortie 

## Configuration Google

Lancez l'application Google Home puis dans le Menu (à gauche) cliquez sur "Contrôle de maison".

Cliquez sur le "+" et cherchez "Jeedom Smarthome" dans la liste, une fois selectionné indiquez vos identifiants Market

L'application va normalement se synchroniser avec vos équipements, vous pourrez ensuite les mettres dans les pieces et piloter vocalement votre domotique

# FAQ

>**Pourquoi faut-il affecter des pièces à chaque équipement ?**
>
>Car Google ne permet pas de le faire par l'API vous devez donc absolument le faire manuellement

>**Pourquoi faut-il attendre 24h pour que l'activation de Google Smarthome soit en place ?**
>
>Car nous avons dédié un serveur juste pour faire le pont avec votre Jeedom et Google Home et celui-ci ne se synchronise que toute les 24h avec le market.

>**Google ne fait pas toujours ce que je dis**
>
> Effectivement lors de nos tests nous avons rencontrer un soucis assez genant : les pluriels et genre.
> Exemple : "Ok google ferme tous les volets" ne marchera pas à cause du s à volet alors que "Ok google ferme volet" marchera

>**Comment faire pour resynchroniser les équipements avec Google**
>
> En lisant simplement le message lors d'une sauvegarde sur la page du plugin, il vous indiquera comment faire