<?php

namespace components;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


class BroadcastAPI
{
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
     * @param int $fromDate Unix timestamp
     * @param int $page Page of the group messages list
     * @param int $pageSize
     * @return false
     * @throws GuzzleException
     */
    public function getMessages(
        string $groupUid,
        int $fromDate,
        int $page = 1,
        int $pageSize = 2
    ): bool
    {
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
                $messageDate = strtotime($message['createdAt']);
                if ($messageDate < $fromDate) continue;
                $actualMessage = $this->getMessage($groupUid, $message['id']);
                if ($actualMessage)
                {
                    $messageData = \GuzzleHttp\json_decode($actualMessage, true);
                    if ($messageData['type'] === 'text')
                    {
                        $text = $messageData['body'];
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
            }
            $pagingCount = $decodedContent['_paging']['count'];
            $pagingTotal = $decodedContent['_paging']['total'];
            $pagingPage = $decodedContent['_paging']['page'];
            $this->maxPage = ceil($pagingTotal/$pagingCount);
            $nextPage = ++$pagingPage;
            while ($nextPage <= $this->maxPage)
            {
                $this->getMessages($groupUid, $fromDate, $nextPage, $pageSize);
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