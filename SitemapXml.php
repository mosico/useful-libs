<?php
/**
 * Created by PhpStorm.
 * User: mmo
 * Date: 11/28/2014
 * Time: 3:09 PM
 */

class SitemapXml
{
    private $siteUrl = 'http://www.investopedia.com/';
    private $sitemapUri = '/';
    private $sitemapFileNameTpl = 'sitemap{num}.xml';
    private $indexFileName = 'sitemap_index.xml';
    private $savePath = __DIR__;
    private $totalFiles = 15;
    private $videoSitemapFile = 'sitemap_video.xml';

    const XML_TYPE_SITEMAP = 'sitemap';               //sitemap.xml
    const XML_TYPE_SITEMAP_INDEX = 'sitemap_index';   //sitemap_index.xml
    const XML_TYPE_VIDEO_SITEMAP = 'sitemap_video';   //sitemap_index.xml

    public function __construct($config)
    {
        if (!empty($config['siteUrl'])) {
            $this->siteUrl = $config['siteUrl'];
            if (substr($this->siteUrl, -1, 1) !== '/') {
                $this->siteUrl .= '/';
            }
        }
        if (!empty($config['sitemapUri'])) {
            $this->sitemapUri = $config['sitemapUri'];
        }
        if (!empty($config['sitemapFileNameTpl'])) {
            $this->sitemapFileNameTpl = $config['sitemapFileNameTpl'];
        }
        if (!empty($config['indexFileName'])) {
            $this->indexFileName = $config['indexFileName'];
        }
        if (!empty($config['savePath'])) {
            $this->savePath = rtrim($config['savePath'], '/');
        }
        if (!empty($config['totalFiles'])) {
            $this->totalFiles = (int) $config['totalFiles'];
        }
        if (!empty($config['videoSitemapName'])) {
            $this->videoSitemapFile = $config['videoSitemapName'];
        }
    }

    public function generateSitemap($nodes)
    {
        if (empty($nodes) || !is_array($nodes)) {
            return;
        }

        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlStr .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($nodes as $node) {
            $url = $this->fmtUrl($node->Url);
            $date = $node->SiteDate;
            $xmlStr .= "  <url><loc>{$url}</loc><lastmod>{$date}</lastmod></url>" . PHP_EOL;
        }

        $xmlStr .= '</urlset>' . PHP_EOL;
        return $xmlStr;
    }

    public function generateVideo($nodes)
    {
        if (empty($nodes) || !is_array($nodes)) {
            return;
        }

        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlStr .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . PHP_EOL;

        $nodes = $this->fmtVideoItems($nodes);
        foreach ($nodes as $node) {
            $xmlStr .= "  <url>" . PHP_EOL;
            $xmlStr .= "    <loc>{$node['loc']}</loc>" . PHP_EOL;
            $xmlStr .= "    <video:video>" . PHP_EOL;
            $xmlStr .= "      <video:thumbnail_loc>{$node['urijpg']}</video:thumbnail_loc>" . PHP_EOL;
            $xmlStr .= "      <video:title>{$node['title']}</video:title>" . PHP_EOL;
            $xmlStr .= "      <video:description>{$node['body']}</video:description>" . PHP_EOL;
            $xmlStr .= "      <video:content_loc>{$node['urivideo']}</video:content_loc>" . PHP_EOL;
            $xmlStr .= "      <video:publication_date>{$node['sitedate']}</video:publication_date>" . PHP_EOL;
            $xmlStr .= "      <video:category>{$node['categoryname']}</video:category>" . PHP_EOL;
            $xmlStr .= "    </video:video>" . PHP_EOL;
            $xmlStr .= "  </url>" . PHP_EOL;
        }

        $xmlStr .= '</urlset>' . PHP_EOL;
        return $xmlStr;
    }

    public function generateIndex($files)
    {
        if (empty($files) || !is_array($files)) {
            return;
        }

        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xmlStr .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($files as $item) {
            $url = $this->fmtUrl($item['file'], self::XML_TYPE_SITEMAP_INDEX);
            $date = $item['date'];
            $xmlStr .= "  <sitemap><loc>{$url}</loc><lastmod>{$date}</lastmod></sitemap>" . PHP_EOL;
        }

        $xmlStr .= '</sitemapindex>' . PHP_EOL;
        return $xmlStr;
    }

