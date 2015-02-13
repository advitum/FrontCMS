<?php
	
	namespace Advitum\Frontcms;
	
	class Html
	{
		public static function attributes($attributes) {
			foreach($attributes as $key => $value) {
				$attributes[$key] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
			}
			return implode(' ', $attributes);
		}
	}
	
?>