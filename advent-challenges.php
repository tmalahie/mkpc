<?php
switch ($year) {
case 2018:
	$adventChallenges = array(
		1 => array(
			'description' => ($language
				? 'Finish <strong>Battle Course 4</strong> with <strong>5 balloons</strong>.'
				: 'Finir l\'<strong>Arène Bataille 4</strong> avec <strong>5 ballons</strong>.'
			),
			'extra' => ($language
				? 'With 8 players, no teams'
				: 'Avec 8 participants, sans équipes'
			),
			'img' => 'images/selectors/select_map44.png',
			'settings' => 'mkteam=0&mkplayers=8'
		),
		2 => array(
			'description' => ($language
				? 'Finish custom track <a href="map.php?i=1" target="_blank">Circuit Mario 5</a> in <strong>Time Trial</strong> in less then <strong>one minute</strong>.'
				: 'Finir le <a href="map.php?i=1" target="_blank">Circuit Mario 5</a> en mode <strong>Contre-la-montre</strong> en moins de <strong>une minute</strong>.'
			),
			'img' => 'trackicon.php?id=1&type=1',
			'link' => 'map.php?i=1'
		),
		3 => array(
			'description' => ($language
				? 'In <strong>Bowser Castle 3</strong>, finish 1<sup>st</sup> in <strong>VS mode</strong> with <strong>50 participants</strong>.'
				: 'Dans le <strong>Château de Bowser 3</strong>, finir 1<sup>er</sup> en mode <strong>Course VS</strong> avec <strong>50 participants</strong>.'
			),
			'extra' => ($language
				? 'In difficult mode, no teams'
				: 'En mode difficile, sans équipes'
			),
			'img' => 'images/selectors/select_map19.png',
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=50'
		),
		4 => array(
			'description' => ($language
				? 'Make <strong>40 points</strong> in <strong>Grand Prix</strong> mode on <strong>Gold Mushroom cup</strong>.'
				: 'Faire <strong>40 points</strong> en mode <strong>Grand Prix</strong> sur la <strong>coupe champignon doré</strong>.'
			),
			'img' => 'images/objects/speciale.gif',
			'imgW' => 'auto'
		),
		5 => array(
			'description' => ($language
				? 'In <a href="arena.php?id=3472" target="_blank">Arène Impossible 2</a> custom arena, reach the <strong>central area</strong>.'
				: 'Sur l\'<a href="arena.php?id=3472" target="_blank">Arène Impossible 2</a>, atteindre la <strong>zone centrale</strong>.'
			),
			'extra' => ($language
				? 'Yes, it\'s possible... Be creative!'
				: 'Si, c\'est possible... Soyez créatif !'
			),
			'img' => 'trackicon.php?id=3472&type=0',
			'link' => 'arena.php?id=3472'
		),
		6 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> in <a href="map.php?i=73" target="_blank">Forteresse diabolique</a> custom track in <strong>VS mode</strong>.'
				: 'Finir 1<sup>er</sup> sur la <a href="map.php?i=73" target="_blank">Forteresse diabolique</a> en mode <strong>course VS</strong>.'
			),
			'extra' => ($language
				? 'In difficult mode with 8 players, no teams'
				: 'En mode difficile, avec 8 participants, sans équipes'
			),
			'img' => 'trackicon.php?id=73&type=1',
			'link' => 'map.php?i=73',
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=8'
		),
		7 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> in <strong>Luigi Circuit</strong> in VS mode, <strong>without drifting</strong>.'
				: 'Finir 1<sup>er</sup> sur le <strong>Circuit Luigi</strong> en mode course VS, <strong>sans déraper</strong>.'
			),
			'img' => 'images/selectors/select_map29.png',
			'extra' => ($language
				? 'In difficult mode with 8 players, no teams'
				: 'En mode difficile, avec 8 participants, sans équipes'
			),
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=8'
		),
		8 => array(
			'description' => ($language
				? 'Finish <a href="circuit.php?id=3673" target="_blank">Route Arc-en-Ciel ultrarapide</a> custom track without falling more than <strong>10 times</strong>.'
				: 'Finir la <a href="circuit.php?id=3673" target="_blank">Route Arc-en-Ciel ultrarapide</a> en tombant au plus <strong>10 fois</strong>.'
			),
			'img' => 'trackicon.php?id=3673&type=0',
			'link' => 'circuit.php?id=3673'
		),
		9 => array(
			'description' => ($language
				? 'Finish <strong>Battle Course 4</strong> with <strong>6 balloons</strong>.'
				: 'Finir l\'<strong>Arène Bataille 4</strong> avec <strong>6 ballons</strong>.'
			),
			'extra' => ($language
				? 'With 8 players, no teams'
				: 'Avec 8 participants, sans équipes'
			),
			'img' => 'images/selectors/select_map44.png',
			'settings' => 'mkteam=0&mkplayers=8'
		),
		10 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> on <strong>Ghost Valley 2</strong> with <strong>Luigi</strong>.'
				: 'Finir 1<sup>er</sup> sur la <strong>Vallée Fantôme 2</strong> avec <strong>Luigi</strong>.'
			),
			'extra' => ($language
				? 'In difficult mode with 8 players, no teams'
				: 'En mode difficile, avec 8 participants, sans équipes'
			),
			'img' => 'images/selectors/select_map15.png',
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=8'
		),
		11 => array(
			'description' => ($language
				? 'Finish custom track <a href="map.php?i=6921" target="_blank">Mushroom Gorge V2</a> in <strong>Time Trial</strong> in less then <strong>1:15</strong>.'
				: 'Finir le circuit <a href="map.php?i=6921" target="_blank">Mushroom Gorge V2</a> en mode <strong>Contre-la-montre</strong> en moins de <strong>1:15</strong>.'
			),
			'img' => 'trackicon.php?id=6921&type=1',
			'link' => 'map.php?i=6921'
		),
		12 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> in battle mode with <strong>50 participants</strong> in <strong>Battle Course 1</strong>.'
				: 'Finir 1<sup>er</sup> en bataille avec <strong>50 participants</strong>, sur l\'<strong>Arène Bataille 1</strong>.'
			),
			'extra' => ($language
				? 'In "No "teams" mode'
				: 'En mode "Chacun pour soi"'
			),
			'img' => 'images/selectors/select_map41.png',
			'settings' => 'mkteam=0&mkplayers=50'
		),
		13 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> on VS mode on <strong>Mario Circuit 1</strong>, by starting with <strong>15s delay</strong>. Key <strong>7</strong> to fast-forward'
				: 'Finir 1<sup>er</sup> en course VS sur le <strong>Circuit Mario 1</strong>, en démarrant avec <strong>15s de retard</strong>. Touche <strong>7</strong> pour avance rapide'
			),
			'extra' => ($language
				? 'In difficult mode with 8 players, no teams'
				: 'En mode difficile, avec 8 participants, sans équipes'
			),
			'img' => 'images/selectors/select_map1.png',
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=8'
		),
		14 => array(
			'description' => ($language
				? 'In <strong>teams mode</strong>, in VS mode, get the <strong>ranks 1 to 4</strong> with your team.'
				: 'En <strong>mode équipes</strong>, en course VS, occuper les <strong>places 1 à 4</strong> avec votre équipe.'
			),
			'extra' => ($language
				? 'With 8 players, in non-custom track of your choice, Easy mode allowed'
				: 'Avec 8 participants, sur le circuit de votre choix (hors éditeur), mode facile autorisé'
			),
			'img' => 'images/advent-calendar/teams.png',
			'settings' => 'mkteam=1&mkplayers=8'
		),
		15 => array(
			'description' => ($language
				? 'Finish <a href="circuit.php?id=3673" target="_blank">Route Arc-en-Ciel ultrarapide</a> custom track without falling more than <strong>5 times</strong>.'
				: 'Finir la <a href="circuit.php?id=3673" target="_blank">Route Arc-en-Ciel ultrarapide</a> en tombant au plus <strong>5 fois</strong>.'
			),
			'img' => 'trackicon.php?id=3673&type=0',
			'link' => 'circuit.php?id=3673'
		),
		16 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> in <a href="map.php?i=83" target="_blank">Labyrinthe géant</a> custom track in VS mode, <strong>by starting 8th</strong>.'
				: 'Finir 1<sup>er</sup> sur le <a href="map.php?i=83" target="_blank">Labyrinthe géant</a> en mode course VS, <strong>en commançant 8e</strong>.'
			),
			'extra' => ($language
				? 'In difficult mode with 8 players, no teams'
				: 'En mode difficile, avec 8 participants, sans équipes'
			),
			'img' => 'trackicon.php?id=83&type=1',
			'link' => 'map.php?i=83',
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=8'
		),
		17 => array(
			'description' => ($language
				? 'In <strong>Battle mode</strong>, finist 1<sup>st</sup> <strong>4 times in a row</strong>.'
				: 'En <strong>Mode bataille</strong>, finir 1<sup>er</sup> <strong>4 fois d\'affilée</strong>.'
			),
			'extra' => ($language
				? 'With 8 players, no teams, courses of your choice'
				: 'Avec 8 participants, sans équipes, arènes de votre choix'
			),
			'img' => 'images/advent-calendar/battle.png',
			'settings' => 'mkteam=0&mkplayers=8'
		),
		18 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> in <strong>Koopa Beach 2</strong> in VS mode, <strong>without touching a box item</strong>.'
				: 'Finir 1<sup>er</sup> sur la <strong>Plage Koopa 2</strong> en mode VS, <strong>sans toucher les boîtes à objet</strong>.'
			),
			'img' => 'images/selectors/select_map13.png',
			'extra' => ($language
				? 'In difficult mode with 8 players, no teams'
				: 'En mode difficile, avec 8 participants, sans équipes'
			),
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=8'
		),
		19 => array(
			'description' => ($language
				? 'Hit <strong>15 people</strong> in battle mode in a single game.'
				: 'Toucher <strong>15 personnes</strong> en mode bataille sur une partie.'
			),
			'img' => 'images/advent-calendar/battle.png',
			'extra' => ($language
				? 'With 8 players, no teams, course of your choice'
				: 'Avec 8 participants, sans équipes, arène de votre choix'
			),
			'settings' => 'mkteam=0&mkplayers=8'
		),
		20 => array(
			'description' => ($language
				? 'Finish 1<sup>st</sup> on VS mode on <strong>Mario Circuit 1</strong>, by starting with <strong>20s delay</strong>. Key <strong>7</strong> to fast-forward'
				: 'Finir 1<sup>er</sup> en course VS sur le <strong>Circuit Mario 1</strong>, en démarrant avec <strong>20s de retard</strong>. Touche <strong>7</strong> pour avance rapide'
			),
			'extra' => ($language
				? 'In difficult mode with 8 players, no teams'
				: 'En mode difficile, avec 8 participants, sans équipes'
			),
			'img' => 'images/selectors/select_map1.png',
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=8'
		),
		21 => array(
			'description' => ($language
				? 'In <strong>Bowser Castle IV</strong>, finish 1<sup>st</sup> in <strong>VS mode</strong> with <strong>100 participants</strong>.'
				: 'Dans le <strong>Château de Bowser IV</strong>, finir 1<sup>er</sup> en mode <strong>Course VS</strong> avec <strong>100 participants</strong>.'
			),
			'extra' => ($language
				? 'In difficult mode, no teams'
				: 'En mode difficile, sans équipes'
			),
			'img' => 'images/selectors/select_map39.png',
			'settings' => 'mkteam=0&mkdifficulty=2&mkplayers=100'
		),
		22 => array(
			'description' => ($language
				? 'Finish <strong>37th</strong> in VS mode, with <strong>50 participants</strong>.'
				: 'Finir <strong>37e</strong> en course VS, avec <strong>50 participants</strong>.'
			),
			'img' => 'images/advent-calendar/rank.png',
			'extra' => ($language
				? 'In non-custom track of your choice'
				: 'Sur le circuit de votre choix (hors éditeur)'
			),
			'settings' => 'mkplayers=50'
		),
		23 => array(
			'description' => ($language
				? 'In <a href="battle.php?i=479" target="_blank">Dead Circle</a> custom course, stay for <strong>30 seconds</strong> without losing any balloon.'
				: 'Dans l\'arène <a href="battle.php?i=479" target="_blank">Dead Circle</a>, tenir <strong>30 secondes</strong> sans perdre de ballons.'
			),
			'img' => 'trackicon.php?id=479&type=2',
			'link' => 'battle.php?i=479'
		),
		24 => array(
			'description' => ($language
				? 'In <a href="battle.php?i=588" target="_blank">FKPC</a> custom course, eliminate <strong>all opponents</strong> by yourself.'
				: 'Dans l\'arène <a href="battle.php?i=588" target="_blank">FKPC</a>, éliminer <strong>tous les adversaires</strong> par vous-même.'
			),
			'extra' => ($language
				? 'With 8 players, no teams'
				: 'Avec 8 participants, sans équipes'
			),
			'img' => 'trackicon.php?id=588&type=2',
			'settings' => 'mkteam=0&mkplayers=8',
			'link' => 'battle.php?i=588'
		)
	);
	break;
