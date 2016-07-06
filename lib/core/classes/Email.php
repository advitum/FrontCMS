<?php
	
	namespace Advitum\Frontcms;
	
	class Email
	{
		private $from = false;
		private $type = 'plain';
		private $to = [];
		private $cc = [];
		private $bcc = [];
		private $subject = null;
		private $message = null;
		private $attachments = [];
		
		
		public function __construct() {
			
		}
		
		public function from($from) {
			$this->from = $from;
			
			return $this;
		}
		
		public function to($to) {
			if(is_array($to)) {
				$this->to = array_merge($this->to, $to);
			} else {
				$this->to[] = $to;
			}
			
			return $this;
		}
		
		public function subject($subject) {
			$this->subject = $subject;
			
			return $this;
		}
		
		public function message($message) {
			$this->message = $message;
			
			return $this;
		}
		
		public function send() {
			$headers = [];
			
			if($this->from !== false) {
				$headers[] = 'From: ' . $this->from;
			}
			$headers[] = 'Content-Type: text/plain; charset=UTF-8';
			
			return @mail(implode(',', $this->to), $this->subject, $this->message, implode("\r\n", $headers));
		}
	}
	
?>