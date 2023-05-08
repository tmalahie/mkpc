<?php
define('AVATAR_S', 100);
define('AVATAR_M', 200);
define('AVATAR_L', 400);
define('AVATAR_MINW', 200);
define('AVATAR_MINH', 200);
define('AVATAR_DIR', 'images/avatars/');
define('AVATAR_REL_DIR', '../../images/avatars/');
$AVATAR_COLORS = array(
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
);
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
$LEAGUES_SCORES = array(4000,5000,6000,8000,10000,15000,20000,40000,100000);
function get_league_name($pts) {
	global $language;
	static $league_names;
	if (!isset($league_names)) {
		$league_names = array(
			$language ? 'Unlicensed':'Sans permis',
			$language ? 'Budding pilot':'Pilote en herbe',
			$language ? 'Novice':'Novice',
			$language ? 'Racer':'Coureur',
			$language ? 'Expert':'Expert',
			$language ? 'Champion':'Champion',
			$language ? 'Master':'Maître',
			$language ? 'Legend':'Légende',
			$language ? 'Titan':'Titan',
			'<span style="color:#400080">S</span>'.
			'<span style="color:#993399">u</span>'.
			'<span style="color:#3366FF">p</span>'.
			'<span style="color:#0E9D4E">e</span>'.
			'<span style="color:#55C43D">r</span>'.
			'<span style="color:#CCEE00;color:rgba(128,128,0,0.5)">s</span>'.
			'<span style="color:#FF8800">t</span>'.
			'<span style="color:#E53A35">a</span>'.
			'<span style="color:#A24E24">r</span>'
		);
	}
	return $league_names[get_league_rank($pts)];
}
function get_league_color($pts) {
	static $league_colors;
	if (!isset($league_colors)) {
		$league_colors = array(
			'#000000',
			'#A24E24',
			'#E53A35',
			'#993399',
			'#400080',
			'#3366FF',
			'#55C43D',
			'#0E9D4E',
			'#FF6600',
			'#300060'
		);
	}
	return $league_colors[get_league_rank($pts)];
}
function get_league_rank($pts) {
	global $LEAGUES_SCORES;
	$i = 0;
	while (isset($LEAGUES_SCORES[$i]) && ($pts >= $LEAGUES_SCORES[$i]))
		$i++;
	return $i;
}
$FORUM_RANKS = array(10,50,100,150,200,250,300,350,400,500,1000,2500);
function get_forum_rank($msgs) {
	global $FORUM_RANKS;
	$i = 0;
	while (isset($FORUM_RANKS[$i]) && ($msgs >= $FORUM_RANKS[$i]))
		$i++;
	return $i;
}
function get_forum_rkname($msgs) {
	global $language;
	static $rank_names;
	if (!isset($rank_names)) {
		$rank_names = array(
			'Goomba',
			'Koopa',
			'Boo',
			'Buzzy Beetle',
			'Bowser',
			'Toad',
			$language ? 'Toadsworth':'Papy champi',
			'Peach',
			'Luigi',
			$language ? 'Metal Luigi':'Luigi d\'argent',
			'Mario',
			$language ? 'Golden Mario':'Mario doré',
			$language ? 'King Mario':'Roi Mario'
		);
	}
	return $rank_names[get_forum_rank($msgs)];
}
function get_forum_rkimg($msgs) {
	global $language;
	static $rank_names;
	if (!isset($rank_names)) {
		$rank_names = array(
			'goomba',
			'koopa',
			'boo',
			'buzzybeetle',
			'bowser',
			'toad',
			'toadsworth',
			'peach',
			'luigi',
			'luigiargent',
			'mario',
			'marioor',
			'roimario'
		);
	}
	return $rank_names[get_forum_rank($msgs)];
}
function print_nb_msgs($id) {
	$nb = get_nb_msgs($id);
	$rank = get_forum_rkname($nb);
	$img = get_forum_rkimg($nb);
	echo '<div class="mNbmsgs"><img src="images/messages.png" alt="Messages" class="mNbmsgsIc" /> '.$nb.' - <span><img src="images/ranks/'. $img .'.gif" alt="'. $rank .'" class="mNbmsgsRk" /></span> '. $rank .'</div>';
}
function get_nb_msgs($id) {
	global $msgsCache;
	if (isset($msgsCache['i'.$id]))
		return $msgsCache['i'.$id];
	if ($getAvatar = mysql_fetch_array(mysql_query('SELECT nbmessages FROM `mkprofiles` WHERE id="'. $id .'"'))) {
		if ($getAvatar['nbmessages'])
			return ($avatarsCache['i'.$id]=$getAvatar['nbmessages']);
	}
	return ($msgsCache['i'.$id]=0);
}
if (!isset($avatarsCache))
	$avatarsCache = array();
?>