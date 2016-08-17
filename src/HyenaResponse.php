<?php
/**
 * Created by PhpStorm.
 * User: artycake
 * Date: 8/15/16
 * Time: 15:37
 */
namespace Jeroenherczeg\Hyena;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DomCrawler\Crawler;

class HyenaResponse
{
    /** @var Crawler */
    private $crawler;
    /** @var string */
    private $uri;
    /** @var array */
    private $availableFields = ['name', 'images'];
    private $names = [];
    private $namesString = '';
    private $defaultOptions = [
        'min_image_width'    => 50,
        'min_image_height'   => 50,
        'min_image_filesize' => 16,
    ];
    private $currentOptions = [];

    /**
     * HyenaResponse constructor.
     *
     * @param string $uri
     * @param ResponseInterface $response
     */
    public function __construct($uri, ResponseInterface $response)
    {
        $content = $response->getBody()->getContents();
        $this->crawler = new Crawler($content);
        $this->uri = $uri;
    }

    /**
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function extract(array $fields, array $options = [])
    {
        $result = [];
        $this->currentOptions = array_merge($this->defaultOptions, $options);
        foreach ($fields as $field) {
            if (!is_string($field)) {
                // TODO: throw error
            }
            if (!in_array($field, $this->availableFields)) {
                // TODO: throw error
            }
            $result[$field] = $this->getFieldFromResponse($field);
        }

        return $result;
    }

    private function getFieldFromResponse($field)
    {
        switch ($field) {
            case 'name':
                return $this->getSiteName();
                break;
            case 'images':
                return $this->getSiteImages();
                break;
            default:
                return null;
        }
    }

    private function getSiteImages()
    {
        $images = [];
        $imageNodes = $this->crawler->filter('img');
        foreach ($imageNodes as $content) {
            $imageNode = new Crawler($content);
            $src = $imageNode->attr('src');
            if (strpos($src, 'http') !== 0) {
                $src = trim($this->uri, '/') . '/' . trim($src, '/');
                echo "src: " . $src . "\n";
            }
            $image = new \Imagick();
            try {
                $image->pingImage($src);
                if (
                    $image->getImageWidth() < $this->currentOptions['min_image_width'] ||
                    $image->getImageHeight() < $this->currentOptions['min_image_height'] ||
                    $image->getImageSize() < $this->currentOptions['min_image_filesize']
                ) {
                    continue;
                }
            } catch (Exception $e) {
                echo $e->getMessage() . "\n";
                continue;
            }
            $images[] = $src;
        }

        return $images;
    }

    private function getSiteName()
    {
        $nameFinder = new HyenaNameFinder($this->crawler, $this->uri);

        return $nameFinder->getName();
    }
}
