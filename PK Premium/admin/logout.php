<?php
// admin/logout.php
session_start();
session_destroy();
require_once '../config.php';
redirect('login.php');
?>