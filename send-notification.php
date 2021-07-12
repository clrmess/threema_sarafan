<?php
require_once 'vendor/autoload.php';

use components\BroadcastAPI;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('send_notification_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/sent_notifications.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!empty($_POST))
    {
        $api = new BroadcastAPI();
        try {
            $result = $api->sendMessage($_POST['groupUid'], $_POST['message']);
            if ($result)
            {
                $logger->info('Notification successfully sent to Threema');
            }
            else {
                $logger->error('Failed to send notification to Threema');
            }
        } catch (GuzzleException $e) {
            $logger->error($e->getMessage());

        }
    }
} else {
    $logger->warning('Wrong method used');
}
