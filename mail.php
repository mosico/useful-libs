<?php

/**
 * This send email only can be used on linux OS
 * Used php mail function to send email
 * @author mosico
 * @date 2014-04-08
 */
class Mail
{
	private $_from = '';
	private $_to = '';
	private $_subject = '';
	private $_message = '';
	private $_attachments = array();
	
	/**
	 * Set email information
	 * @param array $options array('from', 'to', 'subject', 'message', 'attach');
	 */
	public function setOptions($options)
	{
		if (empty($options) || !is_array($options)) {
			exit('Options must be an array');
		}
		
		foreach ($options as $name => $option) {
			if ($name == 'from') {
				$this->setEmailFrom($option);
			} elseif ($name == 'to') {
				$this->setEmailTo($option);
			} elseif ($name == 'subject') {
				$this->setSubject($option);
			} elseif ($name == 'message') {
				$this->setMessage($option);
			} elseif ($name == 'attach') {
				$this->setAttach($option);
			}
		}
	}
	
	public function setEmailFrom($email)
	{
		$this->_from = $email;
	}
	
	public function setEmailTo($email)
	{
		$this->_to = $email;
	}
	
	public function setSubject($subject)
	{
		$this->_subject = $subject;
	}
	
	public function setMessage($message)
	{
		$this->_message = $message;
	}
	public function setAttach($attachs)
	{
		$this->_attachments = (array) $attachs;
	}
	
	public function send()
	{
		//Send email only in linux system, Not send on WINNT
		if (PHP_OS == 'Linux') {
			$subject = $this->_subject;
			$from = $this->_from;
			$to = $this->_to;
			$message = $this->_message;
			
			/***********************   Email body start  *****************************/
			$rn = "\r\n";
			$boundary = md5(rand());
			
			// Headers
			$headers = "From: $from" . $rn;
			$headers .= 'Mime-Version: 1.0' . $rn;
			$headers .= 'Content-Type: multipart/related;boundary=' . $boundary . $rn;
			
			$msg = $rn . '--' . $boundary . $rn;
			$msg .= 'Content-Type: text/plain; charset=ISO-8859-1' . $rn;
			$msg .= $rn . strip_tags($message) . $rn;
			
			// Attachments
			foreach ($this->_attachments as $attach) {
				$conAttached = $this->prepareAttachment($attach);
				if ($conAttached !== false) {
					$msg .= $rn . '--' . $boundary . $rn;
					$msg .= $conAttached;
				}
			}
	
			// Fin
			$msg .= $rn . '--' . $boundary . '--' . $rn;
	
			/***********************   Email body end *****************************/
	
			mail($to, $subject, $msg, $headers);
	
			//shell_exec("echo '$message' | mailx -s '$subject' -r '$from' -a '$attach' '$to'");
		}
	}
	
	public function prepareAttachment($path) {
		$rn = "\r\n";
	
		if (file_exists($path)) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$ftype = finfo_file($finfo, $path);
			$file = fopen($path, "r");
			$attachment = fread($file, filesize($path));
			$attachment = chunk_split(base64_encode($attachment));
			fclose($file);
	
			$msg = 'Content-Type: \'' . $ftype . '\'; name="' . basename($path) . '"' . $rn;
			$msg .= "Content-Transfer-Encoding: base64" . $rn;
			$msg .= 'Content-ID: <' . basename($path) . '>' . $rn;
			//$msg .= 'X-Attachment-Id: ebf7a33f5a2ffca7_0.1' . $rn;
			$msg .= $rn . $attachment . $rn . $rn;
			return $msg;
		} else {
			return false;
		}
	}
}


$options = array(
	'from' => 'admin@admin.com',
	'to' => 'to@to.com',
	'subject' => 'This is a test mail',
	'message' => 'HI, you received a tested mail, do not replay.',
	'attach' => array('file_path_1', 'file_path_2'),
);
$Mail = new Mail();
$Mail->setOptions($options);
$Mail->send();