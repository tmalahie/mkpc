<?php
include('getId.php');
include('language.php');
$cids = array();
$editting = true;
include('initdb.php');
if (isset($_GET['cid'])) {
	$id = intval($_GET['cid']);
	if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $id .'" AND mode="'. $mode .'"'))) {
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
<title><?php echo $language ? 'Create cup':'Cr&eacute;er coupe'; ?></title>
<link rel="stylesheet" href="styles/cup.css" />
<script type="text/javascript" src="scripts/creations.js"></script>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var editting = <?php echo $editting ? 'true':'false'; ?>;
var ckey = "cid";
var complete = <?php echo $mode; ?>;
<?php
if (isset($cids))
	echo 'var cids = '. json_encode($cids) .';';
?>
</script>
<script type="text/javascript" src="scripts/cup.js"></script>
<script type="text/javascript" src="scripts/posticons.js"></script>
</head>
<body>
	<div class="container <?php echo $mode ? 'complete':'simplified'; ?>">
		<div id="global-infos" class="editor-section"><?php
			if ($language) {
				?>
			Create a &quot;Grand Prix&quot; cup from the circuits you shared !<br />
			To make the cup, it's quite easy: select 4 circuits in any order and validate.<br />
			Your cup will be created!
				<?php
			}
			else {
				?>
			Cr&eacute;ez une coupe &quot;Grand Prix&quot; &agrave; partir des circuits que vous avez partag&eacute;s !<br />
			Pour cr&eacute;er la coupe, c'est tr&egrave;s simple : s&eacute;lectionnez 4 circuits dans l'ordre de votre choix et validez.<br />
			Votre coupe est cr&eacute;e !
				<?php
			}
			?></div>
		<h1><?php echo $language ? 'Circuits selection':'S&eacute;lection des circuits'; ?> (<span id="nb-selected">0</span>/4) :</h1>
		<?php
		include('utils-circuits.php');
		include('utils-cups.php');
		require_once('collabUtils.php');
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
		if ($nbCircuits) {
			if ($nbCircuits < 4)
				echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t created enough circuits to make a cup<br />Click <a href="'. ($mode ? 'draw.php':'create.php') .'">here</a> to create other ones.':'Vous n\'avez pas encore cr&eacute;&eacute; assez de circuits pour faire une coupe.<br />Cliquez <a href="'. ($mode ? 'draw.php':'create.php') .'">ici</a> pour en cr&eacute;er de nouveaux.') .'</em>';
			?>
			<form method="get" action="<?php echo ($mode ? 'map.php':'circuit.php'); ?>">
				<div id="table-container">
					<table id="table-circuits">
					<?php
					$circuitnb = 1;
					foreach ($listCircuits as $circuit) {
						printCupCircuit($circuit, array(
							'nb' => $circuitnb
						));
						$circuitnb++;
					}
					?>
					</table>
				</div>
				<div id="collab-container">
					+ <a href="#null" onclick="showCollabImportPopup(event)"><?php echo $language ? "Import track of another member..." : "Importer un circuit d'un autre membre..."; ?></a>
				</div>
				<p>
					<span id="cid-ctn"></span>
					<?php
					if (isset($id))
						echo '<input type="hidden" name="nid" value="'.$id.'" />';
					if (isset($_GET['cl']))
						echo '<input type="hidden" name="cl" value="'. htmlspecialchars($_GET['cl']) .'" />';
					?>
					<span class="pretty-title-ctn"><input type="submit" class="submit-selection pretty-title" disabled="disabled" value="<?php echo $language ? 'Validate!':'Valider !'; ?>" /></span>
				</p>
			</form>
			<?php
			printCollabImportPopup('circuit');
		}
		else
			echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t shared circuits in '. ($mode ? 'complete':'simplified') .' mode.<br />Click <a href="'. ($mode ? 'draw.php':'create.php') .'">here</a> to create one.':'Vous n\'avez pas encore partag&eacute; de circuits en mode '. ($mode ? 'complet':'simplifi&eacute;') .'.<br />Cliquez <a href="'. ($mode ? 'draw.php':'create.php') .'">ici</a> pour en cr&eacute;er un.') .'</em>';
		?>
		<div class="editor-navigation">
			<a href="<?php echo $mode ? 'simplecup.php':'completecup.php'; ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a cup in '. ($mode ? 'simplified':'complete') .' mode'):('Cr&eacute;er une coupe en mode '. ($mode ? 'simplifi&eacute;':'complet')); ?></u></a>
			<a href="index.php"><span>&lt; </span><u><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></u></a>
		</div>
	</div>
</body>
</html>
<?php
mysql_close();
?>