<?php
	
	namespace Advitum\Frontcms;
	
	class DB
	{
		public static $db = false;
		private static $tables = array(
			'elements' => "CREATE TABLE `%s` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`page_id` int(11) unsigned NOT NULL,
					`name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					`content` longtext COLLATE utf8_unicode_ci NOT NULL,
					PRIMARY KEY (`id`)
				)",
			'nonces' => "CREATE TABLE `%s` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`action` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
					`nonce` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
					`created` datetime DEFAULT NULL,
					`used` bit(1) DEFAULT b'0',
					PRIMARY KEY (`id`)
				)",
			'pages' => "CREATE TABLE `%s` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`parent_id` int(11) unsigned NOT NULL,
					`navpos` int(11) unsigned DEFAULT '0',
					`hidden` tinyint(1) DEFAULT '1',
					`deleted` tinyint(1) DEFAULT '0',
					`title` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					`slug` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
					`navtitle` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
					`layout` varchar(100) COLLATE utf8_unicode_ci DEFAULT 'default',
					`created` datetime DEFAULT NULL,
					`modified` datetime DEFAULT NULL,
					PRIMARY KEY (`id`)
				)",
			'users' => "CREATE TABLE `%s` (
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`username` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
					`password` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
					`lastseen` datetime DEFAULT NULL,
					`created` datetime DEFAULT NULL,
					PRIMARY KEY (`id`)
				)"
		);
		
		public static function connect($host, $user, $password, $database, $verbose = true) {
			self::$db = @new \mysqli($host, $user, $password, $database);
			
			if($database == '' || self::$db->connect_error) {
				if($verbose) {
					?><?php if(self::$db->connect_error) { ?><p><?php echo self::$db->connect_error; ?></p><?php } ?>
<p>Bitte prüfen Sie Ihre Datenbank-Verbindung.</p>
<?php
					exit();
				} else {
					return false;
				}
			}
			
			self::$db->set_charset('utf8');
			
			foreach(self::$tables as $table => $sql) {
				if(self::selectSingle(sprintf("SHOW TABLES LIKE '%s'", DB::escape($table))) === false) {
					self::query(sprintf($sql, DB::escape($table)));
				}
			}
			
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