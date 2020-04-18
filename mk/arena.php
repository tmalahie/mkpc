<?php
require_once('circuitEnumsQuick.php');
$elements = Array('a','b','c','d','e','f','g','h','i','j');
$getInfos = Array();
foreach ($circuitsData as $c => $arene) {
	if ($c)
		echo ',';
	$pieces = Array(
		Array(false,true,true,true),
		Array(true,false,true,true),
		Array(true,true,false,true),
		Array(true,true,true,false),
		Array(false,true,true,false),
		Array(false,false,true,true),
		Array(true,false,false,true),
		Array(true,true,false,false),
		Array(false,true,false,true),
		Array(true,false,true,false),
		Array(false,false,false,false),
		Array(true,true,true,true),
		Array(false,false,true,false),
		Array(false,false,false,true),
		Array(true,false,false,false),
		Array(false,true,false,false)
	);
	$map = $arene['map'];
	$snes = ($map < 14);
	if ($map == 29)
		$pieces[10] = Array(true,true,true,true);
	$chemins = Array();
	for ($i=0;$i<36;$i++) {
		$chemins[$i] = Array();
		$piece = $pieces[$arene["p$i"]];
		$directions = Array();
		if ($i>5)
			$directions[0] = -6;
		if ($i<30)
			$directions[2] = 6;
		if ($i%6)
			$directions[1] = -1;
		if (($i+1)%6)
			$directions[3] = 1;
		for ($j=0;$j<4;$j++) {
			$direction = isset($directions[$j]) ? $directions[$j]:0;
			if ($direction) {
				$dir = $pieces[$arene['p'.($i+$direction)]];
				if ($piece[$j] && $dir[($j+2)%4])
					array_push($chemins[$i], $i+$direction);
			}
		}
	}
	?>
"map<?php echo ($c+1); ?>" : {
	<?php
	if (isset($arene['id']))
		echo '"id" : '.$arene['id'].',';
	?>
	"map" : "?<?php
	for ($i=0;$i<36;$i++)
		echo 'p'.$i.'='.$arene["p$i"].'&';
	for ($i=0;$i<10;$i++) {
		$e = $elements[$i];
		for ($j=0; isset($arene[$e.$j]); $j++)
		echo $e.$j.'='.$arene[$e.$j].'&';
	}
		echo "map=$map";
	?>",
	"w" : 600,
	"h" : 600,
	"skin" : <?php echo $map; ?>,
	"bgcolor" : [<?php echo implode(',',$bgColors[$map]); ?>],
	"fond" : ["<?php echo implode('","',$bgImages[$map]); ?>"],
	"music" : <?php echo $musicIds[$map]; ?>,
	"collision" : [
	<?php
	if ($snes) {
		for ($i=0;$i<36;$i++) {
			$piece = $pieces[$arene["p$i"]];
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			if (!$piece[0])
				echo "[$x,$y,101,5],";
			if (!$piece[1])
				echo "[$x,$y,5,101],";
			if (!$piece[2])
				echo "[$x,".($y+96).",101,5],";
			if (!$piece[3])
				echo "[".($x+96).",$y,5,101],";
		}
	}
	else {
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			$baseCase = false;
			switch ($map) {
			case 26:
				switch ($arene["p$i"]) {
				case 10:
					echo "[$x,$y,101,101],";
					break;
				default:
					$baseCase = true;
				}
				break;
			case 27:
				switch ($arene["p$i"]) {
				case 8:
				case 9:
				case 10:
					break;
				default:
					$piece = $pieces[$arene["p$i"]];
					if ($piece[0])
						echo "[".($x+26).",$y,5,31],[".($x+70).",$y,5,31],";
					else
						echo "[$x,".($y+26).",101,5],";
					if ($piece[1])
						echo "[$x,".($y+26).",31,5],[$x,".($y+70).",31,5],";
					else
						echo "[".($x+26).",$y,5,101],";
					if ($piece[2])
						echo "[".($x+26).",".($y+70).",5,31],[".($x+70).",".($y+70).",5,31],";
					else
						echo "[$x,".($y+70).",101,5],";
					if ($piece[3])
						echo "[".($x+70).",".($y+26).",31,5],[".($x+70).",".($y+70).",31,5],";
					else
						echo "[".($x+70).",$y,5,101],";
				}
				break;
			case 28:
				switch ($arene["p$i"]) {
				case 10:
					break;
				default:
					$baseCase = true;
					$piece = $pieces[$arene["p$i"]];
					if ($piece[0]&&$piece[1])
						echo "[$x,$y,9,9],";
					if ($piece[1]&&$piece[2])
						echo "[$x,".($y+92).",9,9],";
					if ($piece[2]&&$piece[3])
						echo "[".($x+92).",".($y+92).",9,9],";
					if ($piece[3]&&$piece[0])
						echo "[".($x+92).",$y,9,9],";
				}
				break;
			case 29:
				switch ($arene["p$i"]) {
				case 10:
					break;
				default:
					$baseCase = true;
				}
				break;
			}
			if ($baseCase) {
				$piece = $pieces[$arene["p$i"]];
				if (!$piece[0])
					echo "[$x,$y,101,9],";
				if (!$piece[1])
					echo "[$x,$y,9,101],";
				if (!$piece[2])
					echo "[$x,".($y+92).",101,9],";
				if (!$piece[3])
					echo "[".($x+92).",$y,9,101],";
			}
		}
	}
	?>
	],
	<?php
	if ($map == 29) {
		echo '"horspiste" : [';
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			if ($arene["p$i"] == 10)
				echo '[['.($x+41).','.($y+4).'],['.($x+58).','.($y+4).'],['.($x+80).','.($y+13).'],['.($x+88).','.($y+23).'],['.($x+95).','.($y+50).'],['.($x+88).','.($y+76).'],['.($x+79).','.($y+87).'],['.($x+58).','.($y+95).'],['.($x+41).','.($y+95).'],['.($x+26).','.($y+90).'],['.($x+13).','.($y+79).'],['.($x+8).','.($y+65).'],['.($x+4).','.($y+49).'],['.($x+7).','.($y+38).'],['.($x+12).','.($y+21).'],['.($x+22).','.($y+11).']],';
		}
		echo '],';
	}
	elseif ($map == 30) {
		echo '"horspiste" : [';
		for ($i=0;$i<36;$i++) {
			if ($arene["p$i"] != 10) {
				$piece = $pieces[$arene["p$i"]];
				$x = ($i%6)*100;
				$y = floor($i/6)*100;
				if (!$piece[0])
					echo "[$x,$y,101,22],";
				if (!$piece[1])
					echo "[$x,$y,22,101],";
				if (!$piece[2])
					echo "[$x,".($y+79).",101,22],";
				if (!$piece[3])
					echo "[".($x+79).",$y,22,101],";
				if ($piece[0]&&$piece[1])
					echo "[$x,$y,22,22],";
				if ($piece[1]&&$piece[2])
					echo "[$x,".($y+79).",22,22],";
				if ($piece[2]&&$piece[3])
					echo "[".($x+79).",".($y+79).",22,22],";
				if ($piece[3]&&$piece[0])
					echo "[".($x+79).",$y,22,22],";
			}
		}
		echo '],';
	}
	if ($map == 27) {
		echo '"trous" : [';
		echo '[';
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			switch ($arene["p$i"]) {
			case 10:
				echo "[$x,$y,101,101,NaN,NaN],";
				break;
			default:
				$piece = $pieces[$arene["p$i"]];
				if ($piece[0])
					echo "[$x,$y,27,27,NaN,NaN],[".($x+74).",$y,27,27,NaN,NaN],";
				else
					echo "[$x,$y,27,27,NaN,NaN],";
				if ($piece[1])
					echo "[$x,$y,27,27,NaN,NaN],[$x,".($y+74).",27,27,NaN,NaN],";
				else
					echo "[$x,$y,27,101,NaN,NaN],";
				if ($piece[2])
					echo "[$x,".($y+74).",27,27,NaN,NaN],[".($x+74).",".($y+74).",27,27,NaN,NaN],";
				else
					echo "[$x,".($y+74).",101,27,NaN,NaN],";
				if ($piece[3])
					echo "[".($x+74).",".($y+74).",27,27],[".($x+74).",$y,27,27,NaN,NaN],";
				else
					echo "[".($x+74).",$y,27,101,NaN,NaN],";
			}
		}
		echo ']';
		for ($i=0;$i<3;$i++)
			echo ',[]';
		echo '],';
	}
	elseif ($map == 28) {
		echo '"trous" : [';
		echo '[';
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			if ($arene["p$i"] == 10)
				echo "[$x,$y,101,101,NaN,NaN],";
		}
		echo ']';
		for ($i=0;$i<3;$i++)
			echo ',[]';
		echo '],';
	}
	elseif ($map == 30) {
		echo '"trous" : [';
		for ($j=0;$j<4;$j++) {
			if ($j)
				echo ',';
			echo '[';
			for ($i=0;$i<36;$i++) {
				$x = ($i%6)*100;
				$y = floor($i/6)*100;
				$u = $x+50;
				$v = $y+50;
				if ($arene["p$i"] != 10) {
					$piece = $pieces[$arene["p$i"]];
					if (($j==0)&&!$piece[0])
						echo "[$x,$y,101,8,$u,$v],";
					if (($j==1)&&!$piece[1])
						echo "[$x,$y,8,101,$u,$v],";
					if (($j==2)&&!$piece[2])
						echo "[$x,".($y+93).",101,8,$u,$v],";
					if (($j==3)&&!$piece[3])
						echo "[".($x+93).",$y,8,101,$u,$v],";
					if (($j==2)&&$piece[0]&&$piece[1])
						echo "[$x,$y,8,8,$u,$v],";
					if (($j==3)&&$piece[1]&&$piece[2])
						echo "[$x,".($y+93).",8,8,$u,$v],";
					if (($j==0)&&$piece[2]&&$piece[3])
						echo "[".($x+93).",".($y+93).",8,8,$u,$v],";
					if (($j==1)&&$piece[3]&&$piece[0])
						echo "[".($x+93).",$y,8,8,$u,$v],";
				}
				elseif (!$j)
					echo "[$x,$y,101,101,NaN,NaN],";
			}
			echo ']';
		}
		echo '],';
	}
	?>
	"aipoints" : [[
		<?php
		for ($i=0;$i<36;$i++) {
			$piece = $chemins[$i];
			echo '[';
			for ($j=0;$j<count($piece);$j++)
				echo $piece[$j].',';
			echo '],';
		}
		?>
	]],
	"arme" : [
		<?php
		for ($i=0; isset($arene['o'.$i]); $i++)
			echo '['.$arene['o'.$i].'],';
		?>
	],
	"sauts" : [
		<?php
		$elements = Array('e','f','g','h','i','j');
		for ($i=0;$i<6;$i++) {
			$e = $elements[$i];
			for ($j=0; isset($arene[$e.$j]); $j++) {
				echo '['.$arene[$e.$j].',';
				switch ($e) {
					case 'e' :
					echo '13,7';
					break;
					case 'f' :
					echo '7,13';
					break;
					case 'g' :
					echo '33,7';
					break;
					case 'h' :
					echo '7,33';
					break;
					case 'i' :
					echo '45,7';
					break;
					case 'j' :
					echo '7,45';
				}
				echo ',],';
			}
		}
		?>
	],
	"accelerateurs" : [
		<?php
		$elements = Array('a','b','c','d');
		for ($i=0;$i<4;$i++) {
			$e = $elements[$i];
			for ($j=0; isset($arene[$e.$j]); $j++)
				echo '['.$arene[$e.$j].'],';
			}
		?>
	],
	"startposition" : [
		<?php
		for ($i=0;$i<8;$i++)
			echo '['.($arene["s$i"]%6*100+50).','.(floor($arene["s$i"]/6)*100+50).','.$arene["r$i"].','.$arene["s$i"].'],';
		?>
	],
	"decor" : {
		<?php
		foreach ($decorTypes[$map] as $i=>$decorType) {
			if ($i) echo ',';
			echo '"'.$decorTypes[$map][$i].'":[';
			$prefix = 't'.($i ? $i.'_':'');
			for ($j=0; isset($circuit[$prefix.$j]); $j++) {
				if ($j) echo ',';
				echo '['.$circuit[$prefix.$j].']';
			}
			echo ']';
		}
		?>
		}
	}
	<?php
}
?>