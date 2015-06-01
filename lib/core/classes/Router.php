<?php
	
	namespace Advitum\Frontcms;
	
	class Router
	{
		public static $user = null;
		public static $page = null;
		public static $layout = null;
		
		private static $url = null;
		private static $enqueuedScripts = array();
		private static $enqueuedStyles = array();
		
		public static function init() {
			if(isset($_GET["fcmsquery"])) {
				$url = ROOT_URL . $_GET["fcmsquery"];
			} else {
				$url = '';
			}
			
			$dirty = $url;
			$url = self::sanitize($dirty);
			
			if($url !== $dirty) {
				self::redirect($url);
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
				'all' => false,
				'deleted' => false,
				'list' => true
			);
			
			$params = array_merge($defaults, $params);
			
			$list = true;
			if($params['start'] === $params['end'] && $params['list'] === false) {
				$list = false;
			}
			
			if($depth == 1) {
				$home = Pages::getRoot();
				$parent = $home->id;
			}
			
			$pages = Pages::getChildren($parent, $params['hidden'], $params['all'], $params['deleted']);
			
			if($depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
				if($list) {
					$html .= '<ul>' . "\n";
				}
				
				if($params['home'] && $depth == 1 && $depth >= $params['start'] && ($params['end'] === false || $depth <= $params['end'])) {
					if($list) {
						$html .= '<li>';
					}
					
					$html .= '<a href="' . htmlspecialchars($path)  . '" class="notInMenu' . (self::$url == substr($path, 0, -1) ? ' active' : '') . '">' . htmlspecialchars($home->navtitle_or_title) . '</a>';
					
					if($list) {
						$html .= '</li>';
					}
					$html .= "\n";
				}
				
				foreach($pages as $page) {
					$newPath = htmlspecialchars($path . $page->slug) . '/';
					$active = substr($newPath, 0, -1) == substr(self::$url, 0, strlen($newPath) - 1);
					
					if($list) {
						$html .= '<li>';
					}
					
					$classes = array();
					if($active) {
						$classes[] = 'active';
					}
					$children = Pages::getChildren($page->id, $params['hidden'], $params['all'], $params['deleted']);
					if(count($children)) {
						$classes[] = 'sub';
					}
					if($page->hidden) {
						$classes[] = 'hidden';
					}
					if($page->deleted) {
						$classes[] = 'deleted';
					}
					if($page->navpos == 0) {
						$classes[] = 'notInMenu';
					}
					
					$html .= '<a href="' . $newPath . '"' . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . '>' . htmlspecialchars($page->navtitle_or_title) . '</a>';
					
					if(($active || $params['active'] === false) && isset($children) && count($children)) {
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
			} elseif($depth < $params['start']) {
				foreach($pages as $page) {
					$newPath = htmlspecialchars($path . $page->slug) . '/';
					$active = substr($newPath, 0, -1) == substr(self::$url, 0, strlen($newPath) - 1);
					$children = Pages::getChildren($page->id, $params['hidden'], $params['all'], $params['deleted']);
					if(($active || $params['active'] === false) && count($children)) {
						$html .= "\n" . self::navigation($params, $page->id, $newPath, $depth + 1);
					}
				}
			}
			
			return $html;
		}
		
		public static function redirect($url) {
			header('Location: ' . $url);
			exit();
		}
		
		public static function here() {
			return self::$url === '' ? ROOT_URL : self::$url;
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
					$classes = array(
						self::$layout
					);
					
					$html .= "<body" . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . ">\n" . $content . "\n</body>";
					break;
				case 'head':
					foreach(self::$enqueuedStyles as $style) {
						$html .= '<link rel="stylesheet" type="text/css" href="' . $style . '" />';
					}
					break;
				case 'foot':
					foreach(self::$enqueuedScripts as $script) {
						$html .= '<script type="text/javascript" src="' . $script . '"></script>';
					}
					break;
				case 'edit':
					if(self::$page === null) {
						break;
					}
					
					$html .= Editable::render($attributes);
					
					break;
			}
			
			return $html;
		}
		
		public static function enqueueScript($key, $script) {
			if(!isset(self::$enqueuedScripts[$key])) {
				self::$enqueuedScripts[$key] = $script;
			}
		}
		
		public static function enqueueStyle($key, $style) {
			if(!isset(self::$enqueuedStyles[$key])) {
				self::$enqueuedStyles[$key] = $style;
			}
		}
		
		public static function sanitize($url) {
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
			
			switch(self::$url) {
				case ROOT_URL . 'ajax':
					Ajax::request();
				case ROOT_URL . 'login':
					if(self::$user !== null) {
						Session::setMessage(Language::string('You are already logged in!'), 'success');
						self::redirect(ROOT_URL);
					}
					
					if(isset($_GET['auto'])) {
						Session::setMessage(Language::string('Please login again.'), 'error');
						self::redirect(ROOT_URL . 'login');
					}
					
					if(Form::sent('login')) {
						User::create('admin', 'admin');
						if(User::login(Form::value('login', 'username'), Form::value('login', 'password'))) {
							Session::setMessage(Language::string('Welcome back!'), 'success');
							self::redirect(ROOT_URL);
						} else {
							Session::setMessage(Language::string('Your login details could not be verified!'), 'error');
						}
					}
					
					?><!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="UTF-8">
	<title>Anmelden</title>
	
	<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL; ?>css/admin.css" />
</head>

<body class="login">
	<div class="vCenter"><div>
		<?php echo Session::getMessage(); ?>
		<?php echo Form::create(ROOT_URL . 'login', 'login'); ?>
			<?php echo Form::input('username', array(
				'label' => false,
				'placeholder' => Language::string('Username'),
				'autofocus' => 'autofocus'
			)); ?>
			<?php echo Form::input('password', array(
				'label' => false,
				'placeholder' => Language::string('Password')
			)); ?>
			<div class="back">
				<a class="button" href="<?php echo ROOT_URL; ?>"><?php echo Language::string('Back'); ?></a>
			</div>
		<?php echo Form::end(Language::string('Login')); ?>
	</div></div>
</body>

</html><?php
					break;
				case ROOT_URL . 'logout':
					User::logout();
					Session::setMessage(Language::string('You were logged out!'), 'success');
					self::redirect(ROOT_URL . 'login');
					break;
				default:
					self::$page = Pages::getByUrl(self::$url);
					
					if(self::$user !== null && !isset($_GET['fcms_content'])) {
						Admin::request();
					} else {
						if(self::$page === null) {
							header("Status: 404 Not Found");
							self::$layout = '404';
						} elseif(self::$page->parent_id != 0 && self::$page->slave) {
							$children = Pages::getChildren(self::$page->id, self::$user !== null, true, false);
							if(count($children)) {
								self::redirect(self::here() . '/' . $children[0]->slug);
							} else {
								header("Status: 404 Not Found");
								self::$layout = '404';
							}
						} elseif(!is_file(LAYOUTS_PATH . self::$page->layout . '.tpl')) {
							self::$layout = 'default';
						} else {
							self::$layout = self::$page->layout;
						}
						
						echo self::parseTags(file_get_contents(LAYOUTS_PATH . self::$layout . '.tpl'));
					}
					break;
			}
		}
		
		private static function parseTags($content) {
			$content = str_replace('{ROOT_URL}', ROOT_URL, $content);
			
			if(self::$page !== null) {
				$content = str_replace('{PAGE_TITLE}', self::$page->title, $content);
				
				foreach(PageOptions::$PAGE_OPTIONS as $key => $option) {
					$value = DB::selectValue(sprintf("SELECT `value` FROM `page_options` WHERE `page_id` = %d AND `key` = '%s'", self::$page->id, DB::escape($key)));
					
					if($value === null) {
						$value = '';
					}
					
					$content = str_replace('{PAGE_OPTION.' . $key . '}', htmlspecialchars($value), $content);
				}
			} else {
				$content = str_replace('{PAGE_TITLE}', Language::string('Page not found'), $content);
			}
			
			$content = preg_replace_callback('/<fcms:(?<tag>[a-z-]+)((?:\s+[a-z-]+(?:=(?:"[^"]*"|\'[^\']*\'))?)*)\s+\/>/Usi', get_class() . '::replaceTag', $content);
			
			do {
				$oldContent = $content;
				$content = preg_replace_callback('/<fcms:(?<tag>[a-z-]+)((?:\s+[a-z-]+(?:=(?:"[^"]*"|\'[^\']*\'))?)*)\s*>(?<content>.*)<\/fcms:\1>/Usi', get_class() . '::replaceTag', $content);
			} while($oldContent !== $content);
			
			return $content;
		}
	}
	
?>