<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
include('utils-date.php');
$logTemplates = array(
    'award' => function($var) {
        global $language;
        return '<strong>{{table.mkawards(id='.$var.').name|global.ifEmpty("'. ($language ? 'Deleted award':'Titre supprimé') .'")}}</strong>';
    },
    'challenge' => function($var) {
        global $language;
        return '<a href="challengeTry.php?challenge='.$var.'">{{table.mkchallenges(id='.$var.').name|global.ifEmpty("'. ($language ? 'Untitled':'Sans titre') .'")}}</a>';
    },
    'member' => function($var) {
        global $language;
        return '<a href="profil.php?id='.$var.'">{{table.mkjoueurs(id='.$var.').nom|global.ifNull("<em>'. ($language ? 'Deleted account':'Compte supprimé') .'</em>")}}</a>';
    },
    'topic' => function($var) {
        global $language;
        return '<a href="topic.php?topic='.$var.'">{{table.mktopics(id='.$var.').titre|global.ifNull("<em>'. ($language ? 'Deleted topic':'Topic supprimé') .'</em>")}}</a>';
    },
    'news' => function($var) {
        global $language;
        return '<a href="news.php?id='.$var.'">{{table.mknews(id='.$var.').title|global.ifNull("<em>'. ($language ? 'Deleted news':'News supprimée') .'</em>")}}</a>';
    }
);
$logMapping = array(
    'AChallenge' => array(
        'render' => ($language ? 'accepted challenge ' : 'a accepté le défi ') . $logTemplates['challenge']('$1'),
        'role' => 'clvalidator'
    ),
    'CCircuit' => array(
        'render' => $language ? 'deleted complete track #$1' : 'a supprimé le circuit complet #$1',
        'role' => 'moderator'
    ),
    'Suppr' => array(
        'render' => function(&$group) {
            global $logTemplates, $language;
            if (isset($group[2]))
                return ($language ? 'deleted message #$2 in topic ' : 'a supprimé le message #$2 dans le topic ') . $logTemplates['topic']('$1');
            return 'deleted the topic #$1';
        },
        'role' => 'moderator'
    ),
    'Edit' => array(
        'render' => function(&$group) {
            global $logTemplates, $language;
            if (isset($group[2]))
                return ($language ? 'edited <a href="topic.php?topic=$1&amp;message=$2">message #$2</a> in topic ' : 'a modifié le <a href="topic.php?topic=$1&amp;message=$2">message #$2</a> dans le topic ') . $logTemplates['topic']('$1');
            return ($language ? 'edited topic ' : 'a modifié le topic ') . $logTemplates['topic']('$1');
        },
        'role' => 'moderator'
    ),
    'DChallenge' => array(
        'render' => ($language ? 'changed difficulty of challenge ' : 'a modifié la difficulté du défi ') . $logTemplates['challenge']('$1') .' {{table.mkchallenges(id=$1).difficulty|local.difficulty()}}',
        'locals' => array(
            'difficulty' => function($i) {
                global $language;
                if ($i === null) return '';
                require_once('challenge-consts.php');
                $difficulties = getChallengeDifficulties();
                return ($language ? 'to' : 'en') . ' <strong>' . $difficulties[$i] .'</strong>';
            }
        ),
        'role' => 'clvalidator'
    ),
    'RChallenge' => array(
        'render' => ($language ? 'rejected challenge ' : 'a refusé le défi ') . $logTemplates['challenge']('$1'),
        'role' => 'clvalidator'
    ),
    'Ban' => array(
        'render' => ($language ? 'banned member ' : 'a banni le membre ') . $logTemplates['member']('$1'),
        'role' => 'moderator'
    ),
    'Warn' => array(
        'render' => ($language ? 'warned member ' : 'a averti le membre ') . $logTemplates['member']('$1'),
        'role' => 'moderator'
    ),
    'Unban' => array(
        'render' => ($language ? 'unbanned member ' : 'a débanni le membre ') . $logTemplates['member']('$1'),
        'role' => 'moderator'
    ),
    'SComment' => array(
        'render' => $language ? 'deleted comment #$1 on a track' : 'a supprimé le commentaire #$1 sur un circuit',
        'role' => 'moderator'
    ),
    'pts' => array(
        'render' => function(&$group) {
            global $logTemplates, $language;
            $verb = $language ? 'gave' : 'donné';
            if ($group[1] < 0) {
                $verb = $language ? 'removed' : 'retiré';
                $group[1] = -$group[1];
            }
            return $language ? $verb.' $1 pts to '. $logTemplates['member']('$2') .' in online mode (VS)' : 'a '.$verb.' $1 pts à '. $logTemplates['member']('$2') .' dans le mode en ligne (VS)';
        },
        'role' => 'manager'
    ),
    'Bpts' => array(
        'render' => function(&$group) {
            global $logTemplates, $language;
            $verb = $language ? 'gave' : 'donné';
            if ($group[1] < 0) {
                $verb = $language ? 'removed' : 'retiré';
                $group[1] = -$group[1];
            }
            return $language ? $verb.' $1 pts to '. $logTemplates['member']('$2') .' in online mode (bataille)' : 'a '.$verb.' $1 pts à '. $logTemplates['member']('$2') .' dans le mode en ligne (battle)';
        },
        'role' => 'manager'
    ),
    'nick' => array(
        'render' => ($language ? 'changed <strong>$2</strong>\'s nick to ' : 'a modifié le pseudo de <strong>$2</strong> en ') . $logTemplates['member']('$1'),
        'role' => 'moderator'
    ),
    'SPerso' => array(
        'render' => function(&$matches) {
            global $language;
            $ids = explode(',', $matches[1]);
            $matches[1] = implode(', #', $ids);
            $matches[2] = count($ids);
            return $language ? 'deleted {{$2|global.plural("character%s")}} #$1' : 'a supprimé {{$2|global.plural("le%s perso%s")}} #$1';
        },
        'role' => 'moderator'
    ),
    'LTopic' => array(
        'render' => ($language ? 'locked topic ' : 'a locké le topic ') . $logTemplates['topic']('$1'),
        'role' => 'moderator'
    ),
    'ULTopic' => array(
        'render' => ($language ? 'unlocked topic ' : 'a unlocké le topic ') . $logTemplates['topic']('$1'),
        'role' => 'moderator'
    ),
    'Cup' => array(
        'render' => $language ? 'deleted cup #$1' : 'a supprimé la coupe #$1',
        'role' => 'moderator'
    ),
    'RNews' => array(
        'render' => ($language ? 'rejected news ' : 'a rejeté la news ') . $logTemplates['news']('$1'),
        'role' => 'publisher'
    ),
    'ANews' => array(
        'render' => ($language ? 'accepted news ' : 'a accepté la news ') . $logTemplates['news']('$1'),
        'role' => 'publisher'
    ),
    'EComment' => array(
        'render' => function(&$groups) {
            global $language;
            if ($comment = mysql_fetch_array(mysql_query('SELECT auteur,type,circuit FROM `mkcomments` WHERE id="'. mysql_real_escape_string($groups[1]) .'"'))) {
                $groups[3] = $comment['auteur'];
                $getCircuitData = get_circuit_data($comment['type'],$comment['circuit']);
                $groups[4] = $getCircuitData['name'];
                $groups[5] = $getCircuitData['link'];
                $groups[6] = $getCircuitData['label'];
            }
            $member = '<a href="profil.php?id={{$3}}">{{$3|global.join("mkjoueurs", "id", "nom")|global.ifNull("<em>'. ($language ? 'Deleted account':'Compte supprimé') .'</em>")}}</a>';
            $track = '<a href="$5">{{$4|global.ifNull("<em>'. ($language ? 'Deleted track' : 'Circuit supprimé') .'</em>")}}</a>';
            return $language ? 'updated '. $member .'\'s comment in $6 ' . $track : 'a modifié le commentaire de '. $member .' sur $6 '. $track;
        },
        'role' => 'moderator'
    ),
    'DRating' => array(
        'render' => function(&$groups) {
            global $language;
            $getCircuitData = get_circuit_data($groups[1],$groups[2]);
            $groups[4] = $getCircuitData['name'];
            $groups[5] = $getCircuitData['link'];
            $groups[6] = $getCircuitData['label'];
            $track = '<a href="$5">{{$4|global.ifNull("<em>'. ($language ? 'Deleted circuit' : 'Circuit supprimé') .'</em>")}}</a>';
            return $language ? 'deleted a rating in $6 '. $track : 'a supprimé une note sur $6 '. $track;
        },
        'role' => 'moderator'
    ),
    'SCircuit' => array(
        'render' => $language ? 'deleted quick circuit #$1' : 'a supprimé le circuit simplifié #$1',
        'role' => 'moderator'
    ),
    'SArene' => array(
        'render' => $language ? 'deleted quick arena #$1' : 'a supprimé l\'arène simplifié #$1',
        'role' => 'moderator'
    ),
    'CArene' => array(
        'render' => $language ? 'deleted complete arena #$1' : 'a supprimé l\'arène complet #$1',
        'role' => 'moderator'
    ),
    'SNews' => array(
        'render' => $language ? 'deleted news #$1' : 'a supprimé la news #$1',
        'role' => 'publisher'
    ),
    'ENews' => array(
        'render' => ($language ? 'updated news ' : 'a modifié la news ') . $logTemplates['news']('$1'),
        'role' => 'publisher'
    ),
    'CAwarded' => array(
        'render' => ($language ? 'awarded the title '. $logTemplates['award']('$2') .' to ' : 'a attribué le titre '. $logTemplates['award']('$2') .' à ') . $logTemplates['member']('$1'),
        'role' => 'organizer'
    ),
    'EAwarded' => array(
        'render' => $language ? 'updated message of award '. $logTemplates['award']('$2') .' for member '. $logTemplates['member']('$1') : 'a modifié le message du titre '. $logTemplates['award']('$2') .' pour le membre '. $logTemplates['member']('$1'),
        'role' => 'organizer'
    ),
    'SAwarded' => array(
        'render' => $language ? 'removed award '. $logTemplates['award']('$2')  .' for member '. $logTemplates['member']('$1') : 'a retiré le titre '. $logTemplates['award']('$2') .' pour le membre '. $logTemplates['member']('$1'),
        'role' => 'organizer'
    ),
    'SPicture' => array(
        'render' => ($language ? 'deleted avatar of ' : 'a supprimé l\'avatar de ') . $logTemplates['member']('$1'),
        'role' => 'moderator'
    ),
    'UAChallenge' => array(
        'render' => ($language ? 'reverted validation of challenge ' : 'a annulé la validation du défi ') . $logTemplates['challenge']('$1'),
        'role' => 'clvalidator'
    ),
    'URChallenge' => array(
        'render' => ($language ? 'reverted rejection of challenge ' : 'a annulé le refus du défi ') . $logTemplates['challenge']('$1'),
        'role' => 'clvalidator'
    ),
    'CChallenge' => array(
        'render' => ($language ? 'revalidated challenge ' : 'a revalidé le défi ') . $logTemplates['challenge']('$1'),
        'role' => 'clvalidator'
    ),
    'ENewscom' => array(
        'render' => $language ? 'updated comment #$1 on news <a href="news.php?id={{table.mknewscoms(id=$1).news}}">{{table.mknewscoms(id=$1).news|global.join("mknews","id","title")|global.ifNull("<em>Deleted news</em>")}}</a>' : 'a modifié le commentaire #$1 sur la news <a href="news.php?id={{table.mknewscoms(id=$1).news}}">{{table.mknewscoms(id=$1).news|global.join("mknews","id","title")|global.ifNull("<em>News supprimée</em>")}}</a>',
        'role' => 'moderator'
    ),
    'DNewscom' => array(
        'render' => $language ? 'deleted comment on news #$1' : 'a supprimé le commentaire de news #$1',
        'role' => 'moderator'
    ),
    'Mute' => array(
        'render' => ($language ? 'muted member ' : 'a muté le membre ') . $logTemplates['member']('$1') . ($language ? ' for {{$2|global.plural("%n minute%s")}}' : ' pendant {{$2|global.plural("%n minute%s")}}'),
        'role' => 'moderator'
    ),
    'Unmute' => array(
        'render' => ($language ? 'unmuted member ' : 'a unmuté le membre ') . $logTemplates['member']('$1'),
        'role' => 'moderator'
    ),
    'MCup' => array(
        'render' => $language ? 'deleted multicup #$1' : 'a supprimé la multicoupe #$1',
        'role' => 'moderator'
    ),
    'Flag' => array(
        'render' => $language ? 'updated country of ' . $logTemplates['member']('$1') .' to <strong>{{table.mkcountries(code=$2).name_en|global.ifNull($2)}}</strong>' : 'a modifié le pays de ' . $logTemplates['member']('$1') .' en <strong>{{table.mkcountries(code=$2).name_fr|global.ifNull($2)}}</strong>',
        'role' => 'moderator'
    ),
    'EChallenge' => array(
        'render' => ($language ? 'updated challenge ' : 'a modifié le défi ') . $logTemplates['challenge']('$1'),
        'role' => 'clvalidator'
    ),
    'CAward' => array(
        'render' => ($language ? 'created award ' : 'a créé le titre ') . $logTemplates['award']('$1'),
        'role' => 'organizer'
    ),
    'EAward' => array(
        'render' => ($language ? 'updated award ' : 'a modifié le titre ') . $logTemplates['award']('$1'),
        'role' => 'organizer'
    ),
    'SAward' => array(
        'render' => $language ? 'deleted award #$1' : 'a supprimé le titre #$1',
        'role' => 'organizer'
    ),
    'LNews' => array(
        'render' => ($language ? 'locked comments on news ' : 'a locké les commentaires sur la news ') . $logTemplates['news']('$1'),
        'role' => 'moderator'
    ),
    'ULNews' => array(
        'render' => ($language ? 'unlocked comments on news ' : 'a unlocké les commentaires sur la news ') . $logTemplates['news']('$1'),
        'role' => 'moderator'
    )
);
$logGlobals = array(
    'ifEmpty' => function($res, $fallback) {
        return empty($res) ? $fallback : $res;
    },
    'ifNull' => function($res, $fallback) {
        return ($res === null) ? $fallback : $res;
    },
    'plural' => function($nb, $text, $pluralText=null) {
        if ($pluralText === null)
            $pluralText = str_replace('%s', 's', $text);
        $text = str_replace('%s', '', $text);
        $res = ($nb >= 2) ? $pluralText : $text;
        $res = str_replace('%n', $nb, $res);
        return $res;
    },
    'join' => function($value, $table, $col, $field) {
        if ($value !== null)
            $value = mysql_real_escape_string($value);
        if ($getRow = mysql_fetch_array(mysql_query('SELECT '.$field.' FROM `'.$table.'` WHERE '. mysql_real_escape_string($col) .'="'. $value .'"'))) {
            $res = $getRow[$field];
            if ($res !== null)
                return htmlspecialchars($res);
        }
        return null;
    }
);
require_once('getRights.php');
$availableLogs = array();
if (isset($_GET['role'])) {
    $roleFilter = $_GET['role'];
    if (hasRight($roleFilter)) {
        foreach ($logMapping as $key => $log) {
            if ($log['role'] === $roleFilter)
                $availableLogs[] = $key;
        }
    }
}
else {
    foreach ($logMapping as $key => $log) {
        if (hasRight($log['role']))
            $availableLogs[] = $key;
    }
}
if (empty($availableLogs)) {
    echo 'Access denied';
    exit;
}
function format_log($log) {
    global $logMapping;
    $logArgs = explode(' ', $log);
    $logType = $logArgs[0];
    if (!isset($logMapping[$logType])) return $log;
    $pattern = get_log_pattern($logType, $logArgs);
    return format_log_pattern($pattern, $logArgs);
}
function get_log_pattern($logType, &$logArgs) {
    global $logMapping;
    $renderFn = $logMapping[$logType]['render'];
    switch (gettype($renderFn)) {
    case 'object':
        return $renderFn($logArgs);
    default:
        return $renderFn;
    }
}
function format_log_placeholder($logType) {
    $logArgs = array($logType, '0', '0', '0');
    $res = get_log_pattern($logType, $logArgs);
    $res = preg_replace_callback('#\{\{\$\d+\|global\.plural\("(.+?)"(?:,".+?")?\)\}\}#', function($matches) {
        global $logGlobals;
        $res = str_replace('%n', '•', $matches[1]);
        return $logGlobals['plural'](2, $res);
    }, $res);
    $res = preg_replace('#\{\{.+?\}\}#', '•', $res);
    $res = preg_replace('#\#?\$\d+#', '•', $res);
    $res = strip_tags($res);
    $res = str_replace('• •', '•', $res);
    return $res;
}
function preprocess_pattern($pattern, $logArgs) {
    $offset = 0;
    $res = '';
    while (true) {
        $bracesPos = strpos($pattern, '{{', $offset);
        if ($bracesPos === false)
            $patternPart = substr($pattern, $offset);
        else
            $patternPart = substr($pattern, $offset, $bracesPos-$offset);
        $patternPart = preg_replace_callback('#\$(\d)#', function($matches) use ($logArgs) {
            return evaluate_group($logArgs[$matches[1]]);
        }, $patternPart);
        $res .= $patternPart;
        if ($bracesPos === false) break;
        $offset = strpos($pattern, '}}', $bracesPos);
        if ($offset === false) {
            $res .= substr($pattern, $bracesPos);
            break;
        }
        $res .= substr($pattern, $bracesPos, $offset-$bracesPos);
    }
    return $res;
}
function format_log_pattern($pattern, $logArgs) {
    $pattern = preprocess_pattern($pattern, $logArgs);
    $pattern = preg_replace_callback('#\{\{(.+?)\}\}#', function($matches) use ($logArgs) {
        global $logMapping, $logGlobals;
        $logType = $logArgs[0];
        $expr = $matches[1];
        $exprParts = explode('|', $expr);
        $res = '';
        foreach ($exprParts as $exprPart) {
            $exprArgs = explode('.', $exprPart);
            $exprType = $exprArgs[0];
            switch ($exprType) {
            case 'table':
                if (preg_match('#^(\w+?)\((\w+?)=(.+)\)$#', $exprArgs[1], $query)) {
                    $table = $query[1];
                    $col = $query[2];
                    $value = evaluate_expr($query[3], $logArgs);
                    $field = $exprArgs[2];
                    $res = $logGlobals['join']($value, $table, $col, $field);
                }
                break;
            case 'global':
            case 'local':
                if (preg_match('#^(\w+?)\((.*?)\)$#', $exprArgs[1], $fnParts)) {
                    $fnName = $fnParts[1];
                    $fnArgs = explode(',', $fnParts[2]);
                    foreach ($fnArgs as &$fnArg)
                        $fnArg = evaluate_expr($fnArg, $logArgs);
                    unset($fnArg);
                    switch ($exprType) {
                    case 'global':
                        $fn = $logGlobals[$fnName];
                        break;
                    case 'local':
                        $fn = $logMapping[$logType]['locals'][$fnName];
                        break;
                    }
                    array_unshift($fnArgs, $res);
                    $res = call_user_func_array($fn, $fnArgs);
                }
                break;
            default:
                $res = evaluate_expr($exprType, $logArgs);
            }
        }
        return $res;
    }, $pattern);
    return $pattern;
}
function evaluate_expr($expr, $logArgs) {
    if (preg_match('#^\$(\d+)$#', $expr, $matches))
        return evaluate_group($logArgs[$matches[1]]);
    return json_decode($expr);
}
function evaluate_group(&$group) {
    if ($group === null) return $group;
    return htmlspecialchars($group);
}
function get_circuit_data($type, $id) {
    global $language;
    $res = array(
        'name' => null,
        'label' => null,
        'link' => null
    );
    if (empty($type))
        return $res;
    if ($getCircuit = mysql_fetch_array(mysql_query('SELECT *'. (($type=='mkcircuits') ? ',!type AS is_circuit':'') .' FROM `'. $type .'` WHERE id="'. $id .'"'))) {
        $res['name'] = $getCircuit['nom'];
        switch ($type) {
            case 'mkcircuits':
                $res['link'] = ($getCircuit['is_circuit'] ? 'circuit':'arena') .'.php?id='. $getCircuit['id'];
                if ($getCircuit['is_circuit'])
                    $res['label'] = $language ? "the circuit" : "le circuit";
                else
                    $res['label'] = $language ? "the arena" : "l'arène";
                break;
            case 'circuits':
                $res['link'] = 'map.php?i='. $getCircuit['ID'];
                $res['label'] = $language ? "the circuit" : "le circuit";
                break;
            case 'arenes':
                $res['link'] = 'battle.php?i='. $getCircuit['ID'];
                $res['label'] = $language ? "the arena" : "l'arène";
                break;
            case 'mkcups':
                $res['link'] = ($getCircuit['mode'] ? 'map.php':'circuit.php') .'?cid='. $getCircuit['id'];
                $res['label'] = $language ? "the cup" : "la coupe";
                break;
            case 'mkmcups':
                $res['link'] = ($getCircuit['mode'] ? 'map.php':'circuit.php') .'?mid='. $getCircuit['id'];
                $res['label'] = $language ? "the multicup" : "la multicoupe";
                break;
        }
    }
    return $res;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Admin logs' : 'Logs admin'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<?php
include('o_online.php');
?>
<style type="text/css">
main table {
    font-weight: normal;
    word-break: break-word;
}
main table a {
    font-weight: bold;
    color: #f80;
}
main table a:hover {
    color: #f90;
}

table a.profile {
	color: #820;
}
table a.profile:hover {
	color: #B50;
}

#log {
    width: 400px;
}
#admin-filter {
	line-height: 1.5em;
	text-align: center;
	margin: 0 auto;
}

