<?php


namespace components;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SarafanApi
{
    private string $baseUrl = 'https://sarafan.me/';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
        ]);
    }

    /**
     * @param string $message Reply text
     * @param int $reviewId Review ID
     * @return bool
     * @throws GuzzleException
     */
    public function sendReply(string $message, int $reviewId) :bool
    {
        $response = $this->client->post(
            "threema/reply",
            [
                'form_params' => [
                    'reviewId' => $reviewId,
                    'message' => $message
                ]
            ]
        );
        return $response->getStatusCode() === 200;
    }

    public function getNotifications() :array
    {
        $response = $this->client->get(
            "threema/get-notifications"
        );
        return json_decode($response->getBody());
    }
}