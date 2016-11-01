<?php

abstract class Logger {
    protected $logger;
    public function __construct($db = NULL,$logger = NULL) {
		$logger = new \Monolog\Logger('stockowl_logger');
    	$file_handler = new \Monolog\Handler\StreamHandler("../../../logs/app.log");
    	$logger->pushHandler($file_handler);
    	return $logger;
    }


