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
    private $defaultOptions = [
        'min_image_width'    => 50,
        'min_image_height'   => 50,
        'min_image_filesize' => 16,
        'limit_images'       => 10
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
     * Try to extract requested data from given uri
     *
     * @param array $fields
     * @param array $options
     * @return array
     * @throws HyenaParamsExtension
     */
    public function extract(array $fields, array $options = [])
    {
        $result = [];
        $this->currentOptions = array_merge($this->defaultOptions, $options);
        foreach ($fields as $field) {
            if (!is_string($field)) {
                throw new HyenaParamsExtension('Field name should be a string. ' . gettype($field) . ' is given.');
            }
            if (!in_array($field, $this->availableFields)) {
                throw new HyenaParamsExtension('Field "' . $field . '" not available for request. Available fields: ' . implode(', ', $this->availableFields));
            }
            $result[$field] = $this->getFieldFromResponse($field);
        }

        return $result;
    }

    /**
     * Return value for requested field
     *
     * @param string $field
     * @return array|null|string
     */
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

    /**
     * Collect and return images matching options
     *
     * @return array
     */
    private function getSiteImages()
    {
        $nameFinder = new HyenaImageFinder($this->crawler, $this->uri);

        return $nameFinder->getImages($this->currentOptions);
    }

    /**
     * Find and return name of website
     *
     * @return string
     */
    private function getSiteName()
    {
        $nameFinder = new HyenaNameFinder($this->crawler, $this->uri);

        return $nameFinder->getName();
    }
}
