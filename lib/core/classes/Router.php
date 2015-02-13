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
			
			$pages = Pages::getChildren($parent, $params['hidden'], $params['all']);
			
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
					if($page->hidden) {
						$classes[] = 'hidden';
					}
					if($page->navpos == 0) {
						$classes[] = 'notInMenu';
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
				case 'body':
					$html .= "<body" . (self::$user !== false ? ' class="fcmsHasAdminBar"' : '') . ">\n" . $content . "\n</body>";
					break;
				case 'head':
					if(self::$user !== null) {
						$html = '<link rel="stylesheet" type="text/css" href="' . ADMIN_URL  . 'css/admin.css" />';
					}
					break;
				case 'foot':
					if(self::$user !== null) {
						$html .= self::adminBar();
					}
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
			
			if(self::$url === ROOT_URL . 'login') {
				if(self::$user !== null) {
					Session::setMessage('Sie sind bereits angemeldet!', 'success');
					self::redirect(ROOT_URL);
				}
				
				if(Form::sent('login')) {
					User::create('admin', 'admin');
					if(User::login(Form::value('login', 'username'), Form::value('login', 'password'))) {
						Session::setMessage('Willkommen zurück!', 'success');
						self::redirect(ROOT_URL);
					} else {
						Session::setMessage('Ihre Zugangsdaten konnten nicht verifiziert werden!', 'error');
					}
				}
				
				?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Anmelden</title>
	
	<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL; ?>css/main.css" />
</head>
<body class="fcmsAdminLogin">
	<div class="vCenter"><div>
		<?php echo Session::getMessage(); ?>
		<?php echo Form::create(ROOT_URL . 'login', 'login'); ?>
			<?php echo Form::input('username', array(
				'label' => false,
				'placeholder' => 'Nutzername'
			)); ?>
			<?php echo Form::input('password', array(
				'label' => false,
				'placeholder' => 'Passwort'
			)); ?>
			<div class="back">
				<a class="button" href="<?php echo ROOT_URL; ?>">Zurück</a>
			</div>
		<?php echo Form::end('Anmelden'); ?>
	</div></div>
</body>
</html><?php
			} elseif(self::$url === ROOT_URL . 'logout') {
				User::logout();
				Session::setMessage('Sie wurden abgemeldet!', 'success');
				self::redirect(ROOT_URL . 'login');
			} else {
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
		}
		
		private static function parseTags($content) {
			$content = preg_replace_callback('/<fcms:(?<tag>[a-z-]+)((?:\s+[a-z-]+(?:=(?:"[^"]*"|\'[^\']*\'))?)*)\s+\/>/Usi', get_class() . '::replaceTag', $content);
			
			do {
				$oldContent = $content;
				$content = preg_replace_callback('/<fcms:(?<tag>[a-z-]+)((?:\s+[a-z-]+(?:=(?:"[^"]*"|\'[^\']*\'))?)*)\s*>(?<content>.*)<\/fcms:\1>/Usi', get_class() . '::replaceTag', $content);
			} while($oldContent !== $content);
			
			return $content;
		}
		
		private static function adminBar() {
			$html = '<div id="fcmsAdminBar">';
			
			$html .= Session::getMessage();
			
			$html .= '<div class="fcmsButtons">';
			$html .= '<button id="fcmsOpenPageTree" class="fcmsButton"><i class="fa fa-sitemap"></i></button>';
			$html .= '<button id="fcmsSave" class="fcmsButton"><i class="fa fa-floppy-o"></i></button>';
			$html .= '<a class="fcmsButton" href="' . ROOT_URL . 'logout"><i class="fa fa-sign-out"></i></a>';
			$html .= '</div>';
			
			$html .= '<div id="fcmsPageTree">';
			$html .= self::navigation(array(
				'active' => false,
				'home' => true,
				'hidden' => true,
				'all' => true
			));
			$html .= '</div>';
			
			$html .= '</div>';
			$html .= '<script type="text/javascript">window.jQuery || document.write(\'<script type="text/javascript" src="' . ADMIN_URL  . 'js/jquery-1.11.2.min.js"><\/script>\')</script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/admin.js"></script>';
			
			return $html;
		}
	}
	
?>