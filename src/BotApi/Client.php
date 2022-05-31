<?php

namespace Telegram\BotApi;

use Telegram\BotApi\Traits\Methods as BotApiMethods;
use Telegram\Exceptions\CurlException;
use Telegram\Exceptions\BotApiException;

class Client
{
    use BotApiMethods;

    protected array $predifinedDefaultParameters = [
        'sendMessage' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'copydMessage' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'sendPhoto' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'sendAudio' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'sendDocument' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'sendVideo' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'sendAnimation' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'sendVoice' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'sendVideoNote' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
            'protect_content' => false,
            'allow_sending_without_reply' => true,
        ],
        'editMessageText' => [
            'parse_mode' => 'html',
            'disable_web_page_preview' => true,
        ],
        'editMessageCaption' => [
            'parse_mode' => 'html',
        ],
    ];

    /**
     * Consturct.
     *
     * @param string $token
     * @param array $defaultParameters
     */
    public function __construct(protected string $token, protected array $defaultParameters = [])
    {
        $this->defaultParameters = array_replace_recursive($this->predifinedDefaultParameters, $defaultParameters);

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
     * @param string $method
     * @param array $parameters
     * @return Response
     *
     * @throws BotApiException
     */
    public function method(string $method, array $parameters = []): Response
    {
        $parameters = array_replace_recursive(
            $this->defaultParameters['global'] ?? [],
            $this->defaultParameters[$method] ?? [],
            $parameters,
        );

        curl_setopt($this->client, CURLOPT_URL, 'https://api.telegram.org/bot' . $this->token . '/' . $method);
        curl_setopt($this->client, CURLOPT_POSTFIELDS, $parameters);

        $output = curl_exec($this->client);

        if (curl_error($this->client)) {
            throw new CurlException(
                sprintf('cURL error: [%d] %s', curl_errno($this->client), curl_error($this->client))
            );
        }

        $response = new Response(json_decode($output, true));

        if ($response->hasError()) {
            throw new BotApiException(
                sprintf('Telegram: [%d] %s', $response->error_code, $response->description), $response->error_code
            );
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return self
     */
    public function setToken($token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get default parameters.
     *
     * @return array
     */
    public function getDefaultParameters(): array
    {
        return $this->defaultParameters;
    }

    /**
     * Set default parameters.
     *
     * @return self
     */
    public function setDefaultParameters(array $defaultParameters): self
    {
        $this->defaultParameters = $defaultParameters;

        return $this;
    }

    /**
     * Append (merge) with current default parameters.
     *
     * Example:
     *  [
     *      'sendMessage' => [
     *          'parse_mode' => 'html',
     *      ],
     *  ]
     * @param array $parameters
     * @return self
     */
    public function appendDefaultParameters(array $parameters): self
    {
        $this->defaultParameters = array_replace_recursive($this->defaultParameters, $parameters);

        return $this;
    }
}