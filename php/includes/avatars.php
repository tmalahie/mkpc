<?php
define('AVATAR_S', 100);
define('AVATAR_M', 200);
define('AVATAR_L', 400);
define('AVATAR_MINW', 200);
define('AVATAR_MINH', 200);
define('AVATAR_DIR', 'images/avatars/');
define('AVATAR_REL_DIR', '../../images/avatars/');
$AVATAR_COLORS = [
	'#E15B05',
	'#9C2D02',
	'#43D23A',
	'#4F97BE',
	'#1E98C4',
	'#1ED684',
	'#C0479B',
	'#ACC2AC',
	'#88F987',
	'#EACE98'
];
define('AVATAR_NCOLORS', count($AVATAR_COLORS));
function get_avatar_color($pseudo) {
	global $AVATAR_COLORS;
	if ($pseudo == null)
		return 'rgba(127,124,123,0.2)';
	return $AVATAR_COLORS[crc32($pseudo) % AVATAR_NCOLORS];
}
function print_avatar($id, $w,$unit=null) {
	global $deletionsCache, $language;
	$avatarSrc = get_avatar_img($id);
	if (!$unit)
		$unit = 'px';
	if ($avatarSrc == null) {
		$css = $w ? 'width:'.$w.$unit.';height:'.$w.$unit.';font-size:'.round($w*0.6).$unit.';' : '';
		$deleted = isset($deletionsCache['i'.$id]);
		$pseudo = get_pseudo_text($id);
		?>
		<div class="avatar avatar-blank" style="<?php echo $css; ?>background-color:<?php echo get_avatar_color($deleted ? null : $pseudo); ?><?php if ($deleted) echo ';cursor:default;color:rgba(255,255,255,0.6)' ?>"<?php if ($deleted) echo 'title="'. ($language ? 'Deleted account':'Compte supprimé') .'" onclick="return false"' ?>>
			<?php if ($pseudo !== null) echo strtoupper($pseudo[0]); ?>
		</div>
		<?php
	}
	else {
		$css = $w ? 'width:'.$w.$unit.';height:'.$w.$unit.';' : '';
		$ld = ($w&&($w<=AVATAR_MINW));
		?>
		<div class="avatar avatar-img" style="<?php echo $css; ?>background-image:url('<?php echo AVATAR_DIR.$avatarSrc[$ld?'ld':'hd']; ?>')">
		</div>
		<?php
	}
}
$avatarsCache = array();
$namesCache = array();
$deletionsCache = array();
$msgsCache = array();
$countriesCache = array();
$flagsCache = array();
function to_ld($path) {
	return preg_replace('#\.\w+$#','-ld.png', $path);
}
function get_avatar_img($id) {
	global $avatarsCache, $deletionsCache;
	if (isset($avatarsCache['i'.$id]))
		return $avatarsCache['i'.$id];
	if ($getAvatar = mysql_fetch_array(mysql_query('SELECT p.avatar,j.nom,j.deleted FROM `mkprofiles` p LEFT JOIN `mkjoueurs` j ON p.id=j.id WHERE p.id="'. $id .'"'))) {
		if ($getAvatar['deleted'])
			$deletionsCache['i'.$id] = true;
		elseif ($getAvatar['avatar']) {
			if (!$getAvatar['deleted'])
				return ($avatarsCache['i'.$id]=array('hd'=>$getAvatar['avatar'],'ld'=>to_ld($getAvatar['avatar'])));
		}
	}
	return ($avatarsCache['i'.$id]=null);
}
function clear_avatar_cache($id) {
	global $avatarsCache;
	unset($avatarsCache['i'.$id]);
}
function get_pseudo_text($id) {
	global $namesCache;
	if (isset($namesCache['i'.$id]))
		return $namesCache['i'.$id];
	if ($getName = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
		return ($namesCache['i'.$id]=$getName['nom']);
	return ($namesCache['i'.$id]=null);
}
function get_pseudo_bbcolor($pseudo) {
	return preg_replace('#\[color=([^\]]+)\](.*)\[/color\]#isU', '<span style="color: $1">$2</span>', htmlspecialchars($pseudo));
}
function get_country_id($id) {
	global $countriesCache;
	if (isset($countriesCache['i'.$id]))
		return $countriesCache['i'.$id];
	if ($getCountry = mysql_fetch_array(mysql_query('SELECT country FROM `mkprofiles` WHERE id="'. $id .'"')))
		return ($countriesCache['i'.$id]=$getCountry['country']);
	return ($countriesCache['i'.$id]=0);
}
function get_flag_data($id) {
	return get_flag_from_id(get_country_id($id));
}
function get_flag_from_id($id) {
	global $flagsCache, $language;
	if (!$id) return null;
	if (isset($flagsCache['i'.$id]))
		return $flagsCache['i'.$id];
	if ($getFlag = mysql_fetch_array(mysql_query('SELECT id,code,name_'. ($language ? 'en':'fr') .' AS name FROM `mkcountries` WHERE id="'. $id .'"')))
		return ($flagsCache['i'.$id]=$getFlag);
	return ($flagsCache['i'.$id]=0);
}
function print_flag($id) {
	$flagData = get_flag_data($id);
	if ($flagData)
		echo '<div class="mCountry"><div class="country-ic" style="background-image:url(\'images/flags/'. $flagData['code'] .'.png\')"></div> '. htmlspecialchars($flagData['name']) .'</div>';
}

$LEAGUES = [
    0      => ['name' => _('Unlicensed'),                                    'color' => '#000000'],
    4000   => ['name' => _('Budding pilot'),                                 'color' => '#A24E24'],
    5000   => ['name' =>   'Novice',                                         'color' => '#E53A35'],
    6000   => ['name' => _('Racer'),                                         'color' => '#993399'],
    8000   => ['name' =>   'Expert',                                         'color' => '#400080'],
    10000  => ['name' =>   'Champion',                                       'color' => '#3366FF'],
    15000  => ['name' => _('Master'),                                        'color' => '#55C43D'],
    20000  => ['name' => _('Legend'),                                        'color' => '#0E9D4E'],
    40000  => ['name' =>   'Titan',                                          'color' => '#FF6600'],
    100000 => ['name' =>   '<span class="superstar_grad">Superstar</span>',  'color' => ''       ]
];

function get_league_rank($pts) {
    global $LEAGUES;
    $rank = $LEAGUES[0];
    foreach ($LEAGUES as $key => $league) {
        if ($pts >= $key) {
            $rank = $league;
        } else {
            break;
        }
    }
    return $rank;
}

$FORUM_RANKS = [
	0    => ['name' => 'Goomba',         'img' => 'goomba'     ],
	10   => ['name' => 'Koopa',          'img' => 'koopa'      ],
	50   => ['name' => 'Boo',            'img' => 'boo'        ],
	100  => ['name' => 'Buzzy Beetle',   'img' => 'buzzybeetle'],
	150  => ['name' => 'Bowser',         'img' => 'bowser'     ],
	200  => ['name' => 'Toad',           'img' => 'toad'       ],
	250  => ['name' => _('Toadsworth'),  'img' => 'toadsworth' ],
	300  => ['name' => 'Peach',          'img' => 'peach'      ],
	350  => ['name' => 'Luigi',          'img' => 'luigi'      ],
	400  => ['name' => _('Metal Luigi'), 'img' => 'metalluigi' ],
	500  => ['name' => 'Mario',          'img' => 'mario'      ],
	1000 => ['name' => _('Golden Mario'),'img' => 'goldenmario'],
	2500 => ['name' => _('King Mario'),  'img' => 'kingmario'  ],
];

function get_forum_rank($msgs) {
	global $FORUM_RANKS;
	$rank = $FORUM_RANKS[0];
	foreach ($FORUM_RANKS as $threshold => $info) {
		if ($msgs >= $threshold) {
			$rank = $info;
		} else {
			break;
		}
	}
	return $rank;
}

function print_nb_msgs($id) {
	$nb = get_msg_count($id);
	$rank = get_forum_rank($nb);
	$rankname = $rank['name'];
	$img = $rank['img'];
	echo '<div class="mNbmsgs"><img src="images/messages.png" alt="Messages" class="mNbmsgsIc" /> '.$nb.' - <span><img src="images/ranks/'. $img .'.gif" alt="'. $rankname .'" class="mNbmsgsRk" /></span> '. $rankname .'</div>';
}

function get_msg_count($id) {
	global $msgsCache;
	if (isset($msgsCache['i'.$id]))
		return $msgsCache['i'.$id];
	if ($getAvatar = mysql_fetch_array(mysql_query('SELECT nbmessages FROM `mkprofiles` WHERE id="'. $id .'"'))) {
		if ($getAvatar['nbmessages'])
			return ($msgsCache['i'.$id]=$getAvatar['nbmessages']);
	}
	return ($msgsCache['i'.$id]=0);
}
if (!isset($avatarsCache))
	$avatarsCache = array();
?>