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
	if (empty($challenge) || ('pending_completion' === $challenge['status']) || $moderate) {
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
				if ($moderate)
					mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "EChallenge '. $challenge['id'] .'")');
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
	if ($moderate)
		header('location: '. $_SERVER['REQUEST_URI'].'&clmsg='.$clMsg);
	else
		header('location: '. nextPageUrl('challenges.php', array('ch'=>null,'cl'=>$clRace['clid'],'clmsg'=>$clMsg)));
	exit;
}
elseif (empty($challenge) || ('pending_completion' === $challenge['status']) || $moderate) {
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
if (!$moderate)
	include('o_online.php');
?>

<title><?php echo $language ? 'Challenge editor':'Éditeur de défis'; ?> - Mario Kart PC</title>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
var clRules = <?php echo json_encode($clRulesPayload) ?>;
<?php
if (isset($challenge))
	echo 'var challengeId = '. $challenge['id'] .';';
if (isset($chRules))
	echo 'var chRules = '. json_encode($chRules) .';';
?>
var persoOptions = <?php echo json_encode($persoOptions) ?>;
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
			'<input type="text" name="goal[description]" style="font-size:12px;width:350px;padding-top:4px;padding-bottom:4px" placeholder="'+ (language ? 'example: &quot;Find the secret passage&quot;, &quot;Reach the central zone&quot;':'exemple : &quot;Trouver le passage secret&quot;, &quot;Atteindre la zone centrale&quot;') +'" required="required" maxlength="100" />'+
			'</label></div>'
		);
		break;
	}
	$extra.find("input,select").first().focus();
}
function addContraintRule(clClass) {
	var $rule = $(
		'<div class="challenge-contraint challenge-contraint-selecting">'+
			'<div class="challenge-contraint-selector">'+
				'<select name="constraint[]" class="challenge-contraint-select" required="required"></select>'+
				'<a href="#null" class="challenge-action-undo">'+ (language ? 'Cancel':'Annuler') +'</a>'+
			'</div>'+
			'<div class="challenge-contraint-options">'+
				'<div class="challenge-constraint-form"></div>'+
				'<div class="challenge-constraint-action">'+
					'<a class="challenge-action-edit" href="#null">'+ (language ? "Edit":"Modifier") +'</a>'+
					'<a class="challenge-action-del" href="#null">'+ (language ? "Delete":"Supprimer") +'</a>'+
				'</div>'+
			'</div>'+
		'</div>'
	);
	var $form = $rule.find(".challenge-constraint-form");
	var $rulesSelector = $rule.find(".challenge-contraint-select");
	$rule.find(".challenge-action-edit").click(function() {
		$form.empty();
		var ruleId = $rulesSelector.val();
		$rulesSelector.prop("selectedIndex", 0);
		$rule.addClass("challenge-contraint-selecting");
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
		case 'position':
			addConstraintNb($form,ruleId, language?'Place:':'Place :',{attrs:{min:1},css:{width:'40px'}});
			break;
		case 'difficulty':
			if (clClass == "extra")
				addConstraintSelector($form,ruleId, language?'Difficulty:':'Difficulté :', getConstraintOptions(clClass,ruleId));
			else
				addConstraintAutofill($form, ruleId,rulePayload.description,0);
			break;
		case 'character':
			addConstraintSelector($form,ruleId, language?'Character:':'Perso :', persoOptions);
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
		$rule.removeClass("challenge-contraint-selecting");
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
		var $selectors = $("#challenge-"+clClass+"-list > .challenge-contraint-selecting select");
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
		'<select class="challenge-contraint-value" name="scope['+ruleId+'][value]">'+
		'</select>'+
		'</label>'
	);
	var $extraSelector = $form.find(".challenge-contraint-value");
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
		'<input type="'+inputType+'" class="challenge-contraint-value" name="scope['+ruleId+'][value]" required="required"'+htmlAttrs+' />'+
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
function loadZoneData() {
	var data = document.forms[0].elements["goal[value]"].value;
	return JSON.parse(data);
}
function storeZoneData(data) {
	document.forms[0].elements["goal[value]"].value = JSON.stringify(data);
}
function openZoneEditor() {
	window.open(document.location.href.replace("challengeEdit.php","challengeZone.php"),'chose','scrollbars=1, resizable=1, width=800, height=600');
}
function helpDifficulty() {
	window.open('<?php echo $language ? 'helpDifficulty':'aideDifficulty'; ?>.html','gerer','scrollbars=1, resizable=1, width=500, height=500');
}
$(function() {
	if (typeof chRules != 'undefined') {
		var mainForm = document.forms[0];
		var mainRule = chRules.main;
		$("#challenge-main-rule").val(mainRule.type).change();
		var acceptedKeys = ["value","description","pts"];
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
				var $rulesSelector = $rule.find(".challenge-contraint-select");
				$rulesSelector.val(constraint.type).change();
				if (constraint.value != undefined) {
					var formElt = mainForm.elements["scope["+constraint.type+"][value]"];
					if (formElt) formElt.value = constraint.value;
				}
			}
		}
		if (document.activeElement)
			document.activeElement.blur();
	}
});
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
					<label><?php echo $language ? 'Challenge name (optionnal):':'Nom du défi (facultatif) :'; ?>
					<input type="text" name="name" value="<?php if (isset($challenge)) echo htmlspecialchars($challenge['name']); ?>" /></label>
					<?php
					if ($moderate)
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
					<label><?php echo $language ? 'Challenge name (optionnal):':'Nom du défi (facultatif) :'; ?>
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
					<label><?php echo $language ? 'Challenge name (optionnal):':'Nom du défi (facultatif) :'; ?>
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
	if (!$moderate) {
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