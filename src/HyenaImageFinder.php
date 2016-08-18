<?php
/**
 * Created by PhpStorm.
 * User: artycake
 * Date: 8/18/16
 * Time: 10:38
 */
namespace Jeroenherczeg\Hyena;

use Symfony\Component\DomCrawler\Crawler;

class HyenaImageFinder
{
    /** @var Crawler */
    private $crawler;
    /** @var string */
    private $uri;

    /**
     * HyenaImageFinder constructor.
     *
     * @param Crawler $crawler
     * @param string $uri
     */
    public function __construct($crawler, $uri)
    {
        $this->crawler = $crawler;
        $this->uri = $uri;
    }

    /**
     * Look for img tags, check images by links and return links if images match options
     *
     * @param array $options
     * @return array
     */
    public function getImages(array $options)
    {
        $images = [];
        $imageNodes = $this->crawler->filter('img');
        foreach ($imageNodes as $content) {
            if (count($images) > $options['limit_images']) {
                return $images;
            }
            $imageNode = new Crawler($content);
            $src = $imageNode->attr('src');
            if (!$src) {
                continue;
            }
            if (strpos($src, 'http') !== 0) {
                $src = trim($this->uri, '/') . '/' . trim($src, '/');
                $src = strtolower($src);
            }
            $image = new \Imagick();
            try {
                $image->pingImage($src);
                if (
                    $image->getImageWidth() < $options['min_image_width'] ||
                    $image->getImageHeight() < $options['min_image_height'] ||
                    $image->getImageSize() < $options['min_image_filesize']
                ) {
                    continue;
                }
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n";
                continue;
            }
            $images[] = $src;
        }

        return $images;
    }
}
