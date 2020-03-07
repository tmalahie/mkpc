<?php
	$isCup = (strpos($circuit['cicon'], ',') !== false);
	$note = $circuit['note'];
	$nbNotes = $circuit['nbnotes'];
	$noteTitle = $nbNotes ? (round(($note+1)*100)/100).'/5 '. ($language ? 'on':'sur') .' '. $nbNotes .' vote'. ($nbNotes>1 ? 's':'') : ($language ? 'Unrated':'Non noté');
	$circuitTime = pretty_dates_short($circuit['publication_date'], array('lower' => true, 'shorter' => true));
	$circuitFullDate = pretty_dates($circuit['publication_date'], array('lower' => true));
?>
<tr class="creation_line">
	<td class="creation_icon <?php echo ($isCup ? 'creation_cup':'single_creation'); ?>"<?php
		if (isset($circuit['icon'])) {
			$allMapSrcs = $circuit['icon'];
			foreach ($allMapSrcs as $i=>$iMapSrc)
				$allMapSrcs[$i] = "url('images/creation_icons/$iMapSrc')";
			echo ' style="background-image:'.implode(',',$allMapSrcs).'"';
		}
		else
			echo ' data-cicon="'.$circuit['cicon'].'"';
	?> title="<?php echo $language ? 'Preview':'Aperçu'; ?>" onclick="apercu(<?php echo htmlspecialchars(json_encode($circuit['srcs'])); ?>)">
	</td>
	<td class="creation_description">
		<a href="<?php echo $circuit['href']; ?>" title="<?php echo escapeUtf8(decodeUtf8($circuit['nom'])); ?>">
			<h2><?php echo getNom($circuit); ?></h2>
			<table title="<?php echo $noteTitle; ?>">
				<tr>
					<?php
					for ($i=0;$i<=$note;$i++)
						echo '<td class="star1"></td>';
					$rest = $note-floor($note);
					if ($rest) {
						$w1 = 3+round(9*$rest);
						echo '<td class="startStar" style="width: '. $w1 .'px;"></td>';
						echo '<td class="endStar" style="width: '. (15-$w1) .'px;"></td>';
						$note++;
					}
					for ($i=$note+1;$i<5;$i++)
						echo '<td class="star0"></td>';
					?>
					<td><h3><?php echo getAuteur($circuit); ?></h3></td>
				</tr>
			</table>
			<?php
			if ($circuit['nbcomments']) {
				?>
			<div class="creation_coms" title="<?php echo $circuit['nbcomments']. ' '. ($language ? 'comment':'commentaire') . (($circuit['nbcomments']>1) ? 's':''); ?>"><img src="images/comments.png" alt="Commentaires" /><?php echo $circuit['nbcomments']; ?></div>
				<?php
			}
			?>
			<div class="creation_date" title="<?php echo ($language ? 'Published':'Publié').' '.$circuitFullDate; ?>"><img src="images/records.png" alt="Date" /><?php echo $circuitTime; ?></div>
		</a>
	</td>
</tr>