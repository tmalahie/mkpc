<?php
require_once(__DIR__ . '/utils-date.php');
require_once(__DIR__ . '/getRights.php');
require_once(__DIR__ . '/circuitEscape.php');
require_once(__DIR__ . '/utils-challenges.php');

function home_uc_strlen($str) {
    if ($str === null) return 0;
    return strlen(preg_replace("#(%u[0-9a-fA-F]{4})+#", ".", $str));
}

function home_uc_substr($str, $l) {
    preg_match_all('#(%u[0-9a-fA-F]{4})+#', $str, $positions, PREG_OFFSET_CAPTURE);
    $positions = $positions[0];
    $res = mb_substr(preg_replace("#(%u[0-9a-fA-F]{4})+#", ".", $str), 0, $l);
    foreach ($positions as $position) {
        if ($position[1] >= strlen($res))
            return $res;
        $res = mb_substr($res, 0, $position[1]) . $position[0] . mb_substr($res, $position[1] + 1);
    }
    return $res;
}

function home_controlLength($str, $maxLength) {
    if ($str === null) return '';
    $pts = '...';
    if (home_uc_strlen($str) > $maxLength)
        return home_uc_substr($str, $maxLength - strlen($pts)) . $pts;
    return $str;
}

function home_controlLengthUtf8($str, $len) {
    return htmlEscapeCircuitNames(home_controlLength($str, $len));
}

function getLatestTopics($limit = 10, $offset = 0, $userId = null) {
    global $language;
    $sql = 'SELECT t.id, t.titre, t.nbmsgs, t.category, t.dernier FROM `mktopics` t ' 
         . (hasRight('manager') ? '' : ' WHERE !t.private') 
         . ' ORDER BY t.dernier DESC LIMIT ' . intval($offset) . ',' . intval($limit);
    
    if (!$userId && $language) {
        $sql = 'SELECT * FROM (' . $sql . ') t ORDER BY (category=4) DESC, dernier DESC';
    }
    
    $getTopics = mysql_query($sql);
    $topics = array();
    $topicIds = array();
    
    while ($topic = mysql_fetch_array($getTopics)) {
        $topics[] = $topic;
        $topicIds[] = $topic['id'];
    }
    
    $lastMsgByTopic = array();
    $topicIdsString = implode(',', $topicIds);
    if ($topicIdsString) {
        $getLastMessages = mysql_query('SELECT m.topic, j.nom FROM (SELECT topic, MAX(id) AS maxid FROM mkmessages WHERE topic IN (' . $topicIdsString . ') GROUP BY topic) mm LEFT JOIN mkmessages m ON m.topic=mm.topic AND m.id=mm.maxid LEFT JOIN mkjoueurs j ON m.auteur=j.id');
        while ($message = mysql_fetch_array($getLastMessages)) {
            $lastMsgByTopic[$message['topic']] = $message;
        }
    }
    
    foreach ($topics as &$topic) {
        $topic['lastMessage'] = isset($lastMsgByTopic[$topic['id']]) ? $lastMsgByTopic[$topic['id']] : null;
    }
    
    return $topics;
}

function renderTopicItem($topic) {
    $nbMsgs = $topic['nbmsgs'];
    $message = $topic['lastMessage'];
    ob_start();
    ?>
    <a href="topic.php?topic=<?= $topic['id'] ?>" title="<?= htmlspecialchars($topic['titre']) ?>" data-id="topic-<?= $topic['id'] ?>">
        <h2><?php echo htmlspecialchars(home_controlLength($topic['titre'], 40)); ?></h2>
        <h3>
            <?php
            if ($message && $message['nom']) {
                printf(F_("Latest message by <strong>{message}</strong>", message: $message['nom']));
            } else {
                printf(_("Latest message"));
            }
            echo ' ';
            echo pretty_dates_short($topic['dernier'], array('lower' => true));
            ?>
        </h3>
        <div class="creation_comments" title="<?= FN_("{count} message", "{count} messages", count: $nbMsgs) ?>">
            <img src="images/comments.png" alt="Messages" /> <?= $nbMsgs; ?>
        </div>
    </a>
    <?php
    return ob_get_clean();
}

