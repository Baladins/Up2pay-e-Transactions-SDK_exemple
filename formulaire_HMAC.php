<?php

// --------------- FONCTION DE FORMATAGE DES DONNEES --------------
function Formatage($value, $maxLength) {
        $value = strtoupper(Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC')->transliterate($value));
        $value = preg_replace('/[^A-Z0-9\s]/', '', $value);
        $value = preg_replace('/\\s+/', ' ', $value);
        $value = substr($value, 0, $maxLength);
    return trim($value);
}

// --------------- D�CLARATION DES VARIABLES ---------------

$pbx_site = 'votre n� de site';															//Num�ro de site
$pbx_rang = 'votre n� de rang';															//Num�ro de rang
$pbx_identifiant = 'votre n� d identifiant site';										//Identifiant de site
$pbx_total = 'votre montant';															//Montant de la commande
// Suppression des points ou virgules dans le montant						
$pbx_total = str_replace(",", "", $pbx_total);
$pbx_total = str_replace(".", "", $pbx_total);

$pbx_cmd = 'votre n� de commande';														//Num�ro de commande
$pbx_porteur = 'email de l acheteur';													//Email de l'acheteur

// Param�trage de l'url de retour back office site (notification de paiement IPN) :
$pbx_repondre_a = 'https://www.votre-site.extention/page-de-back-office-site';

// Param�trage des donn�es retourn�es via l'IPN :
$pbx_retour = 'Mt:M;Ref:R;Auto:A;Erreur:E';

// Param�trage des urls de redirection navigateur client apr�s paiement :
$pbx_effectue = 'https://www.votre-site.extention/accepte.php';
$pbx_annule = 'https://www.votre-site.extention/annule.php';
$pbx_refuse = 'https://www.votre-site.extention/refuse.php';

// On r�cup�re la date au format ISO-8601 :
$dateTime = date("c");

// Nombre de produit envoy� dans PBX_SHOPPINGCART :
$pbx_nb_produit = 'nombre de produit dans le panier';									//Nombre de produits dans le panier
// Construction de PBX_SHOPPINGCART :
$pbx_shoppingcart = "<?xml version=\"1.0\" encoding=\"utf-8\"?><shoppingcart><total><totalQuantity>".$pbx_nb_produit."</totalQuantity></total></shoppingcart>";
// Choix de l'authentification dans PBX_SOUHAITAUTHENT
$pbx_souhaitauthent = '02';		//Variable de souhait authentification 3DS (01 par d�faut, 02 pour exemption 3DS)
if($pbx_total > 3000) {
	$pbx_souhaitauthent = '01';	// V�rification du montant maximal pour l'exemption 3DS
}

// Valeurs envoy�es dans PBX_BILLING :
$pbx_prenom_fact = Formatage('prenom de l utilisateur de facturation', 22);		//Variable pr�nom du porteur
$pbx_nom_fact = Formatage('nom de l utilisateur de facturation', 22);			//Variable nom du porteur
$pbx_adresse1_fact = Formatage('ligne1 de l adresse de facturation', 50);		//Variable adresse ligne 1 du porteur
$pbx_adresse2_fact = Formatage('ligne2 de l adresse de facturation', 50);		//Variable adresse ligne 2 du porteur
$pbx_zipcode_fact = Formatage('code postal de l adresse de facturation', 16);	//Variable code postal du porteur
$pbx_city_fact = Formatage('ville de l adresse de facturation', 50);			//Variable ville du porteur
$pbx_country_fact = Formatage('code pays iso-3166-1 num�rique de l adresse de facturation', 3);		//Variable pays du porteur
$pbx_country_code_mobile_phone = '+33'											//Variable indicatif pays du numero de telephone mobile du porteur
$pbx_mobile_phone = '0612345675'												//Variable numero de telephone mobile du porteur

// Construction de PBX_BILLING :
$pbx_billing = "<?xml version=\"1.0\" encoding=\"utf-8\"?><Billing><Address><FirstName>".$pbx_prenom_fact."</FirstName>".
				"<LastName>".$pbx_nom_fact."</LastName><Address1>".$pbx_adresse1_fact."</Address1>".
				"<Address2>".$pbx_adresse2_fact."</Address2><ZipCode>".$pbx_zipcode_fact."</ZipCode>".
				"<City>".$pbx_city_fact."</City><CountryCode>".$pbx_country_fact."</CountryCode>".
				"<CountryCodeMobilePhone>".$pbx_country_code_mobile_phone."</CountryCodeMobilePhone><MobilePhone>".$pbx_mobile_phone."</MobilePhone>".
				"</Address></Billing>";


// --------------- TESTS DE DISPONIBILITE DES SERVEURS ---------------

$serveurs = array('tpeweb.e-transactions.fr', //serveur primaire
'tpeweb1.e-transactions.fr'); //serveur secondaire
$serveurOK = "";

foreach($serveurs as $serveur){
	$doc = new DOMDocument();
	$doc->loadHTMLFile('https://'.$serveur.'/load.html');
	$server_status = "";
	$element = $doc->getElementById('server_status');
	if($element){
	$server_status = $element->textContent;}
	if($server_status == "OK"){
		// Le serveur est pr�t et les services op�rationnels
		$serveurOK = $serveur;
	break;}
	// else : La machine est disponible mais les services ne le sont pas.
}
//curl_close($ch);
if(!$serveurOK){
die("Erreur : Aucun serveur n'a �t� trouv�");}
// Activation de l'univers de recette
//$serveurOK = 'recette-tpeweb.e-transactions.fr';

//Cr�ation de l'url e-Transactions
$urletrans = 'https://'.$serveurOK.'/php/';
echo "Serveur ".$serveurOK;
echo "<br><br>";


// --------------- S�LECTION DE L'ENVIRONNEMENT ---------------
// Recette (paiements de test)  :
		// $urletrans ="https://recette-tpeweb.e-transactions.fr/php/";

// Production (paiements r�els) :
	// URL principale :
		 $urletrans ="https://tpeweb.e-transactions.fr/php/";
	// URL secondaire :
		// $urletrans ="https://tpeweb1.e-transactions.fr/php/";


// --------------- TRAITEMENT DES VARIABLES ---------------

// On cr�e la cha�ne � hacher sans URLencodage
$msg = "PBX_SITE=".$pbx_site.
"&PBX_RANG=".$pbx_rang.
"&PBX_IDENTIFIANT=".$pbx_identifiant.
"&PBX_TOTAL=".$pbx_total.
"&PBX_DEVISE=978".
"&PBX_CMD=".$pbx_cmd.
"&PBX_PORTEUR=".$pbx_porteur.
"&PBX_REPONDRE_A=".$pbx_repondre_a.
"&PBX_RETOUR=".$pbx_retour.
"&PBX_EFFECTUE=".$pbx_effectue.
"&PBX_ANNULE=".$pbx_annule.
"&PBX_REFUSE=".$pbx_refuse.
"&PBX_HASH=SHA512".
"&PBX_TIME=".$dateTime.
"&PBX_SHOPPINGCART=".$pbx_shoppingcart.
"&PBX_BILLING=".$pbx_billing.
"&PBX_SOUHAITAUTHENT=".$pbx_souhaitauthent;


// --------------- R�CUP�RATION ET FORMATAGE DE LA CL� HMAC ---------------
$hmac = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF'; //Renseignez votre cl� HMAC r�cup�r� depuis le back-office Vision � cet endroit
$pbx_hmac = strtoupper(hash_hmac('sha512', $msg, hex2bin($hmac)));

// La cha�ne sera envoy�e en majuscule, d'o� l'utilisation de strtoupper()
// On cr�e le formulaire � envoyer
// ATTENTION : l'ordre des champs dans le formulaire est extr�mement important, il doit
// correspondre exactement � l'ordre des champs dans la cha�ne hach�e.
?>

<!------------------ ENVOI DES INFORMATIONS A e-Transactions (Formulaire) ------------------>

<form method="POST" action="<?php echo $urletrans; ?>">
<input type="hidden" name="PBX_SITE" value="<?php echo $pbx_site; ?>">
<input type="hidden" name="PBX_RANG" value="<?php echo $pbx_rang; ?>">
<input type="hidden" name="PBX_IDENTIFIANT" value="<?php echo $pbx_identifiant; ?>">
<input type="hidden" name="PBX_TOTAL" value="<?php echo $pbx_total; ?>">
<input type="hidden" name="PBX_DEVISE" value="978">
<input type="hidden" name="PBX_CMD" value="<?php echo $pbx_cmd; ?>">
<input type="hidden" name="PBX_PORTEUR" value="<?php echo $pbx_porteur; ?>">
<input type="hidden" name="PBX_REPONDRE_A" value="<?php echo $pbx_repondre_a; ?>">
<input type="hidden" name="PBX_RETOUR" value="<?php echo $pbx_retour; ?>">
<input type="hidden" name="PBX_EFFECTUE" value="<?php echo $pbx_effectue; ?>">
<input type="hidden" name="PBX_ANNULE" value="<?php echo $pbx_annule; ?>">
<input type="hidden" name="PBX_REFUSE" value="<?php echo $pbx_refuse; ?>">
<input type="hidden" name="PBX_HASH" value="SHA512">
<input type="hidden" name="PBX_TIME" value="<?php echo $dateTime; ?>">
<input type="hidden" name="PBX_SHOPPINGCART" value="<?php echo htmlspecialchars($pbx_shoppingcart); ?>">
<input type="hidden" name="PBX_BILLING" value="<?php echo htmlspecialchars($pbx_billing); ?>">
<input type="hidden" name="PBX_SOUHAITAUTHENT" value="<?php echo $pbx_souhaitauthent; ?>">
<input type="hidden" name="PBX_HMAC" value="<?php echo $hmac; ?>">
<input type="submit" value="Envoyer">
</form>