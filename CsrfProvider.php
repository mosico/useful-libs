<?php

class CsrfProvider
{
	private $cache;
	private $sessionId;
	
	const HASH_SALT = 'mNdM*%L@LD84+';
	const HASH_NAME = 'INVESTOPEDIACSRF';
	const EXPIRE_TIME = 1800;	//30 minutes
	
	public function __construct()
	{
		$this->cache = $this->getService()->get('cache');
		if (isset($_COOKIE['PHPSESSID'])) {
			$this->sessionId = $_COOKIE['PHPSESSID'];
		} else {
			$this->sessionId = $this->generateHash();
			setcookie('PHPSESSID', $this->sessionId);
		}
	}
	
	public function getToken($formName, $refreshToken = false)
	{
		$key = $this->getKey($formName);
		$token = $this->cache->getItem($key);
		if (!$token || $refreshToken) {
			$token = $this->generateHash();
			$this->cache->getOptions()->setTtl(self::EXPIRE_TIME);
			$this->cache->setItem($key, $token);
		}
		return $token;
	}
	
	public function clearToken($formName)
	{
		$key = $this->getKey($formName);
		$this->cache->removeItem($key);
	}
	
	public function getKey($formName)
	{
		$formName = trim($formName);
		$key = $this->sessionId .'_'. $formName;
		return $key;
	}
	
	public function generateHash()
	{
		$rd = self::HASH_SALT . $this->getBytes() . self::HASH_NAME;
		return md5($rd);
	}
	
	public function getBytes()
	{
		$length = 32;
		$bytes = '';
		if (function_exists('openssl_random_pseudo_bytes')
		&& (version_compare(PHP_VERSION, '5.3.4') >= 0
				|| strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		) {
			$bytes = openssl_random_pseudo_bytes($length, $usable);
			if (true === $usable) {
				return $bytes;
			}
		}
		if (function_exists('mcrypt_create_iv')
		&& (version_compare(PHP_VERSION, '5.3.7') >= 0
				|| strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		) {
			$bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
			if ($bytes !== false && strlen($bytes) === $length) {
				return $bytes;
			}
		}
		
		return md5(microtime(true) . rand(10000, 99999) . microtime(true));
	}
}