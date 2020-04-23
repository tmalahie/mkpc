<?php
if($_SERVER['HTTP_HOST']!=='local-mkpc.malahieude.info') exit;
if (isset($_GET['p'])) {
    $p = +$_GET['p'];
    $id = 7000+$p;
    include('initdb.php');
    if ($circuit = mysql_fetch_array(mysql_query('SELECT c.*,d.data FROM `circuits` c LEFT JOIN `circuits_data` d ON c.id=d.id WHERE c.id='.$id))) {
	$circuitPayload = json_decode(gzuncompress($circuit['data']));
    $circuitMainData = $circuitPayload->main;
    function printCodeInput($txt) {
        echo '<input type="text" onfocus="this.select()" value="'. htmlspecialchars($txt) .'" style="width:500px" />';
    }
    function formatCircuitData($json) {
        $res = json_encode($json);
        $res = preg_replace('#\[(\d+),(\d+)#', '[".($x+$1).",".($y+$2)."', $res);
        $res = preg_replace('#".\((\$\w)\+0\)."#', '$1', $res);
        $res = preg_replace('#^\[(.+)\]$#', '$1', $res).',';
        return $res;
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
            foreach ($trousData as &$trouData) {
                if (isset($trouData[0][3]) && is_numeric($trouData[0][3])) {
                    $trouData[0][2]++;
                    $trouData[0][3]++;
                    $trouData = array_merge($trouData[0],$trouData[1]);
                }
            }
            unset($trouData);
            printCodeInput(formatCircuitData($trousData));
            echo ',<br />';
        }
	}
	unset($trousData);
?>
<?php
    }
    mysql_close();
}
?>