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


$pos = strrpos( $_SERVER["REQUEST_URI"], '?' );                         // cherche dernier separateur
$data = substr( $_SERVER["REQUEST_URI"], $pos+1 ); 

// Verification de la signature (1 = BON)
$CheckSig = PbxVerSign( $data, '../pubkey.pem' );

if($CheckSig==1) {

	$amount=$_GET['amount'];
	$ref = base64_decode($_GET['ident']);
	$usrid=substr($ref,0,strrpos($ref, ';'));

	$auto=$_GET['auto'];
	$trans=$_GET['trans'];
	$trace = $data;

	$db = Db_buckutt::getInstance();

$db->query("UPDATE ts_user_usr SET usr_credit = (usr_credit - 1000) WHERE usr_id = '%u';", Array(9422));

	// TODO :: Il faut vérifier la variable $auto si elle est = à XXXXX.. c'est que c'est une transaction en mode dévellopeur
	//         Si elle n'est pas la (ou code d'erreur? faut lire la doc) ça veut dire que la transaction n'a pas eu lieu.
	$num = $db->numRows($db->query("SELECT rty_id FROM t_recharge_rec WHERE usr_id_buyer='%u' AND rec_trace like '%s'", array($usrid, $trace)));
	echo $num;
	if ($num > 0) {
		// TODO4: LOG le fait qu'un utilisateur à essayé de recharger une seconde fois avec un rechargement déjà éffectué. !!
		echo "BOUH!";
		exit();
	}

	$db->query("UPDATE ts_user_usr SET usr_credit = (usr_credit + '%u') WHERE usr_id = '%u';", Array($amount, $usrid));
	$db->query(("INSERT INTO t_recharge_rec (rty_id, usr_id_buyer, usr_id_operator, poi_id, rec_date, rec_credit, rec_trace) VALUES ('%u', '%u', '%u', '%u', NOW(), '%u', '%s')"), array(3, $usrid, $usrid, 1, $amount, $trace));
} else {
	// TODO3 :: Ajouter des logs dans un fichier
	// Ici il faut loger le fait que la signature est fausse.
}
