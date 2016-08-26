<?php

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
        $imageNodes = $this->crawler->filter('[property="og:image"]');
        foreach ($imageNodes as $content) {
            if (count($images) > $options['limit_images']) {
                return $images;
            }
            $imageNode = new Crawler($content);
            $src = $imageNode->attr('content');
            if (!$src) {
                continue;
            }
            if ($this->checkImage($src, $options)) {
                $images[] = $src;
            }
        }
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
            if ($this->checkImage($src, $options)) {
                $images[] = $src;
            }
        }

        return $images;
    }

    private function checkImage($src, $options)
    {
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
                return false;
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";

            return false;
        }

        return true;
    }
}
