<?php
require_once('touch.php');
require_once('challenge-consts.php');
$clRulesByType = array(
	'main' => array(
		'finish_circuit_first' => array(
			'description' => $language ? 'Finish in the 1st position':'Finir le circuit en 1re position',
			'course' => array('vs')
		),
		'finish_circuit_time' => array(
			'description_mockup' => $language ? 'Complete the track in less than a given time':'Finir le circuit dans un temps imparti',
			'description_lambda' => function($language,&$scope) {
				$timeStr = stringifyTime($scope->value);
				return $language ? "Complete the track in less than $timeStr":"Finir le circuit en moins de $timeStr";
			},
			'parser' => function(&$scope) {
				$scope['value'] = parseTime($scope['value']);
			},
			'formatter' => function(&$scope) {
				$scope->value = formatTime($scope->value);
			},
			'placeholder' => array(
				'timeStr' => $language ? 'x seconds':'x secondes'
			),
			'course' => array('vs')
		),
		'finish_circuit' => array(
			'description' => $language ? 'Complete the track':'Finir le circuit',
			'course' => array('vs')
		),
		'finish_arena_first' => array(
			'description' => $language ? 'Finish the game in the 1st position':'Finir la partie en 1re position',
			'course' => array('battle')
		),
		'finish_arena' => array(
			'description' => $language ? 'Finish the game':'Finir la partie',
			'course' => array('battle')
		),
		'hit' => array(
			'description' => $language ? 'Hit $value opponent$s':'Toucher $value personne$s',
			'description_mockup' => $language ? 'Hit N opponents':'Toucher N personnes',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('battle')
		),
		'eliminate' => array(
			'description' => $language ? 'Eliminate $value opponent$s by yourself':'Éliminer $value adversaire$s par vous-même',
			'description_mockup' => $language ? 'Eliminate N opponents by yourself':'Éliminer N adversaires par vous-même',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('battle')
		),
		'survive' => array(
			'description_mockup' => $language ? 'Survive for a given time':'Survivre un certain temps',
			'description_lambda' => function($language,&$scope) {
				$timeStr = stringifyTime($scope->value);
				return $language ? "Survive more than $timeStr":"Survivre plus de $timeStr";
			},
			'parser' => function(&$scope) {
				$scope['value'] = parseTime($scope['value']);
			},
			'formatter' => function(&$scope) {
				$scope->value = formatTime($scope->value);
			},
			'course' => array('battle')
		),
		'reach_zone' => array(
			'description_mockup' => $language ? 'Reach zone...':'Atteindre la zone...',
			'description_lambda' => function($language,&$scope) {
				return $scope->description;
			},
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			},
			'course' => array('vs','battle')
		),
		'reach_zones' => array(
			'description_mockup' => $language ? 'Go through N zones...':'Passer par N zones...',
			'description_lambda' => function($language,&$scope) {
				return $scope->description;
			},
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
				if (isset($scope['ordered']))
					$scope['ordered'] = +$scope['ordered'];
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			},
			'course' => array('vs','battle')
		),
		'hit_items' => array(
			'description' => $language ? 'Hit all item boxes':'Toucher toutes les boites à objet',
			'course' => array('vs','battle')
		),
		'collect_coins' => array(
			'description_mockup' => $language ? 'Collect coins...':'Collecter des pièces...',
			'description_lambda' => function($language,&$scope) {
				if (isset($scope->nb)) {
					$nb = $scope->nb;
					$s = ($nb >= 2) ? 's':'';
					return $language ? "Collect $nb coin$s":"Collecter $nb pièce$s";
				}
				return $language ? 'Collect all coins':'Collecter toutes les pièces';
			},
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
				if (empty($scope['nb']))
					unset($scope['nb']);
				else
					$scope['nb'] = +$scope['nb'];
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			},
			'course' => array('vs','battle')
		),
		'gold_cup' => array(
			'description' => $language ? 'Get the gold cup':'Obtenir la coupe d\'or',
			'course' => array('cup'),
			'autoset' => function(&$res, $scope) {
				$res['course'] = 'GP';
			}
		),
		'gold_cups' => array(
			'description' => $language ? 'Get all gold cups':'Obtenir toutes les coupes d\'or',
			'course' => array('mcup'),
			'autoset' => function(&$res, $scope) {
				$res['course'] = 'GP';
			}
		),
		'finish_circuits_first' => array(
			'description_mockup' => $language ? 'Finish 1st N times in a row':'Finir 1er N fois d\'affilée',
			'description' => $language ? 'Finish 1st $value time$s in a row':'Finir 1er $value fois d\'affilée',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs', 'battle', 'cup', 'mcup')
		),
		'pts_greater' => array(
			'description_mockup' => $language ? 'Make at least x points in N races':'Faire au moins x points sur N courses',
			'description' => $language ? 'Make at least $pts points in $value race$s':'Faire au moins $pts points sur $value course$s',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('cup', 'mcup')
		),
		'pts_equals' => array(
			'description_mockup' => $language ? 'Make exactly x points in N races':'Faire exactement x points sur N courses',
			'description' => $language ? 'Make exactly $pts points in N race$s':'Faire exactement $pts points sur $value course$s',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('cup', 'mcup')
		)
	),
	'basic' => array(
		'game_mode' => array(
			'description' => $language ? 'in $options[$value] mode':'en mode $options[$value]',
			'description_mockup' => $language ? 'game mode (VS, TT)...':'mode de jeu (VS, CLM)...',
			'scope' => array(
				'options' => $language ? array('VS','Time Trial') : array('Course VS','Contre-la-montre')
			),
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'autoset' => function(&$res, $scope) {
				$courseValues = array('VS','CM');
				$res['course'] = $courseValues[$scope->value];
			},
			'course' => array('vs')
		),
		'game_mode_cup' => array(
			'description' => $language ? 'in $options[$value] mode':'en mode $options[$value]',
			'description_mockup' => $language ? 'game mode (GP, VS)...':'mode de jeu (GP, VS)...',
			'scope' => array(
				'options' => $language ? array('Grand Prix','VS') : array('Grand Prix','Course VS')
			),
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'autoset' => function(&$res, $scope) {
				$courseValues = array('GP','VS');
				$res['course'] = $courseValues[$scope->value];
			},
			'course' => array('cup', 'mcup')
		),
		'difficulty' => array(
			'description_mockup' => $language ? 'in difficult mode':'en mode difficile',
			'course' => array('vs', 'cup', 'mcup'),
			'this_class' => function(&$scope) {
				return ($scope->value == 0);
			}
		),
		'participants' => array(
			'description_mockup' => $language ? 'with 8 participants':'avec 8 participants',
			'course' => array('vs', 'battle', 'cup', 'mcup'),
			'this_class' => function(&$scope) {
				return ($scope->value == 8);
			}
		),
		'no_teams' => array(
			'description' => $language ? 'no teams':'sans équipes',
			'course' => array('vs', 'battle', 'cup', 'mcup'),
			'additional' => true,
			'autoset' => function(&$res, $scope) {
				$res['selectedTeams'] = 0;
			}
		)
	),
	'extra' => array(
		'balloons' => array(
			'description' => $language ? 'with $value balloon$s or more':'avec $value ballon$s ou plus',
			'description_mockup' => $language ? 'With x balloons or more':'Avec x ballons ou plus',
			'course' => array('battle')
		),
		'balloons_lost' => array(
			'description' => $language ? 'by losing at most $value balloon$s':'en perdant au plus $value ballon$s',
			'description_mockup' => $language ? 'By losing at most x balloons':'En perdant au plus x ballons',
			'description_lambda' => function($language,&$scope) {
				if (!$scope->value)
					return $language ? 'without losing any balloons':'sans perdre de ballons';
				return null;
			},
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('battle')
		),
		'no_drift' => array(
			'description' => $language ? 'without drifting':'sans déraper',
			'course' => array('vs', 'battle', 'cup', 'mcup')
		),
		'avoid_items' => array(
			'description' => $language ? 'without touching item boxes':'sans toucher les boites à objet',
			'course' => array('vs', 'battle', 'cup', 'mcup')
		),
		'no_item' => array(
			'description' => $language ? 'without using any object':'sans utiliser d\'objets',
			'course' => array('vs', 'battle', 'cup', 'mcup')
		),
		'avoid_decors' => array(
			'description_mockup' => $language ? 'without touching a decor...':'sans toucher un décor...',
			'description_lambda' => function($language,&$scope) {
				$decorTree = $language ? 'tree' : 'arbre';
				$decorMapping = array(
					'tuyau' => $language ? 'pipe':'tuyau',
					'taupe' => $language ? 'Monty Mole':'Topi Taupe',
					'poisson' => 'Cheep-Cheep',
					'plante' => $language ? 'Piranha Plant':'Plante Piranha',
					'boo' => 'Boo',
					'thwomp' => 'Thwomp',
					'spectre' => 'Thwomp',
					'assets/oil1' => $language ? 'oil spill':'tâche d\'huile',
					'assets/oil2' => $language ? 'puddle':'flaque',
					'crabe' => $language ? 'crab':'crabe',
					'cheepcheep' => 'Cheep-Cheep',
					'movingtree' => $language ? 'moving tree' : 'arbre mobile',
					'pokey' => 'Pokey',
					'firesnake' => $language ? 'fire snake' : 'serpent de feu',
					'box' => $language ? 'box':'caisse',
					'snowball' => $language ? 'snow ball':'boule de neige',
					'cannonball' => $language ? 'pinball ball':'boule de flipper',
					'truck' => 'bus',
					'pendulum' => $language ? 'pendulum':'pendule',
					'assets/pivothand' => $language ? 'clock hand':'aiguille',
					'snowman' => $language ? 'snowman':'bonhomme de neige',
					'goomba' => 'Goomba',
					'fireplant' => $language ? 'fire plant' : 'plante de feu',
					'piranhaplant' => $language ? 'Piranha Plant':'Plante Piranha',
					'tortitaupe' => $language ? 'Monty Mole' : 'Torti Taupe',
					'billball' => $language ? 'Bullet Bill' : 'Bill Ball',
					'firering' => $language ? 'fire circle':'cercle de feu',
					'fire3star' => $language ? 'fire triplet':'triolet de feu',
					'topitaupe' => $language ? 'Monty Mole' : 'Topi Taupe',
					'chomp' => 'Chomp',
					'movingthwomp' => 'Thwomp',
					'firebar' => $language ? 'fire bar' : 'barre de feu',
					'tree' => $decorTree,
					'palm' => $decorTree,
					'coconut' => $decorTree,
					'sinistertree' => $decorTree,
					'falltree' => $decorTree,
					'mountaintree' => $decorTree,
					'fir' => $decorTree,
					'mariotree' => $decorTree,
					'peachtree' => $decorTree,
					'assets/flower1' => $language ? 'flower':'fleur',
					'assets/flower2' => $language ? 'flower':'fleur',
					'assets/flower3' => $language ? 'flower':'fleur',
					'assets/bumper' => 'bumber'
				);
				$itemsToAvoid = array();
				foreach ($scope->value as $key=>$decor) {
					if (!empty($decor->name))
						$decorName = $decor->name;
					elseif (isset($decorMapping[$key]))
						$decorName = $decorMapping[$key];
					else
						$decorName = $key;
					$itemsToAvoid[] = $decorName;
				}
				$itemsToAvoid = array_values(array_unique($itemsToAvoid));
				$itemsToAvoidString = '';
				$nbItems = count($itemsToAvoid);
				foreach ($itemsToAvoid as $i=>$item) {
					if ($i) {
						if ($i === ($nbItems-1))
							$itemsToAvoidString .= $language ? ' nor ':' ni ';
						else
							$itemsToAvoidString .= ', ';
					}
					$itemsToAvoidString .= $item;
				}
				return $language ? 'without touching any '. $itemsToAvoidString:'sans toucher '. (in_array($itemsToAvoidString[0],array('a','e','i','o','u')) ? "d'":"de ") . $itemsToAvoidString;
			},
			'course' => array('vs', 'battle')
		),
		'character' => array(
			'description_mockup' => $language ? 'With character...':'Avec le perso...',
			'description_lambda' => function($language,&$scope) {
				$sPerso = $scope->value;
				return ($language ? 'with ':'avec ') . getCharacterName($sPerso);
			},
			'course' => array('vs', 'battle', 'cup', 'mcup'),
			'placeholder' => array(
				'value' => '...'
			),
			'autoset' => function(&$res, $scope) {
				$res['selectedPerso'] = $scope->value;
			}
		),
		'falls' => array(
			'description' => $language ? 'by falling at most $value time$s':'en tombant au plus $value fois',
			'description_mockup' => $language ? 'By falling at most...':'En tombant au plus...',
			'description_lambda' => function($language,&$scope) {
				if (!$scope->value)
					return $language ? 'without falling':'sans tomber';
				return null;
			},
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs', 'battle', 'cup')
		),
		'no_stunt' => array(
			'description' => $language ? 'without making stunts':'sans faire de figures',
			'course' => array('vs', 'battle', 'cup', 'mcup')
		),
		'time' => array(
			'description_mockup' => $language ? 'in less than... (time)':'en moins de... (temps)',
			'description_lambda' => function($language,&$scope) {
				$timeStr = stringifyTime($scope->value);
				return $language ? "in less than $timeStr":"en moins de $timeStr";
			},
			'parser' => function(&$scope) {
				$scope['value'] = parseTime($scope['value']);
			},
			'formatter' => function(&$scope) {
				$scope->value = formatTime($scope->value);
			},
			'course' => array('vs', 'battle')
		),
		'backwards' => array(
			'description' => $language ? 'by driving backwards':'en marche arrière',
			'course' => array('vs', 'battle', 'cup', 'mcup')
		),
		'time_delay' => array(
			'description' => $language ? 'by starting with $value​s delay':'en partant avec $value​s de retard',
			'description_mockup' => $language ? 'by starting with x seconds delay':'en partant avec x secondes de retard',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs')
		),
		'mini_turbo' => array(
			'description' => $language ? 'by performing $value​ Mini-Turbo$s':'en réalisant $value​ dérapage$s Turbo',
			'description_mockup' => $language ? 'by performing N Turbo drifts':'en réalisant N dérapages Turbo',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs','battle', 'cup', 'mcup')
		),
		'super_turbo' => array(
			'description' => $language ? 'by performing $value​ Super Mini-Turbo$s':'en réalisant $value​ Super Mini-Turbo$s',
			'description_mockup' => $language ? 'by performing N Super Mini-Turbo':'en réalisant N Super Mini-Turbo',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs','battle', 'cup', 'mcup')
		),
		'stunts' => array(
			'description' => $language ? 'by performing $value​ stunt$s':'en réalisant $value​ figure$s',
			'description_mockup' => $language ? 'by performing N stunts':'en réalisant N figures',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs','battle', 'cup', 'mcup')
		),
		'position' => array(
			'description_mockup' => $language ? 'in n-th place':'en n-eme position',
			'description_lambda' => function($language,&$scope) {
				return $language ? 'in '. getPositionName($scope->value) .' place' : 'en '. getPositionName($scope->value) .' position';
			},
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs')
		),
		'with_pts' => array(
			'description_mockup' => $language ? 'with x points or more':'avec x points ou plus',
			'description' => $language ? 'with $value point$s or more':'avec $value point$s ou plus',
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('cup', 'mcup')
		),
		'different_circuits' => array(
			'description' => $language ? 'in different circuits':'sur des circuits différents',
			'course' => array('cup', 'mcup')
		),
		'difficulty' => array(
			'description_mockup' => $language ? 'difficulty...':'difficulté...',
			'description' => $language ? 'in $options[$value] mode':'en mode $options[$value]',
			'scope' => array(
				'options' => $language ? array('difficult','medium','easy') : array('difficile','moyen','facile')
			),
			'parser' => function(&$scope) {
				$scope['value'] = +$scope['value'];
			},
			'course' => array('vs', 'cup', 'mcup'),
			'additional_lambda' => function(&$scope) {
				return ($scope->value == 0);
			},
			'autoset' => function(&$res, $scope) {
				$res['selectedDifficulty'] = 2-$scope->value;
			}
		),
		'participants' => array(
			'description_mockup' => $language ? 'with x participants':'avec x participants',
			'description' => $language ? 'with $value participant$s':'avec $value participant$s',
			'course' => array('vs', 'battle', 'cup', 'mcup'),
			'additional_lambda' => function(&$scope) {
				return ($scope->value == 8);
			},
			'autoset' => function(&$res, $scope) {
				$res['selectedPlayers'] = $scope->value;
			}
		)
	)
);
$clRules = array();
foreach ($clRulesByType as &$rulesList) {
	foreach ($rulesList as $key => &$rules)
		$rules['type'] = $key;
	$clRules = array_replace($clRules,$rulesList);
}
unset($rulesList);
unset($rules);
function listChallenges($clRace, &$params=array()) {
	global $identifiants;
	if (isset($params['status']))
		$statusCheck = 'status IN ("'. implode('","',$params['status']) .'")';
	else {
		$myCircuit = false;
		if (isset($identifiants)) {
			if ($getClist = mysql_fetch_array(mysql_query('SELECT id,type,circuit FROM `mkclrace` WHERE id="'. $clRace .'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3])))
				$myCircuit = true;
		}
		if ($myCircuit)
			$statusCheck = 'status!="deleted"';
		else {
			if (isset($params['id']) && mysql_fetch_array(mysql_query('SELECT player FROM `mkrights` WHERE player="'.$params['id'].'" AND privilege="clvalidator"')))
				$statusCheck = 'status IN ("pending_moderation","active")';
			else
				$statusCheck = 'status="active"';
		}
	}
	$res = array();
	$getChallenges = mysql_query('SELECT * FROM mkchallenges WHERE clist="'. $clRace .'" AND '. $statusCheck);
	while ($challenge = mysql_fetch_array($getChallenges))
		$res[] = getChallengeDetails($challenge, $params);
	if (!empty($params['alltracks']) && !empty($getClist)) {
		$subCls = array();
		$newParams = $params;
		unset($newParams['alltracks']);
		$allSubTracks = array(
			'mode' => 0,
			'circuits' => array()
		);
		switch ($getClist['type']) {
		case 'mkmcups':
			if ($getMode = mysql_fetch_array(mysql_query('SELECT mode FROM mkmcups WHERE id='. $getClist['circuit'])))
				$allSubTracks['mode'] = $getMode['mode'];
			$getCls = mysql_query('SELECT DISTINCT cl.id,c.circuit0,c.circuit1,c.circuit2,c.circuit3 FROM mkclrace cl INNER JOIN mkmcups_tracks t ON t.mcup='. $getClist['circuit'] .' AND t.cup=cl.circuit INNER JOIN mkcups c ON c.id=t.cup WHERE cl.type="mkcups" ORDER BY t.ordering');
			while ($subCl = mysql_fetch_array($getCls)) {
				$subCls[] = $subCl['id'];
				for ($i=0;$i<4;$i++)
					$allSubTracks['circuits'][] = $subCl["circuit$i"];
			}
			break;
		case 'mkcups':
			if ($getTracks = mysql_fetch_array(mysql_query('SELECT circuit0,circuit1,circuit2,circuit3,mode FROM mkcups WHERE id='. $getClist['circuit']))) {
				$allSubTracks['mode'] = $getTracks['mode'];
				for ($i=0;$i<4;$i++)
					$allSubTracks['circuits'][] = $getTracks["circuit$i"];
			}
		}
		if (!empty($allSubTracks['circuits'])) {
			$trackIdsString = implode(',',$allSubTracks['circuits']);
			$getClTracks = mysql_query('SELECT DISTINCT id FROM mkclrace WHERE type="'. ($allSubTracks['mode'] ? 'circuits':'mkcircuits') .'" AND circuit IN ('.$trackIdsString.')');
			while ($subCl = mysql_fetch_array($getClTracks))
				$subCls[] = $subCl['id'];
		}
		foreach ($subCls as $clRaceId)
			$res = array_merge($res, listChallenges($clRaceId,$newParams));
	}
	return $res;
}
require_once('circuitEscape.php');
function getChallengeDetails($challenge, &$params=array()) {
	$challengeData = json_decode($challenge['data']);
	$res = array(
		'id' => $challenge['id'],
		'name' => $challenge['name'],
		'difficulty' => getChallengeDifficulty($challenge),
		'status' => $challenge['status'],
		'validation' => $challenge['validation'],
		'data' => $challengeData,
		'description' => getChallengeDescription($challengeData)
	);
	if (!empty($params['utf8']))
		$res['name'] = iconv('utf-8', 'windows-1252', $challenge['name']);
	if (!empty($params['rating']))
		$res['rating'] = array('avg' => $challenge['avgrating'], 'nb' => $challenge['nbratings']);
	if (!empty($params['circuit'])) {
		$res['circuit'] = getCircuitPayload($challenge);
		if (empty($params['utf8']) && empty($params['circuit.raw']) && !empty($res['circuit'])) {
			$res['circuit']['name'] = htmlspecialchars(escapeCircuitNames(iconv('windows-1252', 'utf-8', $res['circuit']['name'])));
			$res['circuit']['author'] = htmlspecialchars(escapeCircuitNames(iconv('windows-1252', 'utf-8', $res['circuit']['author'])));
		}
	}
	if (!empty($params['winners'])) {
		$getWinners = mysql_query('SELECT w.player,w.creator,j.nom,UNIX_TIMESTAMP(w.date) AS date FROM `mkclwin` w INNER JOIN `mkjoueurs` j ON w.player=j.id WHERE challenge='. $challenge['id']);
		$winners = array();
		while ($winner = mysql_fetch_array($getWinners)) {
			if (!$winner['creator']) {
				$winners[] = array(
					'player' => $winner['player'],
					'nick' => $winner['nom'],
					'date' => $winner['date']
				);
			}
			if ($winner['player'] == $params['id'])
				$res['succeeded'] = true;
		}
		$res['winners'] = $winners;
	}
	return $res;
}
function getCircuitPayload(&$clRace) {
	$res = array();
	if ($clCircuit = mysql_fetch_array(mysql_query('SELECT * FROM `'. $clRace['type'] .'` WHERE id="'. $clRace['circuit'] .'"'))) {
		$res['name'] = $clCircuit['nom'];
		$res['author'] = $clCircuit['auteur'];
		$res['identifiant'] = $clCircuit['identifiant'];
		$res['identifiant2'] = $clCircuit['identifiant2'];
		$res['identifiant3'] = $clCircuit['identifiant3'];
		$res['identifiant4'] = $clCircuit['identifiant4'];
		$linkBg = '';
		$linkPreview = array();
		$linksCached = array();
		$linkUrl = '';
		switch ($clRace['type']) {
		case 'circuits':
			$linkUrl = 'map.php?i='. $clCircuit['ID'];
			$linkBg = 'trackicon.php?id='. $clCircuit['ID'] .'&type=1';
			$linksCached[] = 'racepreview' . $clCircuit['ID'] .'.png';
			break;
		case 'mkcircuits':
			$linkUrl = ($clCircuit['type'] ? 'arena':'circuit') .'.php?id='. $clCircuit['id'];
			$linkBg = 'trackicon.php?id='. $clCircuit['id'] .'&type=0';
			$linksCached[] = 'mappreview' . $clCircuit['id'] .'.png';
			break;
		case 'arenes':
			$linkUrl = 'battle.php?i='. $clCircuit['ID'];
			$linkBg = 'trackicon.php?id='. $clCircuit['ID'] .'&type=2';
			$linksCached[] = 'coursepreview' . $clCircuit['ID'] .'.png';
			break;
		case 'mkcups':
			$linkUrl = ($clCircuit['mode'] ? 'map':'circuit') .'.php?cid='. $clCircuit['id'];
			if ($clCircuit['mode'])
				$baseCache = 'racepreview';
			else
				$baseCache = 'mappreview';
			for ($i=0;$i<4;$i++) {
				$lId = $clCircuit['circuit'.$i];
				$linkBg .= ($i?',':'') . 'trackicon.php?id='. $lId .'&type='. $clCircuit['mode'];
				$linksCached[] = $baseCache . $lId .'.png';
			}
			break;
		case 'mkmcups':
			$linkUrl = ($clCircuit['mode'] ? 'map':'circuit') .'.php?mid='. $clCircuit['id'];
			$linkBg .= 'trackicon.php?id='. $clCircuit['id'] .'&type=4';
			$linksCached[] = 'mcuppreview'. $clCircuit['id'] .'.png';
		}
		$allCached = true;
		foreach ($linksCached as $link) {
			$filename = 'images/creation_icons/'.$link;
			if (file_exists($filename))
				touch_async($filename);
			else {
				$allCached = false;
				break;
			}
		}
		$res['srcs'] = $linkPreview;
		$res['href'] = $linkUrl;
		if ($allCached) $res['icon'] = $linksCached;
		$res['cicon'] = $linkBg;
	}
	return $res;
}
function getChallenge($chId, $isModerator=false) {
	global $identifiants;
	return mysql_fetch_array(mysql_query('SELECT c.* FROM `mkchallenges` c'. ($isModerator ? ' WHERE c.id="'. $chId .'"':' LEFT JOIN `mkclrace` l ON l.id=c.clist WHERE c.id="'. $chId .'" AND (l.id IS NULL OR (l.identifiant='.$identifiants[0].' AND l.identifiant2='.$identifiants[1].' AND l.identifiant3='.$identifiants[2].' AND l.identifiant4='.$identifiants[3].'))')));
}
function getClRace($clId, $isModerator=false) {
	global $identifiants;
	if ($res = mysql_fetch_array(mysql_query('SELECT * FROM `mkclrace` WHERE id="'. $clId .'"'))) {
		if ($isModerator)
			return $res;
		if (($res['identifiant'] == $identifiants[0]) && ($res['identifiant2'] == $identifiants[1]) && ($res['identifiant3'] == $identifiants[2]) && ($res['identifiant4'] == $identifiants[3]))
			return $res;
	}
	return null;
}
function getCharacterName($sPerso) {
	global $language;
	if ($language) {
		if ($sPerso == "maskass")
			$res = "shy guy";
		elseif ($sPerso == "skelerex")
			$res = "dry bones";
		elseif ($sPerso == "harmonie")
			$res = "rosalina";
		elseif ($sPerso == "roi_boo")
			$res = "king boo";
		elseif ($sPerso == "frere_marto")
			$res = "hammer bro";
		elseif ($sPerso == "bowser_skelet")
			$res = "dry bowser";
		elseif ($sPerso == "flora_piranha")
			$res = "petey piranha";
	}
	else {
		if ($sPerso == "frere_marto")
			$res = "frère marto";
	}
	if (!isset($res)) $res = $sPerso;
	$res = ucwords(str_replace('_', ' ', $res));
	return $res;
}
function getPositionName($place) {
	global $language;
	if ($language) {
		$centaines = $place%100;
		if (($centaines >= 10) && ($centaines < 20))
			return $place.'th';
		else {
			switch ($place%10) {
			case 1 :
				return $place.'st';
				break;
			case 2 :
				return $place.'nd';
				break;
			case 3 :
				return $place.'rd';
				break;
			default :
				return $place.'th';
			}
		}
	}
	else
		return $place.($place>1 ? 'e':'re');
}
function parseTime($value) {
	if (preg_match('#^(\d*):(\d*):(\d*)$#', $value, $matches))
		return round($matches[1]*60 + $matches[2] + $matches[3]/pow(10,strlen($matches[3])), 3);
	elseif (preg_match('#^(\d*):(\d*)$#', $value, $matches))
		return $matches[1]*60 + $matches[2];
	else
		return round($value, 3);
}
function formatTime($seconds) {
	$min = floor($seconds/60);
	$sec = floor($seconds)%60;
	if ($sec < 10) $sec = '0'.$sec;
	$ms = round(1000*fmod($seconds,1));
	if (!$ms)
		return "$min:$sec";
	while (strlen($ms) < 3)
		$ms = '0'.$ms;
	return "$min:$sec:$ms";
}
function stringifyTime($seconds) {
	if (($seconds >= 60) || round(1000*fmod($seconds,1)))
		return formatTime($seconds);
	else
		return $seconds.'s';
}
function getRuleDescription($rule,$rulesClass=null) {
	global $clRules, $clRulesByType, $language;
	if (is_array($rule))
		$rule = (object) $rule;
	if ($rulesClass)
		$data = $clRulesByType[$rulesClass][$rule->type];
	else
		$data = $clRules[$rule->type];
	if (!empty($rule->mockup)) {
		if (isset($data['description_mockup']))
			$res = $data['description_mockup'];
		elseif (!empty($rule->mockup) && isset($data['placeholder'])) {
			foreach ($data['placeholder'] as $name => $value) {
				if (!isset($rule->$name))
					$rule->$name = $value;
			}
		}
	}
	if (!isset($res) && isset($data['description_lambda']))
		$res = $data['description_lambda']($language,$rule);
	if (!isset($res)) {
		$res = $data['description'];
		$scope = (array)$rule;
		if (isset($scope['value']))
			$scope['s'] = ($scope['value']>=2 ? 's':'');
		if (isset($data['scope']))
			$scope = array_merge($data['scope'],$scope);
		$res = preg_replace_callback('#\$(\w+)#', function($matches) use ($scope) {
			$k = $matches[1];
			if (isset($scope[$k]) && !is_array($scope[$k]))
				return $scope[$k];
			return $matches[0];
		}, $res);
		$res = preg_replace_callback('#\$(\w+)\[(\w+)\]#', function($matches) use ($scope) {
			$a = $matches[1];
			$k = $matches[2];
			if (isset($scope[$a]) && is_array($scope[$a]) && isset($scope[$a][$k]))
				return $scope[$a][$k];
			return $matches[0];
		}, $res);
	}
	if (!empty($rule->mockup))
		$res = ucfirst($res);
	return $res;
}
function isAdditionalRule($rulesData,$scope) {
	if (isset($rulesData['additional_lambda']))
		return $rulesData['additional_lambda']($scope);
	if (isset($rulesData['additional']))
		return $rulesData['additional'];
	return false;
}
function mergeChallengeRules($challengeData) {
	return array_merge(array($challengeData->goal), $challengeData->constraints);
}
function getChallengeDescription($challengeData) {
	global $clRules;
	$mainDesc = getRuleDescription($challengeData->goal);
	$constraintDescs = array();
	$extraDesc = array();
	foreach ($challengeData->constraints as $data) {
		$rulesData = $clRules[$data->type];
		if (isAdditionalRule($rulesData,$data))
			$extraDesc[] = getRuleDescription($data);
		else
			$constraintDescs[] = getRuleDescription($data);
	}
	$challengeRulesStr = $mainDesc;
	if (!empty($constraintDescs))
		$challengeRulesStr .= ' ' . implode(', ', $constraintDescs);
	$res = array('main' => $challengeRulesStr);
	if (!empty($extraDesc))
		$res['extra'] = ucfirst(implode(', ', $extraDesc));
	return $res;
}
function updateChallengeDifficulty($challenge, $newDifficulty) {
	$oldDifficulty = $challenge['difficulty'];
	if ($oldDifficulty == $newDifficulty) return;
	$challengeId = $challenge['id'];
	if ('active' === $challenge['status']) {
		$challengeRewards = getChallengeRewards();
		$challengeReward = $challengeRewards[$oldDifficulty];
		$getWins = mysql_query('SELECT player FROM `mkclwin` WHERE challenge="'. $challengeId .'"');
		$newChallengeReward = $challengeRewards[$newDifficulty];
		$diffReward = $newChallengeReward-$challengeReward;
		while ($clWin = mysql_fetch_array($getWins))
			mysql_query('UPDATE `mkjoueurs` SET pts_challenge=pts_challenge+'.$diffReward.' WHERE id="'. $clWin['player'] .'"');
	}
	mysql_query('UPDATE `mkchallenges` SET difficulty="'. $newDifficulty .'" WHERE id="'. $challengeId .'"');
}
function resetChallengeCompletion($challenge) {
	$challengeId = $challenge['id'];
	mysql_query('DELETE FROM `mkclwin` WHERE challenge="'. $challengeId .'"');
	mysql_query('UPDATE `mkchallenges` SET status="pending_completion",avgrating=0,nbratings=0 WHERE id="'. $challengeId .'"');
}
function activateChallenge($challenge) {
	global $id;
	$challengeId = $challenge['id'];
	$challengeRewards = getChallengeRewards();
	$difficulty = $challenge['difficulty'];
	$challengeReward = $challengeRewards[$difficulty];
	$getWins = mysql_query('SELECT player FROM `mkclwin` WHERE challenge="'. $challengeId .'"');
	while ($clWin = mysql_fetch_array($getWins))
		mysql_query('UPDATE `mkjoueurs` SET pts_challenge=pts_challenge+'.$challengeReward.' WHERE id="'. $clWin['player'] .'"');
	mysql_query('UPDATE `mkchallenges` SET status="active",date=NULL WHERE id="'. $challengeId .'"');
	if ($id) {
		$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
		while ($follower = mysql_fetch_array($getFollowers))
			mysql_query('INSERT INTO `mknotifs` SET type="follower_challenge", user="'. $follower['follower'] .'", link="'. $challengeId .'"');
	}
}
function challengeAutoSet(&$res,$challenge) {
	global $clRules;
	$challengeData = json_decode($challenge['data']);
	$challengeRules = mergeChallengeRules($challengeData);
	foreach ($challengeRules as $challengeRule) {
		$clRule = $clRules[$challengeRule->type];
		if (isset($clRule['autoset']))
			$clRule['autoset']($res,$challengeRule);
	}
}
/*function getSQLWhereIn($column,$list) {
	if (empty($list)) return '0';
	else {
		foreach ($list as &$elt)
			$elt = +$elt;
	}
	return "$column IN (". implode(',', $list) .")";
}*/
function getRewardedPlayers($filters) {
	$where1 = array('1');
	$where2 = array('1');
	if (isset($filters['player']))
		$where1[] = 'w.player="'.$filters['player'].'"';
	if (isset($filters['reward']))
		$where2[] = 'r.reward="'.$filters['reward'].'"';
	if (isset($filters['challenges'])) {
		$where1[] = 'w.challenge="'.$filters['challenge'].'"';
		$where2[] = 'r.challenge="'.$filters['challenge'].'"';
	}
	$getPlayers = mysql_query(
		'SELECT rw.reward,rw.player FROM (
			SELECT r.reward,w.player,COUNT(*) AS nb FROM mkclrewardchs r
			INNER JOIN mkclwin w ON r.challenge=w.challenge AND w.creator=0
			WHERE '. implode(' AND ', $where1) .'
			GROUP BY r.reward,w.player
		) rw
		INNER JOIN (
			SELECT r.reward,COUNT(*) AS nb FROM mkclrewardchs r
			WHERE '. implode(' AND ', $where2) .' GROUP BY r.reward
		) rc
		ON rw.reward=rc.reward AND rw.nb=rc.nb'
	);
	$res = array();
	while ($player = mysql_fetch_array($getPlayers))
		$res[] = $player;
	return $res;
}
function isRuleElligible(&$rule,&$course) {
	return in_array($course, $rule['course']);
}
?>