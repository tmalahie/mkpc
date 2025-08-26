<?php
$configFile = __DIR__.'/../../railConfig.json';
if (isset($_GET['reset']))
    @unlink($configFile);
if (is_file($configFile)) {
	$config = json_decode(file_get_contents($configFile), true);
} else {
	$config = array(
        'mini-turbo-activation' => 5,
        'super-turbo-activation' => 25,
        'mini-turbo-duration' => 15,
        'super-turbo-duration' => 30,
        'rail-speed' => 7.5,
        'angle-similarity' => 0.6,
    );
}
$updated = false;
foreach ($config as $key => $value) {
    if (isset($_POST[$key])) {
        $config[$key] = $_POST[$key];
        $updated = true;
    }
}
if ($updated)
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
?>
<html>
<head>
    <title>Rail Config</title>
    <style>
        label {
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>Rail Config</h1>
    <form method="post" action="railConfig.php">
        <label>Mini Turbo Activation (in frames)
        <input type="number" name="mini-turbo-activation" value="<?= $config['mini-turbo-activation'] ?>"></label>
        <label>Super Turbo Activation (in frames)
        <input type="number" name="super-turbo-activation" value="<?= $config['super-turbo-activation'] ?>">
        </label>
        <label>Mini Turbo Duration (in frames)
        <input type="number" name="mini-turbo-duration" value="<?= $config['mini-turbo-duration'] ?>">
        </label>
        <label>Super Turbo Duration (in frames)
        <input type="number" name="super-turbo-duration" value="<?= $config['super-turbo-duration'] ?>">
        </label>
        <label>Rail Speed (in px/frames)
        <input type="number" name="rail-speed" step="0.1" value="<?= $config['rail-speed'] ?>">
        </label>
        <label>Required angle similarity (0-1)
        <input type="number" name="angle-similarity" step="0.01" value="<?= $config['angle-similarity'] ?>">
        </label>
        <input type="submit" value="Save">&nbsp;
        <a href="?reset" onclick="return confirm('Reset the config?');">Reset to default</a>
    </form>
    <a href="index.php">Back</a>
</body>
</html>