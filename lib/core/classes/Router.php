<?php
	
	namespace Advitum\Frontcms;
	
	class Router
	{
		public static $user = null;
		public static $page = null;
		private static $url = null;
		private static $elements = array();
		
		public static function init() {
			if(isset($_SERVER["REDIRECT_URL"])) {
				$url = $_SERVER["REDIRECT_URL"];
			} else {
				$url = '';
			}
			
			$dirty = $url;
			$url = self::sanitize($dirty);
			
			if($url !== $dirty) {
				Router::redirect($url);
			}
			
			DB::connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
			
			self::$url = $url;
			self::render();
		}
		
		public static function navigation($params = array(), $parent = 0, $path = ROOT_URL, $depth = 1) {
			$html = '';
			
			$defaults = array(
				'start' => 1,
				'end' => false,
				'active' => true,
				'home' => true,
				'hidden' => false,
				'all' => false
			);
			
			$params = array_merge($defaults, $params);
			
			$list = true;
			if($params['start'] === $params['end']) {
				$list = false;
			}
			
			if($depth == 1) {
				$home = Pages::getRoot();
				$parent = $home->id;
			}
			
			$pages = Pages::getChildren($parent);
			
			if($list) {
				$html .= '<ul>' . "\n";
			}
			
			if($params['home'] && $depth == 1 && $depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
				if($list) {
					$html .= '<li>';
				}
				
				$html .= '<a href="' . htmlspecialchars($path)  . '" class="' . (self::$url == substr($path, 0, -1) ? 'active' : '') . '">' . htmlspecialchars($home->navtitle_or_title) . '</a>';
				
				if($list) {
					$html .= '</li>';
				}
				$html .= "\n";
			}
			
			foreach($pages as $page) {
				$newPath = htmlspecialchars($path . $page->slug) . '/';
				$active = substr($newPath, 0, -1) == substr(self::$url, 0, strlen($newPath) - 1);
				
				if($depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
					if($list) {
						$html .= '<li>';
					}
					
					$classes = array();
					if($active) {
						$classes[] = 'active';
					}
					$children = Pages::getChildren($page->id);
					if(count($children)) {
						$classes[] = 'sub';
					}
					
					$html .= '<a href="' . $newPath . '"' . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . '>' . htmlspecialchars($page->navtitle_or_title) . '</a>';
				}
				
				if(($active || $params['active'] === false) && count($children)) {
					$html .= "\n" . self::navigation($params, $page->id, $newPath, $depth + 1);
				}
				
				if($depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
					if($list) {
						$html .= '</li>';
					}
				}
				
				$html .= "\n";
			}
			
			if($list) {
				$html .= '</ul>' . "\n";
			}
			
			return $html;
		}
		
		public static function content() {
			$content = '';
			
			if(self::$page === null) {
				
			} else {
				
			}
			
			return $content;
		}
		
		public static function redirect($url) {
			header('Location: ' . $url);
			exit();
		}
		
		public static function here() {
			return self::$url;
		}
		
		public static function urlPath($url) {
			$path = explode('/', $url);
			$sanPath = array();
			
			foreach($path as $folder) {
				$folder = trim($folder);
				if($folder != '') {
					$sanPath[] = $folder;
				}
			}
			
			return $sanPath;
		}
		
		public static function replaceTag($match) {
			$tag = $match['tag'];
			$content = isset($match['content']) && !empty($match['content']) ? $match['content'] : false;
			$attributes = array();
			if(isset($match[2]) && !empty($match[2])) {
				$matches = array();
				preg_match_all('/([a-z-]+)(?:=(?:"([^"]*)"|\'([^\']*)\'))?/si', $match[2], $matches, PREG_SET_ORDER);
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
			}
			
			$html = '';
			
			switch($tag) {
				case 'partial':
					if(isset($attributes['partial']) && is_file(PARTIALS_PATH . $attributes['partial'] . '.tpl')) {
						$html .= self::parseTags(file_get_contents(PARTIALS_PATH . $attributes['partial'] . '.tpl'));
					}
					break;
				case 'title':
					if(self::$page === null) {
						$html .= 'Seite nicht gefunden | ';
					} else {
						$html .= self::$page->title . ' | ';
					}
					break;
				case 'navigation':
					$html .= self::navigation($attributes);
					break;
				case 'edit':
					if(self::$page === null) {
						break;
					}
					
					if(isset($attributes['type'])) {
						$type = $attributes['type'];
					} else {
						$type = 'rich';
					}
					
					if(isset($attributes['name'])) {
						$name = str_replace('_', '-', $attributes['name']);
					} else {
						$name = 'element';
					}
					
					if(!isset(self::$elements[$name])) {
						self::$elements[$name] = 1;
					} else {
						$name .= '_' . self::$elements[$name]++;
					}
					
					$element = DB::selectSingle(sprintf("SELECT * FROM `elements` WHERE `page_id` = %d AND `name` = '%s' LIMIT 1", self::$page->id, DB::escape($name)));
					if($element) {
						switch($type) {
							case 'rich':
								$html .= $element->content;
								break;
							case 'plain':
								$html .= htmlspecialchars($element->content);
								break;
						}
					}
					
					break;
			}
			
			return $html;
		}
		
		private static function sanitize($url) {
			$sanPath = self::urlPath($url);
			
			if(count($sanPath) > 0) {
				$sanUrl = '/' . implode('/', $sanPath);
			} else {
				$sanUrl = '';
			}
			
			return $sanUrl;
		}
		
		private static function render() {
			self::$user = User::get();
			self::$page = Pages::getByUrl(self::$url);
			
			$layout = '';
			if(self::$page === null) {
				header("Status: 404 Not Found");
				$layout = file_get_contents(LAYOUTS_PATH . '404.tpl');
			} elseif(!is_file(LAYOUTS_PATH . self::$page->layout . '.tpl')) {
				$layout = file_get_contents(LAYOUTS_PATH . 'default.tpl');
			} else {
				$layout = file_get_contents(LAYOUTS_PATH . self::$page->layout . '.tpl');
			}
			
			echo self::parseTags($layout);
		}
		
		private static function parseTags($content) {
			$content = preg_replace_callback('/<fcms:(?<tag>[a-z-]+)((?:\s+[a-z-]+(?:=(?:"[^"]*"|\'[^\']*\'))?)*)\s+\/>/Usi', get_class() . '::replaceTag', $content);
			
			do {
				$oldContent = $content;
				$content = preg_replace_callback('/<fcms:(?<tag>[a-z-]+)((?:\s+[a-z-]+(?:=(?:"[^"]*"|\'[^\']*\'))?)*)\s*>(?<content>.*)<\/fcms:\1>/Usi', get_class() . '::replaceTag', $content);
			} while($oldContent !== $content);
			
			return $content;
		}
	}
	
?>