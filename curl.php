<?php

class Curl
{
// 	static $cookies = 'JSESSIONID=979256BD7971862DE8C4EEA1D43ACFF2; BIGipServerotn=1356398858.64545.0000';
	private $saveFile = 'curlCookie.txt';
	
	private $ch = null;
	private $url = '';
	private $options = array();
	private $isPost = false;
	private $fields = array();
	private $cookies = array();
	private $isReturnHeader = false;
	private $headerString = '';
	
	private $defaultOptions = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
		CURLOPT_REFERER => 'https://kyfw.12306.cn/otn/confirmPassenger/initDc',
		CURLOPT_SSL_VERIFYPEER => false,
	);
	
	public function __construct($url = null)
	{
		$this->setUrl($url);
		$this->options = $this->defaultOptions;
		$this->ch = curl_init();
	}
	
	public function setUrl($url, $queryStr = '')
	{
		$isSetUrl = false;
		if (!empty($url)) {
			$url = trim($url);
			if ($url) {
				$this->url = $url;
				$isSetUrl = true;
			}
		}
		if (!empty($queryStr) && $this->url) {
			$isSetUrl = true;
		}
		if ($isSetUrl) {
			if (!empty($queryStr)) {
				$pre = stripos($this->url, '?') == null ? '?' : '&';
				$this->url .= $pre . $queryStr;
			}
			$this->setOption(CURLOPT_URL, $this->url);
		}
		return $this;
	}
	
	public function isReturnHeader($isReturn = false)
	{
		$this->setOption(CURLOPT_HEADER, $isReturn);
		//if ($isReturn) {
		//	$this->setOption(CURLOPT_VERBOSE, 1);
		//}
		return $this;
	}
	
	public function setPostField($field, $val = null)
	{
		$this->isPost = true;
		$this->setField($field, $val);
		return $this;
	}
	
	public function setGetField($field, $val = null)
	{
		$this->isPost = false;
		$this->setField($field, $val);
		return $this;
	}
	
	private function setField($field, $val = null)
	{
		if (is_array($field)) {
			foreach ($field as $n => $v) {
				$this->fields[$n] = $v;
			}
		} else {
			$this->fields[$field] = $val;
		}
	}
	
	public function setCookie($cookie, $val  = null)
	{
		if (is_array($cookie)) {
			foreach ($cookie as $n => $v) {
				$this->cookies[$n] = $v;
			}
		} else {
			$this->cookies[$cookie] = $val;
		}
		return $this;
	}
	
	public function resetOptions()
	{
		$this->options = $this->defaultOptions;
		$this->fields = array();
		$this->cookies = array();
		return $this;
	}
	
	public function emptyOptions()
	{
		$this->options = array();
		$this->fields = array();
		$this->cookies = array();
		return $this;
	}
	
	public function setOption($option, $val = null)
	{
		if (is_array($option)) {
			foreach ($option as $n => $v) {
				if ($n == CURLOPT_HEADER) {
					$v = (bool) $v;
					$this->isReturnHeader = $v;
				}
				$this->options[$n] = $v;
			}
		} else {
			if ($option == CURLOPT_HEADER) {
				$val = (bool) $val;
				$this->isReturnHeader = $val;
			}
			$this->options[$option] = $val;
		}
		return $this;
	}
	
	public function getOptions()
	{
		return $this->options;
	}
	
	public function fetch($url = null)
	{
		$queryStr = '';
		
		if (!empty($this->cookies)) {
			$this->setOption(CURLOPT_COOKIE, $this->getCookieString());
		}
		if (!empty($this->fields)) {
			if ($this->isPost) {
				$this->setOption(CURLOPT_POSTFIELDS, $this->getQueryString());
				$this->setOption(CURLOPT_POST, count($this->fields));
			} else {
				$queryStr = $this->getQueryString();
			}
		}
		
		$this->setUrl($url, $queryStr);
		if (!$this->ch) {
			$this->ch = curl_init();
		}
		curl_setopt_array($this->ch, $this->options);
		
		$content = curl_exec($this->ch);
		if ($this->isReturnHeader) {
			$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
			$this->headerString = substr($content, 0, $header_size);
			$content = substr($content, $header_size);
		}
		return $content;
	}
	
	public function getResponseHeaders()
	{
		$headers = array();
		$arr = explode("\n", $this->headerString);
		$count = count($arr);
		for ($i = 0; $i < $count; $i++) {
			$tmpStr = trim($arr[$i]);
			if (empty($tmpStr)) {
				continue;
			}
			if ($i == 0) {
				$headers['status'] = explode(' ', $tmpStr)[1];
				continue;
			}
			list($key, $val) = explode(': ', $tmpStr);
			$headers[$key] = $val;
		}
		return $headers;
	}
	
	public function getResponseCookies()
	{
		$cookies = array();
		$matchs = array();
		$patt = '/Set-Cookie:\s*([^=]+)=([^;|\n]+)/i';
		preg_match_all($patt, $this->headerString, $matchs, PREG_SET_ORDER);
		foreach ($matchs as $key => $val) {
			$cookies[$val[1]] = $val[2];
		}
		return $cookies;
	}
	
	public function getInfo()
	{
		return curl_getinfo($this->ch);
	}
	
	public function getQueryString()
	{
		$str = '';
		foreach ($this->fields as $name => $val) {
			$urlVal = urlencode($val);
			$str .= "{$name}={$urlVal}&";
		}
		$str = trim($str, '&');
		return $str;
	}
	
	public function getCookieString()
	{
		$str = '';
		foreach ($this->cookies as $name => $val) {
			$cookieVal = urlencode($val);
			$str .= "{$name}={$val}; ";
		}
		$str = trim($str, '; ');
		return $str;
	}
	
	public function __destruct()
	{
		if ($this->ch) curl_close($this->ch);
	}
}


// $url_1 = 'http://myweb.com/test/t.php';
// $Curl = new Curl();
// $html_1 = $Curl->setPostField(array('name'=>'mosico', 'com'=>'vcb'))->isReturnHeader(true)->setCookie(array('coo_1'=>'test cookie', 'coo_2'=>'中文'))->fetch($url_1);
// $html_1_header = $Curl->getResponseHeaders();
// $html_1_cookie = $Curl->getResponseCookies();
// header('Content-Type: text/html; charset=UTF-8');
// var_dump($html_1_header, $html_1_cookie);
// echo $html_1;
// exit;




