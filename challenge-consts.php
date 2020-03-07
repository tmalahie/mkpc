<?php
function getChallengeDifficulties() {
	global $language;
	return array(
		$language ? 'Easy':'Facile',
		$language ? 'Medium':'Moyen',
		$language ? 'Difficult':'Difficile',
		$language ? 'Extreme':'Extrême',
		$language ? 'Impossible':'Impossible'
	);
}
function getChallengeRewards() {
	global $language;
	return array(1,2,5,10,20);
}
function getChallengeReward($challenge) {
	$rewards = getChallengeRewards();
	return $rewards[$challenge['difficulty']];
}
function getChallengeColors() {
	return array(
		'#5DCB32',
		'#FDF320',
		'#FEA932',
		'#FD5C00',
		'#D50F00'
	);
}
function getChallengeDifficulty($challenge) {
	$difficulties = getChallengeDifficulties();
	$colors = getChallengeColors();
	$diffId = $challenge['difficulty'];
	return array(
		'level' => $diffId,
		'name' => $difficulties[$diffId],
		'color' => $colors[$diffId]
	);
}
?>