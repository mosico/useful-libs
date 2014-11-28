<?php
/**
 * Created by PhpStorm.
 * User: mmo
 * Date: 11/28/2014
 * Time: 3:12 PM
 */

class GZip
{
    private $txtFile = 'sitemap2.xml';
    private $gzFile = 'sitemap2.xml.gz';

    public function __construct($txtFile = '', $gzFile = '')
    {
        if (!empty($txtFile)) {
            $this->txtFile = $txtFile;
        }
        if (!empty($gzFile)) {
            $this->gzFile = $gzFile;
        }
    }

    public function compress()
    {
        $zp = gzopen($this->gzFile, 'wb9');
        gzwrite($zp, file_get_contents($this->txtFile));
        gzclose($zp);
        echo 'Compress Done.';
    }

    public function read()
    {
        $zp = gzopen($this->gzFile, 'r');
        do {
            $content = gzread($zp, 1000);
            echo $content;
        } while ($content);
        gzclose($zp);
    }

    public function readFile()
    {
        $r = readgzfile($this->gzFile);
        var_dump($r);
    }
}


$gzip = new GZip();
// $gzip->compress();
$gzip->read();
// $gzip->readFile();