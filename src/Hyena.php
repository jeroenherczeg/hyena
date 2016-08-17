<?php
namespace Jeroenherczeg\Hyena;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class Hyena
{
    /**
     * Create a new Skeleton Instance
     */
    public function __construct()
    {
        // constructor body
    }

    public function visit($uri)
    {
        try {
            $client = new Client(['base_uri' => $uri]);
            $response = $client->request('GET', '/', [
                'debug'           => false,
                'connect_timeout' => 5,
                'headers'         => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36'
                ]
            ]);
        } catch (ConnectException $e) {
            return null;
        }

        return new HyenaResponse($uri, $response);
    }
}
