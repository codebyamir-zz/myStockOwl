<?php

require('Notifier.php');
require('Inventory.php');


$db['host']   = 'localhost';
$db['user']   = 'user';
$db['pass']   = '';
$db['dbname'] = 'stockowl';

// DB connection
$pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],$db['user'], $db['pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Logger
$logger = new \Monolog\Logger('stockowl_notify');
$file_handler = new \Monolog\Handler\StreamHandler("/var/www/html/stockowl/logs/app-notify.log");
$logger->pushHandler($file_handler);

$notify = new Notifier($pdo,$logger);
$notify->expireSubscriptions();

foreach ($notify->listSubscriptions() as $s)
{
	$info = new Inventory($s['api_url'],$logger);

	$count = $info->countStockIKEA($s['store_code'],$s['product_number']);
	$notify->updateSubscription($s['id']);

	if ($count > 0)
	{
		$notify->closeSubscription($s);
	}

}

?>
