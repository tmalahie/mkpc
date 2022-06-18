<?php
include('../includes/initdb.php');
include('../includes/api.php');
require_once('../includes/auth.php');
$id = getUserId();
$activeCourses = array();
$activePlayers = array(
  'vs' => array(),
  'battle' => array()
);
if ($id) {
  $time = time();
  $limCoTime = floor(($time-35)*1000/67);
  require_once('../../../public_links.php');
  $getPlayingUsers = mysql_query('(
    SELECT j.id,j.nom,j.course,j.pts_vs,j.pts_battle,
    0 AS connecte,m.mode,m.cup,m.time,m.link,0 AS state
    FROM mariokart m INNER JOIN mkjoueurs j ON m.id=j.course
    WHERE map=-1 AND time>='.($time-1).' AND m.link IN ('.$publicLinksString.') AND j.id!='.$id.'
  ) UNION (
    SELECT j.id,j.nom,j.course,j.pts_vs,j.pts_battle,
    0 AS connecte,m.mode,m.cup,m.time,m.link,1 AS state
    FROM mariokart m INNER JOIN mkjoueurs j ON m.id=j.course
    WHERE time>='.(($time-1)*1000).' AND m.link IN ('.$publicLinksString.') AND j.id!='.$id.'
  ) UNION (
    SELECT j.id,j.nom,j.course,j.pts_vs,j.pts_battle,p.connecte,m.mode,m.cup,m.time,m.link,2 AS state
    FROM mkjoueurs j INNER JOIN mariokart m ON j.course=m.id
    INNER JOIN mkplayers p ON j.id=p.id
    WHERE p.connecte>='.$limCoTime.' AND m.link IN ('.$publicLinksString.') AND j.id!='.$id.'
  )');
  $limTimes = array(
    0 => $time+25,
    2 => floor(($time-5)*1000/67)
  );
  while ($playingUser = mysql_fetch_array($getPlayingUsers)) {
    $playingUser['game'] = ($playingUser['cup'] ? ($playingUser['mode']%8 >= 4) : $playingUser['mode']) ? 'battle' : 'vs';
    $playingUser['pk'] = $playingUser['link'].':'.$playingUser['mode'].':'.$playingUser['cup'];
    $playingUser['pts'] = $playingUser['pts_'.($playingUser['game'])];
    $course = $playingUser['course'];
    if (!isset($activeCourses[$course])) {
      $activeCourses[$course] = array(
        'active' => false,
        'players' => array()
      );
    }
    $activeCourses[$course]['players'][$playingUser['id']] = $playingUser;
    if (!$activeCourses[$course]['active']) {
      switch ($playingUser['state']) {
      case 0:
        $isActiveCourse = (count($activeCourses[$course]['players'])>=2) || ($playingUser['time']>=$limTimes[0]);
        break;
      case 1:
        $isActiveCourse = true;
        break;
      case 2:
        $isActiveCourse = ($playingUser['connecte']>=$limTimes[2]);
        break;
      }
      if ($isActiveCourse)
        $activeCourses[$course]['active'] = true;
    }
  }
  foreach ($activeCourses as &$activeCourse) {
    if ($activeCourse['active']) {
      foreach ($activeCourse['players'] as &$activePlayer) {
        $game = $activePlayer['game'];
        $playerId = $activePlayer['id'];
        $activePlayers[$game][$playerId] = $activePlayer;
      }
      unset($activePlayer);
    }
  }
}

function get_creation_link(&$params) {
  $url = 'online.php';
  $urlParams = array();
  if ($params['cup']) {
    $isMCup = ($params['mode']==8);
    $isSingle = (($params['mode']%4)>=2);
    $complete = (($params['mode']%2)>=1);
    $urlParams[] = ($isMCup?'mid':($isSingle?($complete?'i':'id'):($complete?"cid":"sid")))."=".$params['cup'];
  }
  if ($params['game'] === 'battle')
    $urlParams[] = 'battle';
  if ($params['link'])
    $urlParams[] = 'key='.$params['link'];
  if (!empty($urlParams))
    $url .= '?'.implode('&',$urlParams);
  return $url;
}
function get_creation_payload(&$params) {
  if (empty($params['cup'])) return null;
  $isMCup = ($params['mode']==8);
  $isBattle = ($params['game'] === 'battle');
  $isSingle = (($params['mode']%4)>=2);
  $complete = (($params['mode']%2)>=1);
  if ($isBattle)
    $table = $complete ? 'arenes':'mkcircuits';
  elseif ($isMCup)
    $table = 'mkmcups';
  elseif ($isSingle)
    $table = $complete ? 'circuits':'mkcircuits';
  else
    $table = 'mkcups';
  $getNom = mysql_fetch_array(mysql_query('SELECT nom FROM `'.$table.'` WHERE id='.$params['cup']));
  if (!$getNom) return null;
  return array(
    'type' => $table,
    'id' => $params['cup'],
    'name' => $getNom['nom']
  );
}
function get_rules_payload(&$params) {
  global $publicLinksData;
  $link = $params['link'];
  if (isset($publicLinksData[$link]))
    return $publicLinksData[$link];
}
function get_players_payload($players) {
  $res = array();
  foreach ($players as $player) {
    $res[] = array(
      'id' => $player['id'],
      'name' => $player['nom'],
      'pts' => $player['pts']
    );
  }
  return $res;
}

function game_pk_sort($k1,$k2) {
  $p1 = explode(':',$k1);
  $p2 = explode(':',$k2);
  for ($i=0;$i<3;$i++) {
    if ($p1[$i] < $p2[$i])
      return -1;
    elseif ($p2[$i] < $p1[$i])
      return 1;
  }
  return 0;
}
$activePlayersByLink = array();
foreach ($activePlayers as $game=>$players) {
  $playersWithLink = array();
  foreach ($players as $player)
    $playersWithLink[$player['pk']][] = $player;
  uksort($playersWithLink, 'game_pk_sort');
  $activePlayersByLink[$game] = $playersWithLink;
}

$res = array();
foreach ($activePlayersByLink as $game=>$linkWithPlayers) {
  $res[$game] = array();
  foreach ($linkWithPlayers as $ph => $players) {
    $firstPlayer = $players[0];
    $res[$game][] = array(
      'href' => get_creation_link($firstPlayer),
      'creation' => get_creation_payload($firstPlayer),
      'rules' => get_rules_payload($firstPlayer),
      'players' => get_players_payload($players)
    );
  }
}

renderResponse($res);