<?php
include('getId.php');
include('language.php');
include('initdb.php');
include('ip_banned.php');
if (isBanned()) {
	mysql_close();
	exit;
}
require_once('utils-challenges.php');
if (isset($_GET['moderate'])) {
	include('session.php');
	require_once('getRights.php');
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
include('challenge-cldata.php');
if (isset($_POST['name'])) {
	$clMsg = null;
	if (empty($challenge) || ('pending_completion' === $challenge['status']) || !empty($moderate)) {
		if (isset($_POST['goal']) && isset($_POST['difficulty'])) {
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
				mysql_query('UPDATE `mkchallenges` SET name="'. $_POST['name'] .'",difficulty="'. $_POST['difficulty'] .'",data="'. mysql_real_escape_string($dataJson) .'",validation="" WHERE id="'. $challenge['id'] .'"');
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
			if (isset($_POST['difficulty']))
				updateChallengeDifficulty($challenge, $_POST['difficulty']);
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
	if (isset($challenge))
		$chRules = getChallengeRulesByType($challenge);
	ob_start();
	include('getPersos.php');
	$listPersos = json_decode(ob_get_clean());
	ob_start();
	include('getLocks.php');
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
				if (isset($circuitData->assets)) {
					foreach ($circuitData->assets as $key => $data) {
						switch ($key) {
						case 'pointers':
							$circuitDecors[] = 'assets/pivothand';
							break;
						default:
							$decorTypes = array();
							foreach ($data as $d) {
								if (!isset($decorTypes[$d[0]])) {
									$decorTypes[$d[0]] = 1;
									$circuitDecors[] = 'assets/'.$d[0];
								}
							}
						}
					}
				}
				$decorParams = isset($circuitData->decorparams) ? $circuitData->decorparams:new \stdClass();
				$decorExtra = isset($decorParams->extra) ? $decorParams->extra:new \stdClass();
				foreach ($circuitDecors as $type) {
					$decorOption = array(
						'value' => $type
					);
					if (isset($decorExtra->{$type}) && isset($decorExtra->{$type}->custom)) {
						$customDecor = $decorExtra->{$type}->custom;
						$decorId = intval($customDecor->id);
						$actualType = $customDecor->type;
						if ($customData = mysql_fetch_array(mysql_query('SELECT name,sprites FROM mkdecors WHERE id='. $decorId))) {
							require_once('utils-decors.php');
							$decorSrcs = decor_sprite_srcs($customData['sprites']);
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
			require_once('circuitEnumsQuick.php');
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
<link rel="stylesheet" href="styles/challenges.css?reload=1" />
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<?php
if (empty($moderate))
	include('o_online.php');
?>

<title><?php echo $language ? 'Challenge editor':'Éditeur de défis'; ?> - Mario Kart PC</title>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var clRules = <?php echo isset($clRulesPayload) ? json_encode($clRulesPayload) : 'null'; ?>;
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
			'<input type="text" size="2" name="goal[value]" placeholder="1:30" required="required" autocomplete="off" pattern="\\d*(:\\d*){0,2}" />'+
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
			'<button type="button" onclick="openZoneEditor()">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'+
			'<div style="font-size:16px"><label>'+ (language ? 'Description: ':'Description : ') +
			'<input type="text" name="goal[description]" style="font-size:12px;width:350px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? 'example: &quot;Find the secret passage&quot;, &quot;Reach the central zone&quot;':'Ex : &quot;Trouver le passage secret&quot;, &quot;Atteindre la zone centrale&quot;') +'" required="required" maxlength="100" />'+
			'</label></div>'
		);
		break;
	case 'reach_zones':
		$extra.html(
			'<div style="margin:10px 0"><label>'+ (language ? 'Locations: ':'Emplacements : ') +
			'<input type="hidden" name="goal[value]" value="[]" />'+
			'<input type="hidden" name="goal[ordered]" value="0" />'+
			'<button type="button" onclick="openZoneEditor(\'zones\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'+
			'<div style="font-size:16px"><label>'+ (language ? 'Description: ':'Description : ') +
			'<input type="text" name="goal[description]" style="font-size:12px;width:350px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? 'example: &quot;Pass through 8 rings&quot;, &quot;Roll over the 4 pillars&quot;':'Ex : &quot;Traverser les 8 anneaux&quot;, &quot;Rouler sur les 4 pilliers&quot;') +'" required="required" maxlength="100" />'+
			'</label></div>'
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
		for (var i=0;i<decorOptions.length;i++) {
			var decorOption = decorOptions[i];
			if (decorOption.value.startsWith("assets/")) continue;
			var decorIcon = decorOption.icon || "images/map_icons/"+ decorOption.value +".png";
			$extraSelector.append('<button type="button" data-rule-type="main" data-rule-key="destroy_decors" data-value="'+ decorOption.value +'" style="background-image:url(\''+ decorIcon +'\')" onclick="toggleDecor(this)"></button>');
		}
		break;
	}
	$extra.find("input,select").first().focus();
}
function addContraintRule(clClass) {
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
		case 'extra_decors':
			$form.html(
				'<div style="margin:10px 0"><label>'+ (language ? 'Location: ':'Emplacement : ') +
				'<input type="hidden" name="scope[extra_decors][value]" value="[]" />'+
				'<button type="button" onclick="openZoneEditor(\'decors\')">'+ (language ? "Indicate...":"Indiquer...") +'</label></div>'
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
			addConstraintNb($form,ruleId, language?'Place:':'Place :',{attrs:{min:1},css:{width:'40px'}});
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
			break;
		case 'avoid_decors':
			$form.html(
				'<div>'+ (language?'Decor(s):':'Décor(s) :') +' '+
				'<input type="text" style="display:none" name="avoid_decors_fake1" required="required" >'+
				'<div class="challenge-rule-btn-options challenge-constraint-btn-options"></div>'+
				'<div class="challenge-rule-decor-names challenge-constraint-decor-names"></div>'
			);
			var $extraSelector = $form.find(".challenge-constraint-btn-options");
			for (var i=0;i<decorOptions.length;i++) {
				var decorOption = decorOptions[i];
				var decorIcon = decorOption.icon || "images/map_icons/"+ decorOption.value +".png";
				$extraSelector.append('<button type="button" data-rule-type="constraint" data-rule-key="avoid_decors" data-value="'+ decorOption.value +'" style="background-image:url(\''+ decorIcon +'\')" onclick="toggleDecor(this)"></button>');
			}
			break;
		case 'init_item':
			$form.html(
				'<div>'+ (language?'Item:':'Objet :') +' '+
				'<input type="text" style="display:none" name="scope[init_item][value]" required="required" >'+
				'<div class="challenge-rule-btn-options challenge-item-btn-options"></div>'
			);
			var $extraSelector = $form.find(".challenge-item-btn-options");
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
		case 'balloons':
			addConstraintNb($form,ruleId, language?'Nb balloons:':'Nb ballons :',{attrs:{min:0},css:{width:'30px'}});
			break;
		case 'balloons_lost':
			addConstraintNb($form,ruleId, language?'Max lost balloons:':'Max ballons perdus :',{attrs:{min:0},css:{width:'30px'}});
			break;
		case 'falls':
			addConstraintNb($form,ruleId, language?'Max falls:':'Max chutes :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'mini_turbo':
			addConstraintNb($form,ruleId, language?'Mini Turbos:':'Mini Turbos :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'super_turbo':
			addConstraintNb($form,ruleId, language?'Super Turbos:':'Super Turbos :',{attrs:{min:0},css:{width:'40px'}});
			break;
		case 'stunts':
			addConstraintNb($form,ruleId, language?'Stunts:':'Figures :',{attrs:{min:0},css:{width:'40px'}});
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
	$rulesSelector.append('<option value="">'+ (language ? 'Select constraint...':'Sélectionner contrainte...'));
	for (var ruleId in listRules) {
		if (!selectedConstraints[ruleId]) {
			var $option = $('<option value="'+ ruleId +'">'+ listRules[ruleId].description +'</option>');
			$rulesSelector.append($option);
		}
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
	$form.html(
		'<label>'+ label +' '+
		'<select class="challenge-constraint-value" name="scope['+ruleId+'][value]">'+
		'</select>'+
		'</label>'
	);
	var $extraSelector = $form.find(".challenge-constraint-value");
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
function ucfirst(word) {
	if (!word) return word;
	return word.charAt(0).toUpperCase() + word.substring(1);
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
function getZoneInputKey(editorType) {
	switch (editorType) {
	case "startpos":
		return "scope[start_pos]";
	case "decors":
		return "scope[extra_decors]";
	default:
		return "goal";
	}
}
function loadZoneData(editorType) {
	var inputKey = getZoneInputKey(editorType);
	var data = document.forms[0].elements[inputKey+"[value]"].value;
	var meta = {};
	var metaKeys = ["ordered"];
	for (var i=0;i<metaKeys.length;i++) {
		var $elt = document.forms[0].elements[inputKey+"["+metaKeys[i]+"]"];
		if ($elt)
			meta[metaKeys[i]] = $elt.value;
	}
	return {
		data: JSON.parse(data),
		meta: meta
	}
}
function storeZoneData(data,meta, editorType) {
	var inputKey = getZoneInputKey(editorType);
	document.forms[0].elements[inputKey+"[value]"].value = JSON.stringify(data);
	for (var key in meta) {
		var $elt = document.forms[0].elements[inputKey+"["+key+"]"];
		if ($elt) $elt.value = meta[key];
	}
}
function openZoneEditor(type) {
	window.open(document.location.href.replace("challengeEdit.php","challengeZone.php")+(type?("&type="+type):""),'chose','scrollbars=1, resizable=1, width=800, height=600');
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
	});
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
	var multiSelectAllowed = (ruleKey === "item_distribution");
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
function helpDifficulty() {
	window.open('<?php echo $language ? 'helpDifficulty':'aideDifficulty'; ?>.html','gerer','scrollbars=1, resizable=1, width=500, height=500');
}
$(function() {
	if (typeof chRules != 'undefined') {
		var mainForm = document.forms[0];
		var mainRule = chRules.main;
		$("#challenge-main-rule").val(mainRule.type).change();
		var acceptedKeys = ["value","description","ordered","pts"];
		for (var i=0;i<acceptedKeys.length;i++) {
			var key = acceptedKeys[i];
			if (mainRule[key]) {
				var ruleElt = mainForm.elements["goal["+key+"]"];
				if (ruleElt) ruleElt.value = mainRule[key];
			}
		}
		var constraintClasses = ["basic", "extra"];
		for (var i=0;i<constraintClasses.length;i++) {
			var constraintClass = constraintClasses[i];
			var constraintRules = chRules[constraintClass];
			for (var j=0;j<constraintRules.length;j++) {
				var constraint = constraintRules[j];
				var $rule = addContraintRule(constraintClass);
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
					case "init_item":
						var btn = mainForm.querySelector(".challenge-item-btn-options button[data-value='"+ constraint.value +"']");
						if (btn)
							toggleItem(btn);
						break;
					case "item_distribution":
						for (var i=0;i<constraint.value.length;i++) {
							var btn = mainForm.querySelector(".challenge-item-btn-options button[data-rule-key='item_distribution'][data-value='"+ constraint.value[i] +"']");
							if (btn)
								toggleItem(btn);
						}
						break;
					case "cc":
						var mirrorElt = mainForm.elements["scope["+constraint.type+"][mirror]"];
						if (mirrorElt) mirrorElt.checked = constraint.mirror;
						break;
					case "start_pos":
						var noCpuElt = mainForm.elements["scope["+constraint.type+"][no_cpu]"];
						if (noCpuElt) noCpuElt.checked = constraint.no_cpu;
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
				echo '<div class="challenge-msg-success">'. ($language ? 'The challenge has been edited':'Le défi a été modifié') .'. <a href="javascript:window.opener.location.reload();window.close()">'. ($language ? 'Back':'Retour') .'</a></div>';
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
				<button type="button" onclick="addContraintRule('basic')"><?php echo $language ? 'Add constraint':'Ajouter une contrainte'; ?></button>
				<h2><?php echo $language ? 'Additional constraints':'Contraines additionnelles'; ?></h2>
				<div class="challenge-constraints-list" id="challenge-extra-list"></div>
				<button type="button" onclick="addContraintRule('extra')"><?php echo $language ? 'Add constraint':'Ajouter une contrainte'; ?></button>
			</fieldset>
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
						echo 'Please read the <a class="pretty-link" href="javascript:helpDifficulty()">recommandations</a> about difficulty selection';
					else
						echo 'Merci de lire les <a class="pretty-link" href="javascript:helpDifficulty()">recommandations</a> sur le choix de la difficulté';
					?>
					</div>
				</div>
			</fieldset>
			<?php
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
	<div class="pub">
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- Mario Kart PC -->
		<ins class="adsbygoogle"
		     style="display:inline-block;width:468px;height:60px"
		     data-ad-client="ca-pub-1340724283777764"
		     data-ad-slot="6691323567"></ins>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
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