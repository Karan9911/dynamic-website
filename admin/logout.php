<?php
session_start();
require_once '../includes/functions.php';

logoutAdmin();
header('Location: login.php');
exit;
?>