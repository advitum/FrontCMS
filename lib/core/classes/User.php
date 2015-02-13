<?php
	
	namespace Advitum\Frontcms;
	
	class User
	{
		public static function create($username, $password) {
			if(DB::count(sprintf("SELECT COUNT(*) FROM `users` WHERE `username` = '%s'", DB::escape($username))) == 0) {
				DB::insert('users', array(
					'username' => $username,
					'password' => self::generateHash($password),
					'`created` = NOW()'
				));
				return true;
			} else {
				return false;
			}
		}
		
		public static function update($id, $username, $password) {
			if(DB::count(sprintf("SELECT COUNT(*) FROM `users` WHERE `username` = '%s'", DB::escape($username)))) {
				DB::update('users', array(
					'username' => $username,
					'password' => self::generateHash($password)
				), sprintf("WHERE id = %d", DB::escape($id)));
				return true;
			} else {
				return false;
			}
		}
		
		public static function delete($id) {
			DB::delete('users', sprintf("WHERE id = %d", DB::escape($id)));
		}
		
		public static function get() {
			$user = null;
			
			if(isset($_SESSION['user_id'])) {
				$user = DB::selectSingle("SELECT * FROM users WHERE id = " . intval($_SESSION['user_id']) . " LIMIT 1");
				DB::update('users', array('`lastseen` = NOW()'), "WHERE id = " . $user->id);
			}
			
			return $user;
		}
		
		public static function login($username, $password) {
			self::logout();
			$user = DB::selectSingle("SELECT * FROM users WHERE username = '" . DB::escape($username) . "' LIMIT 1");
			
			if($user !== false && self::checkHash($password, $user->password)) {
				$_SESSION['user_id'] = $user->id;
				DB::update('users', array('`lastseen` = NOW()'), "WHERE id = " . $user->id);
				return true;
			} else {
				return false;
			}
		}
		
		public static function logout() {
			unset($_SESSION['user_id']);
		}
		
		public static function generateHash($password) {
			$cost = 11;
			$password = urlencode($password);
			$salt = substr(base64_encode(openssl_random_pseudo_bytes(17)),0,22);
			$salt = str_replace("+",".",$salt);
			
			$param = '$2y$' . str_pad($cost,2,"0",STR_PAD_LEFT) . '$' . $salt;
			
			return crypt($password, $param);
		}
		
		public static function checkHash($password, $hash) {
			$password = urlencode($password);
			
			return crypt($password, $hash) == $hash;
		}
	}
	
?>