<?php
require_once 'vendor/autoload.php';

use components\BroadcastAPI;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$logger = new Logger('send_media_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/sent_notifications.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!empty($_POST))
    {
        $api = new BroadcastAPI();
        try {
            $result = $api->sendMessage($_POST['groupUid'], '',true, $_POST['file'], $_POST['filename'], $_POST['caption']);
            if ($result)
            {
                $logger->info('Media was successfully sent to Threema');
            } else {
                $logger->error('Failed to send media to Threema');
            }
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $logger->error($e->getMessage());
        }
    }
} else {
    $logger->warning('Wrong method used');
}
