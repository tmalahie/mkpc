<?php
	require_once('isDS.php');
?>
<header>
	<table>
		<tr>
			<td id="header_left">
				<?php /*<img src="images/header_left.png" alt="/|" />*/ ?>
				<img src="images/<?php echo IS_DS ? 'header_left2':'header_lwinter'; ?>.png" alt="/|" />
			</td>
			<td id="header_center">
				<?php
				//if ($id)
				//	echo '<a href="news.php?id=14473"><img src="images/header_es.png" alt="MARIO KART PC" /></a>';
				//else
					echo '<img src="images/header_transparent'. (IS_DS ? '2':'') .'.png" alt="MARIO KART PC" />';
				?>
			</td>
			<td id="header_right">
				<?php /*<img src="images/header_right.png" alt="|\" />*/ ?>
				<img src="images/<?php echo IS_DS ? 'header_right2':'header_rwinter'; ?>.png" alt="|\" />
			</td>
		</tr>
	</table>
</header>