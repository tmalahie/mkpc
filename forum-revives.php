<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Forum Mario Kart PC - <?php echo $language ? 'Revived topics':'Topics deterrés'; ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
.radio-block {
	display: inline-block;
}
.radio-sm {
	font-size: 0.8em;
	position: relative;
	top: -0.1em;
}
</style>
<script type="text/javascript" src="scripts/topic.js"></script>
<script type="text/javascript" src="scripts/forum-search.js"></script>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
$RES_PER_PAGE = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page']:1;

function zerofill($s,$l) {
	while (strlen($s) < $l)
		$s = '0'.$s;
	return $s;
}
?>
<main>
<h1>Forum Mario Kart PC - <?php echo $language ? 'Revived topics':'Topics deterrés'; ?></h1>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<div id="search-results">
<?php
function toSQLSearch($search) {
    $search = str_replace('"', '""', $search);
    $search = str_replace('\\', '\\\\\\\\', $search);
    $search = str_replace('%', '\\%', $search);
    $search = '%'. $search .'%';
    return $search;
}
$oneset = true;
if ($oneset) {
	include('avatars.php');
	require_once('getRights.php');
	include('bbCode.php');
	$from = '`mkmessages` m INNER JOIN `mkmessages` m2 ON m.topic=m2.topic AND m.id=m2.id+1 INNER JOIN `mktopics` t ON t.id=m.topic';
	$wheres = array(
        'm.date BETWEEN m2.date + INTERVAL 3 MONTH AND m2.date + INTERVAL 6 MONTH',
        't.category!=0'
    );
	$where = implode(' AND ',$wheres);
	$maxRes = ($page+7)*$RES_PER_PAGE;
	$getNbRes = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS nb FROM (SELECT m.id FROM $from WHERE $where LIMIT $maxRes) t"));
	$nbres = $getNbRes['nb'];
	$isMax = ($nbres == $maxRes);
	?>
	<h2><?php echo $language ? 'Results':'Résultats'; ?> <?php
	if ($nbres) {
		echo min(($page-1)*$RES_PER_PAGE+1,$nbres);
		echo '-';
		echo min($page*$RES_PER_PAGE,$nbres);
		echo $language ? ' out of ' : ' sur ';
		echo $nbres.($isMax ? '+':'');
	}
	else
		echo '(0)';
	?></h2>
	<?php
	if ($nbres) {
		require_once('reactions.php');
		printReactionUI();

		$sql = "SELECT m.id,t.titre,m.topic,m.message,m.auteur,t.private,m.date FROM $from WHERE $where";
		$sql .= ' ORDER BY m.date DESC LIMIT '.(($page-1)*$RES_PER_PAGE).','.$RES_PER_PAGE;
		$search = mysql_query($sql);
		?>
		<div class="fMessages">
		<?php
		$topicName = '';
		$isManager = hasRight('manager');
		$searchResults = array();
		while ($result = mysql_fetch_array($search)) {
			if ($result['private'] && !$isManager)
				continue;
			$searchResults[] = $result;
		}
		if (empty($searchResults)) {
			echo '<h4>';
			echo $language ? 'No result in this page. It generally occurs when messages are deleted or made private. Please check the next or previous page' : 'Aucun résultat sur cette page. Cela se produit généralement lorsque les messages sont supprimés ou rendus privés. Essayez la page suivante ou précédente';
			echo '</h4>';
		}
		else {
			populateReactionsData('topic', $searchResults);

			foreach ($searchResults as $result) {
				if ($result['titre'] != $topicName) {
					$topicName = $result['titre'];
					echo '</div>';
					echo '<h2><a href="topic.php?topic='.$result['topic'].'">'.htmlspecialchars($topicName).'</a></h2>';
					echo '<div class="fMessages" data-topic="'.$result['topic'].'">';
				}
				print_forum_msg($result,false);
			}
		}
		?>
		</div>
		<?php
		$nbPages = ceil($nbres/$RES_PER_PAGE);
		if ($nbPages > 1) {
			?>
			<div class="topicPages"><p>
				Page : <?php
				$get = $_GET;
				foreach ($get as $k => $getk)
					$get[$k] = stripslashes($get[$k]);
				require_once('utils-paging.php');
				$allPages = makePaging($page,$nbPages);
				foreach ($allPages as $i=>$block) {
					if ($i)
						echo '...&nbsp; &nbsp;';
					foreach ($block as $p) {
						$get['page'] = $p;
						if ($p == $page)
							echo $p;
						else
							echo '<a href="?'. http_build_query($get) .'#search-results">'. $p .'</a>';
						echo ' &nbsp; ';
					}
				}
				if ($isMax)
					echo '...';
				?>
			</p></div>
			<?php
		}
	}
	else {
		echo '<h4>';
		echo $language ? 'No result for this search' : 'Aucun résultat pour cette recherche';
		echo '</h4>';
	}
}
?>
</div>
<p class="forumButtons"><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#author', {
	onSelect: function(event, term, item) {
		preventSubmit(event);
	}
});
</script>
<?php
mysql_close();
?>
</body>
</html>