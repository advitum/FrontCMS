<?php
	
	namespace Advitum\Frontcms;
	
	class Nonce
	{
		public static function get($action) {
			$nonce = self::random();
			
			 DB::insert('nonces', array(
			 	'action' => $action,
			 	'nonce' => $nonce,
			 	'`created` = NOW()',
			 	'used' => 0
			));
			
			return $nonce;
		}
		
		public static function field($action) {
			$nonce = self::get($action);
			$field = Form::hidden('_nonce', $nonce);
			return $field;
		}
		
		public static function check($action) {
			$nonce = false;
			$result = false;
			
			if(isset($_GET['_nonce'])) {
				$nonce = $_GET['_nonce'];
			} elseif(isset($_POST['_nonce'])) {
				$nonce = $_POST['_nonce'];
			}
			
			if($nonce !== false) {
				$nonce = DB::selectSingle("SELECT id FROM nonces WHERE `nonce` = '" . DB::escape($nonce) . "' AND `action` = '" . DB::escape($action) . "' AND NOW() - created < 3600 AND used = 0 LIMIT 1");
				
				if($nonce !== false) {
					DB::update('nonces', array('used' => 1), "WHERE id = " . $nonce->id);
					$result = true;
				}
			}
			
			if($result === false) {
				Session::setMessage('Are you sure you want to do that?');
			}
			
			return $result; 
		}
		
		public static function random() {
			$available = '0123456789abcdefghijklmnopqrstuvwxyz';
			$countAvailable = strlen($available);
			
			$length = 10;
			$count = 0;
			do {
				$nonce = '';
				if($count > 20) {
					$count = 0;
					$length++;
				}
				
				for($i = 0; $i < $length; $i++) {
					$nonce .= $available[mt_rand(0, $countAvailable - 1)];
				}
				
				$count++;
			} while(DB::selectSingle("SELECT id FROM nonces WHERE `nonce` = '" . DB::escape($nonce) . "' LIMIT 1") !== false);
			
			return $nonce;
		}
	}
?>