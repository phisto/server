<?php

require_once 'db/Db_buckutt.class.php';
require_once 'db/Mysql.class.php';

function LoadKey( $keyfile, $pub=true, $pass='' ) {         // chargement de la clé (publique par défaut)

    $fp = $filedata = $key = FALSE;                         // initialisation variables
    $fsize =  filesize( $keyfile );                         // taille du fichier
    if( !$fsize ) return FALSE;                             // si erreur on quitte de suite
    $fp = fopen( $keyfile, 'r' );                           // ouverture fichier
    if( !$fp ) return FALSE;                                // si erreur ouverture on quitte
    $filedata = fread( $fp, $fsize );                       // lecture contenu fichier
    fclose( $fp );                                          // fermeture fichier
    if( !$filedata ) return FALSE;                          // si erreur lecture, on quitte
    if( $pub )
        $key = openssl_pkey_get_public( $filedata );        // recuperation de la cle publique
    else                                                    // ou recuperation de la cle privee
        $key = openssl_pkey_get_private( array( $filedata, $pass ));
    return $key;                                            // renvoi cle ( ou erreur )
}

// comme precise la documentation Paybox, la signature doit être
// obligatoirement en dernière position pour que cela fonctionne

function GetSignedData( $qrystr, &$data, &$sig ) {          // renvoi les donnes signees et la signature

    $pos = strrpos( $qrystr, '&' );                         // cherche dernier separateur
    $data = substr( $qrystr, 0, $pos );                     // et voila les donnees signees
    $pos= strpos( $qrystr, '=', $pos ) + 1;                 // cherche debut valeur signature
    $sig = substr( $qrystr, $pos );                         // et voila la signature
    $sig = base64_decode( urldecode( $sig ));               // decodage signature
}

// $querystring = chaine entière retournée par Paybox lors du retour au site (méthode GET)
// $keyfile = chemin d'accès complet au fichier de la clé publique Paybox

function PbxVerSign( $qrystr, $keyfile ) {                  // verification signature Paybox

    $key = LoadKey( $keyfile );                             // chargement de la cle
    if( !$key ) return -1;                                  // si erreur chargement cle
//  penser à openssl_error_string() pour diagnostic openssl si erreur
    GetSignedData( $qrystr, $data, $sig );                  // separation et recuperation signature et donnees
    return openssl_verify( $data, $sig, $key );             // verification : 1 si valide, 0 si invalide, -1 si erreur
}

function mymail() {
	global $_GET, $CheckSig;
     $to      = 'mattgu74@gmail.com';
     $subject = 'CommandSports.fr : Paiement en ligne : Action : '. $_GET['action'];
     $message = 'La signature a renvoyé : '. $CheckSig." \r\n" . $message." \r\n" . var_export($_GET,true);
     $headers = 'From: payutc@assos.utc.fr' . "\r\n" .
     'Reply-To: mattgu74@gmail.com' . "\r\n" .
     'X-Mailer: PHP/' . phpversion();

     mail($to, $subject, $message, $headers);
}


$pos = strrpos( $_SERVER["REQUEST_URI"], '?' );                         // cherche dernier separateur
$data = substr( $_SERVER["REQUEST_URI"], $pos+1 ); 

if($_GET['action'] == 'retour') {                 
	$data = substr( $data, 14 ); 
}

// Verification de la signature (1 = BON)
$CheckSig = PbxVerSign( $data, 'pubkey.pem' );

mymail();

$montant=$_GET['amout'];
$cmd=$_GET['ident'];

$auto=$_GET['auto'];
$trans=$_GET['trans'];


if($_GET['action'] == 'erreur') { // On a une erreur on se fout un peu de la signature
	$num_err=$_GET['NUMERR'];
	$resa->paiementErreur($num_err);
	if ( $num_err == -1 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : erreur de lecture des paramètres via stin. <br>");
		}
	else if ( $num_err == -2 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : erreur d'allocation mémoire. <br>");
		}
	else if ( $num_err == -3 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : erreur de lecture des paramètres QUERY_STRING ou CONTENT_LENGTH. <br>");
		}
	else if ( $num_err == -4 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_RETOUR, PBX_ANNULE, PBX_REFUSE ou PBX_EFFECTUE sont trop longs (<150 caractères). <br>");
		}
	else if ( $num_err == -5 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : ouverture de fichiers (si PBX_MODE contient 3) : fichier local inexistant, non trouvé ou erreur d'accès. <br>");
		}

	else if ( $num_err == -6 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : ouverture de fichiers (si PBX_MODE contient 3) : fichier local mal formé, vide ou ligne mal formatée. <br>");
		}
	else if ( $num_err == -7 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : Il manque une variable obligatoire. <br>");
		}
	else if ( $num_err == -8 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : Une variable numérique contient un caractère non numérique. <br>");
		}
	else if ( $num_err == -9 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_SITE contient un numéro de site qui ne fait pas exactement 7 caractères. <br>");
		}
	else if ( $num_err == -10 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_RANG contient un numéro de rang qui ne fait pas exactement 2 caractères. <br>");
		}
	else if ( $num_err == -11 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_TOTAL fait plus de 10 ou moins de 3 caractères numériques. <br>");
		}
	else if ( $num_err == -12 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_LANGUE ou PBX_DEVISE contient un code qui ne fait pas exactement 3 caractères. <br>");
		}
	else if ( $num_err == -16 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_PORTEUR ne contient pas une adresse e-mail valide. <br>");
		}
	else if ( $num_err == -17 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_CLE ne contient pas une clé (mot de passe) valide. <br>");
		}
	else if ( $num_err == -18 )
		{
		print ("<center><b><h2>Erreur appel PAYBOX.</h2></center></b>");
		print ("<br><br><br>");
		print (" message erreur : PBX_RETOUR : Données à retourner inconnues. <br>");
		}

	print("<b>Numéro de l'erreur : </b>$num_err\n");
	exit();
} else if($_GET['action'] == 'annule') { // On a une annulation
		//mymail();
		if($CheckSig)
			$resa->paiementAnnule();
		else
			echo 'Erreur Signature';
		
} else if($_GET['action'] == 'refuse') { // la transaction a ete refuse
		//mymail();
		if($CheckSig)
			$resa->paiementRefuse();
		else
			echo 'Erreur Signature';
} else if($_GET['action'] == 'effectue') { // a priori ça a l'air bon
		//mymail();
		if($CheckSig)
			$resa->paiementEffectue($trans,$token,$auto);
		else
			echo 'Erreur Signature';
} else if($_GET['action'] == 'retour') { // le bordel envoit direct du serveur :)
		if($CheckSig)
			$resa->paiementEffectue($trans,$token,$auto);
		else
			echo 'Erreur Signature';
		exit(); // SUR CETTE URL IL NE FAUT RIEN RETOURNER
} else { // Je sais pas 
	echo 'Erreur inconnu';
}




$db = Db_buckutt::getInstance();

$amount = "1000";
$usrid = "9422";


//$db->query("UPDATE ts_user_usr SET usr_credit = (usr_credit + '%u') WHERE usr_id = '%u';", Array($amount, $usrid));
//$db->query(("INSERT INTO t_recharge_rec (rty_id, usr_id_buyer, usr_id_operator, poi_id, rec_date, rec_credit, rec_trace) VALUES ('%u', '%u', '%u', '%u', NOW(), '%u', '%s')"), array(3, $rtn[2], $rtn[2], 1, $rtn[1], $trace));