function renderTopicItems($topics) {
    $html = '';
    foreach ($topics as $topic) {
        $html .= renderTopicItem($topic);
    }
    return $html;
}

function getLatestNews($limit = 8, $offset = 0, $userId = null) {
    global $language;
    date_default_timezone_set('Europe/Paris');
    
    $getNews = mysql_query('SELECT n.id, n.title, n.nbcomments,
        name' . $language . ' AS name, author,
        category, c.name' . $language . ' AS catname,
        n.publication_date
        FROM `mknews` n
        INNER JOIN `mkcats` c ON n.category=c.id
        WHERE status="accepted"
        ORDER BY n.publication_date DESC
        LIMIT ' . intval($offset) . ',' . intval($limit));
    
    $lastNewsDate = time();
    if ($userId) {
        $lastNewsDate -= 7 * 86400;
        if ($lastNewsRead = mysql_fetch_array(mysql_query('SELECT date FROM `mknewsread` WHERE user=' . $userId))) {
            $lastNewsDate = max($lastNewsDate, strtotime($lastNewsRead['date']));
        }
    }
    
    $newsList = array();
    while ($news = mysql_fetch_array($getNews)) {
        $name = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id=' . $news['author']));
        $news['authorName'] = $name ? $name['nom'] : null;
        $news['isNew'] = (strtotime($news['publication_date']) > $lastNewsDate);
        $newsList[] = $news;
    }
    
    date_default_timezone_set('UTC');
    return $newsList;
}

function renderNewsItem($news) {
    $nbMsgs = $news['nbcomments'];
    $isNew = $news['isNew'];
    ob_start();
    ?>
    <a href="news.php?id=<?php echo $news['id']; ?>" title="<?php echo htmlspecialchars($news['title']); ?>" data-id="news-<?php echo $news['id']; ?>"<?php if ($isNew) echo ' class="news_new"'; ?>>
        <h2><?php echo htmlspecialchars(home_controlLength($news['title'], 40)); ?></h2>
        <h3>
            <?php
            if ($news['authorName']) {
                printf(P_("Categories", "In <strong>%s</strong> by <strong>%s</strong>"), $news['catname'], $news['authorName']);
            } else {
                printf(P_("Categories", "In <strong>%s</strong>"), $news['catname']);
            }
            ?>
            <?= pretty_dates_short($news['publication_date'], array('lower' => true)); ?>
        </h3>
        <div class="creation_comments" title="<?= FN_("{count} comment", "{count} comments", count: $nbMsgs) ?>">
            <img src="images/comments.png" alt="Messages" /> <?php echo $nbMsgs; ?>
        </div>
    </a>
    <?php
    return ob_get_clean();
}

function renderNewsItems($newsList) {
    $html = '';
    foreach ($newsList as $news) {
        $html .= renderNewsItem($news);
    }
    return $html;
}

function getLatestCreations($limit = 14, $offset = 0) {
    require_once(__DIR__ . '/utils-circuits.php');
    
    $nbByType = array(1, 1, 2, 2, 3, 3, 2, 2, 1, 1, 1, 1);
    $totalNeeded = $offset + $limit;
    
    $page = ceil($totalNeeded / 20);
    if ($page < 1) $page = 1;
    
    $tracksList = listCreations($page, $nbByType, null, $aCircuits);
    $tracksList = home_sortCreationLines($tracksList);
    
    return array_slice($tracksList, 0, $limit);
}

function home_sortCreationLines($lines) {
    $logb = log(1.7);
    foreach ($lines as &$line) {
        $publishedSince = time() - strtotime($line['publication_date']);
        $publishedSince = max($publishedSince, 0);
        $recency = 8 - log($publishedSince / 2000) / $logb;
        $recency = min(max($recency, 3), 8);
        $note = $line['note'] - 1;
        $nbnotes = max($line['nbnotes'], 1);
        if ($note == -1) {
            if ($recency == 8)
                $note = $recency;
            else
                $note = 2;
        } elseif ($recency > $note) {
            if ($note >= 2.6)
                $note = $recency;
            elseif ($note <= 1.4)
                $nbnotes = max($nbnotes, 2);
        }
        $line['score'] = ($recency + $note * $nbnotes) / (1 + $nbnotes);
    }
    usort($lines, function($line1, $line2) {
        $score1 = $line1['score'];
        $score2 = $line2['score'];
        if ($score1 < $score2) return 1;
        if ($score2 < $score1) return -1;
        $time1 = strtotime($line1['publication_date']);
        $time2 = strtotime($line2['publication_date']);
        if ($time1 < $time2) return 1;
        if ($time1 < $time2) return -1;
        return 0;
    });
    return $lines;
}

function home_getNom($circuit) {
    $maxL = 25;
    $res = ($circuit['nom'] ? home_controlLengthUtf8($circuit['nom'], $maxL) : (_('Untitled')));
    if (isset($circuit['prefix']) && (home_uc_strlen($circuit['nom']) + mb_strlen($circuit['prefix']) <= $maxL))
        $res = '<small>' . $circuit['prefix'] . ' </small>' . $res;
    return $res;
}

function home_getAuteur($circuit) {
    if ($circuit['auteur']) {
        return F_("By <strong>{author}</strong>", author: home_controlLengthUtf8($circuit['auteur'], 15));
    }
    return '';
}

function renderCreationItem($circuit) {
    global $language;
    $isCup = (strpos($circuit['cicon'], ',') !== false);
    $note = $circuit['note'];
    $nbNotes = $circuit['nbnotes'];
    $noteTitle = $nbNotes ? (round($note * 100) / 100) . '/5 ' . ($language ? 'on' : 'sur') . ' ' . $nbNotes . ' vote' . ($nbNotes > 1 ? 's' : '') : ($language ? 'Unrated' : 'Non noté');
    $circuitTime = pretty_dates_short($circuit['publication_date'], array('lower' => true, 'shorter' => true));
    $circuitFullDate = pretty_dates($circuit['publication_date'], array('lower' => true));
    
    ob_start();
    ?>
    <tr class="creation_line" data-id="creation-<?php echo $circuit['category'] . '-' . $circuit['id']; ?>">
        <td class="creation_icon <?php echo ($isCup ? 'creation_cup' : 'single_creation'); ?>"<?php
            if (isset($circuit['icon'])) {
                $allMapSrcs = $circuit['icon'];
                foreach ($allMapSrcs as $i => $iMapSrc)
                    $allMapSrcs[$i] = "url('images/creation_icons/$iMapSrc')";
                echo ' style="background-image:' . implode(',', $allMapSrcs) . '"';
            } else
                echo ' data-cicon="' . $circuit['cicon'] . '"';
        ?> title="<?php echo $language ? 'Preview' : 'Aperçu'; ?>" onclick="apercu(<?php echo htmlspecialchars(json_encode($circuit['srcs'])); ?>)">
        </td>
        <td class="creation_description">
            <a href="<?php echo $circuit['href']; ?>" title="<?php echo htmlEscapeCircuitNames($circuit['nom']); ?>">
                <h2><?php echo home_getNom($circuit); ?></h2>
                <table title="<?php echo $noteTitle; ?>">
                    <tr>
                        <?php
                        $noteDisplay = $note;
                        for ($i = 1; $i <= $noteDisplay; $i++)
                            echo '<td class="star1"></td>';
                        $rest = $noteDisplay - floor($noteDisplay);
                        if ($rest) {
                            $w1 = 3 + round(9 * $rest);
                            echo '<td class="startStar" style="width: ' . $w1 . 'px;"></td>';
                            echo '<td class="endStar" style="width: ' . (15 - $w1) . 'px;"></td>';
                            $noteDisplay++;
                        }
                        for ($i = $noteDisplay; $i < 5; $i++)
                            echo '<td class="star0"></td>';
                        ?>
                        <td><h3><?php echo home_getAuteur($circuit); ?></h3></td>
                    </tr>
                </table>
                <?php if ($circuit['nbcomments']) { ?>
                    <div class="creation_coms" title="<?php echo $circuit['nbcomments'] . ' ' . ($language ? 'comment' : 'commentaire') . (($circuit['nbcomments'] > 1) ? 's' : ''); ?>">
                        <img src="images/comments.png" alt="Commentaires" /><?php echo $circuit['nbcomments']; ?>
                    </div>
                <?php } ?>
                <div class="creation_date" title="<?php echo ($language ? 'Published' : 'Publié') . ' ' . $circuitFullDate; ?>">
                    <img src="images/records.png" alt="Date" /><?php echo $circuitTime; ?>
                </div>
            </a>
        </td>
    </tr>
    <?php
    return ob_get_clean();
}

function renderCreationItems($tracksList) {
    $html = '';
    foreach ($tracksList as $circuit) {
        $html .= renderCreationItem($circuit);
    }
    return $html;
}

function getLatestChallenges($limit = 15, $offset = 0, $userId = null) {
    $getChallenges = mysql_query('SELECT c.*, l.type, l.circuit FROM mkchallenges c INNER JOIN mkclrace l ON c.clist=l.id WHERE c.status="active" AND l.type!="" ORDER BY date DESC LIMIT ' . intval($offset) . ',' . intval($limit));
    
    $challengeParams = array(
        'circuit' => true,
        'circuit.raw' => true
    );
    if ($userId) {
        $challengeParams['winners'] = true;
        $challengeParams['id'] = $userId;
    }
    
    $challenges = array();
    while ($challenge = mysql_fetch_array($getChallenges)) {
        $challengeDetails = getChallengeDetails($challenge, $challengeParams);
        $challenges[] = $challengeDetails;
    }
    
    return $challenges;
}

function renderChallengeItem($challengeDetails) {
    global $language;
    $circuit = isset($challengeDetails['circuit']) ? $challengeDetails['circuit'] : array();
    $circuitAuthor = isset($circuit['author']) ? $circuit['author'] : '';
    $circuitName = isset($circuit['name']) ? $circuit['name'] : '';
    $difficultyName = isset($challengeDetails['difficulty']['name']) ? $challengeDetails['difficulty']['name'] : '';
    $descriptionMain = isset($challengeDetails['description']['main']) ? $challengeDetails['description']['main'] : '';
    
    ob_start();
    ?>
    <a href="<?php echo 'challengeTry.php?challenge=' . $challengeDetails['id']; ?>" title="<?php echo htmlspecialchars($descriptionMain); ?>" data-id="challenge-<?php echo $challengeDetails['id']; ?>"<?php if (isset($challengeDetails['succeeded'])) echo ' class="challenges_section_succeeded"'; ?>>
        <h2><?php echo home_controlLength($descriptionMain, 100); ?></h2>
        <h3><?php echo ucfirst(($circuitAuthor ? (($language ? 'by' : 'par') . ' <strong>' . home_controlLengthUtf8($circuitAuthor, 10) . '</strong> ') : '') . ($circuitName ? (($language ? 'in' : 'dans') . ' <strong>' . home_controlLengthUtf8($circuitName, 30 - min(10, strlen($circuitAuthor)) - strlen($difficultyName)) . '</strong>') : '')); ?> - <strong><?php echo $difficultyName; ?></strong></h3>
    </a>
    <?php
    return ob_get_clean();
}

function renderChallengeItems($challenges) {
    $html = '';
    foreach ($challenges as $challenge) {
        $html .= renderChallengeItem($challenge);
    }
    return $html;
}

function getRecentActivity($limit = 14, $offset = 0) {
    global $language;
    require_once(__DIR__ . '/utils-cups.php');
    
    $getComments = mysql_query('SELECT class, circuit, type, message, time, name, date, recency FROM ((SELECT NULL AS class, mkcomments.circuit, mkcomments.type, mkcomments.message, mkcomments.date, mkjoueurs.nom AS name, NULL as time, (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(date))*1 AS recency FROM `mkcomments` INNER JOIN `mkjoueurs` ON mkcomments.auteur=mkjoueurs.id ORDER BY mkcomments.id DESC LIMIT ' . intval($offset + $limit * 3) . ') UNION ALL (SELECT class, circuit, type, NULL as message, date, name, time, (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(date))*2 AS recency FROM `mkrecords` WHERE type!="" AND best=1 ORDER BY id DESC LIMIT ' . intval($offset + $limit * 3) . ') ORDER BY recency) as c GROUP BY c.type, c.circuit ORDER BY recency LIMIT ' . intval($offset) . ',' . intval($limit));
    
    $activities = array();
    while ($comment = mysql_fetch_array($getComments)) {
        if (($getCircuit = fetchCreationData($comment['type'], $comment['circuit'])) && ($getCircuit['name'] !== null)) {
            $activity = array(
                'comment' => $comment,
                'circuit' => $getCircuit
            );
            
            switch ($comment['type']) {
                case 'mkmcups':
                $activity['url'] = getCupPage($getCircuit['mode']) . '.php?mid=' . $getCircuit['id'];
                    break;
                case 'mkcups':
                    $activity['url'] = getCupPage($getCircuit['mode']) . '.php?cid=' . $getCircuit['id'];
                    break;
                case 'mkcircuits':
                    $activity['url'] = ($getCircuit['type'] ? 'arena.php' : 'circuit.php') . '?id=' . $getCircuit['id'];
                    break;
                case 'arenes':
                    $activity['url'] = 'battle.php?i=' . $getCircuit['ID'];
                    break;
                case 'circuits':
                    $activity['url'] = 'map.php?i=' . $getCircuit['ID'];
                    break;
            }
            
            if ($comment['message'] !== null) {
                $activity['activityType'] = 'comments';
                $activity['displayMessage'] = $comment['message'];
            } else {
                $activity['activityType'] = 'records';
                $timeMS = $comment['time'];
                $ms = $timeMS % 1000;
                $secs = floor($timeMS / 1000) % 60;
                $mins = floor($timeMS / 60000);
                $records = mysql_query('SELECT time FROM `mkrecords` WHERE class="' . $comment['class'] . '" AND circuit="' . $comment['circuit'] . '" AND type="' . $comment['type'] . '" AND best=1');
                $place = 1;
                $nbRecords = 0;
                while ($record = mysql_fetch_array($records)) {
                    if ($record['time'] < $comment['time'])
                        $place++;
                    $nbRecords++;
                }
                $activity['displayMessage'] = $mins . ':' . home_zerofill($secs, 2) . ':' . home_zerofill($ms, 3) . ' (' . $place . '<sup>' . home_getRank($place) . '</sup>' . ' ' . ($language ? 'out of' : 'sur') . ' ' . $nbRecords . ')';
            }
            
            $activities[] = $activity;
        }
    }
    
    return $activities;
}

function home_zerofill($nb, $l) {
    $nb .= '';
    while (strlen($nb) < $l)
        $nb = '0' . $nb;
    return $nb;
}

function home_getRank($n) {
    $languageForOrdinals = P_("language for ordinals", "en");
    $dec = $n % 100;
    if ($languageForOrdinals == "fr") {
        if ($n > 1)
            return 'e';
        else
            return 'er';
    } else {
        if (($dec >= 10) && ($dec < 20))
            return 'th';
        switch ($n % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
            default:
                return 'th';
        }
    }
}

function renderActivityItem($activity) {
    global $language;
    $type = $activity['activityType'];
    $message = $activity['displayMessage'];
    $url = $activity['url'];
    $comment = $activity['comment'];
    $getCircuit = $activity['circuit'];
    
    ob_start();
    ?>
    <a href="<?php echo $url; ?>" data-id="activity-<?php echo $comment['type'] . '-' . $comment['circuit']; ?>"<?php if ($type == 'comments') echo ' title="' . htmlspecialchars($message) . '"'; ?>>
        <h2><img src="images/<?php echo $type; ?>.png" alt="<?php echo $type; ?>" /> <?php echo ($type == 'comments') ? htmlspecialchars(home_controlLength($message, 40)) : $message; ?></h2>
        <h3><?php echo ($language ? 'By' : 'Par') . ' <strong>' . htmlspecialchars(home_controlLength($comment['name'], 10)) . '</strong>'; ?> <?php echo ($getCircuit['name'] ? (($language ? 'in' : 'dans') . ' <strong>' . home_controlLengthUtf8($getCircuit['name'], 20) . '</strong>') : ''); ?> <?php echo pretty_dates_short($comment['date'], array('lower' => true)); ?></h3>
    </a>
    <?php
    return ob_get_clean();
}

function renderActivityItems($activities) {
    $html = '';
    foreach ($activities as $activity) {
        $html .= renderActivityItem($activity);
    }
    return $html;
}

