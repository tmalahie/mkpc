<?php
if (($_SERVER['HTTP_HOST'] !== 'mkpc.malahieude.net') || isset($_GET['metakey']))
	echo '<script type="text/javascript" src="scripts/mk.js?reload=8"></script>';
else
	echo '<script type="text/javascript" src="scripts/mk.v83.js"></script>';
?>
<?php
if (isset($_GET['metakey'])) {
	$dbToOpen = null;
	if (!isset($dbh)) {
		$dbToOpen = 1;
		include('initdb.php');
	}
	if ($getMetaSettings = mysql_fetch_array(mysql_query('SELECT settings FROM metaitem WHERE id="'. $_GET['metakey'] .'"'))) {
		?>
<script type="text/javascript">
window.metaItemSettings = <?php echo $getMetaSettings['settings']; ?>;
</script>
		<?php
	}
	if ($dbToOpen)
		mysql_close();
}
?>