<?php
/**
 * Created by PhpStorm.
 * User: artycake
 * Date: 8/17/16
 * Time: 13:14
 */
namespace Jeroenherczeg\Hyena;

use Symfony\Component\DomCrawler\Crawler;

class HyenaNameFinder
{
    /** @var Crawler */
    private $crawler;
    /** @var string */
    private $uri;
    /** @var string */
    private $domain;
    /** @var array */
    private $names = [];

    /**
     * HyenaNameFinder constructor.
     *
     * @param Crawler $crawler
     * @param string $uri
     */
    public function __construct(Crawler $crawler, $uri)
    {
        $this->crawler = $crawler;
        $this->uri = $uri;
        preg_match('/https?:\/\/(www.)?([a-zA-Z0-9.-]+)(?=\.[a-zA-Z{2,3}])/', $this->uri, $parts);
        $this->domain = $parts[2];
    }

    /**
     * Return name parsed from website
     *
     * @return string
     */
    public function getName()
    {
        if ($name = $this->getNameFromOgSiteName()) {
            return $name;
        }
        if ($name = $this->getNameFromTitle()) {
            return $name;
        }
        $this->collectNameStrings();
        $parts = $this->getMostRelevantKeys();
        if (count($parts) == 0) {
            $parts = preg_split('/[\s_,.-]+/', strtolower($this->domain));
        }

        return $this->createNameFromParts($parts);
    }

    /**
     * Add name to names' array and increase count if repeats
     *
     * @param $name
     */
    private function addName($name)
    {
        $name = str_replace('-', ' ', str_slug($name));
        if (!isset($this->names[$name])) {
            $this->names[$name] = ['count' => 0, 'name' => mb_strtolower($name)];
        }
        $this->names[$name]['count']++;
    }

    /**
     * Look for domain name inside title (common case)
     * in case of success return part of title similar to domain name
     * otherwise add title to name arrays and return null
     *
     * @param string $domain
     * @param string $title
     * @return null|string
     */
    private function extractNameFromTitle($domain, $title)
    {
        $pattern = '/[^a-z0-9]+/';
        $compressedDomain = preg_replace($pattern, '', strtolower($domain));
        $compressedTitle = preg_replace($pattern, '', strtolower($title));
        // Title contains no letters or numbers
        if (!$compressedTitle) {
            return null;
        }
        // Title contains only domain name
        if (strpos($compressedDomain, $compressedTitle) !== false) {
            return $this->clearName($title);
        }
        /** @var bool $fromStart - if title starts with domain name */
        $fromStart = strpos($compressedTitle, $compressedDomain) !== 0;
        while (strpos($title, ' ') !== false && $compressedDomain != $compressedTitle) {
            if ($fromStart) {
                $title = substr($title, strpos($title, ' '));
            } else {
                $title = substr($title, 0, strrpos($title, ' '));
            }
            $title = $this->clearName($title);
            $compressedTitle = preg_replace($pattern, '', strtolower($title));
            if (strpos($compressedTitle, $compressedDomain) === 0) {
                $fromStart = false;
            }
        }
        // Remaining part of title equals domain name
        if ($compressedDomain == $compressedTitle) {
            $clearTitle = $this->clearName($title);
            if (strtolower($clearTitle) === $clearTitle && strpos('.', $clearTitle) === false) {
                return $this->createNameFromParts(explode(' ', $clearTitle));
            }

            return $clearTitle;
        }
        // Title contains domain name with domain zone
        if ($fromStart == false) {
            if (strpos($title, '.') !== false) {
                return $this->clearName($title);
            }
        }
        // Domain consists of multiple parts, i.e. subdomains
        // Try to find part of title equals each of part of domain name
        if (strpos($domain, '.') !== false) {
            $domainParts = explode('.', $domain);
            $resultTitle = null;
            while (count($domainParts) && $resultTitle == null) {
                $resultTitle = $this->extractNameFromTitle(array_shift($domainParts), $title);
            }
            if ($resultTitle) {
                return $resultTitle;
            }
        }

        return null;
    }

    /**
     * Compare all names' strings and return most common words
     *
     * @return array
     */
    private function getMostRelevantKeys()
    {
        usort($this->names, function ($a, $b) {
            if ($a['count'] == $b['count']) {
                return 0;
            }

            return $a['count'] > $b['count'] ? -1 : 1;
        });
        $keys = [];
        $keysByName = [];
        if (count($this->names)) {
            foreach ($this->names as $name) {
                $stringName = $name['name'];
                if (!isset($keysByName[$stringName])) {
                    $keysByName[$stringName] = [];
                }
                $keysByName[$stringName] = preg_split('/[\s_,.-]+/', strtolower($stringName));
                foreach ($this->names as $nameToCompare) {
                    $_keys = preg_split('/[\s_,.-]+/', strtolower($nameToCompare['name']));
                    $newKeys = array_intersect($keysByName[$stringName], $_keys);
                    if (count($newKeys)) {
                        $keysByName[$stringName] = $newKeys;
                    }
                }
            }
            usort($keysByName, function ($a, $b) {
                return count($a) > count($b) ? -1 : 1;
            });
            $resultKeys = $keysByName[0];
            foreach ($keysByName as $_keys) {
                $newResultKeys = array_intersect($resultKeys, $_keys);
                if (count($newResultKeys)) {
                    $resultKeys = $newResultKeys;
                }
            }
            $keys = $resultKeys;
        }

        return array_unique($keys);
    }

