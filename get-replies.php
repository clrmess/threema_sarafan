<?php
require_once 'vendor/autoload.php';

use components\BroadcastAPI;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('get_replies_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/get-replies.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!empty($_POST))
    {
        $api = new BroadcastAPI();
        try {
            $result = $api->getMessages($_POST['groupUid'], $_POST['fromDate']);
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
}
else {
    $logger->warning('Wrong method used');
}
