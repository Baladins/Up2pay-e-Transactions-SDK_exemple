# Up2pay e-Transactions
Last update 23/04/2024

Version modifié et corrigé du "Kit d'intégration PHP" Disponible à l'addresse suivante:
- https://www.ca-moncommerce.com/espace-client-mon-commerce/up2pay-e-transactions/telecharger-mes-modules/php/

## A propos
Le fichier formulaire_HMAC.php est un exemple de fichier permettant l'accès à la plateforme e-Transactions. 

Pour rediriger l’internaute sur la page de paiement e-Transactions, des variables sont obligatoires :
     •    Des variables fixes propres au contrat : Site, rang, identifiant…. 
     •    Des variables propres à la commande : référence commande, montant, mode de paiement, email du client, date et heure…
     •    L'authentification par clé HMAC a ainsi été mise en place pour vérifier l’authenticité du site commerçant via l’utilisation d’une donnée secrète commune.

Etapes du script de paiement e-Transactions HMAC : 
     1.   Déclaration des variables
     2.   Création de la chaine à hasher ($msg) contenant les variables dans un ordre défini X
     3.   Hashage de la chaine à hasher avec un algorithme de hash + la clé HMAC transformée en binaire + $msg = PBX_HMAC
     4.   Envoi du formulaire contenant l’url à appeler (production ou préproduction) + les variables et leur valeur respective dans le même ordre X (dont l’algorithme utilisé) + PBX_HMAC précédemment calculé

Vous accédez à la page de paiement dans le cas où tout est respecté. 
Le cas échéant un message d’erreur s'affichera sur la page de paiement.

Le fichier formulaire_HMAC_iFrame.php permet de faire la même chose que le fichier formulaire_HMAC.php mais avec un paiement en iFrame au lieu d'avoir une redirection.
Les instructions ci-dessous sont donc valables pour les deux fichiers.

## Carte de test

En environnement de test, vous pouvez utiliser la carte suivante:
- Numéro de carte : 1111222233334444
- CVV : 123
- Date Val : exemple 12/24

## Adresses IP entrantes/sortantes

```
Plateforme      Produit         Domaine                             IP en entrée            IP en sortie
Production     
                Redirection     tpeweb.e-transactions.fr            194.2.160.85            194.2.122.190
                                tpeweb1.e-transactions.fr           195.25.67.12            195.25.67.22
               GAE  
                                ppps.e-transactions.fr              194.2.160.89   
                                ppps1.e-transactions.fr             195.25.67.10   

Recette   
                Redirection    
                                recette-tpeweb.paybox.com           195.25.7.146  195.25.67.22
                                preprod-tpeweb.e-transactions.fr    195.25.7.147  195.25.67.22
                                preprod-tpeweb.paybox.com           195.25.7.146  195.25.67.22
                GAE  
                                preprod-ppps.e-transactions.fr      195.25.7.147  
```

## FAQ


Question: Vous rencontrez un message d'erreur sur la page de paiement

Réponse: Vérifiez la cohérence des variables saisies: exemple PBX_TOTAL doit être supérieur à 100, PBX_Porteur doit contenir une adresse mail, ...
Vérifiez également que la clé HMAC utilisée a bien été confirmée/validée par mail, suite à sa génération sur Vision.


Question: Vous recevez un mail intitulé Warning e-Transactions (= la commande ne se valide pas sur votre boutique)

Réponse: Lorsqu'une transaction est créée ou que son statut change, un IPN est envoyé à l’URL de notification que vous aurez définie via PBX_REPONDRE_A.

Si le serveur commerçant n’accuse pas réception de ce retour IPN, alors un mail de Warning est envoyé par mail aux adresses définies dans le back office Up2pay e-Transactions
Ce warning contient le code erreur renvoyé: il s’agit généralement d’un code erreur HTTP ou d’un message d’erreur Curl.

Exemples courants:

- HTTP 301/302/307/308: L’URL de retour IPN n’est pas directement accessible car une redirection temporaire ou permanente est en place.
- HTTP 401: Accès refusé. Vérifier que l'accès à cette URL n'est pas protégée par une authentification par login/mot de passe.
- HTTP 414: URL trop longue.
- HTTP 500 : Le serveur a échoué à accomplir une demande apparemment valable. Vérifier que le format de l'URL PBX_REPONDRE_A est bien valide
- HTTP 503 (service indisponible): Vérifier que le site commerçant n'est pas en maintenance
- Operation timed out after 20001 milliseconds with 0 bytes received (code http: 0 – code curl: 28): Correspond à un time-out. Causes possibles: Serveur saturé, proxy mal paramétré, ...
- SSL certificate problem: unable to get local issuer certificate: Le certificat SSL n’est pas totalement opérationnel et pose problème lors des contrôles de sécurité (vérifications CURL). Vérifier qu’il ne manque pas l’installation d’un certificat intermédiaire sur votre plate-forme.

Pour plus d'informations, rendez-vous sur https://www.ca-moncommerce.com/espace-client-mon-commerce/e-transactions/
