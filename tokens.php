<?php
function generate_token() {
	$hexa = 'abcdefghijklmnopqrtuvwhxyABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';
	$base = strlen($hexa);
	$n = 32;
	$res = '';
	for ($i=0;$i<$n;$i++)
		$res .= $hexa[rand(0,$base-1)];
	return $res;
}
function assign_token() {
	if (!isset($_SESSION['csrf']))
		$_SESSION['csrf'] = generate_token();
}
?>