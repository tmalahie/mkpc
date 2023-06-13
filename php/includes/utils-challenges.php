<?php
require_once('touch.php');
require_once('challenge-consts.php');
require_once('cache_creations.php');
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
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('battle')
		),
		'eliminate' => array(
			'description' => $language ? 'Eliminate $value opponent$s by yourself':'Éliminer $value adversaire$s par vous-même',
			'description_mockup' => $language ? 'Eliminate N opponents by yourself':'Éliminer N adversaires par vous-même',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
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
				$lang = $language ? 'en' : 'fr';
				if (isset($scope->description->{$lang}))
					return htmlspecialchars($scope->description->{$lang});
				return htmlspecialchars($scope->description);
			},
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
				if (!empty($scope['translated'])) {
					$scope['description'] = array(
						'fr' => $scope['description_fr'],
						'en' => $scope['description_en']
					);
				}
				else {
					unset($scope['description_fr']);
					unset($scope['description_en']);
				}
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			},
			'course' => array('vs','battle')
		),
		'reach_zones' => array(
			'description_mockup' => $language ? 'Go through N zones...':'Passer par N zones...',
			'description_lambda' => function($language,&$scope) {
				$lang = $language ? 'en' : 'fr';
				if (isset($scope->description->{$lang}))
					return htmlspecialchars($scope->description->{$lang});
				return htmlspecialchars($scope->description);
			},
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
				if (isset($scope['ordered']))
					$scope['ordered'] = intval($scope['ordered']);
				if (!empty($scope['translated'])) {
					$scope['description'] = array(
						'fr' => $scope['description_fr'],
						'en' => $scope['description_en']
					);
				}
				else {
					unset($scope['description_fr']);
					unset($scope['description_en']);
				}
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
					$scope['nb'] = intval($scope['nb']);
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			},
			'course' => array('vs','battle')
		),
		'destroy_decors' => array(
			'description_mockup' => $language ? 'Destroy decors...':'Détruire les décors...',
			'description_lambda' => function($language,&$scope) {
				if (isset($scope->nb)) {
					$nb = $scope->nb;
					$decorName = getChallengeDecorName($scope->value, $scope->name, $nb);
					return $language ? "Destroy $nb $decorName":"Détruire $nb $decorName";
				}
				$decorName = getChallengeDecorName($scope->value, $scope->name, 2);
				return $language ? "Destroy all $decorName":"Détruire les $decorName";
			},
			'parser' => function(&$scope) {
				$decors = $scope['value'];
				foreach ($decors as $type => $options) {
					$scope['value'] = $type;
					if (!empty($options['name']))
						$scope['name'] = $options['name'];
					break;
				}
				if (empty($scope['nb']))
					unset($scope['nb']);
				else
					$scope['nb'] = intval($scope['nb']);
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
		'gold_cups_n' => array(
			'description_mockup' => $language ? 'Get N gold cups':'Obtenir N coupes d\'or',
			'description' => $language ? 'Get $value gold cup$s':'Obtenir $value coupe$s d\'or',
			'course' => array('mcup'),
			'autoset' => function(&$res, $scope) {
				$res['course'] = 'GP';
			}
		),
		'finish_circuits_first' => array(
			'description_mockup' => $language ? 'Finish 1st N times in a row':'Finir 1er N fois d\'affilée',
			'description' => $language ? 'Finish 1st $value time$s in a row':'Finir 1er $value fois d\'affilée',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'pts_greater' => array(
			'description_mockup' => $language ? 'Make at least x points in N races':'Faire au moins x points sur N courses',
			'description' => $language ? 'Make at least $pts points in $value race$s':'Faire au moins $pts points sur $value course$s',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('cup', 'mcup', 'bcup', 'mbcup')
		),
		'pts_equals' => array(
			'description_mockup' => $language ? 'Make exactly x points in N races':'Faire exactement x points sur N courses',
			'description' => $language ? 'Make exactly $pts points in $value race$s':'Faire exactement $pts points sur $value course$s',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('cup', 'mcup', 'bcup', 'mbcup')
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
				$scope['value'] = intval($scope['value']);
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
				$scope['value'] = intval($scope['value']);
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
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup'),
			'this_class' => function(&$scope) {
				return ($scope->value == 8);
			}
		),
		'cc' => array(
			'description' => $language ? 'in ${value}cc class':'en mode ${value}cc',
			'description_mockup' => $language ? 'Class (cc)':'Cylindrée (cc)',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup'),
			'description_lambda' => function($language,&$scope) {
				if ($language)
					return 'in '.$scope->value.'cc'.(isset($scope->mirror) ? ' mirror':'').' class';
				else
					return 'en mode '.$scope->value.'cc'.(isset($scope->mirror) ? ' mirroir':'');
				exit;
			},
			'parser' => function(&$scope) {
				if (isset($scope['mirror']))
					$scope['mirror'] = 1;
			}
		),
		'no_teams' => array(
			'description' => $language ? 'no teams':'sans équipes',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup'),
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
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('battle')
		),
		'no_drift' => array(
			'description' => $language ? 'without drifting':'sans déraper',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'avoid_items' => array(
			'description' => $language ? 'without touching item boxes':'sans toucher les boites à objet',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'no_item' => array(
			'description' => $language ? 'without using any item':'sans utiliser d\'objets',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'avoid_decors' => array(
			'description_mockup' => $language ? 'without touching a decor...':'sans toucher un décor...',
			'description_lambda' => function($language,&$scope) {
				$itemsToAvoid = array();
				foreach ($scope->value as $key=>$decor)
					$itemsToAvoid[] = getChallengeDecorName($key, $decor->name);
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
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup'),
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
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('vs', 'battle', 'cup', 'bcup')
		),
		'no_stunt' => array(
			'description' => $language ? 'without making stunts':'sans faire de figures',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
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
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'forwards' => array(
			'description' => $language ? 'without going backwards':'sans reculer',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'without_turning' => array(
			'description_mockup' => $language ? 'without turning...':'sans tourner...',
			'description_lambda' => function($language,&$scope) {
				$direction = '';
				switch ($scope->value) {
					case 'left':
						$direction = $language ? ' left' : ' à gauche';
						break;
					case 'right':
						$direction = $language ? ' right' : ' à droite';
						break;
				}
				return $language ? "without turning $direction" : "sans tourner $direction";
			},
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'time_delay' => array(
			'description' => $language ? 'by starting with ${value}s delay':'en partant avec ${value}s de retard',
			'description_mockup' => $language ? 'by starting with x seconds delay':'en partant avec x secondes de retard',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('vs')
		),
		'mini_turbo' => array(
			'description' => $language ? 'by performing $value Mini-Turbo$s':'en réalisant $value dérapage$s Turbo',
			'description_mockup' => $language ? 'by performing N Turbo drifts':'en réalisant N dérapages Turbo',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('vs','battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'super_turbo' => array(
			'description' => $language ? 'by performing $value Super Mini-Turbo$s':'en réalisant $value Super Mini-Turbo$s',
			'description_mockup' => $language ? 'by performing N Super Mini-Turbo':'en réalisant N Super Mini-Turbo',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('vs','battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'stunts' => array(
			'description' => $language ? 'by performing $value stunt$s':'en réalisant $value figure$s',
			'description_mockup' => $language ? 'by performing N stunts':'en réalisant N figures',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('vs','battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'position' => array(
			'description_mockup' => $language ? 'in n-th place':'en n-eme position',
			'description_lambda' => function($language,&$scope) {
				return $language ? 'in '. getPositionName($scope->value) .' place' : 'en '. getPositionName($scope->value) .' position';
			},
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('vs')
		),
		'with_pts' => array(
			'description_mockup' => $language ? 'with x points or more':'avec x points ou plus',
			'description' => $language ? 'with $value point$s or more':'avec $value point$s ou plus',
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
			},
			'course' => array('cup', 'mcup', 'bcup', 'mbcup')
		),
		'different_circuits' => array(
			'description' => $language ? 'in different circuits':'sur des circuits différents',
			'course' => array('cup', 'mcup')
		),
		'different_arenas' => array(
			'description' => $language ? 'in different arenas':'sur des arènes différentes',
			'course' => array('bcup', 'mbcup')
		),
		'difficulty' => array(
			'description_mockup' => $language ? 'difficulty...':'difficulté...',
			'description_lambda' => function($language,&$scope) {
				return $language ? 'in '.$scope->options[$scope->value+2].' mode':'en mode '.$scope->options[$scope->value+2];
			},
			'scope' => array(
				'options' => $language ? array('impossible','extreme','difficult','medium','easy') : array('impossible','extrême','difficile','moyen','facile')
			),
			'parser' => function(&$scope) {
				$scope['value'] = intval($scope['value']);
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
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup'),
			'additional_lambda' => function(&$scope) {
				return ($scope->value == 8);
			},
			'autoset' => function(&$res, $scope) {
				$res['selectedPlayers'] = $scope->value;
			}
		)
	),
	'setup' => array(
		'start_pos' => array(
			'description' => null,
			'description_mockup' => $language ? 'start at location...':'commencer à... (position)',
			'course' => array('vs', 'battle'),
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
				if (isset($scope['no_cpu']))
					$scope['no_cpu'] = 1;
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			}
		),
		'init_item' => array(
			'description' => null,
			'description_mockup' => $language ? 'start with item...':'commencer avec l\'objet...',
			'course' => array('vs', 'battle')
		),
		'item_distribution' => array(
			'description' => null,
			'description_mockup' => $language ? 'item distribution...':'distribution des objets...',
			'course' => array('vs', 'battle'),
			'parser' => function(&$scope) {
				$scope['value'] = explode(',', $scope['value']);
			}
		),
		'no_item_box' => array(
			'description' => null,
			'description_mockup' => $language ? 'without items':'sans objets',
			'course' => array('vs', 'battle')
		),
		'extra_items' => array(
			'description' => null,
			'description_mockup' => $language ? 'add extra items...':'ajouter des objets...',
			'course' => array('vs', 'battle'),
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
				if (isset($scope['clear_other']))
					$scope['clear_other'] = 1;
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			}
		),
		'extra_decors' => array(
			'description' => null,
			'description_mockup' => $language ? 'add extra decors...':'ajouter des décors...',
			'course' => array('vs', 'battle'),
			'parser' => function(&$scope) {
				$scope['value'] = json_decode($scope['value']);
				if (!empty($scope['custom_decors']))
					$scope['custom_decors'] = json_decode($scope['custom_decors']);
			},
			'formatter' => function(&$scope) {
				$scope->value = json_encode($scope->value);
			}
		),
		'custom_music' => array(
			'description' => null,
			'description_mockup' => $language ? 'custom music...':'changer la musique...',
			'course' => array('vs', 'battle'),
			'parser' => function(&$scope) {
				if (empty($scope['yt']))
					$scope['value'] = intval($scope['value']);
				else
					$scope['value'] = 0;
			}
		),
		'auto_accelerate' => array(
			'description' => $language ? 'while constantly accelerating' : 'en accélérant en continu',
			'description_mockup' => $language ? 'auto accelerate':'auto-accélérer',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'invert_dirs' => array(
			'description' => $language ? 'with inverted controls':'avec les contrôles inversés',
			'description_mockup' => $language ? 'invert left and right' : 'inverser gauche et droite',
			'course' => array('vs', 'battle', 'cup', 'mcup', 'bcup', 'mbcup')
		),
		'balloons_player' => array(
			'description' => $language ? 'by starting with $value balloon$s':'en commençant avec $value ballon$s',
			'description_mockup' => $language ? 'initial player balloons':'nb ballons initiaux (joueur)',
			'course' => array('battle')
		),
		'balloons_cpu' => array(
			'description' => $language ? 'having CPUs starting with $value balloon$s':'les ordis commençant avec $value ballon$s',
			'description_mockup' => $language ? 'initial CPU balloons':'nb ballons initiaux (ordis)',
			'course' => array('battle')
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
	if (isset($params['status'])) {
		$statusCheck = 'status IN ("'. implode('","',$params['status']) .'")';
		$getClist = mysql_fetch_array(mysql_query('SELECT id,type,circuit FROM `mkclrace` WHERE id="'. $clRace .'"'));
	}
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
			$getCls = mysql_query('SELECT DISTINCT cl.id,c.circuit0,c.circuit1,c.circuit2,c.circuit3 FROM  mkmcups_tracks t INNER JOIN mkcups c ON c.id=t.cup LEFT JOIN mkclrace cl ON cl.type="mkcups" AND cl.circuit=t.cup WHERE t.mcup='. $getClist['circuit'] .' ORDER BY t.ordering');
			while ($subCl = mysql_fetch_array($getCls)) {
				if ($subCl['id'])
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
			switch ($allSubTracks['mode']) {
			case 1:
				$trackTable = 'circuits';
				break;
			case 3:
				$trackTable = 'arenes';
				break;
			default:
				$trackTable = 'mkcircuits';
			}
			$getClTracks = mysql_query('SELECT DISTINCT id FROM mkclrace WHERE type="'. $trackTable .'" AND circuit IN ('.$trackIdsString.')');
			while ($subCl = mysql_fetch_array($getClTracks))
				$subCls[] = $subCl['id'];
		}
		foreach ($subCls as $clRaceId)
			$res = array_merge($res, listChallenges($clRaceId,$newParams));
	}
	return $res;
}
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
	if (!empty($params['rating']))
		$res['rating'] = array('avg' => $challenge['avgrating'], 'nb' => $challenge['nbratings']);
	if (!empty($params['circuit'])) {
		$res['circuit'] = getCircuitPayload($challenge);
		if (empty($params['utf8']) && empty($params['circuit.raw']) && !empty($res['circuit'])) {
			$res['circuit']['name'] = htmlEscapeCircuitNames($res['circuit']['name']);
			$res['circuit']['author'] = htmlEscapeCircuitNames($res['circuit']['author']);
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
require_once('utils-cups.php');
function getCircuitPayload(&$clRace) {
	global $language;
	$res = array();
	if (!$clRace['type'])
		return $res;
	if ($clCircuit = fetchCreationData($clRace['type'], $clRace['circuit'], array(
		'select' => 'c.*,s.thumbnail'
	))) {
		$res['name'] = $clCircuit['name'];
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
			$linkUrl = getCupPage($clCircuit['mode']) .'.php?cid='. $clCircuit['id'];
			switch ($clCircuit['mode']) {
			case 1:
				$baseCache = 'racepreview';
				break;
			case 3:
				$baseCache = 'coursepreview';
				break;
			default:
				$baseCache = 'mappreview';
			}
			include('creation-entities.php');
			$cTable = $CREATION_ENTITIES[$clCircuit['mode']]['table'];
			$getThumbnails = mysql_query('SELECT circuit,thumbnail FROM mktracksettings WHERE type="'. $cTable .'" AND circuit IN ('. $clCircuit['circuit0'] .','. $clCircuit['circuit1'] .','. $clCircuit['circuit2'] .','. $clCircuit['circuit3'] .') AND thumbnail IS NOT NULL');
			$cThumbnails = array();
			while ($thumbnail = mysql_fetch_array($getThumbnails))
				$cThumbnails[$thumbnail['circuit']] = $thumbnail['thumbnail'];
			for ($i=0;$i<4;$i++) {
				$lId = $clCircuit['circuit'.$i];
				if (isset($cThumbnails[$lId])) {
					$linkCached = 'uploads/'. $cThumbnails[$lId];
					$linkIcon = cachePathRelative($linkCached);
				}
				else {
					$iconType = $clCircuit['mode'];
					if ($iconType >= 2)
						$iconType = 0;
					$linkIcon = 'trackicon.php?id='. $lId .'&type='. $iconType;
					$linkCached = $baseCache . $lId .'.png';
				}
				$linkBg .= ($i?',':'') . $linkIcon;
				$linksCached[] = $linkCached;
			}
			break;
		case 'mkmcups':
			$linkUrl = getCupPage($clCircuit['mode']) .'.php?mid='. $clCircuit['id'];
			$linkBg .= 'trackicon.php?id='. $clCircuit['id'] .'&type=4';
			$linksCached[] = 'mcuppreview'. $clCircuit['id'] .'.png';
		}
		$allCached = true;
		if ($clCircuit['thumbnail']) {
			$linkBg = 'uploads/'.$clCircuit['thumbnail'];
			$linksCached = array($linkBg);
		}
		else {
			foreach ($linksCached as $link) {
				$filename = cachePath($link);
				if (file_exists($filename))
					touch_async($filename);
				else {
					$allCached = false;
					break;
				}
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
	if ($res = mysql_fetch_array(mysql_query('SELECT c.* FROM `mkchallenges` c'. ($isModerator ? ' WHERE c.id="'. $chId .'"':' LEFT JOIN `mkclrace` l ON l.id=c.clist WHERE c.id="'. $chId .'" AND (l.id IS NULL OR (l.identifiant='.$identifiants[0].' AND l.identifiant2='.$identifiants[1].' AND l.identifiant3='.$identifiants[2].' AND l.identifiant4='.$identifiants[3].'))'))))
		return $res;
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
	if (!isset($res) && isset($data['description_lambda'])) {
		$scope = $rule;
		if (isset($data['scope'])) {
			foreach ($data['scope'] as $k=>$v)
				$scope->{$k} = $v;
		}
		$res = $data['description_lambda']($language,$scope);
	}
	if (!isset($res)) {
		$res = $data['description'];
		if ($res === null) return $res;
		$scope = (array)$rule;
		if (isset($scope['value']))
			$scope['s'] = ($scope['value']>=2 ? 's':'');
		if (isset($data['scope']))
			$scope = array_merge($data['scope'],$scope);
		$res = preg_replace_callback('#\$\{?(\w+)\}?#', function($matches) use ($scope) {
			$k = $matches[1];
			if (isset($scope[$k]) && !is_array($scope[$k]))
				return $scope[$k];
			return $matches[0];
		}, $res);
		$res = preg_replace_callback('#\$\{?(\w+)\[(\w+)\]\}?#', function($matches) use ($scope) {
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
		else {
			$constraintDesc = getRuleDescription($data);
			if ($constraintDesc !== null)
				$constraintDescs[] = $constraintDesc;
		}
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
			$elt = intval($elt);
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
function getChallengeDecorName($key, &$name, $nb=0) {
	global $language;
	$plural = ($nb > 1);
	$e = $plural ? 'e' : '';
	$s = $plural ? 's' : '';
	$x = $plural ? 'x' : '';
	$decorTree = $language ? "tree$s" : "arbre$s";
	$decorMapping = array(
		'tuyau' => $language ? "pipe$s":"tuyau$x",
		'taupe' => $language ? "Monty Mole$s":"Topi Taupe$s",
		'poisson' => "Cheep-Cheep$s",
		'plante' => $language ? "Piranha Plant$s":"Plante$s Piranha",
		'boo' => "Boo$s",
		'thwomp' => "Thwomp$s",
		'spectre' => "Thwomp$s",
		'assets/oil1' => $language ? "oil spill$s":"tâche$s d'huile",
		'assets/oil2' => $language ? "puddle$s":"flaque$s",
		'crabe' => $language ? "crab$s":"crabe$s",
		'cheepcheep' => "Cheep-Cheep$s",
		'movingtree' => $language ? "moving tree$s" : "arbre$s mobile",
		'pokey' => "Pokey$s",
		'firesnake' => $language ? "fire snake$s" : "serpent$s de feu",
		'box' => $language ? "box$e$s":"caisse$s",
		'snowball' => $language ? "snow ball$s":"boule$s de neige",
		'cannonball' => $language ? "pinball ball$s":"boule$s de flipper",
		'truck' => $language ? "bus$e$s" : "bus",
		'pendulum' => $language ? "pendulum$s":"pendule$s",
		'assets/pivothand' => $language ? "clock hand$s":"aiguille$s",
		'snowman' => $language ? ($plural ? "snowmen":"snowman"):"bonhomme$s de neige",
		'goomba' => "Goomba$s",
		'fireplant' => $language ? "fire plant$s" : "plante$s de feu",
		'piranhaplant' => $language ? "Piranha Plant$s":"Plante$s Piranha",
		'tortitaupe' => $language ? "Monty Mole$s" : "Torti Taupe$s",
		'billball' => $language ? "Bullet Bill$s" : "Bill Ball$s",
		'billball1' => $language ? "Bullet Bill$s" : "Bill Ball$s",
		'billball2' => $language ? "Bullet Bill$s" : "Bill Ball$s",
		'billball3' => $language ? "Bullet Bill$s" : "Bill Ball$s",
		'firering' => $language ? "fire circle$s":"cercle$s de feu",
		'fire3star' => $language ? "fire triplet$s":"triolet$s de feu",
		'topitaupe' => $language ? "Monty Mole$s" : "Topi Taupe$s",
		'chomp' => "Chomp$s",
		'movingthwomp' => "Thwomp$s",
		'firebar' => $language ? "fire bar$s" : "barre$s de feu",
		'tree' => $decorTree,
		'palm' => $decorTree,
		'coconut' => $decorTree,
		'sinistertree' => $decorTree,
		'falltree' => $decorTree,
		'mountaintree' => $decorTree,
		'fir' => $decorTree,
		'mariotree' => $decorTree,
		'peachtree' => $decorTree,
		'assets/flower1' => $language ? "flower$s":"fleur$s",
		'assets/flower2' => $language ? "flower$s":"fleur$s",
		'assets/flower3' => $language ? "flower$s":"fleur$s",
		'assets/bumper' => "bumper$s",
		'assets/flipper' => "flipper$s"
	);
	$lang = $language ? 'en' : 'fr';
	if (isset($name->{$lang}))
		return htmlspecialchars($name->{$lang});
	if (!empty($name))
		return htmlspecialchars($name);
	if (isset($decorMapping[$key]))
		return $decorMapping[$key];
	return $key;
}
?>