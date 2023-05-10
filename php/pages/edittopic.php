<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>	">
<head>
<title><?= _('Mario Kart PC Forum'); ?></title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />

<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
?>
<?php
$page = 'forum';
include('../includes/menu.php');
?>
<main>
	<?php
	if ($id) {
		include('../includes/smileys.php');
		?>
<h1><?php echo $language ? 'Edit':'Message'; ?></h1>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
		<?php
		require_once('../includes/getRights.php');
		$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
		if ($getBanned && $getBanned['banned'])
			include('../includes/ban_msg.php');
		elseif (isset($_POST['titre']) && isset($_POST['message']) && isset($_POST['message'])) {
			$lastMessage = mysql_fetch_array(mysql_query('SELECT * FROM `mkmessages` WHERE id=1 AND topic="'. $_GET['topic'] .'"'));
			if (($lastMessage['auteur'] == $id) || hasRight('moderator')) {
				$categoryID = intval($_POST['category']);
				if ($category = mysql_fetch_array(mysql_query('SELECT id FROM `mkcategories` WHERE id="'. $categoryID .'"'. (hasRight('manager') ? '':' AND adminonly=0')))) {
					$private = (isset($_POST['admin']) && hasRight('manager')) ? 1:0;
					mysql_query('UPDATE `mktopics` SET titre="'. $_POST['titre'] .'",private='.$private.',category="'. $categoryID .'" WHERE id="'. $_GET['topic'] .'"');
					mysql_query('UPDATE `mkmessages` SET message="'. $_POST['message'] .'" WHERE id=1 AND topic="'. $_GET['topic'] .'"');
					if ($lastMessage['auteur'] != $id)
						mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Edit '. $_GET['topic'] .'")');
					preg_match_all('#\B@([a-zA-Z0-9\-_]+?)#isU', stripcslashes($_POST['message']), $mentions);
					preg_match_all('#\B@([a-zA-Z0-9\-_]+?)#isU', $lastMessage['message'], $mentions0);
					function array_rmvalue(&$arr,&$val) {
						if (($key = array_search($val, $arr)) !== false)
						    unset($arr[$key]);
					}
					$oldMentions = array_unique($mentions0[1]);
					foreach ($mentions[1] as $pseudo)
						array_rmvalue($oldMentions,$pseudo);
					$newMentions = array_unique($mentions[1]);
					foreach ($mentions0[1] as $pseudo)
						array_rmvalue($newMentions,$pseudo);
					foreach ($oldMentions as $pseudo) {
						$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
						if ($getMid=mysql_fetch_array($getMids))
							mysql_query('DELETE FROM `mknotifs`  WHERE type="forum_mention" AND user="'. $getMid['id'] .'" AND link="'.$_GET['topic'].','. 1 .'"');
					}
					foreach ($newMentions as $pseudo) {
						$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
						if ($getMid=mysql_fetch_array($getMids))
							mysql_query('INSERT INTO `mknotifs`  SET type="forum_mention", user="'. $getMid['id'] .'", link="'.$_GET['topic'].','. 1 .'"');
					}
					preg_match_all('#\[quote=(.+)\].*\[\/quote\]#isU', stripcslashes($_POST['message']), $quotes);
					preg_match_all('#\[quote=(.+)\].*\[\/quote\]#isU', $lastMessage['message'], $quotes0);
					$oldQuotes = array_unique($quotes0[1]);
					foreach ($quotes[1] as $pseudo)
						array_rmvalue($oldQuotes,$pseudo);
					$newQuotes = array_unique($quotes[1]);
					foreach ($quotes0[1] as $pseudo)
						array_rmvalue($newQuotes,$pseudo);
					foreach ($oldQuotes as $pseudo) {
						$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
						if ($getMid=mysql_fetch_array($getMids))
							mysql_query('DELETE FROM `mknotifs`  WHERE type="forum_quote" AND user="'. $getMid['id'] .'" AND link="'.$_GET['topic'].','. 1 .'"');
					}
					foreach ($newQuotes as $pseudo) {
						$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
						if ($getMid=mysql_fetch_array($getMids))
							mysql_query('INSERT INTO `mknotifs`  SET type="forum_quote", user="'. $getMid['id'] .'", link="'.$_GET['topic'].','. 1 .'"');
					}
					echo $language ? '<p id="successSent">Message edited successfully<br />
					<a href="topic.php?topic='. urlencode($_GET['topic']).'">Click here</a> to go to the topic.<br />
					<a href="category.php?category='. $categoryID .'">Click here</a> to return to the category.<br />
					<a href="forum.php">Click here</a> to return to the forum.</p>' :
					'<p id="successSent">Message modifi&eacute; avec succ&egrave;s<br />
					<a href="topic.php?topic='. urlencode($_GET['topic']) .'">Cliquez ici</a> pour acc&eacute;der au topic.<br />
					<a href="category.php?category='. $categoryID .'">Cliquez ici</a> pour retourner à la catégorie.<br />
					<a href="forum.php">Cliquez ici</a> pour retourner au forum.</p>';
				}
				else
					echo '<p style="text-align: center">'. ($language ? 'Non-existant category.':'Catégorie inexistante.') .'</p>';
			}
			else
				echo '<p style="text-align: center">'. ($language ? 'Error while editting message.':'Erreur lors de la modification du message.') .'</p>';
		}
		else {
		?>
<form method="post" action="edittopic.php?topic=<?php echo urlencode($_GET['topic']); ?>" onsubmit="if(!this.titre.value){alert('<?php echo $language ? 'Please enter a title':'Veuillez entrer un titre'; ?>');return false}if(!this.message.value){alert('<?php echo $language ? 'Please enter a message':'Veuillez entrer un message'; ?>');return false}this.querySelector('[type=submit]').disabled=true">
<table id="nMessage">
<tr><td class="mLabel"><label for="titre"><?php echo $language ? 'Title':'Titre'; ?> :</label></td>
<td class="mInput"><input type="text" id="titre" name="titre" value="<?php
	$getTopic = mysql_fetch_array(mysql_query('SELECT titre,category,private FROM `mktopics` WHERE id="'. $_GET['topic'] .'"'));
	echo htmlspecialchars($getTopic['titre']);
?>" /></td></tr>
<tr><td class="mLabel"><label for="category"><?php echo $language ? 'Category':'Catégorie'; ?> :</label></td>
<td class="mInput">
	<select id="category" name="category">
		<?php
		include('../includes/category_fields.php');
		$categories = mysql_query('SELECT id,'. $categoryFields .' FROM `mkcategories`'.(hasRight('manager') ? '':' WHERE adminonly=0') .' ORDER BY '. $orderingField);
		$currentCategory = null;
		while ($category = mysql_fetch_array($categories)) {
			echo '<option value="'. $category['id'] .'"'. (($category['id']==$getTopic['category']) ? ' selected="selected"':'') .'>'. htmlspecialchars($category['nom']) .'</option>';
			if ($category['id'] == $getTopic['category'])
				$currentCategory = $category;
		}
		?>
	</select>
</td></tr>
<tr><td class="mLabel">BBcode :<br /><a href="javascript:helpBbCode()"><?php echo $language ? 'Help':'Aide'; ?></a></td><td><?php include('../includes/bbButtons.php'); ?></td></tr>
<tr><td class="mLabel"><p><label for="message">Message :</label></p>
<p><?php
for ($i=0;$i<$nbSmileys;$i++)
	echo ' <a href="javascript:ajouter(\''. $smileys[$i] .'\')"><img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" /></a> ';
?>
<a href="javascript:moresmileys()" id="more-smileys"><?php echo $language ? 'More smileys':'Plus de smileys'; ?></a></p>
</td><td class="mInput"><textarea name="message" id="message" rows="10"><?php
	$getMessage = mysql_fetch_array(mysql_query('SELECT message FROM `mkmessages` WHERE id=1 AND topic="'. $_GET['topic'] .'"'));
	echo htmlspecialchars($getMessage['message']);
?></textarea></td></tr>
<?php
if (hasRight('manager')) {
	?>
	<tr>
	 <td class="mLabel"><input type="checkbox" id="admin_cb" name="admin"<?php if ($getTopic['private']) echo ' checked="checked" '; ?> /></td>
	 <td class="mInput">
	  <label for="admin_cb"><?php echo ($language ? 'Admin only':'Visible uniquement par les admins'); ?></label>
	 </td>
	</tr>
	<?php
}
?>
<tr><td colspan="2" class="mLabel"><input type="button" value="<?php echo $language ? 'Preview':'Aper&ccedil;u'; ?>" onclick="apercu()" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Send':'Envoyer'; ?>" /></td></tr>
</table>
</form>
<?php
include('../includes/preview-msg.php');
?>
<p class="forumButtons" style="margin: 10px 0 0 23%">
	<a href="topic.php?topic=<?php echo urlencode($_GET['topic']); ?>"><?php echo $language ? 'Back to the topic':'Retour au topic'; ?></a><br />
	<a href="category.php?category=<?php echo $currentCategory['id']; ?>"><?php echo $language ? 'Back to '. $currentCategory['nom']:'Retour à '. $currentCategory['nom']; ?></a><br />
	<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
			<?php
		}
	}
	else
		include('../includes/needCo.php');
?>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
<script type="text/javascript" src="scripts/msg.php"></script>
</body>
</html>