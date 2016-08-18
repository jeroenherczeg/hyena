<?php
namespace Jeroenherczeg\Hyena;

use Guzzle\Http\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class Hyena
{
    /**
     * Return response object based on visited url
     *
     * @param string $uri
     * @return HyenaResponse|null
     */
    public function visit($uri)
    {
        $lastUri = '';
        try {
            $client = new Client(['base_uri' => $uri]);
            $response = $client->request('GET', '/', [
                'debug'           => false,
                'connect_timeout' => 5,
                'allow_redirects' => [
                    'track_redirects' => true
                ],
                'headers'         => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36'
                ]
            ]);
            $uris = explode(', ', $response->getHeaderLine('X-Guzzle-Redirect-History'));
            $lastUri = end($uris);
            if ($lastUri) {
                preg_match('/(https?:\/\/[^\/]+)/', $lastUri, $parts);
                $lastUri = $parts[1];
            }
        } catch (RequestException $e) {
            echo $e->getResponse() . "\n";

            return null;
        } catch (ConnectException $e) {
            echo $e->getResponse() . "\n";

            return null;
        }
        if ($lastUri == '') {
            $lastUri = $uri;
        }

        return new HyenaResponse($lastUri, $response);
    }
}
