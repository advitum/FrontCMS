<?php
	
	namespace Advitum\Frontcms;
	
	class Language
	{
		private static $strings = [];
		
		public static function string($defaultString) {
			if(isset(self::$strings[$defaultString])) {
				return self::$strings[$defaultString];
			}
			return $defaultString;
		}
		
		public static function add($strings) {
			self::$strings = array_merge(self::$strings, $strings);
		}
	}
	
?>