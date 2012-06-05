<?php

$SADMIN = @new SoapClient($CONF['soap_url']);

/*
echo "<pre>";
print_r($SADMIN->__getFunctions());
echo "</pre>";
*/

function get_histo($user) {
	$depense = str_getcsv($user->getHistoriqueAchats(0, time()),';','"','\\');
	$recharge = str_getcsv($user->getHistoriqueRecharge(0, time()),';','"','\\');
	$histo = array_merge($depense, $recharge);
	arsort($histo);
	$return = array();
	foreach ($histo as $elt)
	{
		$line = str_getcsv($elt);
		if (count($line) == 7)
		{
			$line['type'] = "DEPENSE";
			$return[] = $line;
		} else if (count($line) == 6) {
			$line['type'] = "RECHARGEMENT";
			$return[] = $line;
		}
	}
	return $return;
}

function format_amount($val) {
	return number_format($val/100, 2, ',', ' ');
}

function affichage_histo($user) {
	$histo = get_histo($user);
	$return = "<table id='historique' class='table table-striped'>";
	foreach ($histo as $elt)
	{
		$return .= '<tr>';
		if ($elt['type'] == "DEPENSE") 
		{
			$return .= '<td>'.date('d/m/y H:i',$elt[0]).'</td><td>'.$elt[1].' ('.$elt[4].')</td><td><span class="label label-important"> <i class="icon-minus icon-white"></i> '.format_amount($elt[6]).' €</span></td>';
		} else {
			$return .= '<td>'.date('d/m/y H:i',$elt[0]).'</td><td>Rechargement</td><td><span class="label label-success"> <i class="icon-plus icon-white"></i> '.format_amount($elt[5]).' €</span></td>';
		}
		$return .= "</tr>";
	}
	$return .= "</table>";
	$return .= '<div class="pagination pagination-centered"><ul id="paging"></ul></div>';
	return $return;
}

