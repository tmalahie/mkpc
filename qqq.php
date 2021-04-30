<?php
if($_SERVER['HTTP_HOST']!=='local-mkpc.malahieude.info') exit;
if (isset($_GET['p'])) {
    $p = +$_GET['p'];
    $isBattle = isset($_GET['battle']);
    $id = 7000+$p;
    include('initdb.php');
    if ($circuit = mysql_fetch_array(mysql_query('SELECT c.*,d.data FROM `circuits` c LEFT JOIN `circuits_data` d ON c.id=d.id WHERE c.id='.$id))) {
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
    $circuitMainData = $circuitPayload->main;
    function printCodeInput($txt) {
        echo '<input type="text" onfocus="this.select()" value="'. htmlspecialchars($txt) .'" style="width:500px" />';
    }
    function formatCircuitData($json,$suffix=',') {
        $res = json_encode($json);
        $res = preg_replace('#\[(\d+),(\d+)#', '[".($x+$1).",".($y+$2)."', $res);
        $res = preg_replace('#".\((\$\w)\+0\)."#', '$1', $res);
        $res = preg_replace('#^\[(.+)\]$#', '$1', $res).$suffix;
        return $res;
    }
    function formatFlowData($json,$suffix=',',$l=null) {
        $njson = $json;
        if (null !== $l) {
            foreach ($json as $i=>&$data) {
                $l0 = hypot($data[1][0],$data[1][1]);
                $data[1][0] *= $l/$l0;
                $data[1][1] *= $l/$l0;
            }
            unset($data);
        }
        foreach ($json as $i=>&$data) {
            $data[1][0] = round($data[1][0],2);
            $data[1][1] = round($data[1][1],2);
        }
        unset($data);
        foreach ($json as $i=>$ignored)
            $njson[$i][1] = '__'.$i.'__';
        $res = formatCircuitData($njson,'');
        $res = preg_replace_callback('#"__(\d+)__"#', function ($matches) use($json) {
            return json_encode($json[$matches[1]][1]);
        }, $res).$suffix;
        return $res;
    }
    function formatCircuitPos($d,$v) {
        return $d ? '".('.('$'.$v).'+'.$d.')."':('$'.$v);
    }
?>
collision: <?php
	foreach ($circuitPayload->collision as &$collisionData) {
		if (isset($collisionData[3]) && is_numeric($collisionData[3])) {
			$collisionData[2]++;
			$collisionData[3]++;
		}
	}
    unset($collisionData);
    printCodeInput(formatCircuitData($circuitPayload->collision));
?>,<br />
<?php
	foreach ($circuitPayload->horspistes as $type=>&$hpsData) {
        echo 'horspistes['.$type.']: ';
		foreach ($hpsData as &$hpData) {
			if (isset($hpData[3]) && is_numeric($hpData[3])) {
				$hpData[2]++;
				$hpData[3]++;
			}
		}
        unset($hpData);
        printCodeInput(formatCircuitData($hpsData));
        echo ',<br />';
	}
	unset($hpsData);
?>
<?php
	foreach ($circuitPayload->trous as $i=>&$trousData) {
        if (!empty($trousData)) {
            echo 'trous['.$i.']: ';
            $replace = null;
            if ($isBattle) {
                $x = 49;
                $y = 49;
                $replace = '$u,$v';
            }
            else {
                switch ($p) {
                case 0:
                    $x = 49;
                    $y = 11;
                    break;
                case 1:
                    $x = 11;
                    $y = 49;
                    break;
                case 2:
                    $x = 49;
                    $y = 88;
                    break;
                case 3:
                    $x = 88;
                    $y = 49;
                    break;
                case 4:
                    $x = 34;
                    $y = 65;
                    break;
                case 5:
                    $x = 65;
                    $y = 65;
                    break;
                case 6:
                    $x = 65;
                    $y = 34;
                    break;
                case 7:
                    $x = 34;
                    $y = 34;
                    break;
                case 10:
                    $x = 49;
                    $y = 49;
                    break;
                default:
                    $replace = '$replace';
                }
            }
            if (!$replace) {
                $x = formatCircuitPos($x,'x');
                $y = formatCircuitPos($y,'y');
                $replace = $x.','.$y;
            }
            $str = '';
            foreach ($trousData as &$trouData) {
                if (isset($trouData[0][3]) && is_numeric($trouData[0][3])) {
                    $trouData[0][2]++;
                    $trouData[0][3]++;
                    $trouData = array_merge($trouData[0],$trouData[1]);
                    $str .= '['.formatCircuitPos($trouData[0],'x').','.formatCircuitPos($trouData[1],'y').','.$trouData[2].','.$trouData[3].','.$replace.'],';
                }
                else {
                    $str .= '[['.formatCircuitData($trouData[0],'').'],['.$replace.']],';
                }
            }
            unset($trouData);
            printCodeInput($str);
            echo ',<br />';
        }
	}
	unset($trousData);
    if (!empty($circuitPayload->flows)) {
        echo 'flows: ';
		foreach ($circuitPayload->flows as &$flowData) {
			if (isset($flowData[3]) && is_numeric($flowData[3])) {
				$flowData[2]++;
				$flowData[3]++;
			}
		}
        unset($flowData);
        printCodeInput(formatFlowData($circuitPayload->flows,',',7));
        echo ',<br />';
	}
	unset($hpsData);
?>
<?php
    }
    mysql_close();
}
?>