<?php

use components\BroadcastAPI;
use components\SarafanApi;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'vendor/autoload.php';

$api = new SarafanApi();
$threemaApi = new BroadcastAPI();
$logger = new Logger('notifications_from_sarafan_log');
$logger->pushHandler(new StreamHandler(__DIR__.'/logs/notifications_from_sarafan.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());
try {
    $result = $api->getNotifications();
    if ($result)
    {
        foreach ($result as $item)
        {
            $threemaApi->sendMessage($threemaApi->groupsDict[$item->group_id],$item->message_text);
            if (!empty($item->img))
            {
                $threemaApi->sendMessage($threemaApi->groupsDict[$item->group_id], '',true, $item->img, $item->img_name, $item->caption);
            }
            if (!empty($item->audio))
            {
                $threemaApi->sendMessage($threemaApi->groupsDict[$item->group_id], '',true, $item->audio, $item->audio_name, $item->caption);
            }
            if (!empty($item->video))
            {
                $threemaApi->sendMessage($threemaApi->groupsDict[$item->group_id], '',true, $item->video, $item->video_name, $item->caption);
            }
        }
        $logger->info('Successfully got notifications');
    }
    else {
        $logger->error('Error while getting notifications');
    }
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    $logger->error($e->getMessage());
}