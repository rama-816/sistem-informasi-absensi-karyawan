<?php
// logout.php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/helpers.php';
session_destroy();
redirect('login.php');
