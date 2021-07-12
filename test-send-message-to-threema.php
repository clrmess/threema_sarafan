<?php

use components\BroadcastAPI;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'vendor/autoload.php';

$api = new BroadcastAPI();
$logger = new Logger('test_threema_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/test-threema.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());
try {
    $result = $api->sendMessage('', 'Test message');
    if ($result)
    {
        $logger->info('Successfully sent message to Threema | Review ID = 106871');
    }
    else {
        $logger->error('Error while sending message to Threema | Review ID = 106871');
    }
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    $logger->error($e->getMessage());

}