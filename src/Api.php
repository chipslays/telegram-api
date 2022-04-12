<?php

namespace Telegram;

use Telegram\Traits\BotApiMethods;
use CurlHandle;
use Exception;

class Api
{
    use BotApiMethods;

    protected ?CurlHandle $client = null;

    public function __construct(protected string $token)
    {
        $this->client = curl_init();
        curl_setopt($this->client, CURLOPT_ENCODING, '');
        curl_setopt($this->client, CURLOPT_TIMEOUT, 3600);
        curl_setopt($this->client, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->client, CURLOPT_RESOLVE, ['api.telegram.org:443:api.telegram.org']);
        curl_setopt($this->client, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($this->client, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
        curl_setopt($this->client, CURLOPT_POST, true);
    }

    /**
     * Request to Telegram Bot Api method.
     *
     * @param string $method
     * @param array $parameters
     * @return Response
     *
     * @throws Exception
     */
    public function method(string $method, array $parameters = []): Response
    {
        curl_setopt($this->client, CURLOPT_URL, 'https://api.telegram.org/bot' . $this->token . '/' . $method);
        curl_setopt($this->client, CURLOPT_POSTFIELDS, $parameters);

        $output = curl_exec($this->client);

        if (curl_error($this->client)) {
            throw new Exception(sprintf('Curl error code %d, %s', curl_errno($this->client), curl_error($this->client)));
        }

        $response = new Response(json_decode($output, true));

        if ($response->hasError()) {
            dump([
                'method' => $method,
                'parameters' => $parameters,
                'response' => $response->all(),
            ]);
        }

        return $response;
    }
}