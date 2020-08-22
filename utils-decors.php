<?php
define('DECORS_DIR', 'images/sprites/uploads/');
function decor_sprite_srcs($hash) {
	$res = array (
		'hd' => DECORS_DIR.$hash.'.png',
		'ld' => DECORS_DIR.$hash.'-ld.png'
	);
	if (!file_exists($res['ld']))
		$res['ld'] = $res['hd'];
	return $res;
}
$CUSTOM_DECOR_TYPES = array(
    'tuyau' => null,
    'taupe' => null,
    'poisson' => null,
    'plante' => null,
    'boo' => null,
    'thwomp' => null,
    'spectre' => null,
    'crabe' => array('nbsprites' => 2),
    'cheepcheep' => null,
    'movingtree' => null,
    'pokey' => array('nbsprites' => 5),
    'firesnake' => array('nbsprites' => 3),
    'box' => null,
    'snowball' => array('nbsprites' => 3),
    'cannonball' => null,
    'truck' => array('nbsprites' => 22),
    'pendulum' => null,
    'snowman' => null,
    'goomba' => array('nbsprites' => 3),
    'fireplant' => array('nbsprites' => 4),
    'piranhaplant' => array('nbsprites' => 2),
    'tortitaupe' => null,
    'billball' => null,
    'topitaupe' => null,
    'chomp' => array('nbsprites' => 8),
    'movingthwomp' => array('nbsprites' => 3),
    'firebar' => null,
    'tree' => null,
    'palm' => null,
    'coconut' => null,
    'sinistertree' => null,
    'falltree' => null,
    'mountaintree' => null,
    'fir' => null,
    'mariotree' => null,
    'peachtree' => null
);
?>