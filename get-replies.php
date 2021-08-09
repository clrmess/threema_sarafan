<?php
require_once 'vendor/autoload.php';

use components\BroadcastAPI;
use components\SarafanApi;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('get_replies_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/get-replies.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());

$api = new BroadcastAPI();
$sarafanApi = new SarafanApi();

foreach ($api->groupsDict as $sarafanId => $threemaId)
{
    try {
        $result = $api->getMessages($threemaId);
        if ($result)
        {
            $logger->info('Threema successfully returned messages');
        }
        else {
            $logger->error('Error returned by Threema');
        }
    } catch (GuzzleException $e) {
        $logger->error($e->getMessage());
    }
}

