<div id="fMessages" class="preview-msg">
<?php
$message = array('id' => 0, 'auteur' => $id, 'date' => '', 'infoDate' => '', 'message' => '');
include('avatars.php');
include('bbCode.php');
print_forum_msg($message,false);
?>
</div>