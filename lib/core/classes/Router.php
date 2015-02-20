<?php
	
	namespace Advitum\Frontcms;
	
	class Router
	{
		public static $user = null;
		public static $page = null;
		public static $layout = null;
		private static $url = null;
		
		public static function init() {
			if(isset($_GET["url"])) {
				$url = ROOT_URL . $_GET["url"];
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
				'deleted' => false
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
			
			$pages = Pages::getChildren($parent, $params['hidden'], $params['all'], $params['deleted']);
			
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
					if($page->deleted) {
						$classes[] = 'deleted';
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
					
					if(self::$user !== false) {
						$classes[] = 'fcmsHasAdminBar';
					}
					
					$html .= "<body" . (count($classes) ? ' class="' . implode(' ', $classes) . '"' : '') . ">\n" . $content . "\n</body>";
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
					
					$html .= Editable::render($attributes);
					
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
			
			switch(self::$url) {
				case ROOT_URL . 'ajax':
					if(self::$user !== null) {
						switch(@$_GET['action']) {
							case 'properties':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								if($page->parent_id != 0) {
									$possibleParents = Pages::possibleParents($page->id);
								}
								
								$layoutFiles = scandir(LAYOUTS_PATH);
								$layouts = array();
								foreach($layoutFiles as $file) {
									if(is_file(LAYOUTS_PATH . $file) && substr($file, -4) === '.tpl') {
										$layout = substr($file, 0, -4);
										$layouts[] = array($layout, $layout);
									}
								}
								sort($layouts);
								
								if(Form::sent('properties')) {
									$fields = array(
										'title' => array(
											'message' => 'Bitte geben Sie einen Seitentitel ein.'
										),
										'layout' => array(
											'rule' => function($value) use($layouts) {
												foreach($layouts as $layout) {
													if($layout[0] === $value) {
														return true;
													}
												}
												return false;
											},
											'message' => 'Bitte wählen Sie ein Layout aus.'
										)
									);
									
									if($page->parent_id != 0) {
										$fields['slug'] = array(
											'message' => 'Bitte geben Sie ein URL-Segment ein.'
										);
										$field['parent_id'] = array(
											'rule' => function($value) use($possibleParents) {
												if($value == 0) {
													return true;
												}
												foreach($possibleParents as $parent) {
													if($parent[0] == $value) {
														return true;
													}
												}
												return false;
											},
											'message' => 'Bitte wählen Sie eine übergeordnete Seite aus.'
										);
									}
									
									if(Validator::validate('properties', $fields)) {
										$values = array(
											'title' => Form::value('properties', 'title'),
											'layout' => Form::value('properties', 'layout')
										);
										if(Form::value('properties', 'navtitle') != '') {
											$values['navtitle'] = Form::value('properties', 'navtitle');
										} else {
											$values[] = 'navtitle = NULL';
										}
										if($page->parent_id != 0) {
											$values['slug'] = Form::value('properties', 'slug');
											$values['parent_id'] = Form::value('properties', 'parent_id');
										}
										DB::update('pages', $values, sprintf("WHERE `id` = %s", $page->id));
										
										Session::setMessage('Die Eigenschaften wurden gespeichert.', 'success');
										$result = array(
											'success' => true
										);
										break;
									}
								}
								
								$html = '';
								
								$html .= Form::create('#', 'properties');
								
								$html .= Form::input('title', array(
									'label' => 'Seitentitel',
									'placeholder' => 'Seitentitel',
									'default' => $page->title
								));
								$html .= Form::input('navtitle', array(
									'label' => 'Navigationstitel',
									'placeholder' => 'Navigationstitel',
									'default' => $page->navtitle
								));
								if($page->parent_id != 0) {
									$html .= Form::input('slug', array(
										'label' => 'URL Segment',
										'placeholder' => 'URL Segment',
										'default' => $page->slug
									));
									
									$html .= Form::input('parent_id', array(
										'label' => 'Unterseite von',
										'type' => 'select',
										'default' => $page->parent_id,
										'options' => $possibleParents
									));
								}
								
								$html .= Form::input('layout', array(
									'label' => 'Layout',
									'type' => 'select',
									'default' => $page->layout,
									'options' => $layouts
								));
								
								$html .= Form::end();
								
								if(Form::sent('properties')) {
									$result = array(
										'success' => false,
										'error' => 'validation',
										'response' => $html
									);
								} else {
									$result = array(
										'success' => true,
										'response' => $html
									);
								}
								
								break;
							case 'restore':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null || $page->parent_id == 0) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								DB::update('pages', array(
									'deleted' => 0
								), sprintf("WHERE `id` = %d", $page->id));
								$result = array(
									'success' => true
								);
								
								break;
							case 'delete':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null || $page->parent_id == 0) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								DB::update('pages', array(
									'deleted' => 1
								), sprintf("WHERE `id` = %d", $page->id));
								$result = array(
									'success' => true
								);
								
								break;
							case 'deletefinal':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null || $page->parent_id == 0) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								DB::delete('pages', sprintf("WHERE `id` = %d", $page->id));
								$result = array(
									'success' => true
								);
								
								break;
							case 'show':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null || $page->parent_id == 0) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								DB::update('pages', array(
									'hidden' => 0
								), sprintf("WHERE `id` = %d", $page->id));
								$result = array(
									'success' => true
								);
								
								break;
							case 'hide':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null || $page->parent_id == 0) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								DB::update('pages', array(
									'hidden' => 1
								), sprintf("WHERE `id` = %d", $page->id));
								$result = array(
									'success' => true
								);
								
								break;
							case 'showInMenu':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null || $page->parent_id == 0) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								DB::update('pages', array(
									'navpos' => 1
								), sprintf("WHERE `id` = %d", $page->id));
								$result = array(
									'success' => true
								);
								
								break;
							case 'hideInMenu':
								$page = Pages::getByUrl(self::sanitize(@$_GET['url']));
								
								if($page === null || $page->parent_id == 0) {
									$result = array(
										'success' => false,
										'error' => 'page'
									);
									break;
								}
								
								DB::update('pages', array(
									'navpos' => 0
								), sprintf("WHERE `id` = %d", $page->id));
								$result = array(
									'success' => true
								);
								
								break;
							case 'add':
								$parent = Pages::getRoot();
								DB::insert('pages', array(
									'parent_id' => $parent->id,
									'navpos' => 0,
									'hidden' => 1,
									'deleted' => 0,
									'title' => 'Neue Seite',
									'slug' => 'neue-seite',
									'layout' => 'default',
									'created = NOW()',
									'modified = NOW()'
								));
								$result = array(
									'success' => true
								);
								
								break;
							case 'sorting':
								$sorting = json_decode(@$_GET['sorting']);
								if(is_array($sorting)) {
									foreach($sorting as $element) {
										$page = Pages::getByUrl(self::sanitize($element->url));
										if($page !== null) {
											DB::update('pages', array(
												'navpos' => $element->position
											), sprintf("WHERE `id` = %d", $page->id));
										}
									}
								}
								
								$result = array(
									'success' => true
								);
								
								break;
							case 'media':
								$html = '';
								
								$html .= '<div id="fcmsMedia">';
								$html .= '<aside>';
								$html .= 'Neue Bilder hochladen: <input type="file" id="fcmsMediaUpload" />';
								$html .= '<ul id="fcmsErrorList"></ul>';
								$html .= '</aside>';
								$html .= '<ul id="fcmsMediaList">';
								
								$images = array();
								$files = scandir(MEDIA_PATH);
								foreach($files as $image) {
									if(is_file(MEDIA_PATH . $image) && preg_match('/\.(gif|jpe?g|png)$/i', $image)) {
										$images[] = array(
											$image, filectime(MEDIA_PATH . $image)
										);
									}
								}
								usort($images, function($a, $b) {
									return $a[1] > $b[1] ? -1 : 1;
								});
								foreach($images as $image) {
									$html .= '<li data-file="' . $image[0] . '"><img src="' . ROOT_URL . 'autoimg/w100-h100-c' . ROOT_URL . 'upload/media/' . $image[0] . '" alt="" /></li>';
								}
								
								$html .= '</ul>';
								$html .= '</div>';
								
								$result = array(
									'success' => true,
									'response' => $html
								);
								
								break;
							case 'media-upload':
								$uploadHandler = new UploadHandler(array(
									'upload_dir' => MEDIA_PATH,
									'upload_url' => ROOT_URL . 'upload/media/',
									'image_versions' => array()
								));
								exit();
								
								break;
							default:
								$result = array(
									'success' => false,
									'error' => 'action'
								);
								break;
						}
					} else {
						$result = array(
							'success' => false,
							'error' => 'authorisation'
						);
					}
					
					echo json_encode($result);
					exit();
				case ROOT_URL . 'login':
					if(self::$user !== null) {
						Session::setMessage('Sie sind bereits angemeldet!', 'success');
						self::redirect(ROOT_URL);
					}
					
					if(isset($_GET['auto'])) {
						Session::setMessage('Bitte melden Sie sich erneut an.', 'error');
						self::redirect(ROOT_URL . 'login');
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
	
	<link rel="stylesheet" type="text/css" href="<?php echo ADMIN_URL; ?>css/admin.css" />
</head>

<body class="fcmsAdminLogin">
	<div class="vCenter"><div>
		<?php echo Session::getMessage(); ?>
		<?php echo Form::create(ROOT_URL . 'login', 'login'); ?>
			<?php echo Form::input('username', array(
				'label' => false,
				'placeholder' => 'Nutzername',
				'autofocus' => 'autofocus'
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
					break;
				case ROOT_URL . 'logout':
					User::logout();
					Session::setMessage('Sie wurden abgemeldet!', 'success');
					self::redirect(ROOT_URL . 'login');
					break;
				default:
					self::$page = Pages::getByUrl(self::$url);
					
					if(self::$page !== null && self::$user !== null && isset($_POST['element']) && is_array($_POST['element'])) {
						DB::delete('elements', sprintf("WHERE `page_id` = %d", self::$page->id));
						foreach($_POST['element'] as $key => $value) {
							if(!empty($value)) {
								DB::insert('elements', array(
									'page_id' => self::$page->id,
									'name' => $key,
									'content' => $value
								));
							}
						}
						DB::update('pages', array(
							'modified = NOW()'
						), sprintf("WHERE `id` = %d", self::$page->id));
						Session::setMessage('Ihre Änderungen wurden gespeichert.', 'success');
						self::redirect(self::here());
					}
					
					if(self::$page === null) {
						header("Status: 404 Not Found");
						self::$layout = '404';
					} elseif(!is_file(LAYOUTS_PATH . self::$page->layout . '.tpl')) {
						self::$layout = 'default';
					} else {
						self::$layout = self::$page->layout;
					}
					
					echo self::parseTags(file_get_contents(LAYOUTS_PATH . self::$layout . '.tpl'));
					break;
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
			
			$html .= '<div class="fcmsButtons left">';
			$html .= '<button id="fcmsEdit" class="fcmsButton" title="Bearbeiten"><i class="fa fa-pencil"></i></button>';
			$html .= '<button id="fcmsSave" class="fcmsButton" title="Speichern"><i class="fa fa-floppy-o"></i></button>';
			$html .= '<button id="fcmsAbort" class="fcmsButton" title="Abbrechen"><i class="fa fa-times"></i></button>';
			$html .= '</div>';
			
			$html .= Session::getMessage();
			
			$html .= '<div class="fcmsButtons right">';
			$html .= '<button id="fcmsOpenPageTree" class="fcmsButton" title="Seitenbaum einblenden"><i class="fa fa-sitemap"></i></button>';
			$html .= '<a class="fcmsButton" href="' . ROOT_URL . 'logout" title="Abmelden"><i class="fa fa-sign-out"></i></a>';
			$html .= '</div>';
			
			$html .= '<div id="fcmsPageTree">';
			$html .= '<div class="fcmsButtons">';
			$html .= '<button id="fcmsAdd" class="fcmsButton" title="Seite hinzufügen"><i class="fa fa-plus"></i></button>';
			$html .= '<button id="fcmsShowDeleted" class="fcmsButton" title="Gelöschte Seiten einblenden"><i class="fa fa-trash-o"></i></button>';
			$html .= '</div>';
			
			$html .= self::navigation(array(
				'active' => false,
				'home' => true,
				'hidden' => true,
				'all' => true,
				'deleted' => true
			));
			$html .= '</div>';
			
			$html .= '</div>';
			$html .= '<script type="text/javascript">window.jQuery || document.write(\'<script type="text/javascript" src="' . ADMIN_URL  . 'js/jquery-1.11.2.min.js"><\/script>\')</script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/tinymce/tinymce.min.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/tinymce/jquery.tinymce.min.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/localstorage.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/jquery.lightbox.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/box.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/contextmenu.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/jquery.ui.widget.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/jquery.iframe-transport.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/jquery.fileupload.js"></script>';
			$html .= '<script type="text/javascript" src="' . ADMIN_URL  . 'js/admin.js"></script>';
			$html .= '<script type="text/javascript">
				var root = "' . ROOT_URL . '";
			</script>';
			return $html;
		}
	}
	
?>