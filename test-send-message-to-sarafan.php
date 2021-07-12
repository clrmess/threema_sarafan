<?php

use components\SarafanApi;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'vendor/autoload.php';

$api = new SarafanApi();
$logger = new Logger('test_sarafan_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/test-sarafan.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());
try {
    $result = $api->sendReply('Test reply', 108795);
    if ($result)
    {
        $logger->info('Successfully sent message to Sarafan | Review ID = 108795');
    }
    else {
        $logger->error('Error while sending message to Sarafan | Review ID = 108795');
    }
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    $logger->error($e->getMessage());
}