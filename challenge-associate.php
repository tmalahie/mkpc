<?php
function challengeAssociate($clTable,$clCid,$clId) {
	global $identifiants;
	mysql_query('UPDATE `mkclrace` SET type="'.$clTable.'",circuit="'.$clCid.'" WHERE id="'.$clId.'" AND identifiant='. $identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
}
?>