<?php
// Forward to public/index.php
// This file is at the root to ensure Vercel includes the entire project directory in the Lambda function!
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/public/index.php';
