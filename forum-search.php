<?php
include('getId.php');
include('language.php');
include('session.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Forum Mario Kart PC</title>
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
$page = isset($_GET['page']) ? $_GET['page']:1;

function zerofill($s,$l) {
	while (strlen($s) < $l)
		$s = '0'.$s;
	return $s;
}

$category = isset($_GET['category']) ? $_GET['category']:'';
$title = isset($_GET['title']) ? stripslashes($_GET['title']):'';
$author = isset($_GET['author']) ? $_GET['author']:'';
$message = isset($_GET['message']) ? stripslashes($_GET['message']):'';
$d0 = isset($_GET['d0']) ? $_GET['d0']:'';
$m0 = isset($_GET['m0']) ? $_GET['m0']:'';
$y0 = isset($_GET['y0']) ? $_GET['y0']:'';
$date0 = $y0 ? zerofill($y0,4).'-'.zerofill($m0?$m0:1,2).'-'.zerofill($d0?$d0:1,2).' 00:00:00':'';
$d1 = isset($_GET['d1']) ? $_GET['d1']:'';
$m1 = isset($_GET['m1']) ? $_GET['m1']:'';
$y1 = isset($_GET['y1']) ? $_GET['y1']:'';
$date1 = $y1 ? zerofill($y1,4).'-'.zerofill($m1?$m1:31,2).'-'.zerofill($d1?$d1:12,2).' 23:59:59':'';
$topiconly = isset($_GET['type']) && ('topics' === $_GET['type']);
$oneset = (is_numeric($category)||$title||$author||$message||$date0||$date1||$topiconly);
?>
<main>
<h1>Forum Mario Kart PC - <?php echo $language ? 'Advanced search':'Recherche avancée'; ?></h1>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<form method="get" class="advanced-search" action="forum-search.php#search-results">
	<table>
		<tr>
			<td class="ligne">
				<label><?php echo $language ? 'Search for':'Rechercher'; ?></label>
			</td>
			<td>
				<label class="radio-block"><input type="radio" name="type" value="topics"<?php echo $topiconly ? ' checked="checked"':''; ?> /> <span class="radio-sm"><?php echo $language ? 'Topics':'Des topics'; ?></span></label>
				<label class="radio-block"><input type="radio" name="type" value="messages"<?php echo $topiconly ? '':' checked="checked"'; ?> /> <span class="radio-sm"><?php echo $language ? 'Messages':'Des messages'; ?></span></label>
			</td>
		</tr>
		<tr>
			<td class="ligne">
				<label for="category"><?php echo $language ? 'Category':'Catégorie'; ?></label>
			</td>
			<td>
				<select name="category"  id="category">
					<option value=""><?php echo $language ? 'All categories':'Toutes les catégories'; ?></option>
					<?php
					include('category_fields.php');
					$categories = mysql_query('SELECT id,'. $categoryFields .' FROM `mkcategories` ORDER BY '. $orderingField);
					while ($cat = mysql_fetch_array($categories))
						echo '<option value="'.$cat['id'].'"'. (($cat['id']===$category)?' selected="selected"':'') .'>'.$cat['nom'].'</option>';
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="ligne">
				<label for="title"><?php echo $language ? 'Topic title<br /><em>(containing)</em>':'Titre du topic<br /><em>(contenant)</em>'; ?></label>
			</td>
			<td>
				<input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>" />
			</td>
		</tr>
		<tr>
			<td class="ligne">
				<label for="author"><?php echo $language ? 'Author':'Auteur'; ?></label>
			</td>
			<td>
				<input type="text" name="author" id="author" value="<?php echo htmlspecialchars($author); ?>" />
			</td>
		</tr>
		<tr>
			<td class="ligne">
				<label for="message"><?php echo $language ? 'Message<br /><em>(containing)</em>':'Message<br /><em>(contenant)</em>'; ?></label>
			</td>
			<td>
				<input type="text" name="message" id="message" value="<?php echo htmlspecialchars($message); ?>" />
			</td>
		</tr>
		<tr>
			<td class="ligne">
				<label for="d0">Date</label>
			</td>
			<td class="search-right">
				<?php echo $language ? 'Between':'Entre'; ?>
				<input type="number" name="d0" id="d0" class="search-xs-2" min="1" max="31" value="<?php echo htmlspecialchars($d0); ?>" /> /
				<input type="number" name="m0" class="search-xs-2" min="1" max="12" value="<?php echo htmlspecialchars($m0); ?>" /> /
				<input type="number" name="y0" class="search-xs-4" min="1000" max="9999" value="<?php echo htmlspecialchars($y0); ?>" /><br />
				<?php echo $language ? 'And':'Et'; ?>
				<input type="number" name="d1" class="search-xs-2" min="1" max="31" value="<?php echo htmlspecialchars($d1); ?>" /> /
				<input type="number" name="m1" class="search-xs-2" min="1" max="12" value="<?php echo htmlspecialchars($m1); ?>" /> /
				<input type="number" name="y1" class="search-xs-4" min="1000" max="9999" value="<?php echo htmlspecialchars($y1); ?>" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" class="action_button" value="<?php echo $language ? 'Search':'Rechercher'; ?>" />
			</td>
		</tr>
	</table>
</form>
<div id="search-results">
<?php
function toSQLSearch($search) {
    $search = str_replace('"', '""', $search);
    $search = str_replace('\\', '\\\\\\\\', $search);
    $search = str_replace('%', '\\%', $search);
    $search = '%'. $search .'%';
    return $search;
}
if ($oneset) {
	include('avatars.php');
	require_once('getRights.php');
	include('bbCode.php');
	$from = '`mkmessages` m INNER JOIN `mktopics` t ON t.id=m.topic';
	$wheres = array();
	//$wheres[] = 'language='. $language;
	if (is_numeric($category))
		$wheres[] = 't.category='. $category;
	if ($title)
		$wheres[] = 't.titre LIKE "'. toSQLSearch($title) .'"';
	if ($author) {
		if ($getAuthorId = mysql_fetch_array(mysql_query('SELECT id FROM mkjoueurs WHERE nom="'. $author .'"')))
			$wheres[] = 'm.auteur="'. $getAuthorId['id'] .'"';
		else
			$wheres[] = 'm.auteur=0';
	}
	if ($message) {
		//$message = '+'.implode(' +', preg_split("/[\s,]+/", $message));
		$wheres[] = 'MATCH(m.message) AGAINST ("\"'. mysql_real_escape_string(str_replace('"','',$message)) .'\"" IN BOOLEAN mode)';
	}
	if ($date0)
		$wheres[] = 'm.date >= "'. $date0 .'"';
	if ($date1)
		$wheres[] = 'm.date <= "'. $date1 .'"';
	if ($topiconly)
		$wheres[] = 'm.id=1';
	$where = implode(' AND ',$wheres);
	$maxRes = ($page+7)*$RES_PER_PAGE;
	$getNbRes = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS nb FROM (SELECT m.id FROM $from WHERE $where LIMIT $maxRes) t"));
	$nbres = $getNbRes['nb'];
	$isMax = ($nbres == $maxRes);
	?>
	<h2><?php echo $language ? 'Results':'Résultats'; ?> <?php
	if ($nbres) {
		echo ($page-1)*$RES_PER_PAGE;
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
		$sql = "SELECT m.id,t.titre,m.topic,m.message,m.auteur,t.private,m.date FROM $from WHERE $where";
		$sql .= ' ORDER BY t.dernier DESC, m.topic DESC, m.date DESC LIMIT '.(($page-1)*$RES_PER_PAGE).','.$RES_PER_PAGE;
		$search = mysql_query($sql);
		?>
		<div class="fMessages">
		<?php
		$topicName = '';
		$isManager = hasRight('manager');
		$oneResult = false;
		while ($result = mysql_fetch_array($search)) {
			if ($result['private'] && !$isManager)
				continue;
			$oneResult = true;
			if ($result['titre'] != $topicName) {
				$topicName = $result['titre'];
				echo '</div>';
				echo '<h2><a href="topic.php?topic='.$result['topic'].'">'.htmlspecialchars($topicName).'</a></h2>';
				echo '<div class="fMessages" data-topic="'.$result['topic'].'">';
			}
			print_forum_msg($result,false);
		}
		if (!$oneResult) {
			echo '<h4>';
			echo $language ? 'No result in this page. It generally occurs when topics are deleted or private. Please check the following page' : 'Aucun résultat sur cette page. Cela se produit généralement lorsque les sujets sont supprimés ou privés. Essayez la page suivante';
			echo '</h4>';
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