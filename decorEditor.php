<?php
include('language.php');
include('getId.php');
include('initdb.php');
require_once('utils-decors.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/decor-editor.css" />
<title><?php echo $language ? 'Decor editor':'Éditeur de décors'; ?></title>
</head>
<body>
<h2><?php echo $language ? 'Decor editor':'Éditeur de décors'; ?></h2>
    <div class="decors-list-container">
        Work in progress
    </div>
    <div class="decors-bottom">
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- Mario Kart PC -->
        <ins class="adsbygoogle"
            style="display:inline-block;width:468px;height:60px"
            data-ad-client="ca-pub-1340724283777764"
            data-ad-slot="6691323567"></ins>
        <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
</body>
</html>
<?php
mysql_close();
?>
