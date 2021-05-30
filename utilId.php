<?php
if (!isset($mkSalt)) {
	require('config/id.php');
	function base64($nb) {
		static $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';
		$res = '';
		for ($i=0;$i<6;$i++) {
			$res = $alphabet[$nb%64].$res;
			$nb = floor($nb/64);
		}
		return $res;
	}
	function unbase64($txt) {
		static $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_';
		static $correspondance = null;
		if ($correspondance == null) {
			$correspondance = array();
			for ($i=0;$i<64;$i++)
				$correspondance[$alphabet[$i]] = $i;
		}
		$res = 0;
		for ($i=0;$i<6;$i++) {
			$res *= 64;
			$res += $correspondance[$txt[$i]];
		}
		return $res;
	}
	function encode_mkid($tokenData) {
		global $mkSalt;
		return $tokenData.md5($mkSalt.$tokenData);
	}
	function decode_mkid($mkToken) {
		global $mkSalt;
		$tokenData = substr($mkToken,0,-32);
		$tokenChecksum = substr($mkToken,-32);
		if (md5($mkSalt.$tokenData) == $tokenChecksum)
			return $tokenData;
		return null;
	}
	function store_mkid() {
		global $identifiants;
		if (($identifiants[1] == 0) && ($identifiants[2] == 0) && ($identifiants[3] == 0))
			$_COOKIE['mktoken'] = encode_mkid(base64($identifiants[0]));
		else
			$_COOKIE['mktoken'] = encode_mkid(base64($identifiants[0]).base64($identifiants[1]).base64($identifiants[2]).base64($identifiants[3]));
		@setcookie('mktoken', $_COOKIE['mktoken'], 4294967295,'/');
	}
	function fetch_mkid() {
		global $identifiants;
		$tokenData = decode_mkid($_COOKIE['mktoken']);
		if (null !== $tokenData) {
			$identifiants = Array();
			for ($i=0;$i<4;$i++)
				$identifiants[$i] = 0;
			$lData = strlen($tokenData);
			for ($i=0;$i*6<$lData;$i++)
				$identifiants[$i] = unbase64(substr($tokenData,6*$i,6*($i+1)));
			for ($i=0;$i<4;$i++) {
				if (!is_numeric($identifiants[$i])) {
					unset($identifiants);
					return false;
				}
			}
		}
		return true;
	}
}
?>