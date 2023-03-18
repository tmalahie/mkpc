<?php
include('getId.php');
include('language.php');
$cids = array();
$editting = true;
$readOnly = false;
include('initdb.php');
require_once('collabUtils.php');
$isBattle = isset($_GET['battle']);
if (isset($_GET['cid'])) {
	$id = intval($_GET['cid']);
	$cupMode = $isBattle*2 + $mode;
	if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $id .'" AND mode="'. $cupMode .'"'))) {
		if (($getCup['identifiant'] == $identifiants[0]) && ($getCup['identifiant2'] == $identifiants[1]) && ($getCup['identifiant3'] == $identifiants[2]) && ($getCup['identifiant4'] == $identifiants[3])) {
			$hasReadGrants = true;
			$hasWriteGrants = true;
		}
		else {
			$collab = getCollabLinkFromQuery('mkcups', $id);
			$hasReadGrants = isset($collab['rights']['view']);
			$hasWriteGrants = isset($collab['rights']['edit']);
		}
		if (!$hasReadGrants) {
			mysql_close();
			exit;
		}
		$readOnly = !$hasWriteGrants;
		for ($i=0;$i<4;$i++)
			$cids[$i] = $getCup['circuit'. $i];
	}
	else
		$editting = false;
}
else {
	if (isset($_GET['nid']))
		$id = intval($_GET['nid']);
	for ($i=0;$i<4;$i++) {
		if (isset($_GET['cid'.$i]))
			$cids[$i] = $_GET['cid'.$i];
		else {
			$editting = false;
			break;
		}
	}
}
require_once('circuitEscape.php');
function escapeUtf8($str) {
	return htmlentities(escapeCircuitNames($str));
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language ? 'en':'fr'; ?>" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="viewport" content="width=device-width" />
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />

<?php
include('o_online.php');
?>
<title><?php echo $language ? 'Create cup':'Créer coupe'; ?></title>
<link rel="stylesheet" href="styles/cup.css?reload=1" />
<script type="text/javascript" src="scripts/creations.js"></script>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var editting = <?php echo $editting ? 'true':'false'; ?>;
var ckey = "cid";
var complete = <?php echo $mode; ?>;
var readOnly = <?php echo $readOnly ? 1:0; ?>;
var isBattle = <?php echo $isBattle ? 1:0; ?>;
<?php
if (isset($cids))
	echo 'var cids = '. json_encode($cids) .';';
?>
</script>
<script type="text/javascript" src="scripts/cup.js"></script>
<script type="text/javascript" src="scripts/posticons.js"></script>
</head>
<body<?php if ($readOnly) echo ' class="readonly"'; ?>>
	<div class="container <?php echo $mode ? 'complete':'simplified'; ?> <?php echo $isBattle ? 'is-battle':''; ?>">
		<div id="global-infos" class="editor-section"><?php
		if ($isBattle) {
			if ($language) {
				?>
			Create a &quot;Grand Prix&quot; cup from the circuits you shared !<br />
			To make the cup, it's quite easy: select 4 circuits in any order and validate.<br />
			Your cup will be created!
				<?php
			}
			else {
				?>
			Créez une &quot;coupe bataille&quot; afin de regrouper les arènes que vous avez partagées !<br />
			Pour créer la coupe, c'est très simple : sélectionnez 4 arènes dans l'ordre de votre choix et validez.<br />
			Votre coupe est crée !
				<?php
			}
		}
		else {
			if ($language) {
				?>
			Create a &quot;Grand Prix&quot; cup from the circuits you shared!<br />
			To make the cup, it's quite easy: select 4 circuits in any order and validate.<br />
			Your cup will be created!
				<?php
			}
			else {
				?>
			Create a &quot;Battle cup&quot; to gather the arenas you shared!<br />
			To make the cup, it's quite easy: select 4 circuits in any order and validate.<br />
			Your cup will be created!
				<?php
			}
		}
		?></div>
		<h1><?php
			if ($isBattle)
				echo $language ? 'Arena selection':'Sélection des arènes';
			else
				echo $language ? 'Circuits selection':'Sélection des circuits';
		?> (<span id="nb-selected">0</span>/4) :</h1>
		<?php
		require_once('utils-circuits.php');
		require_once('utils-cups.php');
		if ($isBattle)
			$type = 7-$mode;
		else
			$type = 5-$mode;
		$aCircuits = array($aCircuits[$type]);
		$aParams = array(
			'pids' => $identifiants,
			'type' => $type
		);
		$listCircuits = listCreations(1,null,null,$aCircuits,$aParams);
		$misingTrackIds = array();
		foreach ($cids as $cid)
			$misingTrackIds[$cid] = true;
		foreach ($listCircuits as $circuit)
			unset($misingTrackIds[$circuit['id']]);
		if (!empty($misingTrackIds)) {
			$aParams = array(
				'ids' => array_keys($misingTrackIds),
				'type' => $type
			);
			$misingTracks = listCreations(1,null,null,$aCircuits,$aParams);
			$listCircuits = array_merge($misingTracks, $listCircuits);
		}
		$nbCircuits = count($listCircuits);
		$trackBuilderPage = $isBattle ? ($mode ? 'course.php':'arene.php') : ($mode ? 'draw.php':'create.php');
		if (!$nbCircuits)
			echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t shared any '. ($isBattle ? "arenas":"circuits") .' in '. ($mode ? 'complete':'quick') .' mode.<br />Click <a href="'. $trackBuilderPage .'">here</a> to create one.':'Vous n\'avez pas encore partagé '. ($isBattle ? "d'arènes":"de circuits") .' en mode '. ($mode ? 'complet':'simplifié') .'.<br />Cliquez <a href="'. $trackBuilderPage .'">ici</a> pour en créer un.') .'</em>';
		elseif ($nbCircuits < 4)
			echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t shared enough '. ($isBattle ? "arenas":"circuits") .' to make a cup<br />Click <a href="'. $trackBuilderPage .'">here</a> to create other ones.':'Vous n\'avez pas encore partagé assez '. ($isBattle ? "d'arènes":"de circuits") .' pour faire une coupe.<br />Cliquez <a href="'. $trackBuilderPage .'">ici</a> pour en créer de nouveaux.') .'</em>';
		?>
		<form method="get" action="<?php echo $isBattle ? ($mode ? 'battle.php':'arena.php') : ($mode ? 'map.php':'circuit.php'); ?>">
			<div id="table-container">
				<table id="table-circuits">
					<tbody>
					<?php
					$circuitnb = 1;
					foreach ($listCircuits as $circuit) {
						printCupCircuit($circuit, array(
							'nb' => $circuitnb
						));
						$circuitnb++;
					}
					?>
					</tbody>
				</table>
			</div>
			<div id="collab-container">
				+ <a href="#null" onclick="showCollabImportPopup(event)"><?php echo $language ? "Import ". ($isBattle ? 'arena' : 'track') ." of another member..." : "Importer ". ($isBattle ? 'une arène' : 'un circuit') ." d'un autre membre..."; ?></a>
			</div>
			<p>
				<span id="cid-ctn"></span>
				<?php
				if (isset($id))
					echo '<input type="hidden" name="nid" value="'.$id.'" />';
				if (isset($_GET['cl']))
					echo '<input type="hidden" name="cl" value="'. htmlspecialchars($_GET['cl']) .'" />';
				if (isset($collab))
					echo '<input type="hidden" name="collab" value="'. htmlspecialchars($collab['key']) .'" />';
				?>
				<span class="pretty-title-ctn"><input type="submit" class="submit-selection pretty-title" disabled="disabled" value="<?php echo $language ? 'Validate!':'Valider !'; ?>" /></span>
			</p>
		</form>
		<?php
		printCollabImportPopup($isBattle ? 'arena' : 'circuit', $mode, $isBattle);
		?>
		<div class="editor-navigation">
			<a href="<?php echo ($mode ? 'completecup.php':'simplecup.php').($isBattle ? '':'?battle'); ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a cup of '. ($isBattle ? 'circuits':'arenas')):('Créer une coupe '. ($isBattle ? 'de circuits':'d\'arènes')); ?></u></a>
			<a href="<?php echo ($mode ? 'simplecup.php':'completecup.php').($isBattle ? '?battle':''); ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a cup in '. ($mode ? 'quick':'complete') .' mode'):('Créer une coupe en mode '. ($mode ? 'simplifié':'complet')); ?></u></a>
			<a href="index.php"><span>&lt; </span><u><?php echo $language ? 'Back to Mario Kart PC':'Retour à Mario Kart PC'; ?></u></a>
		</div>
	</div>
</body>
</html>
<?php
mysql_close();
?>