<?php
require_once '../config/db.php';

session_destroy();
session_unset();

$_SESSION['success'] = 'You have been logged out successfully';
redirect('../index.php');
