<?php
include('getId.php');
include('language.php');
$mids = array();
$editting = true;
$optionsJson = isset($_GET['opt']) ? $_GET['opt']:null;
include('initdb.php');
mysql_set_charset('utf8');
if (isset($_GET['mid'])) {
	$id = $_GET['mid'];
	$getCups = mysql_query('SELECT cup FROM `mkmcups_tracks` WHERE mcup="'. $id .'" ORDER BY ordering');
	while ($getCup = mysql_fetch_array($getCups))
		$mids[] = $getCup['cup'];
	if (empty($mids))
		$editting = false;
	if (null === $optionsJson) {
		if ($getMCup = mysql_fetch_array(mysql_query('SELECT options FROM `mkmcups` WHERE id="'. $id .'"')))
			$optionsJson = $getMCup['options'];
	}
}
else {
	if (isset($_GET['nid']))
		$id = $_GET['nid'];
	for ($i=0;isset($_GET['mid'.$i]);$i++)
		$mids[$i] = $_GET['mid'.$i];
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
<title><?php echo $language ? 'Create multicup':'Créer multicoupe'; ?></title>
<link rel="stylesheet" href="styles/cup.css" />
<script type="text/javascript" src="scripts/creations.js"></script>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var editting = <?php echo $editting ? 'true':'false'; ?>;
var ckey = "mid";
var isBattle = <?php echo $isBattle ? 1:0; ?>;
<?php
if (isset($mids))
	echo 'var cids = '. json_encode($mids) .';';
?>
</script>
<script type="text/javascript" src="scripts/cup.js"></script>
<script type="text/javascript" src="scripts/posticons.js"></script>
</head>
<body onload="initGUI()">
	<div class="container <?php echo $mode ? 'complete':'simplified'; ?>">
		<div id="global-infos" class="editor-section"><?php
			if ($language) {
				?>
			This editor allows you to merge several cups on the same page.<br />
			You have created cups of a same series and you want to join them together?<br />
			You often play online on your circuits and you don't want to be limited to 4 races?<br />
			This mode is made for you! You can join <strong>up to 18 cups</strong>!<br />
			Just select the creations of your choice, like in the cups editor.
				<?php
			}
			else {
				?>
			Cet éditeur vous permet de rassembler plusieurs coupes sur une même page.<br />
			Vous avez créé des coupes d'une même série et vous souhaitez les réunir ?<br />
			Vous jouez souvent en ligne sur vos circuits et vous ne voulez pas être limité à 4 courses ?<br />
			Ce mode est fait pour vous ! Vous pouvez réunir <strong>jusqu'à 18 coupes</strong> !<br />
			Sélectionnez simplement les créations de votre choix, comme dans l'éditeur de coupes.
				<?php
			}
			?></div>
		<form method="get" action="<?php echo ($mode ? 'map.php':'circuit.php'); ?>">
		<div class="editor-content editor-content-active">
			<h1><?php echo $language ? 'Cups selection':'Sélection des coupes'; ?> (<span id="nb-selected">0</span>) :</h1>
			<?php
			include('utils-circuits.php');
			$type = 3-$mode;
			$aCircuits = array($aCircuits[$type]);
			$aParams = array(
				'pids' => $identifiants,
				'type' => $type
			);
			$listCups = listCreations(1,null,null,$aCircuits,$aParams);
			$nbCups = count($listCups);
			if ($nbCups) {
				if ($nbCups < 2)
					echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You need to have created at least 2 cups to create a multicup<br />Click <a href="'. ($mode ? 'completecup.php':'simplecup.php') .'">here</a> to create a new cup.':'Vous devez avoir au moins 2 coupes pour créer une multicoupe.<br />Cliquez <a href="'. ($mode ? 'completecup.php':'simplecup.php') .'">ici</a> pour en créer une nouvelle.') .'</em>';
				?>
				<div id="table-container">
					<table id="table-circuits">
					<?php
					$cupnb = 1;
					foreach ($listCups as $cup) {
						?>
						<tr id="circuit<?php echo $cup['id']; ?>" data-id="<?php echo $cup['id']; ?>" onclick="selectCircuit(this)">
							<td class="td-preview td-preview-cup" <?php
							if (isset($cup['icon'])) {
								$allMapSrcs = $cup['icon'];
								foreach ($allMapSrcs as $j=>$jMapSrc)
									$allMapSrcs[$j] = "url('images/creation_icons/$jMapSrc')";
								echo ' style="background-image:'.implode(',',$allMapSrcs).'"';
							}
							else
								echo ' data-cicon="'.$cup['cicon'].'"';
							?> onclick="previewImg(event,<?php echo htmlspecialchars(json_encode($cup['srcs'])); ?>)"></td>
							<td class="td-name"><em><?php echo $cupnb; ?></em><?php echo ($cup['nom'] ? escapeUtf8($cup['nom']):($language ? 'Untitled':'Sans titre')); ?></td>
							<td class="td-access">&rarr; <a href="<?php echo $cup['href']; ?>" target="_blank" onclick="event.stopPropagation()"><?php echo $language ? 'Access':'Accéder'; ?></a></td>
						</tr>
						<?php
						$cupnb++;
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
					<input type="hidden" id="cup-options" name="opt" value="<?php echo htmlspecialchars($optionsJson) ?>" />
					<span class="pretty-title-ctn"><input type="submit" class="submit-selection pretty-title" disabled="disabled" value="<?php echo $language ? 'Validate!':'Valider !'; ?>" /></span>
					<a class="editor-switch-options" href="javascript:showEditorContent(1)"><?php echo $language ? 'Advanced&nbsp;options':'Options&nbsp;avancées'; ?></a>
				</p>
			</div>
			<div class="editor-content">
				<h1><?php echo $language ? 'Advanced options':'Options avancées'; ?></h1>
				<h2><?php echo $language ? 'Multicup appearance:':'Apparence de votre coupe :'; ?> <a id="reset-cup-appearance" href="javascript:resetCupAppearance()">[<?php echo $language ? 'Reset':'Réinitialiser'; ?>]</a></h2>
				<div id="cup-appearance">
				</div>
				<p>
					<div class="pretty-title-ctn"><input type="submit" class="submit-selection pretty-title" disabled="disabled" value="<?php echo $language ? 'Validate!':'Valider !'; ?>" /></div>
					<a class="editor-switch-options" href="javascript:showEditorContent(0)"><?php echo $language ? 'Back':'Retour'; ?></a>
				</p>
			<?php
		}
		else
			echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t shared cups in '. ($mode ? 'complete':'simplified') .' mode.<br />Click <a href="'. ($mode ? 'completecup.php':'simplecup.php') .'">here</a> to create one.':'Vous n\'avez pas encore partagé de coupes en mode '. ($mode ? 'complet':'simplifié') .'.<br />Cliquez <a href="'. ($mode ? 'completecup.php':'simplecup.php') .'">ici</a> pour en créer une.') .'</em>';
		?>
		</div>
		</form>
		<div class="editor-navigation">
			<a href="<?php echo $mode ? 'simplecups.php':'completecups.php'; ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a multicup in '. ($mode ? 'simplified':'complete') .' mode'):('Créer une multicoupe en mode '. ($mode ? 'simplifié':'complet')); ?></a></u>
			<a href="index.php"><span>&lt; </span><u><?php echo $language ? 'Back to Mario Kart PC':'Retour à Mario Kart PC'; ?></u></a>
		</div>
	</div>
</body>
</html>
<?php
mysql_close();
?>