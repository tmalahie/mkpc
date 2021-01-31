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
					echo "[$x,$y,100,100],";
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
	switch ($map) {
	case 29:
		echo '"horspiste" : [';
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			if ($arene["p$i"] == 10)
				echo '[['.($x+41).','.($y+4).'],['.($x+58).','.($y+4).'],['.($x+80).','.($y+13).'],['.($x+88).','.($y+23).'],['.($x+95).','.($y+50).'],['.($x+88).','.($y+76).'],['.($x+79).','.($y+87).'],['.($x+58).','.($y+95).'],['.($x+41).','.($y+95).'],['.($x+26).','.($y+90).'],['.($x+13).','.($y+79).'],['.($x+8).','.($y+65).'],['.($x+4).','.($y+49).'],['.($x+7).','.($y+38).'],['.($x+12).','.($y+21).'],['.($x+22).','.($y+11).']],';
		}
		echo '],';
		break;
	case 30:
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
					echo "[$x,$y,100,100,NaN,NaN],";
			}
			echo ']';
		}
		echo '],';
		break;
	case 27:
		echo '"trous" : [';
		echo '[';
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			switch ($arene["p$i"]) {
			case 10:
				echo "[$x,$y,100,100,NaN,NaN],";
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
		break;
	case 28:
		echo '"trous" : [';
		echo '[';
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			if ($arene["p$i"] == 10)
				echo "[$x,$y,100,100,NaN,NaN],";
		}
		echo ']';
		for ($i=0;$i<3;$i++)
			echo ',[]';
		echo '],';
		break;
	case 49:
		echo '"horspistes" : {"eau":[';
		for ($i=0;$i<36;$i++) {
			$x = ($i%6)*100;
			$y = floor($i/6)*100;
			switch ($arene["p$i"]) {
			case 0:
				echo "[$x,".($y+4).",100,6],[[$x,".($y+90)."],[".($x+6).",".($y+94)."],[".($x+10).",".($y+100)."],[$x,".($y+100)."]],[[".($x+90).",".($y+100)."],[".($x+94).",".($y+94)."],[".($x+100).",".($y+90)."],[".($x+100).",".($y+100)."]],";
				break;
			case 1:
				echo "[".($x+3).",$y,7,100],[[".($x+90).",".($y+100)."],[".($x+94).",".($y+94)."],[".($x+100).",".($y+90)."],[".($x+100).",".($y+100)."]],[[".($x+100).",".($y+10)."],[".($x+94).",".($y+6)."],[".($x+90).",$y],[".($x+100).",$y]],";
				break;
			case 2:
				echo "[$x,".($y+90).",100,7],[[".($x+100).",".($y+10)."],[".($x+94).",".($y+6)."],[".($x+90).",$y],[".($x+100).",$y]],[[".($x+10).",$y],[".($x+6).",".($y+6)."],[$x,".($y+10)."],[$x,$y]],";
				break;
			case 3:
				echo "[".($x+90).",$y,7,100],[[".($x+10).",$y],[".($x+6).",".($y+6)."],[$x,".($y+10)."],[$x,$y]],[[$x,".($y+90)."],[".($x+6).",".($y+94)."],[".($x+10).",".($y+100)."],[$x,".($y+100)."]],";
				break;
			case 4:
				echo "[[".($x+10).",".($y+100)."],[".($x+6).",".($y+94)."],[$x,".($y+90)."],[$x,".($y+100)."]],[[$x,".($y+10)."],[".($x+44).",".($y+10)."],[".($x+69).",".($y+19)."],[".($x+81).",".($y+31)."],[".($x+90).",".($y+56)."],[".($x+90).",".($y+100)."],[".($x+97).",".($y+100)."],[".($x+97).",".($y+3)."],[$x,".($y+3)."]],";
				break;
			case 5:
				echo "[[".($x+100).",".($y+90)."],[".($x+94).",".($y+94)."],[".($x+90).",".($y+100)."],[".($x+100).",".($y+100)."]],[[".($x+10).",".($y+100)."],[".($x+10).",".($y+56)."],[".($x+19).",".($y+31)."],[".($x+31).",".($y+19)."],[".($x+56).",".($y+10)."],[".($x+100).",".($y+10)."],[".($x+100).",".($y+3)."],[".($x+3).",".($y+3)."],[".($x+3).",".($y+100)."]],";
				break;
			case 6:
				echo "[[".($x+90).",$y],[".($x+94).",".($y+6)."],[".($x+100).",".($y+10)."],[".($x+100).",$y]],[[".($x+100).",".($y+90)."],[".($x+56).",".($y+90)."],[".($x+31).",".($y+81)."],[".($x+19).",".($y+69)."],[".($x+10).",".($y+44)."],[".($x+10).",$y],[".($x+3).",$y],[".($x+3).",".($y+97)."],[".($x+100).",".($y+97)."]],";
				break;
			case 7:
				echo "[[$x,".($y+10)."],[".($x+6).",".($y+6)."],[".($x+10).",$y],[$x,$y]],[[".($x+90).",$y],[".($x+90).",".($y+44)."],[".($x+81).",".($y+69)."],[".($x+69).",".($y+81)."],[".($x+44).",".($y+90)."],[$x,".($y+90)."],[$x,".($y+97)."],[".($x+97).",".($y+97)."],[".($x+97).",$y]],";
				break;
			case 8:
				echo "[$x,".($y+3).",100,7],[$x,".($y+90).",100,7],";
				break;
			case 9:
				echo "[".($x+3).",$y,7,100],[".($x+90).",".($y+1).",7,99],";
				break;
			case 11:
				echo "[[$x,".($y+10)."],[".($x+6).",".($y+6)."],[".($x+10).",$y],[$x,$y]],[[".($x+90).",$y],[".($x+94).",".($y+6)."],[".($x+100).",".($y+10)."],[".($x+100).",$y]],[[$x,".($y+90)."],[".($x+6).",".($y+94)."],[".($x+10).",".($y+100)."],[$x,".($y+100)."]],[[".($x+90).",".($y+100)."],[".($x+94).",".($y+94)."],[".($x+100).",".($y+90)."],[".($x+100).",".($y+100)."]],";
				break;
			case 12:
				echo "[[".($x+90).",".($y+100)."],[".($x+90).",".($y+30)."],[".($x+85).",".($y+21)."],[".($x+78).",".($y+15)."],[".($x+63).",".($y+12)."],[".($x+37).",".($y+12)."],[".($x+23).",".($y+15)."],[".($x+15).",".($y+21)."],[".($x+10).",".($y+31)."],[".($x+10).",".($y+100)."],[".($x+3).",".($y+100)."],[".($x+3).",".($y+5)."],[".($x+97).",".($y+5)."],[".($x+97).",".($y+100)."]],";
				break;
			case 13:
				echo "[[".($x+100).",".($y+10)."],[".($x+30).",".($y+10)."],[".($x+21).",".($y+15)."],[".($x+15).",".($y+22)."],[".($x+12).",".($y+37)."],[".($x+12).",".($y+63)."],[".($x+15).",".($y+77)."],[".($x+21).",".($y+85)."],[".($x+31).",".($y+90)."],[".($x+100).",".($y+90)."],[".($x+100).",".($y+97)."],[".($x+5).",".($y+97)."],[".($x+5).",".($y+3)."],[".($x+100).",".($y+3)."]],";
				break;
			case 14:
				echo "[[".($x+10).",$y],[".($x+10).",".($y+70)."],[".($x+15).",".($y+79)."],[".($x+22).",".($y+85)."],[".($x+37).",".($y+88)."],[".($x+63).",".($y+88)."],[".($x+77).",".($y+85)."],[".($x+85).",".($y+79)."],[".($x+90).",".($y+69)."],[".($x+90).",$y],[".($x+97).",$y],[".($x+97).",".($y+95)."],[".($x+3).",".($y+95)."],[".($x+3).",$y]],";
				break;
			case 15:
				echo "[[$x,".($y+90)."],[".($x+70).",".($y+90)."],[".($x+79).",".($y+85)."],[".($x+85).",".($y+78)."],[".($x+88).",".($y+63)."],[".($x+88).",".($y+37)."],[".($x+85).",".($y+23)."],[".($x+79).",".($y+15)."],[".($x+69).",".($y+10)."],[$x,".($y+10)."],[$x,".($y+3)."],[".($x+96).",".($y+3)."],[".($x+95).",".($y+97)."],[$x,".($y+97)."]],";
				break;
			}
		}
		echo ']},';
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
				$replace = "$u,$v";
				switch ($arene["p$i"]) {
				case 0:
					switch ($j) {
					case 0:
						echo "[$x,$y,100,5,".($x+49).",".($y+49)."],";
						break;
					case 1:
						echo "[[[".($x+94).",".($y+100)."],[".($x+100).",".($y+94)."],[".($x+100).",".($y+100)."]],[".($x+49).",".($y+49)."]],";
						break;
					case 3:
						echo "[[[$x,".($y+94)."],[".($x+6).",".($y+100)."],[$x,".($y+100)."]],[".($x+49).",".($y+49)."]],";
						break;
					}
					break;
				case 1:
					switch ($j) {
					case 0:
						echo "[[[".($x+94).",".($y+100)."],[".($x+100).",".($y+94)."],[".($x+100).",".($y+100)."]],[".($x+49).",".($y+49)."]],";
						break;
					case 1:
						echo "[$x,$y,5,100,".($x+49).",".($y+49)."],";
						break;
					case 2:
						echo "[[[".($x+100).",".($y+6)."],[".($x+94).",$y],[".($x+100).",$y]],[".($x+49).",".($y+49)."]],";
						break;
					}
					break;
				case 2:
					switch ($j) {
					case 1:
						echo "[[[".($x+100).",".($y+6)."],[".($x+94).",$y],[".($x+100).",$y]],[".($x+49).",".($y+49)."]],";
						break;
					case 2:
						echo "[$x,".($y+95).",100,5,".($x+49).",".($y+49)."],";
						break;
					case 3:
						echo "[[[".($x+6).",$y],[$x,".($y+6)."],[$x,$y]],[".($x+49).",".($y+49)."]],";
						break;
					}
					break;
				case 3:
					switch ($j) {
					case 0:
						echo "[[[$x,".($y+94)."],[".($x+6).",".($y+100)."],[$x,".($y+100)."]],[".($x+49).",".($y+49)."]],";
						break;
					case 2:
						echo "[[[".($x+6).",$y],[$x,".($y+6)."],[$x,$y]],[".($x+49).",".($y+49)."]],";
						break;
					case 3:
						echo "[".($x+95).",$y,5,100,".($x+49).",".($y+49)."],";
						break;
					}
					break;
				case 4:
					if ($j === 3)
						echo "[[[$x,".($y+94)."],[".($x+6).",".($y+100)."],[$x,".($y+100)."]],[".($x+49).",".($y+49)."]],[[[$x,".($y+4)."],[".($x+54).",".($y+6)."],[".($x+81).",".($y+19)."],[".($x+94).",".($y+48)."],[".($x+96).",".($y+100)."],[".($x+100).",".($y+100)."],[".($x+100).",$y],[$x,$y]],[".($x+49).",".($y+49)."]],";
					break;
				case 5:
					if ($j === 0)
						echo "[[[".($x+94).",".($y+100)."],[".($x+100).",".($y+94)."],[".($x+100).",".($y+100)."]],[".($x+49).",".($y+49)."]],[[[".($x+4).",".($y+100)."],[".($x+6).",".($y+46)."],[".($x+19).",".($y+19)."],[".($x+48).",".($y+6)."],[".($x+100).",".($y+4)."],[".($x+100).",$y],[$x,$y],[$x,".($y+100)."]],[".($x+49).",".($y+49)."]],";
					break;
				case 6:
					if ($j === 1)
						echo "[[[".($x+100).",".($y+6)."],[".($x+94).",$y],[".($x+100).",$y]],[".($x+49).",".($y+49)."]],[[[".($x+100).",".($y+96)."],[".($x+46).",".($y+94)."],[".($x+19).",".($y+81)."],[".($x+6).",".($y+52)."],[".($x+4).",$y],[$x,$y],[$x,".($y+100)."],[".($x+100).",".($y+100)."]],[".($x+49).",".($y+49)."]],";
					break;
				case 7:
					if ($j === 2)
						echo "[[[".($x+6).",$y],[$x,".($y+6)."],[$x,$y]],[".($x+49).",".($y+49)."]],[[[".($x+96).",$y],[".($x+94).",".($y+54)."],[".($x+81).",".($y+81)."],[".($x+52).",".($y+94)."],[$x,".($y+96)."],[$x,".($y+100)."],[".($x+100).",".($y+100)."],[".($x+100).",$y]],[".($x+49).",".($y+49)."]],";
					break;
				case 8:
					if ($j === 3)
						echo "[$x,$y,100,5,".($x+49).",".($y+49)."],[$x,".($y+95).",100,5,".($x+49).",".($y+49)."],";
					break;
				case 9:
					if ($j === 2)
						echo "[$x,$y,5,100,".($x+49).",".($y+49)."],[".($x+95).",$y,5,100,".($x+49).",".($y+49)."],";
					break;
				case 10:
					if (!$j)
						echo "[$x,$y,100,100,NaN,NaN],";
					break;
				case 11:
					if ($j === 2)
						echo "[[[$x,".($y+6)."],[".($x+6).",$y],[$x,$y]],[".($x+49).",".($y+49)."]],[[[".($x+94).",$y],[".($x+100).",".($y+6)."],[".($x+100).",$y]],[".($x+49).",".($y+49)."]],[[[$x,".($y+94)."],[".($x+6).",".($y+100)."],[$x,".($y+100)."]],[".($x+49).",".($y+49)."]],[[[".($x+94).",".($y+100)."],[".($x+100).",".($y+94)."],[".($x+100).",".($y+100)."]],[".($x+49).",".($y+49)."]],";
					break;
				case 12:
					if ($j === 0)
						echo "[[[".($x+5).",".($y+100)."],[".($x+5).",".($y+27)."],[".($x+13).",".($y+13)."],[".($x+34).",".($y+7)."],[".($x+67).",".($y+7)."],[".($x+87).",".($y+13)."],[".($x+95).",".($y+27)."],[".($x+95).",".($y+100)."],[".($x+100).",".($y+100)."],[".($x+100).",$y],[$x,$y],[$x,".($y+100)."]],[".($x+49).",".($y+49)."]],";
					break;
				case 13:
					if ($j === 1)
						echo "[[[".($x+100).",".($y+95)."],[".($x+27).",".($y+95)."],[".($x+13).",".($y+87)."],[".($x+7).",".($y+66)."],[".($x+7).",".($y+33)."],[".($x+13).",".($y+13)."],[".($x+27).",".($y+5)."],[".($x+100).",".($y+5)."],[".($x+100).",$y],[$x,$y],[$x,".($y+100)."],[".($x+100).",".($y+100)."]],[".($x+49).",".($y+49)."]],";
					break;
				case 14:
					if ($j === 2)
						echo "[[[".($x+95).",$y],[".($x+95).",".($y+73)."],[".($x+87).",".($y+87)."],[".($x+66).",".($y+93)."],[".($x+33).",".($y+93)."],[".($x+13).",".($y+87)."],[".($x+5).",".($y+73)."],[".($x+5).",$y],[$x,$y],[$x,".($y+100)."],[".($x+100).",".($y+100)."],[".($x+100).",$y]],[".($x+49).",".($y+49)."]],";
					break;
				case 15:
					if ($j === 3)
						echo "[[[$x,".($y+5)."],[".($x+73).",".($y+5)."],[".($x+87).",".($y+13)."],[".($x+93).",".($y+34)."],[".($x+93).",".($y+67)."],[".($x+87).",".($y+87)."],[".($x+73).",".($y+95)."],[$x,".($y+95)."],[$x,".($y+100)."],[".($x+100).",".($y+100)."],[".($x+100).",$y],[$x,$y]],[".($x+49).",".($y+49)."]],";
					break;
				}
			}
			echo ']';
		}
		echo '],';
		echo '"sea":{"colors":{"water":"#A9EDE680","wave":"#CAFDFE80","foam":"#fff8"},"waves":';
		/*
		0 1
		2 3
		*/
		$graph = array(
			array( // 0
				'0.left' => array(
					'1.right',
					array(
						[[0,6],[100,6]],
						[[0,21],[25,25],[51,21],[76,25],[100,21]]
					)
				),
				'2.left' => array(
					'2.bottom',
					array(
						[[0,94],[6,100]],
						[[0,79],[15,85],[21,100]]
					)
				),
				'3.right' => array(
					'3.bottom',
					array(
						[[100,94],[94,100]],
						[[100,79],[85,84],[79,100]]
					)
				)
			), array( // 1
				'0.top' => array(
					'2.bottom',
					array(
						[[5,0],[5,100]],
						[[21,0],[25,25],[21,50],[25,75],[21,100]]
					)
				),
				'1.right' => array(
					'1.top',
					array(
						[[100,6],[94,0]],
						[[100,21],[85,15],[79,0]]
					)
				),
				'3.right' => array(
					'3.bottom',
					array(
						[[100,94],[94,100]],
						[[100,79],[85,85],[79,100]]
					)
				)
			), array( // 2
				'0.left' => array(
					'0.top',
					array(
						[[0,6],[6,0]],
						[[0,21],[15,15],[21,0]]
					)
				),
				'1.right' => array(
					'1.top',
					array(
						[[100,5],[95,0]],
						[[100,21],[83,15],[79,0]]
					)
				),
				'2.left' => array(
					'3.right',
					array(
						[[0,95],[100,95]],
						[[0,79],[25,75],[50,79],[75,75],[100,79]]
					)
				)
			), array( // 3
				'0.left' => array(
					'0.top',
					array(
						[[0,5],[5,0]],
						[[0,21],[17,15],[21,0]]
					)
				),
				'1.top' => array(
					'3.bottom',
					array(
						[[95,0],[95,100]],
						[[79,0],[75,25],[79,50],[75,75],[79,100]]
					)
				),
				'2.left' => array(
					'2.bottom',
					array(
						[[0,94],[6,100]],
						[[0,79],[15,85],[21,100]]
					)
				)
			)
		);
		$orientedGraph = array();
		foreach ($graph as $i => $graphPieces) {
			$orientedGraph[$i] = array();
			foreach ($graphPieces as $in => $out) {
				$orientedGraph[$i][$in] = $out;
				$waves = $out[1];
				foreach ($waves as &$wave)
					$wave = array_reverse($wave);
				$orientedGraph[$i][$out[0]] = array($in,$waves);
			}
		}
		$graph = $orientedGraph;
		function createSeaFromGraph(&$state) {
			global $graph, $arene;
			foreach ($state['graph'] as $i_ => &$stateGraph) {
				foreach ($stateGraph as $in => &$data) {
					$i = $i_;
					if (null === $data['waves']) {
						$j = count($state['sea']);
						$newSea = array(array(),array());
						do {
							$graphData = $graph[$arene["p$i"]][$in];
							$out = $graphData[0];
							$state['graph'][$i][$in]['waves'] = $j;
							$state['graph'][$i][$out]['waves'] = $j;
							$x = ($i%6)*100;
							$y = floor($i/6)*100;
							foreach ($graphData[1] as $k=>$wave) {
								foreach ($wave as &$pt) {
									$pt[0] += $x;
									$pt[1] += $y;
								}
								unset($pt);
								$newSea[$k] = array_merge($newSea[$k],$wave);
							}
							$dir = explode(".",$out);
							$newDir = $dir;
							$newI = $i;
							switch ($dir[1]) {
							case 'top':
								$newI -= 6;
								$newDir[0] += 2;
								$newDir[1] = 'bottom';
								break;
							case 'bottom':
								$newI += 6;
								$newDir[0] -= 2;
								$newDir[1] = 'top';
								break;
							case 'left':
								$newI--;
								$newDir[0]++;
								$newDir[1] = 'right';
								break;
							case 'right':
								$newI++;
								$newDir[0]--;
								$newDir[1] = 'left';
								break;
							}
							$in = implode(".",$newDir);
							if (isset($state['graph'][$newI][$in])) {
								$i = $newI;
								foreach ($graphData[1] as $k=>$wave)
									array_pop($newSea[$k]);
							}
							else {
								$newDir = $dir;
								switch ($dir[1]) {
								case 'top':
								case 'bottom':
									$newDir[0] += ($newDir[0]%2) ? -1:1;
									break;
								case 'left':
								case 'right':
									$newDir[0] += ($newDir[0]>=2) ? -2:2;
								}
								$in = implode(".",$newDir);
							}
						} while (null === $state['graph'][$i][$in]['waves']);
						$state['sea'][] = $newSea;
					}
					unset($j);
				}
				unset($data);
			}
			unset($stateGraph);
		}
		$state = array(
			'graph' => array(),
			'sea' => array()
		);
		for ($i=0;$i<36;$i++) {
			foreach ($graph[$arene["p$i"]] as $in => $out) {
				$state['graph'][$i][$in] = array(
					'waves' => null
				);
			}
		}
		createSeaFromGraph($state);
		//$state['sea'] = array($state['sea'][0]);
		//$state['sea'] = array_slice($state['sea'], 7,1);
		echo json_encode($state['sea']);
		echo '},';
		break;
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