    /**
     * Look for "og:site_name" meta tag inside page
     * Most relevant name of site defined by owner
     * Return null in case of tag is missing or contains empty value
     *
     * @return null|string
     */
    private function getNameFromOgSiteName()
    {
        $ogSiteNameNode = $this->crawler->filter('[property="og:site_name"]');
        if (iterator_count($ogSiteNameNode)) {
            $nameFromOgTag = $this->clearName($ogSiteNameNode->attr('content'));
            if ($nameFromOgTag) {
                return $nameFromOgTag;
            }
        }

        return null;
    }

    /**
     * Trim all non-significant characters from given string
     *
     * @param string $name
     * @return string
     */
    private function clearName($name)
    {
        $convertedName = urlencode($name);
        // spaces
        $convertedName = str_replace(
            ['%7F', '%81', '%81', '%C5%8D', '%8F', '%C2%90', '%C2%A0', '%0A', '%09'],
            '+',
            $convertedName
        );
        // hyphen
        $convertedName = str_replace(['%E2%80%93'], '-', $convertedName);
        // vertical bar
        $convertedName = str_replace(['%C7%80'], '%7C', $convertedName);
        $name = urldecode($convertedName);
        $clearName = trim($name, ' -_|&:.,•»');

        return $clearName;
    }

    /**
     * Look for title tag
     * Convert title value into latin symbols
     * Try to extract site name from title
     *
     * @return null|string
     */
    private function getNameFromTitle()
    {
        $titleNode = $this->crawler->filter('title');
        if (count($titleNode)) {
            $title = trim($titleNode->text());
            $title = str_replace('-', ' ', str_slug($title));
            $extractedName = $this->extractNameFromTitle($this->domain, $title);
            if ($extractedName) {
                return $extractedName;
            }
            $this->addName($title);
        }
    }

    /**
     * Collect values of all meaningful tags on the page
     * Add found values to names' array
     */
    private function collectNameStrings()
    {
        // Value from H1 tag
        $hNode = $this->crawler->filter('h1');
        if (count($hNode)) {
            foreach ($hNode as $content) {
                $crawler = new Crawler($content);
                $text = trim($crawler->text());
                $this->addName($text);
            }
        }
        // Title or content from link to main page
        $mainPageLinksNode = $this->crawler->filter('a[href="/"], a[href="' . $this->uri . '"], a[href="' . $this->uri . '/"]');
        if (count($mainPageLinksNode)) {
            foreach ($mainPageLinksNode as $content) {
                $linkNode = new Crawler($content);
                $text = trim($linkNode->attr('title'));
                $this->addName($text);
                $text = trim($linkNode->text());
                $this->addName($text);
            }
        }
        //Title or alt from images with logo image
        $logoNode = $this->crawler->filter('.logo img');
        if (count($logoNode)) {
            if ($nameFromLogo = trim($logoNode->attr('title'))) {
                $this->addName($nameFromLogo);
            }
            if ($nameFromLogo = trim($logoNode->attr('alt'))) {
                $this->addName($nameFromLogo);
            }
        }
        //Title or alt from images with logo image
        $logoNode = $this->crawler->filterXPath('.//*[@src[contains(.,\'logo\')]]');
        if (count($logoNode)) {
            if ($nameFromLogo = trim($logoNode->attr('title'))) {
                $this->addName($nameFromLogo);
            }
            if ($nameFromLogo = trim($logoNode->attr('alt'))) {
                $this->addName($nameFromLogo);
            }
        }
        // Value from copyright meta tag
        $copyrightNode = $this->crawler->filterXPath('.//meta[@name[contains(.,\'copyright\')]]');
        if (iterator_count($copyrightNode)) {
            // Clear from copyright sign, year and "By whom"
            $nameFromCopyright = trim(preg_replace('/(©|\d{4}|\..*|\sby.*|copyright)/i', '', $copyrightNode->attr('content')));
            if ($nameFromCopyright) {
                $this->addName($nameFromCopyright);
            }
        }
        // Value from copyright string, commonly in the end of the page
        $copyrightNode = $this->crawler->filterXPath('.//*[text()[contains(.,\'©\')]]');
        if (iterator_count($copyrightNode)) {
            // Clear from copyright sign, year and "By whom"
            $nameFromCopyright = trim(preg_replace('/(©|\d{4}|\..*|\sby.*|copyright)/i', '', $copyrightNode->text()));
            if ($nameFromCopyright) {
                $this->addName($nameFromCopyright);
            }
        }
    }

    /**
     * Convert each word in array to ucfirst
     * Merge words in string trim all non-significant characters
     *
     * @param array $parts
     * @return string
     */
    private function createNameFromParts(array $parts)
    {
        $name = implode(' ', array_map(function ($part) {
            return ucfirst($part);
        }, $parts));

        return $this->clearName($name);
    }
}
