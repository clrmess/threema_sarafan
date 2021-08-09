<?php

namespace components;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class BroadcastAPI
{
    public array $groupsDict = [
        3281 => '', #Вільногірський елеватор в'їзд
        3282 => '', #Вільногірський елеватор Бухгалтерія
        3283 => '', #Вільногірський елеватор Лабораторія
        3284 => '', #Вільногірський елеватор Вагова
        3285 => '', #Пришибский елеватор В'їзд
        3286 => '', #Пришибский елеватор Бухгалтерія
        3287 => '', #Пришибский елеватор Лабораторія
        3288 => '', #Пришибский елеватор Вагова
        3289 => '', #Ингулецький елеватор В'їзд
        3290 => '', #Ингулецький елеватор Бухгалтерія
        3291 => '', #Ингулецький елеватор Лабораторія
        3292 => '', #Ингулецький елеватор Вагова
        3293 => '', #Варварівський елеватор В'їзд
        3294 => '', #Варварівський елеватор Бухгалтерія
        3295 => '', #Варварівський елеватор Лабораторія
        3296 => '', #Варварівський елеватор Вагова
        3297 => '', #Роздорський Елеватор В'їзд
        3298 => '', #Роздорський Елеватор Бухгалтерія
        3299 => '', #Роздорський Елеватор Лабораторія
        3300 => '', #Роздорський Елеватор Вагова
    ];
    private string $baseUrl = 'https://broadcast.threema.ch/api/v1/';
    private string $apiKey = '';
    private string $broadcastUid = '';
    private int $maxPage;
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'X-API-Key' => $this->apiKey
            ]
        ]);
    }

    /**
     * Function to send message to Threema group
     *
     * @param string $groupUid Uid of the group
     * @param string $message Content of the message (Max 3500 bytes)
     * @param bool $type false - for text | true - for file
     * @param string $file Base64 encoded filedata (Max 50 megabytes)
     * @param string $fileName Name of the file (Max 255 bytes)
     * @param string $caption Caption of the file (Max 1000 bytes)
     * @return bool true if response code was 201 (Created) else false
     * @throws GuzzleException
     */
    public function sendMessage(
        string $groupUid,
        string $message = '',
        bool $type = false,
        string $file = '',
        string $fileName = '',
        string $caption = ''
    ) :bool
    {
        $groupUid = $this->groupsDict[$groupUid];

        $response = $type === false ? $this->client->post(
            "identities/$this->broadcastUid/groups/$groupUid/chat",
            [
                'json' => [
                    'type' => 'text',
                    'body' => $message
                ]
            ]
        ) : $this->client->post(
            "identities/$this->broadcastUid/groups/$groupUid/chat",
            [
                'json' => [
                    'type' => 'file',
                    'file' => $file,
                    'filename' => $fileName,
                    'caption' => $caption
                ]
            ]
        );

        return $response->getStatusCode() == 201;
    }

    /**
     * Function to get Threema group message history
     *
     * @param string $groupUid Uid of the group
     * @param int $page Page of the group messages list
     * @param int $pageSize
     * @return false
     * @throws GuzzleException
     */
    public function getMessages(
        string $groupUid,
        int $page = 0,
        int $pageSize = 2
    ): bool
    {
        $groupUid = $this->groupsDict[$groupUid];
        $response = $this->client->get(
            "identities/$this->broadcastUid/groups/$groupUid/chat",
            [
                'query' => [
                    'page' => $page,
                    'pageSize' => $pageSize
                ]
            ]
        );
        if ($response->getStatusCode() === 200)
        {
            $content = $response->getBody()->getContents();
            $decodedContent = \GuzzleHttp\json_decode($content, true);
            foreach ($decodedContent['messages'] as $message)
            {

                if ($message['type'] === 'text')
                {
                    $text = $message['body'];
                    preg_match('/№(\d+)/', $text, $output_array);
                    if (count($output_array) == 2)
                    {
                        $reviewId = $output_array[1];
                        $answer = preg_replace('/(Звернення №).(\d+).(Відповідь: )/', '', $text);
                        $sarafanApi = new SarafanApi();
                        $sarafanApi->sendReply($answer, $reviewId);
                    }
                }
            }
            $pagingCount = $decodedContent['_paging']['count'];
            $pagingTotal = $decodedContent['_paging']['total'];
            $pagingPage = $decodedContent['_paging']['page'];
            $this->maxPage = ceil($pagingTotal/$pagingCount);
            $nextPage = ++$pagingPage;
            while ($nextPage <= $this->maxPage)
            {
                $this->getMessages($groupUid, $nextPage, $pageSize);
            }
        }
        else {
            return false;
        }
    }

    /**
     * Function to get single message from
     *
     * @param string $groupUid Uid of the group
     * @param string $messageUid Uid of the group message
     * @return false|string false if fail (Invalid X-Api-Key|Group not found) | json if success
     * @throws GuzzleException
     */
    public function getMessage(string $groupUid, string $messageUid)
    {
        $groupUid = $this->groupsDict[$groupUid];

        $response = $this->client->get(
            "identities/$this->broadcastUid/groups/$groupUid/chat/$messageUid"
        );
        if ($response->getStatusCode() === 200)
        {
            return $response->getBody()->getContents();
        } else {
            return false;
        }
    }


}