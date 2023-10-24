<?php

namespace App\Utilities;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Pinata
{
    private $client;

    function __construct(string $apiKey, string $secretKey)
    {
        $client = new Client([
                'base_uri' => 'https://api.pinata.cloud',
                RequestOptions::HEADERS => [
                    'pinata_api_key' => $apiKey,
                    'pinata_secret_api_key' => $secretKey,
                ],
            ]
        );

        $this->client = $client;
    }

    function pinFileToIPFS(string $filePath, array $metadata = null): array
    {
        $options = [
            RequestOptions::MULTIPART => [
                [
                    'Content-Type' => 'image/png',
                    'name'     => 'file',
                    'contents' => fopen($filePath, 'rb'),
                ],
            ],
        ];
        if (!empty($metadata)) {
            $options[RequestOptions::MULTIPART][] = [
                'name'     => 'pinataMetadata',
                'contents' => json_encode($metadata),
            ];
        }

        $response = $this->client->post('/pinning/pinFileToIPFS', $options);
        return json_decode($response
            ->getBody()->getContents(), true);
    }

    function pinJSONToIPFS(array $json, array $metadata = null): array
    {
        $content = ($metadata) ? ['pinataMetadata' => $metadata, 'pinataContent' => $json] : $json;
        return $this->doCall('/pinning/pinJSONToIPFS', 'POST', $content);
    }

    private function doCall(string $endpoint, string $method = 'POST', array $params = []): array
    {
        $response = $this->client->request($method, $endpoint,
            [
                RequestOptions::JSON => $params,
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}