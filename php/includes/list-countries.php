<option value="">----------------------------</option>
<?php
$getCountries = mysql_query('SELECT code,name_'. ($language ? 'en':'fr') .' AS name FROM mkcountries ORDER BY ordering,name');
$selCountry = isset($_POST['country']) ? $_POST['country']:(isset($selCountry)?$selCountry:'');
while ($country = mysql_fetch_array($getCountries))
	echo '<option value="'. $country['code'] .'"'. ($selCountry==$country['code'] ? ' selected="selected"':'') .'>'. htmlspecialchars($country['name']) .'</option>';
?>