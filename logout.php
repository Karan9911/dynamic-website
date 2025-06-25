<?php
session_start();
require_once 'includes/functions.php';

logoutUser();
header('Location: index.php');
exit;
?>