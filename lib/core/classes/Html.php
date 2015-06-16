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
				if(isset($attribute[3])) {
					$attributes[$attribute[1]] = $attribute[3];
				} elseif(isset($attribute[2])) {
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
		
		public static function bulkJs($scripts, $fileName) {
			$html = '';
			
			if(defined('DEBUG') && DEBUG) {
				foreach($scripts as $script) {
					$html .= '
<script type="text/javascript" src="' . ADMIN_URL . 'js/' . htmlspecialchars($script) . '"></script>';
				}
			} else {
				if(!is_file(ADMIN_PATH . 'js' . DS . $fileName) || filemtime(ADMIN_PATH . 'js' . DS . $fileName) < time() - 604800) {
					file_put_contents(ADMIN_PATH . 'js' . DS . $fileName, implode("\n", array_map(function($scriptName) {
						return file_get_contents(ADMIN_PATH . 'js' . DS . $scriptName);
					}, $scripts)));
				}
				
				$html .= '
<script type="text/javascript" src="' . ADMIN_URL . 'js/' . htmlspecialchars($fileName) . '"></script>';
			}
			
			return $html;
		}
	}
	
?>