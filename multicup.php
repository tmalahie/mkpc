<?php
include('getId.php');
include('language.php');
$mids = array();
$editting = true;
$cOptions = isset($_GET['opt']) ? $_GET['opt']:null;
include('initdb.php');
require_once('collabUtils.php');
$isBattle = isset($_GET['battle']);
$readOnly = false;
if (isset($_GET['mid'])) {
	$id = intval($_GET['mid']);
	$cupMode = $isBattle*2 + $mode;
	$getMCup = mysql_fetch_array(mysql_query('SELECT options,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkmcups` WHERE id="'. $id .'" AND mode="'. $cupMode .'"'));
	if (($getMCup['identifiant'] == $identifiants[0]) && ($getMCup['identifiant2'] == $identifiants[1]) && ($getMCup['identifiant3'] == $identifiants[2]) && ($getMCup['identifiant4'] == $identifiants[3])) {
		$hasReadGrants = true;
		$hasWriteGrants = true;
	}
	else {
		$collab = getCollabLinkFromQuery('mkmcups', $id);
		$hasReadGrants = isset($collab['rights']['view']);
		$hasWriteGrants = isset($collab['rights']['edit']);
	}
	if (!$hasReadGrants) {
		mysql_close();
		exit;
	}
	$readOnly = !$hasWriteGrants;
	$getCups = mysql_query('SELECT cup FROM `mkmcups_tracks` WHERE mcup="'. $id .'" ORDER BY ordering');
	while ($getCup = mysql_fetch_array($getCups))
		$mids[] = $getCup['cup'];
	if (empty($mids))
		$editting = false;
	if (null === $cOptions)
		$cOptions = $getMCup['options'];
}
else {
	if (isset($_GET['nid']))
		$id = intval($_GET['nid']);
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
var complete = <?php echo $mode; ?>;
var readOnly = <?php echo $readOnly ? 1:0; ?>;
var isBattle = <?php echo $isBattle ? 1:0; ?>;
<?php
if (isset($mids))
	echo 'var cids = '. json_encode($mids) .';';
?>
var cp = <?php include('getPersos.php'); ?>;
var collabCharPlaceholder = "<?php
$collabPlaceholder = array(
	'type' => 'mkchars',
	'creation_id' => 42,
	'secret' => 'y-vf-erny_2401_pbasvezrq'
);
echo getCollabUrl($collabPlaceholder);
?>";
<?php
include('handleCupOptions.php');
?>
</script>
<script type="text/javascript" src="scripts/cup.js"></script>
<script type="text/javascript" src="scripts/posticons.js"></script>
</head>
<body<?php if ($readOnly) echo ' class="readonly"'; ?>>
	<div class="container <?php echo $mode ? 'complete':'simplified'; ?> <?php echo $isBattle ? ' is-battle':''; ?>">
		<div id="global-infos" class="editor-section"><?php
			if ($language) {
				?>
			This editor allows you to merge several cups on the same page.<br />
			You have created cups of a same series and you want to join them together?<br />
			You often play online on your <?php echo $isBattle ? 'arenas' : 'circuits'; ?> and you don't want to be limited to 4 <?php echo $isBattle ? 'battles' : 'races'; ?>?<br />
			This mode is made for you! You can join <strong>up to 40 cups</strong>!<br />
			Just select the creations of your choice, like in the cups editor.
				<?php
			}
			else {
				?>
			Cet éditeur vous permet de rassembler plusieurs coupes sur une même page.<br />
			Vous avez créé des coupes d'une même série et vous souhaitez les réunir ?<br />
			Vous jouez souvent en ligne sur vos <?php echo $isBattle ? 'arènes' : 'circuits'; ?> et vous ne voulez pas être limité à 4 <?php echo $isBattle ? 'batailles' : 'courses'; ?> ?<br />
			Ce mode est fait pour vous ! Vous pouvez réunir <strong>jusqu'à 40 coupes</strong> !<br />
			Sélectionnez simplement les créations de votre choix, comme dans l'éditeur de coupes.
				<?php
			}
			?></div>
		<form method="get" onsubmit="return handleFormSubmit(event)" action="<?php echo $isBattle ? ($mode ? 'battle.php':'arena.php') : ($mode ? 'map.php':'circuit.php'); ?>">
		<div class="editor-content editor-content-active">
			<h1><?php echo $language ? 'Cups selection':'Sélection des coupes'; ?> (<span id="nb-selected">0</span>) :</h1>
			<?php
			require_once('utils-circuits.php');
			require_once('utils-cups.php');
			if ($isBattle)
				$type = 9-$mode;
			else
				$type = 3-$mode;
			$aCircuits = array($aCircuits[$type]);
			$aParams = array(
				'pids' => $identifiants,
				'type' => $type
			);
			$listCups = listCreations(1,null,null,$aCircuits,$aParams);
			$misingCupIds = array();
			foreach ($mids as $cid)
				$misingCupIds[$cid] = true;
			foreach ($listCups as $cup)
				unset($misingCupIds[$cup['id']]);
			if (!empty($misingCupIds)) {
				$aParams = array(
					'ids' => array_keys($misingCupIds),
					'type' => $type
				);
				$misingTracks = listCreations(1,null,null,$aCircuits,$aParams);
				$listCups = array_merge($misingTracks, $listCups);
			}
			$nbCups = count($listCups);
			if (!$nbCups)
				echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You haven\'t shared cups in '. ($mode ? 'complete':'quick') .' mode.<br />Click <a href="'. ($mode ? 'completecup.php':'simplecup.php') . ($isBattle ? '?battle':'') .'">here</a> to create one.':'Vous n\'avez pas encore partagé de coupes en mode '. ($mode ? 'complet':'simplifié') .'.<br />Cliquez <a href="'. ($mode ? 'completecup.php':'simplecup.php') . ($isBattle ? '?battle':'') .'">ici</a> pour en créer une.') .'</em>';
			elseif ($nbCups < 2)
				echo '<em class="editor-section" id="no-circuit">'. ($language ? 'You need to have created at least 2 cups to create a multicup<br />Click <a href="'. ($mode ? 'completecup.php':'simplecup.php') . ($isBattle ? '?battle':'') .'">here</a> to create a new cup.':'Vous devez avoir au moins 2 coupes pour créer une multicoupe.<br />Cliquez <a href="'. ($mode ? 'completecup.php':'simplecup.php') . ($isBattle ? '?battle':'') .'">ici</a> pour en créer une nouvelle.') .'</em>';
			?>
			<div id="table-container">
				<table id="table-circuits">
					<tbody>
					<?php
					$cupnb = 1;
					foreach ($listCups as $cup) {
						printCupCircuit($cup, array(
							'nb' => $cupnb
						));
						$cupnb++;
					}
					?>
					</tbody>
				</table>
			</div>
			<div id="collab-container">
				+ <a href="#null" onclick="showCollabImportPopup(event)"><?php echo $language ? "Import cup of another member..." : "Importer la coupe d'un autre membre..."; ?></a>
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
				<input type="hidden" id="cup-options" name="opt" value="<?php if ($cOptions !== null) echo htmlspecialchars($cOptions) ?>" />
				<span class="pretty-title-ctn"><input type="submit" class="submit-selection pretty-title" disabled="disabled" value="<?php echo $language ? 'Validate!':'Valider !'; ?>" /></span>
				<a class="editor-switch-options" href="javascript:showEditorContent(1)"><?php echo $language ? 'Advanced&nbsp;options':'Options&nbsp;avancées'; ?></a>
			</p>
		</div>
		<div class="editor-content">
			<h1><?php echo $language ? 'Advanced options':'Options avancées'; ?></h1>
			<div id="option-tabs">
				<div class="option-tab-selected" onclick="selectOptionTab(0)">
					<?php echo $language ? 'Multicup appearance':'Apparence de votre coupe'; ?>
				</div><div onclick="selectOptionTab(1)">
					<?php echo $language ? 'Character roster':'Liste des persos'; ?>
				</div>
			</div>
			<div id="option-containers">
				<div class="option-container-selected">
					<h2><?php echo $language ? 'Multicup appearance:':'Apparence de votre coupe :'; ?> <a id="reset-cup-appearance" href="javascript:resetCupAppearance()">[<?php echo $language ? 'Reset':'Réinitialiser'; ?>]</a></h2>
					<div id="cup-appearance"></div>
					<div id="cup-appearance-page">
						<div class="cup-appearance-page-buttons">
							<button type="button" id="cup-appearance-page-prev" onclick="prevCupPage()">◄</button>
							<button type="button" id="cup-appearance-page-next" onclick="nextCupPage()">►</button>
						</div>
					</div>
				</div>
				<div>
					<h2><?php echo $language ? 'Character roster:':'Liste des persos :'; ?> <a id="reset-character-roster" href="javascript:resetCharacterRoster()">[<?php echo $language ? 'Reset':'Réinitialiser'; ?>]</a></h2>
					<div id="character-roster"></div>
					<div class="character-roster-extra">
						<label>
							<input type="checkbox" id="customchars" checked="checked" onclick="resetCupOptions()" /> <?php echo ($language ? 'Allow custom characters' : 'Autoriser les persos custom'); ?>
						</label>
						<span class="pretty-title-ctn"><a href="javascript:void(0)" class="pretty-title" data-title="<?php
							echo htmlspecialchars('<div class="fancy-title-customchars">'. ($language ? "If unchecked, you don't give access to the character editor in the character selector screen. Only the characters in the list above can be selected." : "Si décoché, vous ne donnez pas accès à l'éditeur de persos dans l'écran de sélection du perso. Seuls les persos de la liste ci-dessus pourront être sélectionnés.") .'</div>');
						?>">[?]</a></span>
					</div>
				</div>
			</div>
			<p>
				<div class="pretty-title-ctn"><input type="submit" class="submit-selection pretty-title" disabled="disabled" value="<?php echo $language ? 'Validate!':'Valider !'; ?>" /></div>
				<a class="editor-switch-options" href="javascript:showEditorContent(0)"><?php echo $language ? 'Back':'Retour'; ?></a>
			</p>
		</div>
		</form>
		<?php
		printCollabImportPopup('cup', $mode, $isBattle);
		?>
		<div class="editor-navigation">
			<a href="<?php echo ($mode ? 'completecups.php':'simplecups.php').($isBattle ? '':'?battle'); ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a multicup of '. ($isBattle ? 'circuits':'arenas')):('Créer une multicoupe '. ($isBattle ? 'de circuits':'d\'arènes')); ?></a></u>
			<a href="<?php echo ($mode ? 'simplecups.php':'completecups.php').($isBattle ? '?battle':''); ?>"><span>-&nbsp; </span><u><?php echo $language ? ('Create a multicup in '. ($mode ? 'quick':'complete') .' mode'):('Créer une multicoupe en mode '. ($mode ? 'simplifié':'complet')); ?></a></u>
			<a href="index.php"><span>&lt; </span><u><?php echo $language ? 'Back to Mario Kart PC':'Retour à Mario Kart PC'; ?></u></a>
		</div>
	</div>
</body>
</html>
<?php
mysql_close();
?>