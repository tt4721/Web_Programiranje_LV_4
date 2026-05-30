<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/functions.php';

logoutUser();
session_start();
flashSet('success', 'Uspješno ste se odjavili.');
header('Location: index.php');
exit;