#log-type {
    width: 250px;
}
</style>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
	<h1><?php echo $language ? 'Admin logs' : 'Logs admin'; ?></h1>
    <?php
    if (!isset($_GET['role'])) {
        echo '<p>';
        echo $language
        ? "This page shows the history of all actions made by MKPC staff members"
        : "Cette page affiche l'historique de toutes les actions effectuées par l'équipe admin MKPC";
        echo '</p>';
    }
    ?>
	<form method="get" action="admin-logs.php">
        <blockquote>
            <p id="admin-filter">
                <label for="log-type">
                    <strong><?php echo $language ? 'Log type':'Type de log'; ?></strong>
                </label> :
                <select name="type" id="log-type" onchange="this.form.submit()">
                    <option value=""><?php echo $language ? 'Select' : 'Sélectionner'; ?>...</option>
                    <?php
                    $selectedLog = isset($_GET['type']) ? $_GET['type'] : null;
                    foreach ($availableLogs as $availableLog) {
                        echo '<option value="'. $availableLog .'"'. ($selectedLog === $availableLog ? ' selected="selected"' : '') .'>'. format_log_placeholder($availableLog) .'</option>';
                    }
                    ?>
                </select>
                <?php
                if (isset($_GET['role']))
                    echo '<input type="hidden" name="role" value="'. htmlspecialchars($_GET['role']) .'" />';
                ?>
                <input type="submit" value="<?php echo $language ? 'Filter' : 'Filtrer'; ?>" class="action_button" />
            </p>
        </blockquote>
	</form>
    <table>
        <tr id="titres">
        <td>Date</td>
        <td id="log">Log</td>
        </tr>
    <?php
    $RES_PER_PAGE = 50;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $wheres = array();
    if (empty($_GET['type'])) {
        $availableLogWhere = array();
        foreach ($availableLogs as $availableLog)
            $availableLogWhere[] = "l.log LIKE '%$availableLog %'";
    }
    else {
        $logFilter = $_GET['type'];
        if (in_array($logFilter, $availableLogs))
            $availableLogWhere[] = "l.log LIKE '$logFilter %'";
        else
            $availableLogWhere[] = '0';
    }
    $wheres[] = '(' . implode(' OR ', $availableLogWhere) .')';
    if (isset($_GET['id']))
        $wheres[] = ' l.id="'. $_GET['id'] .'"';
    $where = implode(' AND ', $wheres);
    $getLogs = mysql_query('SELECT l.id,l.auteur,l.date,l.log,j.nom FROM mklogs l LEFT JOIN mkjoueurs j ON l.auteur=j.id WHERE '. $where .' ORDER BY l.id DESC LIMIT '. (($page-1)*$RES_PER_PAGE) .','.$RES_PER_PAGE);
    $logCount = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mklogs l WHERE '. $where));
    while ($log = mysql_fetch_array($getLogs)) {
        ?>
        <tr>
        <td><?php echo to_local_tz($log['date']); ?></td>
        <td><?php
            if ($log['nom'])
                echo '<a class="profile" href="profil.php?id='.$log['auteur'].'">'.$log['nom'].'</a>';
            elseif ($log['auteur'] === 0)
                echo '<em>MKPC</em>';
            else
                echo '<em>'. ($language ? 'Deleted account' : 'Compte supprimé') .'</em>';
            echo ' ';
            echo format_log($log['log']); ?></td>
        </tr>
        <?php
    }
    ?>
    <tr><td colspan="4" id="page"><strong>Page : </strong> 
    <?php
    $get = $_GET;
    function pageLink($page, $isCurrent) {
        global $get;
        $get['page'] = $page;
        echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?'. http_build_query($get) .'">'.$page.'</a>').'&nbsp; ';
    }
    $limite = ceil($logCount['nb']/$RES_PER_PAGE);
    require_once('utils-paging.php');
    $allPages = makePaging($page,$limite);
    foreach ($allPages as $i=>$block) {
        if ($i)
            echo '...&nbsp; ';
        foreach ($block as $p)
            pageLink($p, $p==$page);
    }
    ?>
    </td></tr>
    </table>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>