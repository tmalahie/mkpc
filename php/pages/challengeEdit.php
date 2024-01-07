<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/initdb.php');
include('../includes/ip_banned.php');
if (isBanned()) {
	mysql_close();
	exit;
}
require_once('../includes/utils-challenges.php');
if (isset($_GET['moderate'])) {
	include('../includes/session.php');
	require_once('../includes/getRights.php');
	if (hasRight('clvalidator'))
		$moderate = true;
}
if (isset($_GET['ch'])) {
	$challenge = getChallenge($_GET['ch'], !empty($moderate));
	if ($challenge)
		$clRace = getClRace($challenge['clist'], !empty($moderate));
}
elseif (isset($_GET['cl']))
	$clRace = getClRace($_GET['cl'], !empty($moderate));
include('../includes/challenge-cldata.php');
if (isset($_POST['name'])) {
	$clMsg = null;
	if (empty($challenge) || ('pending_completion' === $challenge['status']) || !empty($moderate)) {
		if (isset($_POST['goal']) && isset($_POST['difficulty'])) {
			require_once('../includes/challenge-consts.php');
			$difficulties = getChallengeDifficulties();
			if (!isset($difficulties[$_POST['difficulty']]))
				$_POST['difficulty'] = 0;
			$data = array();
			$data['goal'] = $_POST['goal'];
			parseChallengeConstraint($data['goal']);
			$data['constraints'] = array();
			$constraints = isset($_POST['constraint']) ? $_POST['constraint']:array();
			$scopes = isset($_POST['scope']) ? $_POST['scope']:array();
			foreach ($constraints as $ruleId) {
				$scope = isset($scopes[$ruleId]) ? $scopes[$ruleId] : null;
				$scope['type'] = $ruleId;
				parseChallengeConstraint($scope);
				$data['constraints'][] = $scope;
			}
			$dataJson = json_encode($data);
			if (isset($challenge) && !empty($clRace)) {
				$validation = '';
				if (!empty($_POST['feedbacks']) && !empty($moderate)) {
					$validationData = array(
						'feedbacks' => stripslashes($_POST['feedbacks'])
					);
					$validation = mysql_real_escape_string(json_encode($validationData));
				}
				mysql_query('UPDATE `mkchallenges` SET name="'. $_POST['name'] .'",difficulty="'. $_POST['difficulty'] .'",data="'. mysql_real_escape_string($dataJson) .'",validation="'. $validation .'" WHERE id="'. $challenge['id'] .'"');
				if (!empty($moderate))
					mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "EChallenge '. $challenge['id'] .'")');
				$clMsg = 'challenge_edited';
			}
			else {
				if (empty($clRace)) {
					mysql_query('INSERT INTO `mkclrace` SET identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3]);
					$clRace = array('id'=>mysql_insert_id(),'clid'=>null);
					if (!isset($edittingCircuit))
						$clRace['clid'] = $clRace['id'];
				}
				mysql_query('INSERT INTO `mkchallenges` SET clist="'. $clRace['id'] .'",name="'. $_POST['name'] .'",difficulty="'. $_POST['difficulty'] .'",data="'. mysql_real_escape_string($dataJson) .'"');
				$clMsg = 'challenge_created';
			}
			if (isset($clCircuit) && ($clCircuit['identifiant'] == $identifiants[0]) && ($clCircuit['identifiant2'] == $identifiants[1]) && ($clCircuit['identifiant3'] == $identifiants[2]) && ($clCircuit['identifiant4'] == $identifiants[3])) {
				mysql_query('UPDATE `mkclrace` SET type="'. $clTable .'",circuit='. $clCircuit['id'] .' WHERE id='.$clRace['id']);
				if (!isset($edittingCircuit))
					$_GET = array();
			}
		}
	}
	elseif ('deleted' !== $challenge['status']) {
		mysql_query('UPDATE `mkchallenges` SET name="'. $_POST['name'] .'" WHERE id="'. $challenge['id'] .'"');
		if (in_array($challenge['status'], array('pending_publication','pending_moderation'))) {
			if (isset($_POST['difficulty'])) {
				$difficulties = getChallengeDifficulties();
				if (isset($difficulties[$_POST['difficulty']]))
					updateChallengeDifficulty($challenge, $_POST['difficulty']);
			}
		}
		$clMsg = 'challenge_updated';
	}
	mysql_close();
	if (!empty($moderate))
		header('location: '. $_SERVER['REQUEST_URI'].'&clmsg='.$clMsg);
	else
		header('location: '. nextPageUrl('challenges.php', array('ch'=>null,'cl'=>$clRace['clid'],'clmsg'=>$clMsg)));
	exit;
}
elseif (empty($challenge) || ('pending_completion' === $challenge['status']) || !empty($moderate)) {
	$clRulesPayload = rulesPayloadByType($clCourse);
	$clGroupsPayload = $clGroupsByType;
	if (isset($challenge))
		$chRules = getChallengeRulesByType($challenge);
	ob_start();
	include('../includes/getPersos.php');
	$listPersos = json_decode(ob_get_clean());
	ob_start();
	include('../includes/getLocks.php');
	$unlocked = json_decode(ob_get_clean());
	$persoOptions = array();
	$i = 0;
	foreach ($listPersos as $persoId => $value) {
		if ($unlocked[$i]) {
			$persoOptions[] = array(
				'label' => getCharacterName($persoId),
				'value' => $persoId
			);
		}
		$i++;
	}
	$persoOptions[] = array(
		'label' => $language ? 'Custom...' : 'Personnalisé...',
		'value' => ''
	);
	$decorOptions = array();
	if (!empty($clRace) && $clRace['type']) {
		$decorTable = $clRace['type'];
		$decorCircuit = $clRace['circuit'];
	}
	elseif (isset($_GET['page'])) {
		switch ($_GET['page']) {
		case 'circuit':
		case 'arena':
			$decorTable = 'mkcircuits';
			if (isset($_GET['map']))
				$decorMap = $_GET['map'];
			elseif (isset($_GET['id']))
				$decorCircuit = $_GET['id'];
			else
				$decorMap = 1;
			break;
		case 'map':
			if (isset($_GET['i'])) {
				$decorTable = 'circuits';
				$decorCircuit = $_GET['i'];
			}
			break;
		case 'battle':
			if (isset($_GET['i'])) {
				$decorTable = 'arenes';
				$decorCircuit = $_GET['i'];
			}
			break;
		}
	}
	if (isset($decorTable)) {
		switch ($decorTable) {
		case 'circuits':
		case 'arenes':
			if ($getCircuitsData = mysql_fetch_array(mysql_query('SELECT data FROM `'. $decorTable .'_data` WHERE id="'. $decorCircuit .'"'))) {
				$circuitData = json_decode(gzuncompress($getCircuitsData['data']));
				$circuitDecors = array_keys((array)$circuitData->decor);
				$decorParams = isset($circuitData->decorparams) ? $circuitData->decorparams:new \stdClass();
				$decorExtra = isset($decorParams->extra) ? $decorParams->extra:new \stdClass();
				if (isset($circuitData->assets)) {
					foreach ($circuitData->assets as $key => $data) {
						$decorTypes = array();
						foreach ($data as $d) {
							$assetType = $d[0];
							if (!isset($decorTypes[$assetType])) {
								$decorTypes[$assetType] = 1;
								if (isset($decorExtra->{$assetType}->custom))
									$circuitDecors[] = $assetType;
								else {
									switch ($key) {
									case 'pointers':
										$circuitDecors[] = 'assets/pivothand';
										break;
									default:
										$circuitDecors[] = 'assets/'.$assetType;
									}
								}

							}
						}
					}
				}
				foreach ($circuitDecors as $type) {
					$decorOption = array(
						'value' => $type
					);
					if (isset($decorExtra->{$type}->custom)) {
						$customDecor = $decorExtra->{$type}->custom;
						$decorId = intval($customDecor->id);
						$actualType = $customDecor->type;
						if ($customData = mysql_fetch_array(mysql_query('SELECT name,sprites,img_data FROM mkdecors WHERE id='. $decorId))) {
							require_once('../includes/utils-decors.php');
							$decorSrcs = get_decor_srcs($customData);
							$decorOption['icon'] = $decorSrcs['map'];
							$decorOption['custom'] = 1;
							$decorOption['label'] = $customData['name'];
						}
					}
					$decorOptions[] = $decorOption;
				}
			}

			break;
		case 'mkcircuits':
			if (!isset($decorMap)) {
				if ($getMap = mysql_fetch_array(mysql_query('SELECT map FROM `mkcircuits` WHERE id="'. $decorCircuit .'"')))
					$decorMap = $getMap['map'];
			}
			require_once('../includes/circuitEnumsQuick.php');
			$decorTypes = $decorTypes[$decorMap];
			foreach ($decorTypes as $type) {
				$decorOptions[] = array(
					'value' => $type
				);
			}
			break;
		}
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<?php include('../includes/c_challenges.php'); ?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<?php
if (empty($moderate))
	include('../includes/o_online.php');
?>

<title><?php echo $language ? 'Challenge editor':'Éditeur de défis'; ?> - Mario Kart PC</title>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var clRules = <?php echo isset($clRulesPayload) ? json_encode($clRulesPayload) : 'null'; ?>;
var clGroups = <?php echo isset($clGroupsPayload) ? json_encode($clGroupsPayload) : 'null'; ?>;
<?php
if (isset($challenge))
	echo 'var challengeId = '. $challenge['id'] .';';
if (isset($chRules))
	echo 'var chRules = '. json_encode($chRules) .';';
echo 'var clCourse = "'. $clCourse .'";';
?>
var persoOptions = <?php echo isset($persoOptions) ? json_encode($persoOptions) : 'null'; ?>;
var decorOptions = <?php echo isset($decorOptions) ? json_encode($decorOptions) : 'null'; ?>;
var selectedConstraints = {};
function selectMainRule() {
	var ruleValue = $("#challenge-main-rule").val();
	var $extra = $("#challenge-main-extra");
	$extra.empty();
	if (!ruleValue) return;
	switch (ruleValue) {
	case 'finish_circuit_time':
	case 'survive':
		$extra.html(
			'<label>'+ (language ? 'Time:':'Temps :') +' '+
			'<input type="text" style="width:50px" name="goal[value]" placeholder="1:30" required="required" autocomplete="off" pattern="\\d*(:\\d*){0,2}" />'+
			'</label>'
		);
		break;
	case 'hit':
		$extra.html(
			'<label>'+
			'<input type="number" style="width:40px" name="goal[value]" required="required" autocomplete="off" /> '+ (language ? 'opponents':'personnes')+
			'</label>'
		);
		break;
	case 'eliminate':
		$extra.html(
			'<label>'+
			'<input type="number" style="width:40px" name="goal[value]" required="required" autocomplete="off" /> '+ (language ? 'opponents':'adversaires')+
			'</label>'
		);
		break;
	case 'gold_cups_n':
		$extra.html(
			'<label>'+
			'<input type="number" style="width:30px" name="goal[value]" required="required" autocomplete="off" /> '+ (language ? 'cups':'coupes')+
			'</label>'
		);
		break;
	case 'finish_circuits_first':
		$extra.html(
			'<label>'+
			'<input type="number" style="width:30px" name="goal[value]" required="required" autocomplete="off" /> '+ (language ? 'times':'fois')+
			'</label>'
		);
		break;
	case 'pts_greater':
	case 'pts_equals':
		$extra.html(
			'<label>'+
			'<input type="number" style="width:50px" name="goal[pts]" required="required" autocomplete="off" /> '+ (language ? 'pts':'pts')+
			'</label><label>'+ (language ? ' on ':' sur ')+
			'<input type="number" style="width:40px" name="goal[value]" required="required" autocomplete="off" /> '+ (language ? 'races':'courses')+
			'</label>'
		);
		break;
	case 'reach_zone':
		$extra.html(
			'<div style="margin:10px 0"><label>'+ (language ? 'Location: ':'Emplacement : ') +
			'<input type="hidden" name="goal[value]" value="[]" />'+
			'<input type="hidden" name="goal[translated]" value="0" />'+
			'<button type="button" onclick="openZoneEditor()">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'+
			'<div style="font-size:16px" id="goal_reach_zone_description"><label>'+ (language ? 'Description: ':'Description : ') +
			'<input type="text" name="goal[description]" style="font-size:12px;width:350px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? 'example: &quot;Find the secret passage&quot;, &quot;Reach the central zone&quot;':'Ex : &quot;Trouver le passage secret&quot;, &quot;Atteindre la zone centrale&quot;') +'" required="required" maxlength="100" />'+
			'</label>'+
			'<div style="margin-top:0.25em"><a class="pretty-link" href="javascript:toggleGoalDescriptionTr()">'+ (language ? 'Translate description' : 'Traduire la description') +'...</a></div>'+
			'</div>'+
			'<div style="font-size:14px;margin-top:0.25em;display:none" id="goal_reach_zone_description_tr">'+
			'<label>'+ (language ? 'Description (EN): ':'Description (FR) : ') +
			'<input type="text" name="goal[description_'+(language ? 'en':'fr')+']" style="font-size:12px;width:300px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? '&quot;Find the secret passage&quot;, &quot;Reach the central zone&quot;':'&quot;Trouver le passage secret&quot;, &quot;Atteindre la zone centrale&quot;') +'" maxlength="100" />'+
			'</label><br />'+
			'<label>'+ (language ? 'Description (FR): ':'Description (EN) : ') +
			'<input type="text" name="goal[description_'+(language ? 'fr':'en')+']" style="font-size:12px;width:300px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? '&quot;Trouver le passage secret&quot;, &quot;Atteindre la zone centrale&quot;':'&quot;Find the secret passage&quot;, &quot;Reach the central zone&quot;') +'" maxlength="100" />'+
			'</label>'+
			'<div style="margin-top:0.25em"><a class="pretty-link" href="javascript:toggleGoalDescriptionTr()">'+ (language ? 'Stop translating' : 'Ne plus traduire') +'</a></div>'+
			'</div>'
		);
		break;
	case 'reach_zones':
		$extra.html(
			'<div style="margin:10px 0"><label>'+ (language ? 'Locations: ':'Emplacements : ') +
			'<input type="hidden" name="goal[value]" value="[]" />'+
			'<input type="hidden" name="goal[ordered]" value="0" />'+
			'<input type="hidden" name="goal[translated]" value="0" />'+
			'<button type="button" onclick="openZoneEditor(\'zones\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'+
			'<div style="font-size:16px" id="goal_reach_zones_description">'+
			'<label>'+ (language ? 'Description: ':'Description : ') +
			'<input type="text" name="goal[description]" style="font-size:12px;width:350px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? 'example: &quot;Pass through 8 rings&quot;, &quot;Roll over the 4 pillars&quot;':'Ex : &quot;Traverser les 8 anneaux&quot;, &quot;Rouler sur les 4 pilliers&quot;') +'" required="required" maxlength="100" />'+
			'</label>'+
			'<div style="margin-top:0.25em"><a class="pretty-link" href="javascript:toggleGoalDescriptionTr()">'+ (language ? 'Translate description' : 'Traduire la description') +'...</a></div>'+
			'</div>'+
			'<div style="font-size:14px;margin-top:0.25em;display:none" id="goal_reach_zones_description_tr">'+
			'<label>'+ (language ? 'Description (EN): ':'Description (FR) : ') +
			'<input type="text" name="goal[description_'+(language ? 'en':'fr')+']" style="font-size:12px;width:300px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? '&quot;Pass through 8 rings&quot;, &quot;Roll over the 4 pillars&quot;':'&quot;Traverser les 8 anneaux&quot;, &quot;Rouler sur les 4 pilliers&quot;') +'" maxlength="100" />'+
			'</label><br />'+
			'<label>'+ (language ? 'Description (FR): ':'Description (EN) : ') +
			'<input type="text" name="goal[description_'+(language ? 'fr':'en')+']" style="font-size:12px;width:300px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? '&quot;Traverser les 8 anneaux&quot;, &quot;Rouler sur les 4 pilliers&quot;':'&quot;Pass through 8 rings&quot;, &quot;Roll over the 4 pillars&quot;') +'" maxlength="100" />'+
			'</label>'+
			'<div style="margin-top:0.25em"><a class="pretty-link" href="javascript:toggleGoalDescriptionTr()">'+ (language ? 'Stop translating' : 'Ne plus traduire') +'</a></div>'+
			'</div>'
		);
		break;
	case 'collect_coins':
		$extra.html(
			'<div style="margin:10px 0"><label>'+ (language ? 'Coins location: ':'Position des pièces : ') +
			'<input type="hidden" name="goal[value]" value="[]" />'+
			'<button type="button" onclick="openZoneEditor(\'coins\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'+
			'<div>'+ (language ? 'Number of coins to collect:':'Nombre de pièces à récupérer :') + '<br />'+
			'<label><input type="radio" name="goal_coins_all" onclick="toggleGoalAllOption(1)" checked="checked" value="1" />'+ (language ? "All":"Toutes") + '</label> &nbsp; '+
			'<label><input type="radio" name="goal_coins_all" onclick="toggleGoalAllOption(0)" value="0" /> '+
			'<input type="number" style="width:40px" name="goal[nb]" onfocus="selectNbCoins()" placeholder="25" min="1" autocomplete="off" /></label>' +
			'</div>'
		);
		break;
	case 'destroy_decors':
		$extra.html(
			'<div>'+ (language?'Decor:':'Décor :') +' '+
			'<input type="text" style="display:none" name="destroy_decors_fake1" required="required" >'+
			'<div class="challenge-rule-btn-options challenge-main-btn-options"></div>'+
			'<div class="challenge-rule-decor-names challenge-main-decor-names"></div>'+
			'<div class="challenge-main-decor-count">'+ (language ? 'Number of decors to destroy:':'Nombre de décors à détruire :') + '<br />'+
			'<label><input type="radio" name="goal_decors_all" onclick="toggleGoalAllOption(1)" checked="checked" value="1" />'+ (language ? "All":"Tous") + '</label> &nbsp; '+
			'<label><input type="radio" name="goal_decors_all" onclick="toggleGoalAllOption(0)" value="0" /> '+
			'<input type="number" style="width:40px" name="goal[nb]" onfocus="selectNbDecors()" placeholder="5" min="1" autocomplete="off" /></label>' +
			'</div>'
		);
		var $extraSelector = $extra.find(".challenge-main-btn-options");
		var allDecorOpions = getAllDecorOptions(typeof chRules === "undefined" ? null : chRules.main);
		for (var i=0;i<allDecorOpions.length;i++) {
			var decorOption = allDecorOpions[i];
			if (decorOption.value.startsWith("assets/")) continue;
			var decorIcon = decorOption.icon || "images/map_icons/"+ decorOption.value +".png";
			$extraSelector.append('<button type="button" data-rule-type="main" data-rule-key="destroy_decors" data-value="'+ decorOption.value +'" style="background-image:url(\''+ decorIcon +'\')" onclick="toggleDecor(this)"></button>');
		}
		break;
	}
	$extra.find("input,select").first().focus();
}
function addConstraintRule(clClass) {
	var $rule = $(
		'<div class="challenge-contraint challenge-constraint-selecting">'+
			'<div class="challenge-constraint-selector">'+
				'<select name="constraint[]" class="challenge-constraint-select" required="required"></select>'+
				'<a href="#null" class="challenge-action-undo">'+ (language ? 'Cancel':'Annuler') +'</a>'+
			'</div>'+
			'<div class="challenge-constraint-options">'+
				'<div class="challenge-constraint-form"></div>'+
				'<div class="challenge-constraint-action">'+
					'<a class="challenge-action-edit" href="#null">'+ (language ? "Edit":"Modifier") +'</a>'+
					'<a class="challenge-action-del" href="#null">'+ (language ? "Delete":"Supprimer") +'</a>'+
				'</div>'+
			'</div>'+
		'</div>'
	);
	var $form = $rule.find(".challenge-constraint-form");
	var $rulesSelector = $rule.find(".challenge-constraint-select");
	$rule.find(".challenge-action-edit").click(function() {
		$form.empty();
		var ruleId = $rulesSelector.val();
		$rulesSelector.prop("selectedIndex", 0);
		$rule.addClass("challenge-constraint-selecting");
		delete selectedConstraints[ruleId];
		updateConstraintSelectors();
		return false;
	});
	$rule.find(".challenge-action-undo, .challenge-action-del").click(function() {
		var ruleId = $rulesSelector.val();
		if (ruleId) {
			delete selectedConstraints[ruleId];
			updateConstraintSelectors();
		}
		$rule.remove();
		return false;
	});
	var listRules = clRules[clClass];
	$rulesSelector.change(function() {
		var ruleId = $rulesSelector.val();
		if (!ruleId) return;
		var rulePayload = listRules[ruleId];
		switch (ruleId) {
		case 'game_mode':
		case 'game_mode_cup':
			addConstraintSelector($form,ruleId, language?'Game mode:':'Mode de jeu :', getConstraintOptions(clClass,ruleId));
			break;
		case 'without_turning':
			addConstraintSelector($form,ruleId, language ? 'Select:' : 'Option :', [{
				label: language ? "Without turning" : "Sans tourner",
				value: ""
			}, {
				label: language ? "Without turning left" : "Sans tourner à gauche",
				value: "left"
			}, {
				label: language ? "Without turning right" : "Sans tourner à droite",
				value: "right"
			}]);
			break;
		case 'time':
			addConstraintTime($form,ruleId, language?'Time:':'Temps :',{css:{width:'50px'}});
			break;
		case 'time_delay':
			addConstraintNb($form,ruleId, language?'Delay (sec):':'Retard (sec) :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'start_pos':
			$form.html(
				'<div style="position: relative; top: -0.2em"><label>'+ (language ? 'Location: ':'Emplacement : ') +
				'<input type="hidden" name="scope[start_pos][value]" />'+
				'<button type="button" onclick="openZoneEditor(\'startpos\')">'+ (language ? "Indicate...":"Indiquer...") +'</button></label><br />'+
				'<label style="font-size: 0.8em; display: block; text-align: right"><input type="checkbox" name="scope[start_pos][no_cpu]" />&nbsp;'+ (language?'Play without CPUs':'Jouer sans ordis')+'</label></div>'
			);
			$form.parent().css({
				"align-items": "flex-start",
				"margin": "10px 0 0 0"
			});
			break;
		case 'extra_items':
			$form.html(
				'<div style="position: relative; top: -0.2em"><label>'+ (language ? 'Location: ':'Emplacement : ') +
				'<input type="hidden" name="scope[extra_items][value]" />'+
				'<button type="button" onclick="openZoneEditor(\'items\')">'+ (language ? "Indicate...":"Indiquer...") +'</button></label><br />'+
				'<label style="font-size: 0.8em; display: block; text-align: right"><input type="checkbox" name="scope[extra_items][clear_other]" />&nbsp;'+ (language?'Remove other item boxes':'Retirer les autres boîtes à objets')+'</label></div>'
			);
			$form.parent().css({
				"align-items": "flex-start",
				"margin": "10px 0 0 0"
			});
			break;
		case 'extra_decors':
			$form.html(
				'<div style="margin:10px 0"><label>'+ (language ? 'Location: ':'Emplacement : ') +
				'<input type="hidden" name="scope[extra_decors][value]" value="[]" />'+
				'<input type="hidden" name="scope[extra_decors][custom_decors]" value="{}" />'+
				'<button type="button" onclick="openZoneEditor(\'decors\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'
			);
			break;
		case 'extra_walls':
			$form.html(
				'<div style="margin:10px 0"><label>'+ (language ? 'Location: ':'Emplacement : ') +
				'<input type="hidden" name="scope[extra_walls][value]" value="[]" />'+
				'<input type="hidden" name="scope[extra_walls][height]" value="0" />'+
				'<button type="button" onclick="openZoneEditor(\'zones\',\'source=extra_walls\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'
			);
			break;
		case 'place_items':
			$form.html(
				'<div style="margin:10px 0"><label>'+ (language ? 'Location: ':'Emplacement : ') +
				'<input type="hidden" name="scope[place_items][value]" value="[]" />'+
				'<button type="button" onclick="openZoneEditor(\'arms\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'
			);
			break;
		case 'cc':
			$form.html(
				'<label>'+ (language?'Class:':'Cylindrée :') +' '+
				'<input type="text" class="challenge-constraint-value" name="scope[cc][value]" pattern="[1-9]\\d*" maxlength="3" list="scope_cc_list" required="required" style="width:60px" /> cc'+
				'</label> ·<small> </small>'+
				'<label><input type="checkbox" name="scope[cc][mirror]" />&nbsp;'+ (language?'Mirror':'Miroir')+'</label>'+
				'<datalist id="scope_cc_list">'+
					'<option value="50">'+
					'<option value="100">'+
					'<option value="150">'+
					'<option value="200">'+
				'</datalist>'
			);
			break;
		case 'position':
			addConstraintNb($form,ruleId, language?'Rank:':'Place :',{attrs:{min:1},css:{width:'40px'}});
			break;
		case 'position_lower':
			addConstraintNb($form,ruleId, language?'Max rank:':'Pos. max :',{attrs:{min:1},css:{width:'40px'}});
			break;
		case 'difficulty':
			if (clClass == "extra") {
				addConstraintSelector($form,ruleId, language?'Difficulty:':'Difficulté :', getConstraintOptions(clClass,ruleId).map(function(option) {
					return Object.assign({}, option, { value: option.value-2});
				}));
				$form.find('[name="scope['+ruleId+'][value]"]').prop("selectedIndex", 2);
			}
			else
				addConstraintAutofill($form, ruleId,rulePayload.description,0);
			break;
		case 'character':
			addConstraintSelector($form,ruleId, language?'Character:':'Perso :', persoOptions);
			$form.find('.challenge-constraint-value').change(function() {
				var $custom = $form.find('.challenge-rule-character-custom');
				var $customValue = $custom.find('[name="scope[character][custom_name]"]');
				if (this.value === '') {
					$custom.addClass('show');
					$customValue.attr('required', true);
					loadCustomCharacters($form, handleCustomCharacterSelected($form));
				}
				else {
					$custom.removeClass('show');
					$customValue.attr('required', false);
				}
			});
			$form.append(
				'<div class="challenge-rule-character-custom">'+
				'<input type="hidden" name="scope[character][custom_id]" />'+
				'<div class="challenge-rule-character-custom-list challenge-rule-btn-options">'+
				'</div>'+
				'<div class="challenge-rule-character-custom-meta character-name">'+
				(language ? 'Character name':'Nom du perso :') +': <input type="text" name="scope[character][custom_name]" />'+
				'</div>'
			)
			break;
		case 'with_opponents':
			$form.html(
				'<div class="challenge-rule-opponents-list"></div>' + 
				'<div class="challenge-rule-opponents-actions">'+
					'<button type="button" class="opponents-add">'+ (language ? 'Add CPU' : 'Ajouter') +'</button>' +
					'<button type="button" class="opponents-rm">'+ (language ? 'Remove CPU' : 'Supprimer') +'</button>' +
				'</div>'
			);
			var $list = $form.find(".challenge-rule-opponents-list");
			var $opponentsRm = $form.find(".opponents-rm");
			$opponentsRm.hide();
			$form.find(".opponents-add").click(function() {
				var $lastSelector = $list.children().last();
				addCharacterSelector($list, ruleId);
				var $selector = $list.children().last();
				var $lastValue = $lastSelector.find(".challenge-constraint-value");
				var $value = $selector.find(".challenge-constraint-value");
				$value.val($lastValue.val());
				$value.change();
				$opponentsRm.show();
			});
			$opponentsRm.click(function() {
				var $selectors = $list.children();
				$selectors.last().remove();
				if ($selectors.length <= 2)
					$opponentsRm.hide();
			});
			addCharacterSelector($list, ruleId);
			break;
		case 'avoid_decors':
			$form.html(
				'<div>'+ (language?'Decor(s):':'Décor(s) :') +' '+
				'<input type="text" style="display:none" name="avoid_decors_fake1" required="required" >'+
				'<div class="challenge-rule-btn-options challenge-constraint-btn-options"></div>'+
				'<div class="challenge-rule-decor-names challenge-constraint-decor-names"></div>'
			);
			var $extraSelector = $form.find(".challenge-constraint-btn-options");
			var allDecorOpions = getAllDecorOptions(getConstraint(clClass, ruleId));
			for (var i=0;i<allDecorOpions.length;i++) {
				var decorOption = allDecorOpions[i];
				var decorIcon = decorOption.icon || "images/map_icons/"+ decorOption.value +".png";
				$extraSelector.append('<button type="button" data-rule-type="constraint" data-rule-key="avoid_decors" data-value="'+ decorOption.value +'" style="background-image:url(\''+ decorIcon +'\')" onclick="toggleDecor(this)"></button>');
			}
			break;
		case 'avoid_zones':
			$form.html(
				'<div style="margin:10px 0"><label>'+ (language ? 'Avoid Zone(s): ':'Zone(s) à éviter : ') +
				'<input type="hidden" name="scope[avoid_zones][value]" value="[]" />'+
				'<input type="hidden" name="scope[avoid_zones][floor]" value="0" />'+
				'<input type="hidden" name="scope[avoid_zones][translated]" value="0" />'+
				'<button type="button" onclick="openZoneEditor(\'zones\',\'source=avoid_zones\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'+
				'<div style="font-size:16px" id="rule_avoid_zones_description">'+
				'<label>'+ (language ? 'Description: ':'Description : ') +
				'<input type="text" name="scope[avoid_zones][description]" style="font-size:12px;width:350px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? '&quot;without taking the shortcuts&quot;, &quot;without driving on the sand&quot;':'&quot;sans prendre de raccourcis&quot;, &quot;sans rouler sur le sable&quot;') +'" required="required" maxlength="100" />'+
				'</label>'+
				'<div style="margin-top:0.25em"><a class="pretty-link" href="javascript:toggleRuleDescriptionTr()">'+ (language ? 'Translate description' : 'Traduire la description') +'...</a></div>'+
				'</div>'+
				'<div style="font-size:14px;margin-top:0.25em;display:none" id="rule_avoid_zones_description_tr">'+
				'<label>'+ (language ? 'Description (EN): ':'Description (FR) : ') +
				'<input type="text" name="scope[avoid_zones][description_'+(language ? 'en':'fr')+']" style="font-size:12px;width:300px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? '&quot;without taking the shortcuts&quot;, &quot;without driving on the sand&quot;':'&quot;sans prendre de raccourcis&quot;, &quot;sans rouler sur le sable&quot;') +'" maxlength="100" />'+
				'</label><br />'+
				'<label>'+ (language ? 'Description (FR): ':'Description (EN) : ') +
				'<input type="text" name="scope[avoid_zones][description_'+(language ? 'fr':'en')+']" style="font-size:12px;width:300px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? '&quot;sans prendre de raccourcis&quot;, &quot;sans rouler sur le sable&quot;':'&quot;without taking the shortcuts&quot;, &quot;without driving on the sand&quot;') +'" maxlength="100" />'+
				'</label>'+
				'<div style="margin-top:0.25em"><a class="pretty-link" href="javascript:toggleRuleDescriptionTr()">'+ (language ? 'Stop translating' : 'Ne plus traduire') +'</a></div>'+
				'</div>'
			);
			break;
		case 'avoid_item':
			$form.html(
				'<div>'+ (language?'Item(s):':'Objet(s) :') +' '+
				'<input type="text" style="display:none" name="scope[avoid_item][value]" required="required" >'+
				'<div class="challenge-rule-btn-options challenge-item-btn-options"></div>'
			);
			var $extraSelector = $form.find(".challenge-item-btn-options");
			var itemOptions = getItemHitOptions();

			for (var i=0;i<itemOptions.length;i++) {
				var itemOption = itemOptions[i];
				var itemIcon = "images/items/"+ itemOption +".png";
				$extraSelector.append('<button type="button" data-value="'+ itemOption +'" data-rule-key="avoid_item" style="background-image:url(\''+ itemIcon +'\')" onclick="toggleItem(this)"></button>');
			}
			break;
		case 'init_item':
			$form.html(
				'<div>'+ (language?'Item:':'Objet :') +' '+
				'<input type="text" style="display:none" name="scope[init_item][value]" >'+
				'<div class="challenge-rule-btn-options challenge-item-btn-options"></div>'
			);
			var $extraSelector = $form.find(".challenge-item-btn-options");
			$extraSelector.append('<button type="button" data-value="" data-rule-key="init_item" style="background-image:url(\'images/challenges/noitem.png\')" title="'+ (language ? "No item" : "Aucun objet") +'" onclick="toggleItem(this)"></button>');
			var itemOptions = getItemOptions();
			for (var i=0;i<itemOptions.length;i++) {
				var itemOption = itemOptions[i];
				var itemIcon = "images/items/"+ itemOption +".png";
				$extraSelector.append('<button type="button" data-value="'+ itemOption +'" data-rule-key="init_item" style="background-image:url(\''+ itemIcon +'\')" onclick="toggleItem(this)"></button>');
			}
			break;
		case 'item_distribution':
			$form.html(
				'<div>'+ (language?'Item(s):':'Objet(s) :') +' '+
				'<input type="text" style="display:none" name="scope[item_distribution][value]" required="required" >'+
				'<div class="challenge-rule-btn-options challenge-item-btn-options"></div>'
			);
			var $extraSelector = $form.find(".challenge-item-btn-options");
			var itemOptions = getItemOptions();
			for (var i=0;i<itemOptions.length;i++) {
				var itemOption = itemOptions[i];
				var itemIcon = "images/items/"+ itemOption +".png";
				$extraSelector.append('<button type="button" data-value="'+ itemOption +'" data-rule-key="item_distribution" style="background-image:url(\''+ itemIcon +'\')" onclick="toggleItem(this)"></button>');
			}
			break;
		case 'custom_music':
			$form.html(
				'<div>'+ (language?'Music:':'Musique :') +' '+
				'<div class="challenge-rule-music-source">'+
				'<label><input type="radio" name="scope_music_source" onclick="toggleMusicOption(0)" checked="checked" value="1" />'+ (language ? "MKPC track music":"Circuits MKPC") + '</label> &nbsp; '+
				'<label><input type="radio" name="scope_music_source" onclick="toggleMusicOption(1)" value="0" /> '+ (language ? "Youtube music":"Youtube") + '</label>' +
				'</div>'+
				'<div class="challenge-rule-music-value challenge-rule-music-value-base">'+
				'<select class="challenge-constraint-value" name="scope[custom_music][value]">'+
				'</select>'+
				'</div>'+
				'<div class="challenge-rule-music-value challenge-rule-music-value-youtube">'+
				'<label>'+ (language ? "Youtube URL:":"URL Youtube :") +' '+
				'<input type="text" class="challenge-constraint-value" name="scope[custom_music][yt]" placeholder="https://www.youtube.com/watch?v=NNMy4DKKDFA" />'+
				'</label>'+
				'</div>'+
				'</div>'
			);
			initMusicOptions($form.find("select[name='scope[custom_music][value]']"));
			break;
		case 'balloons':
			addConstraintNb($form,ruleId, language?'Nb balloons:':'Nb ballons :',{attrs:{min:0},css:{width:'30px'}});
			break;
		case 'balloons_lost':
			addConstraintNb($form,ruleId, language?'Max lost balloons:':'Max ballons perdus :',{attrs:{min:0},css:{width:'30px'}});
			break;
		case 'balloons_inflate':
			addConstraintNb($form,ruleId, language?'Max inflated balloons:':'Max ballons gonflés :',{attrs:{min:0},css:{width:'30px'}});
			break;
		case 'balloons_player':
			addConstraintNb($form,ruleId, language?'Player balloons:':'Nb ballons (joueur) :',{attrs:{min:1},css:{width:'30px'}});
			break;
		case 'balloons_cpu':
			addConstraintNb($form,ruleId, language?'CPUs balloons:':'Nb ballons (ordis) :',{attrs:{min:1},css:{width:'30px'}});
			break;
		case 'falls':
			addConstraintNb($form,ruleId, language?'Max falls:':'Max chutes :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'max_jumps':
			addConstraintNb($form,ruleId, language?'Max jumps:':'Max sauts :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'max_cannons':
			addConstraintNb($form,ruleId, language?'Max cannons:':'Max canons :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'mini_turbo':
			addConstraintNb($form,ruleId, language?'Mini Turbos:':'Mini Turbos :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'super_turbo':
			addConstraintNb($form,ruleId, language?'Super Turbos:':'Super Turbos :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'stunts':
			addConstraintNb($form,ruleId, language?'Tricks:':'Figures :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'with_pts':
			addConstraintNb($form,ruleId, language?'Nb points:':'Nb points :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'participants':
			if (clClass == "extra")
				addConstraintNb($form,ruleId, language?'Nb participants:':'Nb participants :',{attrs:{min:0,max:999},css:{width:'50px'}});
			else
				addConstraintAutofill($form, ruleId,rulePayload.description,8);
			break;
		default:
			$form.html(rulePayload.description);
			break;
		}
		$rule.removeClass("challenge-constraint-selecting");
		selectedConstraints[ruleId] = true;
		updateConstraintSelectors();
		$form.find("input,select").first().focus();
	});
	updateConstraintSelector(clClass,$rulesSelector);
	$("#challenge-"+clClass+"-list").append($rule);
	return $rule;
}
function updateConstraintSelectors() {
	for (var clClass in clRules) {
		var $selectors = $("#challenge-"+clClass+"-list > .challenge-constraint-selecting select");
		$selectors.each(function(id,rulesSelector) {
			updateConstraintSelector(clClass,$(rulesSelector));
		});
	}
}
function updateConstraintSelector(clClass,$rulesSelector) {
	var listRules = clRules[clClass];
	$rulesSelector.empty();
	var selectConstraintLabel = language ? 'Select constraint...':'Sélectionner contrainte...';
	if (clClass === "setup")
		selectConstraintLabel = language ? 'Select option...':'Sélectionner option...';
	$rulesSelector.append('<option value="">'+ selectConstraintLabel +'</option>');

	var constraintGroups = {};
	for (var j=0;j<clGroups.length;j++) {
		var clGroup = clGroups[j];
		constraintGroups[clGroup.key] = { label: clGroup.label, list: [] };
	}
	for (var ruleId in listRules) {
		if (!selectedConstraints[ruleId]) {
			var group = listRules[ruleId].group;
			if (!constraintGroups[group])
				constraintGroups[group] = { list: [] };
			constraintGroups[group].list.push({
				ruleId: ruleId,
				description: listRules[ruleId].description
			});
		}
	}
	for (var group in constraintGroups) {
		var constraintGroup = constraintGroups[group];
		var groupConstraints = constraintGroup.list;
		if (!groupConstraints.length) continue;
		var $optGroup = $rulesSelector;
		if (constraintGroup.label)
			$optGroup = $('<optgroup label="'+ constraintGroup.label +'"></optgroup>');
		for (var j=0;j<groupConstraints.length;j++) {
			var groupConstraint = groupConstraints[j];
			var $option = $('<option value="'+ groupConstraint.ruleId +'">'+ groupConstraint.description +'</option>');
			$optGroup.append($option);
		}
		if (constraintGroup.label)
			$rulesSelector.append($optGroup);
	}
}
function getConstraintOptions(clClass,ruleId) {
	var ruleOptions = clRules[clClass][ruleId].scope.options;
	var res = [];
	for (var i=0;i<ruleOptions.length;i++)
		res.push({label:ucfirst(ruleOptions[i]),value:i})
	return res;
}
function addConstraintSelector($form,ruleId,label,options) {
	addCustomSelector($form, "scope["+ruleId+"][value]", label, options);
}
function addCustomSelector($div,name,label,options) {
	$div.html(
		'<label>'+ label +' '+
		'<select class="challenge-constraint-value" name="'+name+'">'+
		'</select>'+
		'</label>'
	);
	var $extraSelector = $div.find(".challenge-constraint-value");
	for (var i=0;i<options.length;i++)
		$extraSelector.append('<option value="'+ options[i].value +'">'+ options[i].label +'</option>');
}
function addConstraintNb($form,ruleId,label,options) {
	addConstraintInput($form,ruleId,label,"number",options);
}
function addConstraintTime($form,ruleId,label,options) {
	if (!options) options = {};
	if (!options.attrs) options.attrs = {};
	options.attrs["placeholder"] = "1:30";
	options.attrs["pattern"] = "\\d*(:\\d*){0,2}";
	addConstraintInput($form,ruleId,label,"text",options);
}
function addConstraintInput($form,ruleId,label,inputType,options) {
	if (!options) options = {};
	var htmlAttrs = "";
	if (options.attrs) {
		for (var key in options.attrs)
			htmlAttrs += ' '+key+'="'+options.attrs[key]+'"';
	}
	if (options.css) {
		var cssAttrs = "";
		for (var key in options.css)
			cssAttrs += key+':'+options.css[key]+'";';
		htmlAttrs += 'style="'+ cssAttrs +'"';
	}
	$form.html(
		'<label>'+ label +' '+
		'<input type="'+inputType+'" class="challenge-constraint-value" name="scope['+ruleId+'][value]" required="required"'+htmlAttrs+' />'+
		'</label>'
	);
}
function addConstraintAutofill($form,ruleId,label,value) {
	$form.html('<input type="hidden" name="scope['+ruleId+'][value]" value="'+value+'" />'+ label);
}
function getAllDecorOptions(constraint) {
	var res = {};
	for (var i=0;i<decorOptions.length;i++)
		res[decorOptions[i].value] = decorOptions[i];
	function addDecor(key) {
		if (!res[key])
			res[key] = { value: key };
	}
	if (constraint) {
		var cVal = constraint.value;
		switch (constraint.type) {
		case "avoid_decors":
			for (var key in cVal)
				addDecor(key);
			break;
		case "destroy_decors":
			addDecor(cVal);
			break;
		}
	}
	try {
		var $extraDecors = $('.challenge-edit-form input[name="scope[extra_decors][value]"]');
		var extraDecors = $extraDecors.val();
		if (!extraDecors) {
			extraDecors = chRules.setup.find(function(chRule) {
				return chRule.type === "extra_decors";
			}).value;
		}
		if (extraDecors) {
			var extraDecorsList = JSON.parse(extraDecors);
			for (var i=0;i<extraDecorsList.length;i++) {
				var decorKey = extraDecorsList[i].src;
				if (!decorKey.startsWith("custom-"))
					addDecor(decorKey);
			}
		}
	}
	catch (e) {
		// No extra decor, ignore
	}
	return Object.values(res);
}
function getConstraint(clClass, ruleId) {
	if (typeof chRules === "undefined")
		return null;
	return chRules[clClass].find(function(constraintRule) {
		return constraintRule.type === ruleId;
	});
}
function ucfirst(word) {
	if (!word) return word;
	return word.charAt(0).toUpperCase() + word.substring(1);
}
var customCharacters;
var customCharacterPromise;
var customCharactersCallbacks = [];
function loadCustomCharacters($div, onSelect) {
	if (customCharacters) {
		appendCustomCharacters($div, onSelect);
		return;
	}
	if (!customCharacterPromise) {
		customCharacterPromise = new Promise(function(resolve) {
			o_xhr("myPlayablePersos.php", "", function(res) {
				try {
					customCharacters = JSON.parse(res);
				}
				catch (e) {
					return false;
				}
				resolve();
				return true;
			});
		});
	}
	customCharacterPromise.then(function() {
		appendCustomCharacters($div, onSelect);
	});
}
function appendCustomCharacters($div, onSelect) {
	var $customList = $div.find(".challenge-rule-character-custom-list");
	$customList.empty();
	for (var i=0;i<customCharacters.length;i++) {
		var character = customCharacters[i];
		var characterIcon = character.ld;
		var $charBtn = $('<button type="button" data-value="'+ character.id +'" style="background-image:url(\''+ characterIcon +'\')"></button>');
		$charBtn.click(function() {
			selectCharacter(this, onSelect);
		});
		$charBtn.attr("title", character.name);
		$customList.append($charBtn);
	}
	var $extraBtn = $('<button type="button" class="character-extra"></button>');
	$extraBtn.click(function() {
		selectExtraCharacter($div, onSelect);
	});
	$customList.append($extraBtn);
	setTimeout(function() {
		customCharactersCallbacks.forEach(function(callback) {
			callback();
		});
		customCharactersCallbacks.length = 0;
	});
}
function selectCharacter(btn, onSelect) {
	if (btn.dataset.selected) {
		delete btn.dataset.selected;
		onSelect(null);
	}
	else {
		var previouslySelected = btn.parentNode.querySelectorAll('button[data-selected="1"]');
		for (var i=0;i<previouslySelected.length;i++) {
			var iBtn = previouslySelected[i];
			delete iBtn.dataset.selected;
		}
		btn.dataset.selected = "1";
		onSelect(btn);
	}
}
function selectExtraCharacter($div, onSelect) {
	var $collabPopup = document.getElementById("collab-popup-char");
	$collabPopup.dataset.state = "open";

	closeCollabImportPopup = function() {
		document.removeEventListener("keydown", hideOnEscape);
		delete $collabPopup.dataset.state;
	}
	collabImportCallback = function(data) {
		handleCollabCharacterSelected($div, data, onSelect);
	}
	function hideOnEscape(e) {
		switch (e.keyCode) {
		case 27:
			closeCollabImportPopup();
		}
	}
	document.addEventListener("keydown", hideOnEscape);
	$collabPopup.querySelector('input[name="collablink"]').focus();
}

var closeCollabImportPopup, collabImportCallback;
function importCollabChar(e) {
	e.preventDefault();
	var $form = e.target;
	var url = $form.elements["collablink"].value;
	var urlParams = new URLSearchParams(new URL(url).search);
	var creationId, creationType, creationKey, creationMode;
	try {
		creationId = urlParams.get('id');
		creationKey = urlParams.get('collab');
	}
	catch (e) {
	}
	if (!creationKey) {
		alert(language ? "Invalid URL" : "URL invalide");
		return;
	}
	var $collabPopup = document.getElementById("collab-popup-char");
	$collabPopup.dataset.state = "loading";
	o_xhr("importCollabPerso.php", "id="+creationId+"&collab="+creationKey, function(res) {
		if (!res) {
			alert(language ? "Invalid link" : "Lien invalide");
			$collabPopup.dataset.state = "open";
			return true;
		}
		collabImportCallback(JSON.parse(res));

		closeCollabImportPopup();
		$form.reset();
		return true;
	});
}
function handleCollabCharacterSelected($div, data, onSelect) {
	var $customList = $div.find(".challenge-rule-character-custom-list");
	var $charBtn = $('<button type="button" data-value="'+ data.id +'" style="background-image:url(\''+ data.ld +'\')"></button>');
	$charBtn.attr("title", data.name);
	$charBtn.click(function() {
		selectCharacter(this, onSelect);
	});

	var $extraBtn = $customList.find(".character-extra");
	$charBtn.insertBefore($extraBtn);
	
	$charBtn.click();
}

function addCharacterSelector($div, ruleId) {
	var $selector = $('<div></div>');
	var id = $div.children().length;
	var nb = id+1;
	addCustomSelector($selector, "scope["+ruleId+"][value]["+id+"]", language?'CPU '+nb+':':'Ordi '+nb+' :', persoOptions);
	$selector.find('.challenge-constraint-value').change(function() {
		var $custom = $selector.find('.challenge-rule-character-custom');
		var $customValue = $custom.find('[name="scope['+ruleId+'][custom_id]['+id+']"]');
		if (this.value === '') {
			$custom.addClass('show');
			$customValue.attr('required', true);
			loadCustomCharacters($selector, handleCustomOpponentSelected($selector, ruleId, id));
		}
		else {
			$custom.removeClass('show');
			$customValue.attr('required', false);
		}
	});
	$selector.append(
		'<div class="challenge-rule-character-custom">'+
		'<input type="text" name="scope['+ruleId+'][custom_id]['+id+']" />'+
		'<div class="challenge-rule-character-custom-list challenge-rule-btn-options">'+
		'</div>'
	);
	$div.append($selector);
}
function handleCustomCharacterSelected($form) {
	return function(btn) {
		var $customId = $form.find('[name="scope[character][custom_id]"]');
		var $customName = $form.find('[name="scope[character][custom_name]"]');
		var $customNameCtn = $form.find(".challenge-rule-character-custom-meta.character-name");
		if (btn) {
			$customId.val(btn.dataset.value);
			$customName.val(btn.title);
			$customNameCtn.show();
		}
		else {
			$customId.val("");
			$customNameCtn.hide();
		}
	};
}
function handleCustomOpponentSelected($selector, ruleId, id) {
	return function(btn) {
		var $customId = $selector.find('[name="scope['+ruleId+'][custom_id]['+id+']"]');
		if (btn)
			$customId.val(btn.dataset.value);
		else
			$customId.val("");
	}
}

var validationUndone;
function undoValidation() {
	if (validationUndone) return;
	validationUndone = true;
	o_xhr("challengeUpdateStatus.php", "challenge="+challengeId+"&status=pending_completion", function(res) {
		if (res) {
			document.location.reload();
			return true;
		}
		return false;
	});
}
function getZoneInputKey(editorType,editorSource) {
	switch (editorType) {
	case "startpos":
		return "scope[start_pos]";
	case "decors":
		return "scope[extra_decors]";
	case "items":
		return "scope[extra_items]";
	case "zones":
		if (editorSource === 'avoid_zones')
			return "scope[avoid_zones]";
		if (editorSource === 'extra_walls')
			return "scope[extra_walls]";
		return "goal";
	case "arms":
		return "scope[place_items]";
	default:
		return "goal";
	}
}
function loadZoneData(editorType,editorSource) {
	var inputKey = getZoneInputKey(editorType,editorSource);
	var data = document.forms[0].elements[inputKey+"[value]"].value;
	var meta = {};
	var metaKeys = ["ordered","floor","height","custom_decors"];
	for (var i=0;i<metaKeys.length;i++) {
		var $elt = document.forms[0].elements[inputKey+"["+metaKeys[i]+"]"];
		if ($elt)
			meta[metaKeys[i]] = $elt.value;
	}
	var mainForm = document.forms[0];
	var $extraDecorsInput = mainForm.elements["scope[extra_decors][value]"];
	if ($extraDecorsInput && $extraDecorsInput.value) {
		var extraDecorsData = JSON.parse($extraDecorsInput.value);
		meta.extra_decors = extraDecorsData;
	}
	return {
		data: JSON.parse(data),
		meta: meta
	}
}
function storeZoneData(data,meta, editorType,editorSource) {
	var inputKey = getZoneInputKey(editorType,editorSource);
	document.forms[0].elements[inputKey+"[value]"].value = JSON.stringify(data);
	for (var key in meta) {
		var $elt = document.forms[0].elements[inputKey+"["+key+"]"];
		if ($elt) {
			var metaVal = meta[key];
			if (typeof metaVal === "object")
				metaVal = JSON.stringify(metaVal);
			$elt.value = metaVal;
		}
	}
}
function openZoneEditor(type, extra) {
	window.open(document.location.href.replace("challengeEdit.php","challengeZone.php")+(type?("&type="+type):"")+(extra?"&"+extra:""),'chose','scrollbars=1, resizable=1, width=800, height=600');
}
function toggleGoalAllOption(value) {
	if (value == 1)
		document.forms[0].elements["goal[nb]"].value = "";
	else
		document.forms[0].elements["goal[nb]"].focus();
}
function selectNbCoins() {
	document.forms[0].elements["goal_coins_all"].value = 0;
}
function selectNbDecors() {
	document.forms[0].elements["goal_decors_all"].value = 0;
}
function toggleMusicOption(option) {
	if (option == 1) {
		$(".challenge-rule-music-value-base").hide();
		$(".challenge-rule-music-value-youtube").show();
		$('input[name="scope[custom_music][yt]"]').focus();
	}
	else {
		$(".challenge-rule-music-value-youtube").hide();
		$(".challenge-rule-music-value-base").show();
		$('input[name="scope[custom_music][yt]"]').val('');
	}
}
function initMusicOptions($select) {
	var musicOptions = {
		'SNES': language ? ['Mario Circuit', 'Donut Plains', 'Koopa Beach', 'Choco Island', 'Vanilla Lake', 'Ghost Valley', 'Bowser Castle', 'Rainbow Road', 'Battle Course'] : ['Circuit Mario', 'Plaine Donut', 'Plage Koopa', 'Île Choco', 'Lac Vanille', 'Vallée Fantôme', 'Château de Bowser', 'Route Arc-en-Ciel', 'Arène Bataille'],
		'GBA': language ? ['Mario Circuit', 'Shy Guy Beach', 'Riverside Park', 'Bowser Castle', 'Boo Lake', 'Cheese Land', 'Sky Garden', 'Sunset Wilds', 'Snow Land', 'Ribbon Road', 'Yoshi Desert', 'Lakeside Park', 'Rainbow Road', 'Battle Course'] : ['Circuit Mario', 'Plage Maskass', 'Bord du Fleuve', 'Château de Bowser', 'Lac Boo', 'Pays Fromage', 'Jardin Volant', 'Pays Crépuscule', 'Royaume Sorbet', 'Route Ruban', 'Désert Yoshi', 'Bord du Lac', 'Route Arc-en-Ciel', 'Arène Bataille'],
		'DS': language ? ['Figure 8 Circuit','Yoshi Falls','Cheep Cheep Beach','Luigi\'s Mansion','Desert Hills','Delfino Square','Waluigi Pinball','Shroom Ridge','DK Pass','Tick-Tock Clock','Airship Fortress','Peach Gardens','Bowser\'s Castle', 'Rainbow Road', 'Nintendo DS', 'Twilight House', 'Palm Shore', 'Tart Top'] : ['Circuit en 8', 'Cascade Yoshi', 'Plage Cheep-Cheep', 'Manoir de Luigi', 'Désert du Soleil', 'Quartier Delfino', 'Flipper Waluigi', 'Corniche Champignon', 'Alpes DK', 'Horloge Tic-Tac', 'Bateau Volant', 'Jardin Peach', 'Château de Bowser', 'Route Arc-en-Ciel', 'Nintendo DS', 'Maison de l\'Aube', 'Feuille de Palmier', 'Tarte Sucrée']
	};
	var inc = 0;
	for (var group in musicOptions) {
		var $optgroup = $('<optgroup label="'+ group +'"></optgroup>');
		var groupOptions = musicOptions[group];
		for (var i=0;i<groupOptions.length;i++) {
			inc++;
			var option = groupOptions[i];
			$optgroup.append('<option value="'+ inc +'">'+ option +'</option>');
		}
		$select.append($optgroup);
	}
	toggleMusicOption(0);
}
function toggleDecor(btn, label) {
	var ruleType = btn.dataset.ruleType;
	var ruleKey = btn.dataset.ruleKey;
	var multiSelectAllowed = (ruleKey === "avoid_decors");
	if (multiSelectAllowed) {
		if (btn.dataset.selected)
			delete btn.dataset.selected;
		else
			btn.dataset.selected = "1";
	}
	else {
		var previouslySelected = btn.parentNode.querySelectorAll('button[data-selected="1"]');
		for (var i=0;i<previouslySelected.length;i++) {
			var iBtn = previouslySelected[i];
			delete iBtn.dataset.selected;
			var decorId = ruleKey + "_name_"+ iBtn.dataset.value.replace(/\//g, "-");
			$("#"+decorId).remove();
		}
		btn.dataset.selected = "1";
	}
	
	var decorOption = decorOptions.find(function(decorOption) {
		return (decorOption.value === btn.dataset.value);
	}) || { value: btn.dataset.value };
	var decorIcon = decorOption.icon || "images/map_icons/"+ decorOption.value +".png";
	var decorId = ruleKey + "_name_"+ decorOption.value.replace(/\//g, "-");
	$decorNameCtn = $(".challenge-"+ ruleType +"-decor-names");
	if (btn.dataset.selected) {
		var decorNameKey = (ruleType === "main") ? 'goal[value]['+ decorOption.value +'][name]' : 'scope['+ ruleKey +'][value]['+ decorOption.value +'][name]';
		var $decorNameSelector = $('<div id="'+ decorId +'">'+
			'<img src="'+ decorIcon +'" alt="'+ decorOption.value +'" /> '+
			(language ? 'Decor name:':'Nom du décor :') + ' <input type="text" name="'+ decorNameKey +'" />'+
		'</div>');
		if (!label) label = decorOption.label;
		if (!label) label = "";
		var $decorNameInput = $decorNameSelector.find('input[name="'+ decorNameKey +'"]');
		$decorNameInput.val(label);
		$decorNameCtn.append($decorNameSelector);
		if (decorOption.custom)
			$decorNameInput.attr("required", true)
		else
			$decorNameSelector.hide();
		
		if (ruleKey === "destroy_decors")
			$(".challenge-main-decor-count").show();
	}
	else
		$("#"+decorId).remove();
	$("input[name='"+ ruleKey +"_fake1']").val($decorNameCtn.children().length ? "1":"");
	btn.blur();
}
function toggleItem(btn) {
	var ruleKey = btn.dataset.ruleKey;
	var multiSelectAllowed = (ruleKey === "item_distribution") || (ruleKey === "avoid_item");
	if (multiSelectAllowed) {
		if (btn.dataset.selected)
			delete btn.dataset.selected;
		else
			btn.dataset.selected = "1";
		var selectedBtns = $(btn).parent().find('button[data-selected="1"]');
		var res = [];
		selectedBtns.each(function(i, selectedBtn) {
			res.push(selectedBtn.dataset.value);
		});
		$("input[name='scope["+ ruleKey +"][value]']").val(res.join(","));
	}
	else {
		var previouslySelected = $(btn).parent().find('button[data-selected="1"]');
		previouslySelected.removeAttr("data-selected");
		btn.dataset.selected = "1";
		$("input[name='scope["+ ruleKey +"][value]']").val(btn.dataset.value);
	}
	btn.blur();
}
function toggleGoalDescriptionTr() {
	var ruleValue = $("#challenge-main-rule").val();
	toggleDescriptionTr("goal_"+ruleValue, "goal");
}
function toggleRuleDescriptionTr() {
	toggleDescriptionTr("rule_avoid_zones", "scope[avoid_zones]");
}
function toggleDescriptionTr(idPrefix, namePrefix) {
	var $descriptionDiv = document.getElementById(idPrefix+"_description");
	var $descriptionTrDiv = document.getElementById(idPrefix+"_description_tr");
	var mainForm = document.forms[0];
	var $trStateInput = mainForm.elements[namePrefix+"[translated]"];
	if ($trStateInput.value == 1) {
		$trStateInput.value = 0;
		$descriptionTrDiv.style.display = "none";
		$descriptionDiv.style.display = "block";
		mainForm.elements[namePrefix+"[description]"].required = true;
		mainForm.elements[namePrefix+"[description_fr]"].required = false;
		mainForm.elements[namePrefix+"[description_en]"].required = false;
	}
	else {
		$trStateInput.value = 1;
		$descriptionDiv.style.display = "none";
		$descriptionTrDiv.style.display = "block";
		mainForm.elements[namePrefix+"[description]"].required = false;
		mainForm.elements[namePrefix+"[description_fr]"].required = true;
		mainForm.elements[namePrefix+"[description_en]"].required = true;
		var $trInput = mainForm.elements[namePrefix+"[description_"+(language ? "en":"fr")+"]"];
		if (!$trInput.value)
			$trInput.value = mainForm.elements[namePrefix+"[description]"].value;
	}
}
function helpDifficulty() {
	window.open('<?php echo $language ? 'helpDifficulty':'aideDifficulty'; ?>.html','gerer','scrollbars=1, resizable=1, width=500, height=500');
}
function toggleSetupOptions() {
	$("#challenge-setup-options").slideToggle();
}
$(function() {
	if (typeof chRules != 'undefined') {
		var mainForm = document.forms[0];
		var mainRule = chRules.main;
		$("#challenge-main-rule").val(mainRule.type).change();
		var acceptedKeys = ["value","ordered","pts"];
		for (var i=0;i<acceptedKeys.length;i++) {
			var key = acceptedKeys[i];
			if (mainRule[key]) {
				var ruleElt = mainForm.elements["goal["+key+"]"];
				if (ruleElt) ruleElt.value = mainRule[key];
			}
		}
		switch (mainRule.type) {
		case "reach_zone":
		case "reach_zones":
			if (typeof mainRule.description === "string")
				mainForm.elements["goal[description]"].value = mainRule.description;
			else {
				toggleGoalDescriptionTr();
				mainForm.elements["goal[description]"];
				mainForm.elements["goal[description_fr]"].value = mainRule.description.fr;
				mainForm.elements["goal[description_en]"].value = mainRule.description.en;
			}
			break;
		}
		var constraintClasses = ["basic", "extra", "setup"];
		for (var i=0;i<constraintClasses.length;i++) {
			var constraintClass = constraintClasses[i];
			var constraintRules = chRules[constraintClass];
			for (var j=0;j<constraintRules.length;j++) {
				var constraint = constraintRules[j];
				var $rule = addConstraintRule(constraintClass);
				var $rulesSelector = $rule.find(".challenge-constraint-select");
				$rulesSelector.val(constraint.type).change();
				if (constraint.value != undefined) {
					var formElt = mainForm.elements["scope["+constraint.type+"][value]"];
					if (formElt) formElt.value = constraint.value;
					switch (constraint.type) {
					case "avoid_decors":
						for (var key in constraint.value) {
							var decorData = constraint.value[key];
							var btn = mainForm.querySelector(".challenge-constraint-btn-options button[data-value='"+ key +"']");
							if (btn)
								toggleDecor(btn, decorData.name);
						}
						break;
					case "avoid_zones":
						if (constraint.floor)
							mainForm.elements["scope[avoid_zones][floor]"].value = "1";
						if (typeof constraint.description === "string")
							mainForm.elements["scope[avoid_zones][description]"].value = constraint.description;
						else {
							toggleRuleDescriptionTr();
							mainForm.elements["scope[avoid_zones][description]"];
							mainForm.elements["scope[avoid_zones][description_fr]"].value = constraint.description.fr;
							mainForm.elements["scope[avoid_zones][description_en]"].value = constraint.description.en;
						}
						break;
					case "extra_walls":
						if (constraint.height)
							mainForm.elements["scope[extra_walls][height]"].value = constraint.height;
						break;
					case "character":
						if (constraint.custom_id) {
							(function($rule, constraint) {
								customCharactersCallbacks.push(function() {
									var $valBtn = $rule.find(".challenge-rule-character-custom-list button[data-value='"+ constraint.custom_id +"']");
									if ($valBtn.length) {
										if (constraint.custom_name)
											$valBtn.attr("title", constraint.custom_name);
										$valBtn.click();
									}
									else {
										o_xhr("getPersoData.php?id="+constraint.custom_id, null, function(res) {
											if (res) {
												var data = JSON.parse(res);
												data.name = constraint.custom_name;
												handleCollabCharacterSelected($rule, data, handleCustomCharacterSelected($rule));
											}
											return true;
										});
									}
								});
							})($rule, constraint);
							$(formElt).change();
						}
						break;
					case "with_opponents":
						var values = constraint.value;
						var $list = $rule.find(".challenge-rule-opponents-list");
						var ruleId = constraint.type;
						for (var k=0;k<values.length;k++) {
							if (k)
								addCharacterSelector($list, ruleId);
							var $selector = $list.children().eq(k);
							var $valSelect = $selector.find("select[name='scope["+ruleId+"][value]["+k+"]']");
							var value = values[k];
							if (typeof value === "number") {
								(function($selector, id, ruleId, value) {
									customCharactersCallbacks.push(function() {
										var $valBtn = $selector.find(".challenge-rule-character-custom-list button[data-value='"+ value +"']");
										if ($valBtn.length)
											$valBtn.click();
										else {
											o_xhr("getPersoData.php?id="+value, null, function(res) {
												if (res) {
													var data = JSON.parse(res);
													handleCollabCharacterSelected($selector, data, handleCustomOpponentSelected($selector, ruleId, id));
												}
												return true;
											});
										}
									});
								})($selector, k, ruleId, value);
								$valSelect.val("");
								$valSelect.change();
							}
							else
								$valSelect.val(value);
						}
						break;
					case "init_item":
						var btn = mainForm.querySelector(".challenge-item-btn-options button[data-rule-key='"+ constraint.type +"'][data-value='"+ constraint.value +"']");
						if (btn)
							toggleItem(btn);
						break;
					case "item_distribution":
					case "avoid_item":
						for (var k=0;k<constraint.value.length;k++) {
							var btn = mainForm.querySelector(".challenge-item-btn-options button[data-rule-key='"+ constraint.type +"'][data-value='"+ constraint.value[k] +"']");
							if (btn)
								toggleItem(btn);
						}
						break;
					case "custom_music":
						if (constraint.yt) {
							toggleMusicOption(1);
							mainForm.elements["scope_music_source"][1].checked = true;
							mainForm.elements["scope[custom_music][yt]"].value = constraint.yt;
							mainForm.elements["scope[custom_music][value]"].selectedIndex = 0;
						}
						break;
					case "cc":
						var mirrorElt = mainForm.elements["scope["+constraint.type+"][mirror]"];
						if (mirrorElt) mirrorElt.checked = constraint.mirror;
						break;
					case "start_pos":
						var noCpuElt = mainForm.elements["scope["+constraint.type+"][no_cpu]"];
						if (noCpuElt) noCpuElt.checked = constraint.no_cpu;
						break;
					case "extra_items":
						var noCpuElt = mainForm.elements["scope["+constraint.type+"][clear_other]"];
						if (noCpuElt) noCpuElt.checked = constraint.clear_other;
						break;
					case "extra_decors":
						var customDecorsElt = mainForm.elements["scope["+constraint.type+"][custom_decors]"];
						if (customDecorsElt) customDecorsElt.value = JSON.stringify(constraint.custom_decors);
						break;
					}
				}
			}
		}
		switch (mainRule.type) {
		case "collect_coins":
			if (mainRule.nb) {
				selectNbCoins();
				mainForm.elements["goal[nb]"].value = mainRule.nb;
			}
			break;
		case "destroy_decors":
			var key = mainRule.value;
			var btn = mainForm.querySelector(".challenge-main-btn-options button[data-value='"+ key +"']");
			if (btn)
				toggleDecor(btn, mainRule.name);
			if (mainRule.nb) {
				selectNbDecors();
				mainForm.elements["goal[nb]"].value = mainRule.nb;
			}
		}
		if (document.activeElement)
			document.activeElement.blur();
	}
});
function getItemOptions() {
	if (clCourse === "battle")
		return ["fauxobjet","banane","carapacerouge","carapace","bobomb","bananeX3","carapaceX3","carapacebleue","carapacerougeX3","megachampi","etoile","champi","champior","champiX3","bloops"];
	else
		return ["fauxobjet","banane","carapace","bananeX3","carapacerouge","champi","carapaceX3","poison","bobomb","bloops","champiX3","carapacerougeX3","megachampi","etoile","champior","carapacebleue","billball","eclair"];
}
function getItemHitOptions() {
	var res = ["fauxobjet","banane","carapace","carapacerouge","bobomb","carapacebleue","megachampi","etoile"];
	if (clCourse === "battle")
		res.push("champi");
	else {
		res.push("billball");
		res.push("eclair");
		res.push("poison");
	}
	return res;
}
</script>
</head>
<body>
	<form method="post" action="" class="challenge-edit-form">
		<h1 class="challenge-main-title"><?php
		if (isset($challenge))
			echo $language ? 'Edit challenge' : 'Modifier un défi';
		else
			echo $language ? 'Add challenge' : 'Ajouter un défi';
		?></h1>
		<?php
		if (isset($clRulesPayload)) {
			if (isset($_GET['clmsg']) && ('challenge_edited'==$_GET['clmsg']))
				echo '<div class="challenge-msg-success">'. ($language ? 'The challenge has been edited':'Le défi a été modifié') .'. <a href="javascript:window.close()">'. ($language ? 'Back':'Retour') .'</a></div>';
			?>
			<fieldset class="challenge-main">
				<legend><?php echo $language ? 'Main object':'Objectif principal'; ?></legend>
				<select name="goal[type]" id="challenge-main-rule" required="required" onchange="selectMainRule()">
					<option value=""><?php echo $language ? 'Select object...':'Sélectionner objectif...'; ?></option>
					<?php
					foreach ($clRulesByType['main'] as $ruleId=>$rule) {
						if (isRuleElligible($rule,$clCourse)) {
							$scope = isset($rule['placeholder']) ? $rule['placeholder']:array();
							$scope['type'] = $ruleId;
							$scope['mockup'] = true;
							?>
							<option value="<?php echo $ruleId; ?>"><?php echo getRuleDescription($scope); ?></option>
							<?php
						}
					}
					?>
				</select>
				<div class="challenge-main-extra" id="challenge-main-extra"></div>
			</fieldset>
			<fieldset class="challenge-basic">
				<legend><?php echo $language ? 'Constraints':'Contraintes'; ?></legend>
				<h2><?php echo $language ? 'Basic constraints':'Contraintes de base'; ?></h2>
				<div class="challenge-constraints-list" id="challenge-basic-list"></div>
				<button type="button" onclick="addConstraintRule('basic')"><?php echo $language ? 'Add constraint':'Ajouter une contrainte'; ?></button>
				<h2><?php echo $language ? 'Additional constraints':'Contraines additionnelles'; ?></h2>
				<div class="challenge-constraints-list" id="challenge-extra-list"></div>
				<button type="button" onclick="addConstraintRule('extra')"><?php echo $language ? 'Add constraint':'Ajouter une contrainte'; ?></button>
			</fieldset>
			<?php
			if (!empty($clRulesPayload['setup'])) {
				?>
				<div class="toggle-link"><a href="javascript:toggleSetupOptions()"><?php echo $language ? 'Other setup options...':'Autres options de setup...'; ?></a></div>
				<div id="challenge-setup-options"<?php if (!empty($chRules['setup'])) echo ' style="display:block"'; ?>>
					<fieldset class="challenge-setup">
						<legend><?php echo $language ? 'Setup options':'Setup'; ?></legend>
						<div class="challenge-constraints-explain"><?php echo $language ? 'Change some game setup when the challenge is selected' : 'Modifiez des éléments de la partie lorsque le défi est sélectionné'; ?></div>
						<div class="challenge-constraints-list" id="challenge-setup-list"></div>
						<button type="button" onclick="addConstraintRule('setup')"><?php echo $language ? 'Add option':'Ajouter une option'; ?></button>
					</fieldset>
				</div>
				<?php
			}
			?>
			<fieldset class="challenge-metadata">
				<legend><?php echo $language ? 'Other info':'Autres infos'; ?></legend>
				<div>
					<label><?php echo $language ? 'Challenge name (optional):':'Nom du défi (facultatif) :'; ?>
					<input type="text" name="name" value="<?php if (isset($challenge)) echo htmlspecialchars($challenge['name']); ?>" /></label>
					<?php
					if (!empty($moderate))
						echo '<input type="hidden" name="moderate" value="1" />';
					?>
				</div>
				<div>
					<label><?php echo $language ? 'Difficulty:':'Difficulté :'; ?>
					<select name="difficulty" required="required">
						<option value=""><?php echo $language ? 'Select...':'Sélection...'; ?></option>
						<?php
						$challengeDifficulties = getChallengeDifficulties();
						$selectedDifficulty = isset($challenge) ? $challenge['difficulty']:-1;
						foreach ($challengeDifficulties as $i => $name)
							echo '<option value="'. $i .'"'. ($i==$selectedDifficulty ? ' selected="selected"':'') .'>'. htmlspecialchars($name) .'</option>';
						?>
					</select></label>
					<div id="difficulty-faq">
					<?php
					if ($language)
						echo 'Please read the <a class="pretty-link" href="javascript:helpDifficulty()">recommendations</a> about difficulty selection';
					else
						echo 'Merci de lire les <a class="pretty-link" href="javascript:helpDifficulty()">recommandations</a> sur le choix de la difficulté';
					?>
					</div>
				</div>
			</fieldset>
			<?php
			if (!empty($moderate)) {
				?>
				<fieldset class="challenge-feedbacks">
					<legend><?php echo $language ? 'Moderation':'Modération'; ?></legend>
					<div>
						<label><?php echo $language ? '(Optional) Notes to the creator to explain what you changed:':'(Facultatif) Notes au créateur pour expliquer les changements :'; ?>
						<input type="text" name="feedbacks"<?php
						if ($challenge['validation']) {
							$validation = json_decode($challenge['validation']);
							if (!empty($validation->feedbacks))
								echo ' value="'. htmlspecialchars($validation->feedbacks) .'"';
						}
						?> /></label>
					</div>
				</fieldset>
				<?php
			}
		}
		elseif ('active' === $challenge['status']) {
			echo '<p class="challenge-restricted-editor">';
			echo $language ? 'This challenge is already active, so you are restricted to what you can change.' : 'Ce défi est déjà actif, vous êtes donc restreint dans les modifications.';
			echo '<br />';
			echo $language ? 'If you want to reaccess full editor, delete the challenge and recreate it.' : 'Si vous souhaitez réaccéder à l\'édition complète, supprimez le défi et recréez-le.';
			echo '</p>';
			?>
			<fieldset class="challenge-metadata">
				<div>
					<label><?php echo $language ? 'Challenge name (optional):':'Nom du défi (facultatif) :'; ?>
					<input type="text" name="name" value="<?php if (isset($challenge)) echo htmlspecialchars($challenge['name']); ?>" /></label>
				</div>
			</fieldset>
			<?php
		}
		else {
			echo '<p class="challenge-restricted-editor">';
			echo $language ? 'This challenge has already passed completion validation, so you are restricted to what you can change.' : 'Ce défi a déjà passé la validation de réussite, vous êtes donc restreint dans les modifications.';
			echo '<br />';
			echo $language ? 'If you want to reaccess full editor, you can <a class="pretty-link" href="javascript:undoValidation()" onclick="return confirm(\'Confirm undo? Caution, you\\\'ll have to complete the challenge again\')">undo validation</a>.' : 'Si vous souhaitez réaccéder à l\'édition complète, vous pouvez <a class="pretty-link" href="javascript:undoValidation()" onclick="return confirm(\'Confirmer l\\\'annulation ? Attention, il vous faudra réussir le défi de nouveau\')">annuler la validation</a>.';
			echo '</p>';
			?>
			<fieldset class="challenge-metadata">
				<div>
					<label><?php echo $language ? 'Challenge name (optional):':'Nom du défi (facultatif) :'; ?>
					<input type="text" name="name" value="<?php if (isset($challenge)) echo htmlspecialchars($challenge['name']); ?>" /></label>
				</div>
				<div>
					<label><?php echo $language ? 'Difficulty:':'Difficulté :'; ?>
					<select name="difficulty" required="required">
						<option value=""><?php echo $language ? 'Select...':'Sélection...'; ?></option>
						<?php
						$challengeDifficulties = getChallengeDifficulties();
						$selectedDifficulty = isset($challenge) ? $challenge['difficulty']:-1;
						foreach ($challengeDifficulties as $i => $name)
							echo '<option value="'. $i .'"'. ($i==$selectedDifficulty ? ' selected="selected"':'') .'>'. htmlspecialchars($name) .'</option>';
						?>
					</select></label>
				</div>
			</fieldset>
			<?php
		}
		?>
		<button type="submit" class="challenge-edit-submit"><?php echo $language ? 'Validate!':'Valider !'; ?></button>
	</form>
	<div class="collab-popup" id="collab-popup-char" onclick="closeCollabImportPopup()">
		<div class="popup-content" onclick="event.stopPropagation()">
			<h2><?php echo $language ? "Import a character of another member" : "Importer le perso d'un autre membre"; ?></h2>
			<div>
			<?php
			if ($language) {
				?>
				Enter the character's collaboration link here.<br />
				To get this link, the character owner will simply need
				to click on &quot;Collaborate&quot; on the characters editor page.
				<?php
			}
			else {
				?>
				Saisissez ici le lien de collaboration du perso.<br />
				Pour obtenir ce lien, le propriétaire du perso devra simplement
				cliquer sur &quot;Collaborer&quot; dans la page d'édition des persos.
				<?php
			}
			?>
			</div>
			<form onsubmit="importCollabChar(event)">
				<input type="url" name="collablink" placeholder="<?php
				require_once('../includes/collabUtils.php');
				$placeholderType = 'mkchars';
				$placeholderId = 1;
				$collab = array(
					'type' => $placeholderType,
					'creation_id' => $placeholderId,
					'secret' => 'y-vf-erny_2401_pbasvezrq'
				);
				echo getCollabUrl($collab);
				?>" required="required" />
				<input type="submit" value="Ok" />
			</form>
		</div>
	</div>
	<div class="pub">
		<?php
		require_once('../includes/utils-ads.php');
		showSmallAd();
		?>
	</div>
	<?php
	if (empty($moderate)) {
		?>
	<div class="challenge-navigation">
		<a href="<?php echo nextPageUrl('challenges.php', array('ch'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])); ?>">&lt; <u><?php echo $language ? 'Back to challenges list':'Retour à la liste des défis'; ?></u></a>
	</div>
		<?php
	}
	?>
</body>
</html>
<?php
mysql_close();
?>