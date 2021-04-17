<?php
include('getId.php');
include('language.php');
$cids = array();
$editting = true;
include('initdb.php');
$isBattle = isset($_GET['battle']);
mysql_set_charset('utf8');
if (isset($_GET['cid'])) {
	$id = $_GET['cid'];
	$cupMode = $isBattle*2 + $mode;
	if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $id .'" AND mode="'. $cupMode .'"'))) {
		for ($i=0;$i<4;$i++)
			$cids[$i] = $getCup['circuit'. $i];
	}
	else
		$editting = false;
}
else {
	if (isset($_GET['nid']))
		$id = $_GET['nid'];
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
<link rel="stylesheet" href="styles/cup.css" />
<script type="text/javascript" src="scripts/creations.js"></script>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var editting = <?php echo $editting ? 'true':'false'; ?>;
var ckey = "cid";
var isBattle = <?php echo $isBattle ? 1:0; ?>;
<?php
if (isset($cids))
	echo 'var cids = '. json_encode($cids) .';';
?>
</script>
<script type="text/javascript" src="scripts/cup.js"></script>
<script type="text/javascript" src="scripts/posticons.js"></script>
</head>
<body onload="initGUI()">
	<div class="container <?php echo $mode ? 'complete':'simplified'; ?>">
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
		include('utils-circuits.php');
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
		$nbCircuits = count($listCircuits);
		if ($nbCircuits) {
			if ($nbCircuits < 4)
				echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t created enough '. ($isBattle ? 'arenas':'circuits') .' to make a cup<br />Click <a href="'. ($mode ? 'draw.php':'create.php') .'">here</a> to create other ones.':'Vous n\'avez pas encore créé assez '. ($isBattle ? 'd\'arènes':'de circuits') .' pour faire une coupe.<br />Cliquez <a href="'. ($mode ? 'draw.php':'create.php') .'">ici</a> pour en créer de nouveaux.') .'</em>';
			?>
			<form method="get" action="<?php echo $isBattle ? ($mode ? 'battle.php':'arena.php') : ($mode ? 'map.php':'circuit.php'); ?>">
				<div id="table-container">
					<table id="table-circuits">
					<?php
					$circuitnb = 1;
					foreach ($listCircuits as $circuit) {
						?>
						<tr id="circuit<?php echo $circuit['id']; ?>" data-id="<?php echo $circuit['id']; ?>" onclick="selectCircuit(this)">
							<td class="td-preview" <?php
							if (isset($circuit['icon']))
								echo ' style="background-image:url(\'images/creation_icons/'.$circuit['icon'][0].'\')"';
							else
								echo ' data-cicon="'.$circuit['cicon'].'"';
							?> onclick="previewImg(event,'<?php echo $circuit['srcs'][0]; ?>')"></td>
							<td class="td-name"><em><?php echo $circuitnb; ?></em><?php echo ($circuit['nom'] ? escapeUtf8($circuit['nom']):($language ? 'Untitled':'Sans titre')); ?></td>
							<td class="td-access">&rarr; <a href="<?php echo $circuit['href']; ?>" target="_blank" onclick="event.stopPropagation()"><?php echo $language ? 'Access':'Accéder'; ?></a></td>
						</tr>
						<?php
						$circuitnb++;
					}
					?>
					</table>
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
		}
		else
			echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t shared '. ($isBattle ? 'arenas':'circuits') .' in '. ($mode ? 'complete':'simplified') .' mode.<br />Click <a href="'. ($mode ? 'draw.php':'create.php') .'">here</a> to create one.':'Vous n\'avez pas encore partagé '. ($isBattle ? 'd\'arènes':'de circuits') .' en mode '. ($mode ? 'complet':'simplifié') .'.<br />Cliquez <a href="'. ($mode ? 'draw.php':'create.php') .'">ici</a> pour en créer un.') .'</em>';
		?>
		<div class="editor-navigation">
			<a href="<?php echo ($mode ? 'completecup.php':'simplecup.php').($isBattle ? '':'?battle'); ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a cup of '. ($isBattle ? 'circuits':'arenas')):('Créer une coupe '. ($isBattle ? 'de circuits':'d\'arènes')); ?></u></a>
			<a href="<?php echo ($mode ? 'simplecup.php':'completecup.php').($isBattle ? '?battle':''); ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a cup in '. ($mode ? 'simplified':'complete') .' mode'):('Créer une coupe en mode '. ($mode ? 'simplifié':'complet')); ?></u></a>
			<a href="index.php"><span>&lt; </span><u><?php echo $language ? 'Back to Mario Kart PC':'Retour à Mario Kart PC'; ?></u></a>
		</div>
	</div>
</body>
</html>
<?php
mysql_close();
?>