<?php
$config = require '../config.php';
require_once '../core/Router.php';
require_once '../core/Database.php';

Database::init($config['db']);
Router::route();

?>