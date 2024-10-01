<?php

namespace Cardei\LinkPreview\Readers;

use Cardei\LinkPreview\Contracts\LinkInterface;
use Cardei\LinkPreview\Contracts\ReaderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;


/**
 * Class HttpReader
 */
class HttpReader implements ReaderInterface
{
    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var array $config
     */
    private $config;

    /**
     * @var CookieJar $jar
     */
    private $jar;

    /**
     * HttpReader constructor.
     * @param array|null $config
     */
    public function __construct($config = null)
    {
        $this->jar = new CookieJar();

        $this->config = $config ?: [
            'allow_redirects' => ['max' => 10],
            'cookies' => $this->jar,
            'connect_timeout' => 5,
            'headers' => [
                'User-Agent' => 'Cardei/link-preview v1.2'
            ]
        ];
    }

    /**
     * @param int $timeout
     */
    public function setTimeout($timeout)
    {
        $this->config(['connect_timeout' => $timeout]);
    }

    /**
     * @param array $parameters
     */
    public function config(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            $this->config[$key] = $value;
        }
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new Client();
        }

        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function readLink(LinkInterface $link)
    {

        // TEMPORARY: Increase memory and time limits
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        // Añadir el User-Agent en la petición usando Guzzle
        $client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0'
            ],
            'timeout' => 60, 
            'connect_timeout' => 30,
        ]);

        try {
            $response = $client->request('GET', $link->getUrl());
            $content = $response->getBody()->getContents();

            $link->setContent($content);
            return $link;

        } catch (\Exception $e) {
            // Manejo de errores
            if (config('link-preview.enable_logging') && config('app.debug')) {
                Log::debug('Error while parsing YouTube link: ' . $link->getUrl(), ['error' => $e->getMessage()]);
            }
            // Ignore exceptions for now
            // throw new \Exception("Error fetching the link: " . $e->getMessage());
        }
    }

}
