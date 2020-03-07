<?php
if ($_SERVER['REMOTE_ADDR'] != '91.121.53.188') exit;
backup_tables('localhost','timsiteweb','62kin92z','timsiteweb',$_GET['table']);

/* backup the db OR just a table */
function backup_tables($host,$user,$pass,$name,$tables = '*')
{
	
	$link = mysql_connect($host,$user,$pass);
	mysql_select_db($name,$link);
	mysql_set_charset('utf8');
	
	//get all of the tables
	if($tables == '*')
	{
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}
	
	$bukSize = 10000;
	//cycle through
	foreach($tables as $table)
	{
		$i = 0;
		$keepboing = true;
		while ($keepboing) {
			$result = mysql_query('SELECT * FROM '.$table.' LIMIT '.($i*$bukSize).','.$bukSize);
			$num_fields = mysql_num_fields($result);

			$keepboing = false;

			while($row = mysql_fetch_row($result))
			{
				$keepboing = true;
				echo  'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j < $num_fields; $j++) 
				{
					if (isset($row[$j])) { echo  '"'.str_replace("\n","\\n",addslashes($row[$j])).'"' ; } else { echo  '""'; }
					if ($j < ($num_fields-1)) { echo  ','; }
				}
				echo  ");\n";
			}
			$i++;
		}
	}
	exit;
}
?>