    public function fmtVideoItems($videos)
    {
        $items = array();
        if (empty($videos) || !is_array($videos)) return $items;
        foreach ($videos as $v) {
            $item['loc'] = $this->fmtUrl($v['loc']);
            $item['urijpg'] = $this->encodeUrl(str_replace('public://', 'http://', $v['urijpg']));
            $item['urivideo'] = $this->encodeUrl(str_replace('public://', 'http://', $v['urivideo']));
            $item['title'] = $v['title'];
            $item['categoryname'] = $v['categoryname'];
            $item['body'] = trim(strip_tags(html_entity_decode($v['body'], ENT_QUOTES | ENT_XHTML)));
            $item['sitedate'] = date('Y-m-d\TH:i:s-07:00', $v['sitedate']);
            $items[] = $item;
        }
        return $items;
    }

    public function fmtUrl($uri, $type=null)
    {
        if ($type == self::XML_TYPE_SITEMAP_INDEX) {
            $fineName = substr($uri, strrpos($uri, DIRECTORY_SEPARATOR) + 1);
            $uri = $this->sitemapUri . $fineName;
        }
        $uri = ltrim($uri, '/\\');
        $uri = $this->encodeUrl($uri);
        $url = $this->siteUrl . $uri;
        return $url;
    }

    public function encodeUrl($url)
    {
        $encodedUrl = '';
        $url = trim($url);
        $length = strlen($url);
        if ($length < 0) return $encodedUrl;

        for ($i = 0; $i < $length; $i++) {
            $char = $url{$i};
            $charOrd = ord($char);
            if ($charOrd == 32) {
                //hard code for ' '
                $encodedUrl .= '%20';
            } elseif ($charOrd != 38 & $charOrd > 32 && $charOrd < 127) {
                // 38 = '&', need encode
                $encodedUrl .= $char;
            } else {
                $encodedUrl .= urlencode($char);
            }
        }

        $encodedUrl = htmlspecialchars($encodedUrl, ENT_QUOTES | ENT_XHTML, 'UTF-8');
        return $encodedUrl;
    }

    public function saveSitemap($xmlStr, $num)
    {
        $file = $this->getFilePath(self::XML_TYPE_SITEMAP, $num);
        $rs = $this->saveXMLFile($file, $xmlStr);
        return $rs ? $file : false;
    }

    public function saveIndex($xmlStr)
    {
        $file = $this->getFilePath(self::XML_TYPE_SITEMAP_INDEX);
        $rs = $this->saveXMLFile($file, $xmlStr);
        return $rs ? $file : false;
    }

    public function saveVideo($xmlStr)
    {
        $file = $this->getFilePath(self::XML_TYPE_VIDEO_SITEMAP);
        $rs = $this->saveXMLFile($file, $xmlStr);
        return $rs ? $file : false;
    }

    public function saveXMLFile($file, $xmlStr)
    {
        $isSuc = false;
        if (file_exists($file)) {
            unlink($file);
        }
        if ($this->isGZFile($file)) {
            $zp = gzopen($file, 'wb9');
            if ($zp) {
                gzwrite($zp, $xmlStr);
                gzclose($zp);
                $isSuc = true;
            }
        } else {
            $fp = fopen($file, 'w');
            if ($fp) {
                fwrite($fp, $xmlStr, strlen($xmlStr));
                fclose($fp);
                $isSuc = true;
            }
        }
        return $isSuc;
    }

    public function clearSitemapFiles()
    {
        $files = array();
        $files[] = $this->getFilePath(self::XML_TYPE_SITEMAP_INDEX);
        $files[] = $this->getFilePath(self::XML_TYPE_VIDEO_SITEMAP);
        for ($i = 1; $i < $this->totalFiles; $i++) {
            $files[] = $this->getFilePath(self::XML_TYPE_SITEMAP, $i);
        }
        foreach ($files as $file) {
            if (file_exists($file) && is_writable($file)) {
                unlink($file);
            }
        }
    }

    public function getFilePath($type, $num=1)
    {
        $num = (int) $num;
        $filePath = $this->savePath . DIRECTORY_SEPARATOR;
        if ($type == self::XML_TYPE_SITEMAP) {
            $filePath .= str_replace('{num}', $num, $this->sitemapFileNameTpl);
        } elseif ($type == self::XML_TYPE_SITEMAP_INDEX) {
            $filePath .= $this->indexFileName;
        } elseif ($type == self::XML_TYPE_VIDEO_SITEMAP) {
            $filePath .= $this->videoSitemapFile;
        } else {
            $filePath .= str_replace('{num}', $num, $this->sitemapFileNameTpl);
        }
        return $filePath;
    }

    public function isGZFile($file)
    {
        $isGZ = false;
        $ext = substr($file, strrpos($file, '.'));
        if ($ext == '.gz') {
            $isGZ = true;
        }
        return $isGZ;
    }
}