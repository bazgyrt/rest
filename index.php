<?php
ini_set('display_errors', 1);

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/application/bootstrap.php');

use core\Route;

Route::start();