<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
include('utils-date.php');
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
$logTemplates = array(
    'award' => function($var) {
        return '<strong>{{table.mkawards(id='.$var.').name}}</strong>';
    },
    'challenge' => function($var) {
        return '<a href="challengeTry.php?challenge='.$var.'">{{table.mkchallenges(id='.$var.').name|global.ifEmpty("Sans titre")}}</a>';
    },
    'member' => function($var) {
        return '<a href="profil.php?id='.$var.'">{{table.mkjoueurs(id='.$var.').nom|global.ifNull("<em>Compte supprimé</em>")}}</a>';
    },
    'topic' => function($var) {
        return '<a href="topic.php?topic='.$var.'">{{table.mktopics(id='.$var.').titre|global.ifNull("<em>Topic supprimé</em>")}}</a>';
    },
    'news' => function($var) {
        return '<a href="news.php?id='.$var.'">{{table.mknews(id='.$var.').title|global.ifNull("<em>News supprimée</em>")}}</a>';
    }
);
$logMapping = array(
    'AChallenge' => array(
        'render' => 'a accepté le défi '. $logTemplates['challenge']('$1')
    ),
    'CCircuit' => array(
        'render' => 'a supprimé le circuit complet #$1'
    ),
    'Suppr' => array(
        'render' => function(&$group) {
            global $logTemplates;
            if (isset($group[2]))
                return 'a supprimé le message #$2 dans le topic '. $logTemplates['topic']('$1');
            return 'a supprimé le topic #$1';
        }
    ),
    'Edit' => array(
        'render' => function(&$group) {
            global $logTemplates;
            if (isset($group[2]))
                return 'a modifié le <a href="topic.php?topic=$1&amp;message=$2">message #$2</a> dans le topic '. $logTemplates['topic']('$1');
            return 'a modifié le topic '. $logTemplates['topic']('$1');
        }
    ),
    'DChallenge' => array(
        'render' => 'a modifié la difficulté du défi '. $logTemplates['challenge']('$1') .' {{table.mkchallenges(id=$1).difficulty|local.difficulty()}}',
        'locals' => array(
            'difficulty' => function($i) {
                if ($i === null) return '';
                require_once('challenge-consts.php');
                $difficulties = getChallengeDifficulties();
                return 'en <strong>' . $difficulties[$i] .'</strong>';
            }
        )
    ),
    'RChallenge' => array(
        'render' => 'a refusé le défi '. $logTemplates['challenge']('$1')
    ),
    'Ban' => array(
        'render' => 'a banni le membre '. $logTemplates['member']('$1')
    ),
    'Warn' => array(
        'render' => 'a averti le membre '. $logTemplates['member']('$1')
    ),
    'Unban' => array(
        'render' => 'a débanni le membre '. $logTemplates['member']('$1')
    ),
    'SComment' => array(
        'render' => 'a supprimé le commentaire #$1 sur un circuit'
    ),
    'pts' => array(
        'render' => function(&$group) {
            global $logTemplates;
            $verb = 'donné';
            if ($group[1] < 0) {
                $verb = 'retiré';
                $group[1] = -$group[1];
            }
            return 'a '.$verb.' $1 pts à '. $logTemplates['member']('$2') .' dans le mode en ligne (VS)';
        }
    ),
    'Bpts' => array(
        'render' => function(&$group) {
            global $logTemplates;
            $verb = 'donné';
            if ($group[1] < 0) {
                $verb = 'retiré';
                $group[1] = -$group[1];
            }
            return 'a '.$verb.' $1 pts à '. $logTemplates['member']('$2') .' dans le mode en ligne (bataille)';
        }
    ),
    'nick' => array(
        'render' => 'a modifié le pseudo de <strong>$2</strong> en '. $logTemplates['member']('$1')
    ),
    'SPerso' => array(
        'render' => function(&$matches) {
            $ids = explode(',', $matches[1]);
            $matches[1] = implode(', #', $ids);
            $matches[2] = count($ids);
            return 'a supprimé {{$2|global.plural("le%s perso%s")}} #$1';
        }
    ),
    'LTopic' => array(
        'render' => 'a locké le topic '. $logTemplates['topic']('$1')
    ),
    'ULTopic' => array(
        'render' => 'a unlocké le topic '. $logTemplates['topic']('$1')
    ),
    'Cup' => array(
        'render' => 'a supprimé la coupe #$1'
    ),
    'RNews' => array(
        'render' => 'a rejeté la news '. $logTemplates['news']('$1')
    ),
    'ANews' => array(
        'render' => 'a accepté la news '. $logTemplates['news']('$1')
    ),
    'EComment' => array(
        'render' => function(&$groups) {
            if ($comment = mysql_fetch_array(mysql_query('SELECT auteur,type,circuit FROM `mkcomments` WHERE id="'. mysql_real_escape_string($groups[1]) .'"'))) {
                $groups[3] = $comment['auteur'];
                $getCircuitData = get_circuit_data($comment['type'],$comment['circuit']);
                $groups[4] = $getCircuitData['name'];
                $groups[5] = $getCircuitData['link'];
                $groups[6] = $getCircuitData['label'];
            }
            return 'a modifié le commentaire de <a href="profil.php?id={{$3}}">{{$3|global.join("mkjoueurs", "id", "nom")|global.ifNull("<em>Compte supprimé</em>")}}</a> sur $6 <a href="$5">{{$4|global.ifNull("<em>Circuit supprimé</em>")}}</a>';
        }
    ),
    'DRating' => array(
        'render' => function(&$groups) {
            $getCircuitData = get_circuit_data($groups[1],$groups[2]);
            $groups[4] = $getCircuitData['name'];
            $groups[5] = $getCircuitData['link'];
            $groups[6] = $getCircuitData['label'];
            return 'a supprimé une note sur $6 <a href="$5">{{$4|global.ifNull("<em>Circuit supprimé</em>")}}</a>';
        }
    ),
    'SCircuit' => array(
        'render' => 'a supprimé le circuit simplifié #$1'
    ),
    'SArene' => array(
        'render' => 'a supprimé l\'arène simplifié #$1'
    ),
    'CArene' => array(
        'render' => 'a supprimé l\'arène complet #$1'
    ),
    'SNews' => array(
        'render' => 'a supprimé la news #$1'
    ),
    'ENews' => array(
        'render' => 'a modifié la news '. $logTemplates['news']('$1')
    ),
    'CAwarded' => array(
        'render' => 'a attribué le titre <strong>{{table.mkawards(id=$2).name|global.ifNull("<em>Titre supprimé</em>")}}</strong> à '. $logTemplates['member']('$1')
    ),
    'EAwarded' => array(
        'render' => 'a modifié le message du titre <strong>{{table.mkawards(id=$2).name|global.ifNull("<em>Titre supprimé</em>")}}</strong> pour le membre '. $logTemplates['member']('$1')
    ),
    'SAwarded' => array(
        'render' => 'a retiré le titre <strong>{{table.mkawards(id=$2).name|global.ifNull("<em>Titre supprimé</em>")}}</strong> pour le membre '. $logTemplates['member']('$1')
    ),
    'SPicture' => array(
        'render' => 'a supprimé l\'avatar de '. $logTemplates['member']('$1')
    ),
    'UAChallenge' => array(
        'render' => 'a annulé la validation du défi '. $logTemplates['challenge']('$1')
    ),
    'URChallenge' => array(
        'render' => 'a annulé le refus du défi '. $logTemplates['challenge']('$1')
    ),
    'CChallenge' => array(
        'render' => 'a revalidé le défi '. $logTemplates['challenge']('$1')
    ),
    'ENewscom' => array(
        'render' => 'a modifié le commentaire #$1 sur la news <a href="news.php?id={{table.mknewscoms(id=$1).news}}">{{table.mknewscoms(id=$1).news|global.join("mknews","id","title")|global.ifNull("<em>News supprimée</em>")}}</a>'
    ),
    'DNewscom' => array(
        'render' => 'a supprimé le commentaire de news #$1'
    ),
    'Mute' => array(
        'render' => 'a muté le membre '. $logTemplates['member']('$1') .' pendant $2 {{$2|global.plural("minute%s")}}'
    ),
    'Unmute' => array(
        'render' => 'a unmuté le membre '. $logTemplates['member']('$1')
    ),
    'MCup' => array(
        'render' => 'a supprimé la multicoupe #$1'
    ),
    'Flag' => array(
        'render' => 'a modifié le pays de '. $logTemplates['member']('$1') .' en <strong>{{table.mkcountries(code=$2).name_fr|global.ifNull($2)}}</strong>'
    ),
    'EChallenge' => array(
        'render' => 'a modifié le défi '. $logTemplates['challenge']('$1')
    ),
    'Chat' => array(
        'render' => 'a modifié le défi '. $logTemplates['challenge']('$1')
    ),
    'CAward' => array(
        'render' => 'a créé le titre '. $logTemplates['award']('$1')
    ),
    'EAward' => array(
        'render' => 'a modifié le titre '. $logTemplates['award']('$1')
    ),
    'SAward' => array(
        'render' => 'a supprimé le titre #$1'
    ),
    'LNews' => array(
        'render' => 'a locké les commentaires sur la news '. $logTemplates['news']('$1')
    ),
    'ULNews' => array(
        'render' => 'a unlocké les commentaires sur la news '. $logTemplates['news']('$1')
    )
);
$logGlobals = array(
    'ifEmpty' => function($res, $fallback) {
        return empty($res) ? $fallback : $res;
    },
    'ifNull' => function($res, $fallback) {
        return ($res === null) ? $fallback : $res;
    },
    'plural' => function($res, $text, $pluralText=null) {
        if ($pluralText === null)
            $pluralText = str_replace('%s', 's', $text);
        $text = str_replace('%s', '', $text);
        return ($res >= 2) ? $pluralText : $text;
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
function format_log($log) {
    global $logMapping;
    $logArgs = explode(' ', $log);
    $logType = $logArgs[0];
    if (!isset($logMapping[$logType])) return $log;
    $renderFn = $logMapping[$logType]['render'];
    switch (gettype($renderFn)) {
    case 'object':
        $pattern = $renderFn($logArgs);
        break;
    default:
        $pattern = $renderFn;
        break;
    }
    return format_log_pattern($pattern, $logArgs);
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
    $res = array(
        'name' => null,
        'label' => null,
        'link' => null
    );
    if ($getCircuit = mysql_fetch_array(mysql_query('SELECT *'. (($type=='mkcircuits') ? ',!type AS is_circuit':'') .' FROM `'. $type .'` WHERE id="'. $id .'"'))) {
        $res['name'] = $getCircuit['nom'];
        switch ($type) {
            case 'mkcircuits':
                $res['link'] = ($getCircuit['is_circuit'] ? 'circuit':'arena') .'.php?id='. $getCircuit['id'];
                $res['label'] = $getCircuit['is_circuit'] ? "le circuit" : "l'arène";
                break;
            case 'circuits':
                $res['link'] = 'map.php?i='. $getCircuit['ID'];
                $res['label'] = "le circuit";
                break;
            case 'arenes':
                $res['link'] = 'battle.php?i='. $getCircuit['ID'];
                $res['label'] = "l'arène";
                break;
            case 'mkcups':
                $res['link'] = ($getCircuit['mode'] ? 'map.php':'circuit.php') .'?cid='. $getCircuit['id'];
                $res['label'] = "la coupe";
                break;
            case 'mkmcups':
                $res['link'] = ($getCircuit['mode'] ? 'map.php':'circuit.php') .'?mid='. $getCircuit['id'];
                $res['label'] = "la multicoupe";
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
    <p><?php echo $language
        ? "This page shows the history of all actions made by MKPC staff members"
        : "Cette page affiche l'historique de toutes les actions effectuées par l'équipe admin MKPC"
    ?></p>
    <table>
        <tr id="titres">
        <td>Date</td>
        <td id="log">Log</td>
        </tr>
    <?php
    $RES_PER_PAGE = 50;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $getLogs = mysql_query('SELECT l.id,l.auteur,l.date,l.log,j.nom FROM mklogs l LEFT JOIN mkjoueurs j ON l.auteur=j.id ORDER BY l.id DESC LIMIT '. (($page-1)*$RES_PER_PAGE) .','.$RES_PER_PAGE);
    $logCount = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mklogs'));
    while ($log = mysql_fetch_array($getLogs)) {
        ?>
        <tr>
        <td><?php echo to_local_tz($log['date']); ?></td>
        <td><?php
            if ($log['nom'])
                echo '<a class="profile" href="profil.php?id='.$log['auteur'].'">'.$log['nom'].'</a>';
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
    function pageLink($page, $isCurrent) {
        echo ($isCurrent ? '<span>'.$page.'</span>' : '<a href="?page='.$page.'">'.$page.'</a>').'&nbsp; ';
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