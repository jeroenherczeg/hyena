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
        preg_match('/https?:\/\/(www)?([a-zA-Z0-9._-]*)(?=\.[a-zA-Z{2,3}])/', $this->uri, $parts);
        $this->domain = $parts[2];
    }

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

    private function addName($name, $increase = 1)
    {
        $name = str_replace(['®'], '', $name);
        if (!isset($this->names[$name])) {
            $this->names[$name] = ['count' => 0, 'name' => mb_strtolower($name)];
        }
        $this->names[$name]['count'] += $increase;
    }

    private function extractNameFromTitle($domain, $title)
    {
        $pattern = '/[^a-z0-9]+/';
        $compressedDomain = preg_replace($pattern, '', strtolower($domain));
        $compressedTitle = preg_replace($pattern, '', strtolower($title));
        if (!$compressedTitle) {
            return null;
        }
        if (strpos($compressedDomain, $compressedTitle) !== false) {
            return $title;
        }
//        var_dump($compressedTitle, $compressedDomain);
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
        if ($compressedDomain == $compressedTitle) {
            $clearTitle = $this->clearName($title);
            if (strtolower($clearTitle) === $clearTitle && strpos('.', $clearTitle) === false) {
                return $this->createNameFromParts(explode(' ', $clearTitle));
            }

            return $clearTitle;
        }
        if ($fromStart == false) {
            if (strpos($title, '.') !== false) {
                return $this->clearName($title);
            }
        }
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

    private function clearName($name)
    {
        $convertedName = urlencode($name);
        $convertedName = str_replace(
            ['%7F', '%81', '%81', '%C5%8D', '%8F', '%C2%90', '%C2%A0', '%0A', '%09'],
            '+',
            $convertedName
        );
        $convertedName = str_replace(['%E2%80%93'], '-', $convertedName);
        $convertedName = str_replace(['%C7%80'], '%7C', $convertedName);
        $name = urldecode($convertedName);
        $clearName = trim($name, ' -_|&:.,•»');

        return $clearName;
    }

    private function getNameFromTitle()
    {
        $titleNode = $this->crawler->filter('title');
        if (count($titleNode)) {
            $title = trim($titleNode->text());
            $extractedName = $this->extractNameFromTitle($this->domain, $title);
            if ($extractedName) {
                return $extractedName;
            }
            $this->addName($title);
        }
    }

    private function collectNameStrings()
    {
        $hNode = $this->crawler->filter('h1');
        if (count($hNode)) {
            foreach ($hNode as $content) {
                $crawler = new Crawler($content);
                $text = trim($crawler->text());
//                echo 'From H tag: ' . $text . "\n";
                $this->addName($text);
            }
        }
        $mainPageLinksNode = $this->crawler->filter('a[href="/"], a[href="' . $this->uri . '"], a[href="' . $this->uri . '/"]');
        if (count($mainPageLinksNode)) {
            foreach ($mainPageLinksNode as $content) {
                $linkNode = new Crawler($content);
                $text = trim($linkNode->attr('title'));
//                echo 'From link[title]: ' . $text . "\n";
                $this->addName($text);
                $text = trim($linkNode->text());
//                echo 'From link[text]: ' . $text . "\n";
                $this->addName($text);
            }
        }
        $logoNode = $this->crawler->filter('.logo img');
        if (count($logoNode)) {
            if ($nameFromLogo = trim($logoNode->attr('title'))) {
//                echo 'From logo: ' . $nameFromLogo . "\n";
                $this->addName($nameFromLogo);
            }
        }
        $logoNode = $this->crawler->filterXPath('.//*[@src[contains(.,\'logo\')]]');
        if (count($logoNode)) {
            if ($nameFromLogo = trim($logoNode->attr('title'))) {
//                echo 'From logo image[title] : ' . $nameFromLogo . "\n";
                $this->addName($nameFromLogo);
            }
            if ($nameFromLogo = trim($logoNode->attr('alt'))) {
//                echo 'From logo image[alt] : ' . $nameFromLogo . "\n";
                $this->addName($nameFromLogo);
            }
        }
        $copyrightNode = $this->crawler->filterXPath('.//meta[@name[contains(.,\'copyright\')]]');
        if (iterator_count($copyrightNode)) {
            $nameFromCopyright = trim(preg_replace('/(©|\d{4}|\..*|\sby.*|copyright)/i', '', $copyrightNode->attr('content')));
            if ($nameFromCopyright) {
//                echo 'From copyright [meta]: ' . $nameFromCopyright . "\n";
                $this->addName($nameFromCopyright);
            }
        }
        $copyrightNode = $this->crawler->filterXPath('.//*[text()[contains(.,\'©\')]]');
        if (iterator_count($copyrightNode)) {
            $nameFromCopyright = trim(preg_replace('/(©|\d{4}|\..*|\sby.*|copyright)/i', '', $copyrightNode->text()));
            if ($nameFromCopyright) {
//                echo 'From copyright [footer]: ' . $nameFromCopyright . "\n";
                $this->addName($nameFromCopyright);
            }
        }
    }

    private function createNameFromParts($parts)
    {
        $name = implode(' ', array_map(function ($part) {
            return ucfirst($part);
        }, $parts));

        return $this->clearName($name);
    }
}
