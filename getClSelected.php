<?php
session_start();
echo $_SESSION['clselected'];
unset($_SESSION['clselected']);