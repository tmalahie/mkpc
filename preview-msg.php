<div id="fMessages" class="preview-msg">
<?php
$message = array('auteur' => $id, 'infoDate' => '', 'message' => '');
include('avatars.php');
include('bbCode.php');
print_forum_msg($message,false);
?>
</div>