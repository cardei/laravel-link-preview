<?php

namespace Cardei\LinkPreview\Readers;

use Cardei\LinkPreview\Contracts\LinkInterface;
use Cardei\LinkPreview\Contracts\ReaderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\ConnectException; // Importar correctamente ConnectException
use GuzzleHttp\Exception\RequestException;  // Importar RequestException para manejar errores adicionales
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;

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
            'allow_redirects' => ['max' => 1000],
            'cookies' => $this->jar,
            'connect_timeout' => 60, // Tiempo para conectar al servidor
            'timeout' => 60, // Tiempo total permitido para la solicitud en segundos
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ];
    
        // Agregar un stack de middleware para los intentos
        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retries, $request, $response, $exception) {
            return $retries < 3 && ($exception instanceof ConnectException || ($response && $response->getStatusCode() >= 500));
        }, function ($retries) {
            return 1000 * pow(2, $retries); // Retraso exponencial: 1s, 2s, 4s
        }));
        
    
        $this->config['handler'] = $handlerStack;
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
            $this->client = new Client($this->config);
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
        $client = $this->getClient();

        try {
            $response = $client->request('GET', $link->getUrl(), array_merge($this->config, [
                'on_stats' => function (TransferStats $stats) use (&$link) {
                    $link->setEffectiveUrl($stats->getEffectiveUri());
                }
            ]));

            $link->setContent($response->getBody())
                ->setContentType($response->getHeader('Content-Type')[0] ?? null);
                
        } catch (ConnectException $e) {
            // Manejo de excepción de conexión
            $link->setContent(false)->setContentType(false);
        } catch (RequestException $e) {
            // Manejo de otras excepciones de solicitud HTTP
            $link->setContent(false)->setContentType(false);
            Log::error('RequestException encountered: ' . $e->getMessage(), ['url' => $link->getUrl()]);
        }

        return $link;
    }
}
