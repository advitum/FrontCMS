<?php
	
	namespace Advitum\Frontcms;
	
	class DB
	{
		public static $db = false;
		
		public static function connect($host, $user, $password, $database, $verbose = true) {
			self::$db = @new \mysqli($host, $user, $password, $database);
			
			if($database == '' || self::$db->connect_error) {
				if($verbose) {
					?><?php if(self::$db->connect_error) { ?><p><?php echo self::$db->connect_error; ?></p><?php } ?>
<p>Please check your database setup.</p>
<?php
					exit();
				} else {
					return false;
				}
			}
			
			self::$db->set_charset('utf8');
			
			/*if(self::selectSingle("SHOW TABLES LIKE 'nonces'") === false) {
				self::query("CREATE TABLE `nonces` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `action` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `nonce` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  `used` bit(1) DEFAULT b'0',
  PRIMARY KEY (`id`)
)");
			}
			
			if(self::selectSingle("SHOW TABLES LIKE 'users'") === false) {
				self::query("CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `lastseen` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
)");
			}*/
			
			return true;
		}
		
		public static function query($query) {
			$result = self::$db->query($query);
			
			if($result === false) {
				if(DEBUG) {
					echo $query . '<br />' . self::$db->error;
				}
				exit();
			}
			
			return $result;
		}
		
		public static function selectArray($query) {
			$result = self::query($query);
			$array = array();
			
			while($row = $result->fetch_object()) {
				$array[] = $row;
			}
			
			return $array;
		}
		
		public static function selectSingle($query) {
			$result = self::query($query);
			
			if($row = $result->fetch_object()) {
				return $row;
			} else {
				return null;
			}
		}
		
		public static function selectValue($query) {
			$result = self::query($query);
			
			if($row = $result->fetch_row()) {
				return $row[0];
			} else {
				return null;
			}
		}
		
		public static function count($query) {
			$result = self::query($query);
			
			if($row = $result->fetch_row()) {
				return $row[0];
			} else {
				return 0;
			}
		}
		
		public static function insert($table, $data) {
			self::query("INSERT INTO " . $table . " " . self::dataArray($data));
			return self::$db->insert_id;
		}
		
		public static function update($table, $data, $where) {
			self::query("UPDATE " . $table . " " . self::dataArray($data) . " " . $where);
		}
		
		public static function delete($table, $where) {
			self::query("DELETE FROM " . $table . " " . $where);
		}
		
		public static function dataArray($data) {
			$array = array();
			
			foreach($data as $field => $value) {
				if(is_numeric($field)) {
					$array[] = $value;
				} else {
					if(!is_numeric($value)) {
						$value = "'" . self::escape($value) . "'";
					}
					$array[] = '`' . $field . '` = ' . $value;
				}
			}
			
			return "SET " . implode(', ', $array);
		}
		
		public static function escape($value) {
			return self::$db->real_escape_string($value);
		}
	}
	
?>