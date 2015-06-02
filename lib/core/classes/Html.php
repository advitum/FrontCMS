<?php
	
	namespace Advitum\Frontcms;
	
	class Html
	{
		public static function attributes($attributes) {
			foreach($attributes as $key => $value) {
				$attributes[$key] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
			}
			return implode(' ', $attributes);
		}
		
		public static function parseAttributes($string) {
			$attributes = [];
			$matches = [];
			preg_match_all('/([a-z-]+)(?:=(?:"([^"]*)"|\'([^\']*)\'))?/si', $string, $matches, PREG_SET_ORDER);
			foreach($matches as $attribute) {
				if(isset($attribute[2])) {
					$attributes[$attribute[1]] = $attribute[2];
				} else {
					$attributes[$attribute[1]] = $attribute[1];
				}
			}
			foreach($attributes as $attribute => $value) {
				if($value === 'true' || $value === 'false') {
					$attributes[$attribute] = $value === 'true';
				}
			}
			return $attributes;
		}
	}
	
?>