case 2022:
	require_once('utils-challenges.php');
	include('advent-selected-challenges.php');
	$challengeIds = array_values($selectedChallenges);
	$getChallenges = mysql_query('SELECT c.*,l.type,l.circuit FROM mkchallenges c INNER JOIN mkclrace l ON c.clist=l.id WHERE c.id IN ('. implode(',', $challengeIds).')');
	$challengeById = array();
	while ($challenge = mysql_fetch_array($getChallenges))
		$challengeById[$challenge['id']] = $challenge;
	$adventChallenges = array();
	foreach ($selectedChallenges as $d => $challengeId) {
		$challenge = $challengeById[$challengeId];
		$challengeParams = array(
			'circuit' => true
		);
		$challengeDetails = getChallengeDetails($challenge, $challengeParams);
		$circuitDetails = $challengeDetails['circuit'];
		$circuitLink = "challengeTry.php?challenge=$challengeId";
		$adventChallenge = array(
			'name' => $challenge['name'],
			'description' => ($language
				? '<a href="'. $circuitDetails['href'] .'" target="_blank">'. $circuitDetails['name'] .'</a>: '. $challengeDetails['description']['main']
				: '<a href="'. $circuitDetails['href'] .'" target="_blank">'. $circuitDetails['name'] .'</a> : '. $challengeDetails['description']['main']
			),
			'img' => $circuitDetails['cicon'],
			'link' => $circuitLink
		);
		if (isset($challengeDetails['description']['extra']))
			$adventChallenge['extra'] = $challengeDetails['description']['extra'];
		$adventChallenges[$d] = $adventChallenge;
	}
	$adventChallenges[19]['name'] = $language ? '12 labours of Mario' : 'Les 12 travaux de Mario';
	$adventChallenges[19]['description'] = '<a href="map.php?i=86275" target="_blank">'. $adventChallenges[19]['name'] .'</a>' . ($language ? ': Complete all challenges of the track' : ' : Complétez tous les défis du circuit');
	$adventChallenges[19]['link'] = 'map.php?i=86275';
}
function get_challenges_until($day) {
	global $adventChallenges;
	$adventChallengesUntil = array();
	for ($i=1;$i<=$day;$i++) {
		if (isset($adventChallenges[$i]))
			$adventChallengesUntil[$i] = $adventChallenges[$i];
	}
	return $adventChallengesUntil;
}
?>