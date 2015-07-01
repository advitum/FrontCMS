<?php
	
	namespace Advitum\Frontcms;
	
	class Ajax
	{
		public static function request() {
			if(Router::$user === null) {
				$result = [
					'success' => false,
					'error' => 'authorisation'
				];
			} else {
				$action = get_class() . '::action_' . @$_GET['action'];
				if(is_callable($action)) {
					$result = call_user_func($action);
				} else {
					$result = array(
						'success' => false,
						'error' => 'action'
					);
				}
			}
			
			echo json_encode($result);
			exit();
		}
		
		private static function action_change_password() {
			if(Form::sent('password')) {
				if(Validator::validate('password', [
					'old_password' => [
						'rules' => function($value) {
							return User::checkHash($value, Router::$user->password);
						},
						'message' => Language::string('Your password could not be verified.')
					],
					'new_password' => Language::string('Please enter a new password.'),
					'new_password_repeat' => [
						'rules' => function($value) {
							return $value === Form::value('password', 'new_password');
						},
						'message' => Language::string('You entered two different passwords. Try again!')
					]
				])) {
					User::update(Router::$user->id, Router::$user->username, Form::value('password', 'new_password'));
					
					Session::setMessage(Language::string('Your password was changed.'), 'success');
					
					return [
						'success' => true
					];
				}
			}
			
			$html = '';
			
			$html .= Form::create('#', 'password');
			
			$html .= Form::input('old_password', [
				'type' => 'password',
				'label' => Language::string('Old password')
			]);
			$html .= Form::input('new_password', [
				'type' => 'password',
				'label' => Language::string('New password')
			]);
			$html .= Form::input('new_password_repeat', [
				'type' => 'password',
				'label' => Language::string('New password (repeat)')
			]);
			
			$html .= Form::end();
			
			if(Form::sent('password')) {
				return [
					'success' => false,
					'error' => 'validation',
					'content' => $html
				];
			}
			
			return [
				'success' => true,
				'content' => $html
			];
		}
		
		private static function action_file_browser() {
			$html = '';
			
			$html .= '
<div id="fileBrowser">
	<aside>
		' . Language::string('Upload new files') . ': <input type="file" id="fileUpload" />
		<ul id="errorList"></ul>
	</aside>
	<ul id="fileList">';
			
			$files = [];
			$rawFiles = scandir(FILES_PATH);
			foreach($rawFiles as $filename) {
				if(substr($filename, 0, 1) !== '.' && is_file(FILES_PATH . $filename)) {
					$files[] = [
						$filename, filectime(FILES_PATH . $filename)
					];
				}
			}
			usort($files, function($a, $b) {
				return $a[1] > $b[1] ? -1 : 1;
			});
			foreach($files as $file) {
				$html .= '
		<li data-file="' . $file[0] . '">
			<strong>' . htmlspecialchars($file[0]) . '</strong><br />
			' . Format::fileSize(filesize(FILES_PATH . $file[0])) . '
		</li>';
			}
			
			$html .= '
	</ul>
</div>';
			
			return [
				'success' => true,
				'response' => $html
			];
		}
		
		private static function action_file_upload() {
			$uploadHandler = new UploadHandler(array(
				'upload_dir' => FILES_PATH,
				'upload_url' => ROOT_URL . 'upload/files/',
				'image_versions' => array()
			));
			exit();
		}
		
		private static function action_media_browser() {
			$html = '';
			
			$html .= '
<div id="mediaBrowser">
	<aside>
		' . Language::string('Upload new images') . ': <input type="file" id="mediaUpload" />
		<ul id="errorList"></ul>
	</aside>
	<ul id="mediaList">';
			
			$images = array();
			$files = scandir(MEDIA_PATH);
			foreach($files as $image) {
				if(substr($image, 0, 1) !== '.' && is_file(MEDIA_PATH . $image) && preg_match('/\.(gif|jpe?g|png)$/i', $image)) {
					$images[] = array(
						$image, filectime(MEDIA_PATH . $image)
					);
				}
			}
			usort($images, function($a, $b) {
				return $a[1] > $b[1] ? -1 : 1;
			});
			foreach($images as $image) {
				$html .= '
		<li data-file="' . htmlspecialchars($image[0]) . '"><img src="' . ROOT_URL . 'autoimg/w100-h100-c/upload/media/' . htmlspecialchars($image[0]) . '" alt="" /></li>';
			}
			
			$html .= '
	</ul>
</div>';
			
			return [
				'success' => true,
				'response' => $html
			];
		}
		
		private static function action_media_upload() {
			$uploadHandler = new UploadHandler(array(
				'upload_dir' => MEDIA_PATH,
				'upload_url' => ROOT_URL . 'upload/media/',
				'image_versions' => array()
			));
			exit();
		}
		
		private static function action_menu_sort() {
			$sorting = json_decode(@$_GET['sorting']);
			
			if(is_array($sorting)) {
				foreach($sorting as $element) {
					$page = Pages::getByUrl(Router::sanitize($element->url));
					if($page !== null) {
						DB::update('pages', array(
							'navpos' => $element->position
						), sprintf("WHERE `id` = %d", $page->id));
					}
				}
			}
			
			return [
				'success' => true
			];
		}
		
		private static function action_page_add() {
			$parent = Pages::getRoot();
			
			DB::insert('pages', array(
				'parent_id' => $parent->id,
				'navpos' => 0,
				'hidden' => 1,
				'deleted' => 0,
				'title' => Language::string('New page'),
				'slug' => Language::string('new-page'),
				'layout' => 'default',
				'created = NOW()',
				'modified = NOW()'
			));
			
			Session::setMessage(Language::string('The page was added.'), 'success');
			
			return [
				'success' => true,
				'url' => ROOT_URL . Language::string('new-page')
			];
		}
		
		private static function action_page_browser() {
			$html = '';
			
			$html .= '<div id="pageBrowser">';
			$html .= Router::navigation(array(
				'active' => false,
				'home' => true,
				'hidden' => true,
				'all' => true
			));
			$html .= '</div>';
			
			return [
				'success' => true,
				'response' => $html
			];
		}
		
		private static function action_page_delete() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null || $page->parent_id == 0) {
				return [
					'success' => false,
					'error' => 'page'
				];
			}
			
			DB::update('pages', array(
				'deleted' => 1
			), sprintf("WHERE `id` = %d", $page->id));
			
			Session::setMessage(Language::string('The page was deleted.'), 'success');
			
			return [
				'success' => true
			];
		}
		
		private static function action_page_delete_final() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null || $page->parent_id == 0) {
				return [
					'success' => false,
					'error' => 'page'
				];
			}
			
			DB::delete('pages', sprintf("WHERE `id` = %d", $page->id));
			
			Session::setMessage(Language::string('The page was permanently deleted.'), 'success');
			
			return [
				'success' => true
			];
		}
		
		private static function action_page_hide() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null || $page->parent_id == 0) {
				return [
					'success' => false,
					'error' => 'page'
				];
			}
			
			DB::update('pages', array(
				'hidden' => 1
			), sprintf("WHERE `id` = %d", $page->id));
			
			Session::setMessage(Language::string('The page was hidden.'), 'success');
			
			return [
				'success' => true
			];
		}
		
		private static function action_page_hide_in_menu() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null || $page->parent_id == 0) {
				return [
					'success' => false,
					'error' => 'page'
				];
			}
			
			DB::update('pages', array(
				'navpos' => 0
			), sprintf("WHERE `id` = %d", $page->id));
			
			Session::setMessage(Language::string('The page was removed from the navigation.'), 'success');
			
			return [
				'success' => true
			];
		}
		
		private static function action_page_properties() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null) {
				return [
					'success' => false,
					'error' => 'page'
				];
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
						'message' => Language::string('Please enter a page title.')
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
						'message' => Language::string('Please select a layout.')
					)
				);
				
				if($page->parent_id != 0) {
					$fields['slug'] = array(
						'message' => Language::string('Please enter an URL segment.')
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
						'message' => Language::string('Please select a parent page.')
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
						$values['slave'] = Form::value('properties', 'slave');
						$values['hidden'] = Form::value('properties', 'visible') == 0;
						$values['navpos'] = Form::value('properties', 'inmenu') ? max(1, $page->navpos) : 0;
					}
					DB::update('pages', $values, sprintf("WHERE `id` = %d", $page->id));
					
					DB::delete('page_options', sprintf("WHERE `page_id` = %d", $page->id));
					$options = Form::value('properties', 'options');
					if(is_array($options)) {
						foreach($options as $key => $value) {
							DB::insert('page_options', array(
								'page_id' => $page->id,
								'key' => $key,
								'value' => $value
							));
						}
					}
					
					Session::setMessage(Language::string('The properties were saved.'), 'success');
					
					return [
						'success' => true
					];
				}
			}
			
			$html = '';
			
			$html .= Form::create('#', 'properties');
			
			$html .= Form::input('title', array(
				'label' => Language::string('Page title'),
				'placeholder' => Language::string('Page title'),
				'default' => $page->title
			));
			$html .= Form::input('navtitle', array(
				'label' => Language::string('Navigation title'),
				'placeholder' => Language::string('Navigation title'),
				'default' => $page->navtitle
			));
			
			if($page->parent_id != 0) {
				$html .= Form::input('slug', array(
					'label' => Language::string('URL segment'),
					'placeholder' => Language::string('URL segment'),
					'default' => $page->slug
				));
			}
			
			$html .= Form::input('layout', array(
				'label' => Language::string('Layout'),
				'type' => 'select',
				'default' => $page->layout,
				'options' => $layouts
			));
			
			if($page->parent_id != 0) {
				$html .= Form::input('parent_id', array(
					'label' => Language::string('Child page of'),
					'type' => 'select',
					'default' => $page->parent_id,
					'options' => $possibleParents
				));
				
				$html .= Form::input('slave', array(
					'label' => Language::string('Redirect to children'),
					'type' => 'checkbox',
					'default' => $page->slave
				));
				
				$html .= Form::input('visible', array(
					'label' => Language::string('Visible'),
					'type' => 'checkbox',
					'default' => ($page->hidden == 0)
				));
				
				$html .= Form::input('inmenu', array(
					'label' => Language::string('Show in navigation'),
					'type' => 'checkbox',
					'default' => $page->navpos > 0
				));
			}
			
			if(count(PageOptions::$PAGE_OPTIONS)) {
				$html .= '<h2>' . Language::string('Options') . '</h2>';
				
				foreach(PageOptions::$PAGE_OPTIONS as $key => $option) {
					$value = DB::selectValue(sprintf("SELECT `value` FROM `page_options`WHERE `page_id` = %d AND `key` = '%s'", $page->id, DB::escape($key)));
					
					switch($option['type']) {
						case 'image':
							$html .= Form::input('options.' . $key, array(
								'label' => $option['title'],
								'type' => 'text',
								'class' => 'imageSelect',
								'default' => $value
							));
							break;
						case 'textarea':
							$html .= Form::input('options.' . $key, array(
								'label' => $option['title'],
								'type' => 'textarea',
								'default' => $value
							));
							break;
						default:
							$html .= Form::input('options.' . $key, array(
								'label' => $option['title'],
								'type' => 'text',
								'default' => $value
							));
							break;
					}
				}
			}
			
			$html .= Form::end();
			
			if(Form::sent('properties')) {
				return [
					'success' => false,
					'error' => 'validation',
					'response' => $html
				];
			} else {
				return [
					'success' => true,
					'response' => $html
				];
			}
		}
		
		private static function action_page_restore() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null || $page->parent_id == 0) {
				return [
					'success' => false,
					'error' => 'page'
				];
			}
			
			DB::update('pages', array(
				'deleted' => 0
			), sprintf("WHERE `id` = %d", $page->id));
			
			Session::setMessage(Language::string('The page was restored.'), 'success');
			
			return [
				'success' => true
			];
		}
		
		private static function action_page_show() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null || $page->parent_id == 0) {
				return [
					'success' => false,
					'error' => 'page'
				];
			}
			
			DB::update('pages', array(
				'hidden' => 0
			), sprintf("WHERE `id` = %d", $page->id));
			
			Session::setMessage(Language::string('The page is now visible.'), 'success');
			
			return [
				'success' => true
			];
		}
		
		private static function action_page_show_in_menu() {
			$page = Pages::getByUrl(Router::sanitize(@$_GET['url']));
			
			if($page === null || $page->parent_id == 0) {
				return [
					'success' => false,
					'error' => 'page'
				];
			}
			
			DB::update('pages', array(
				'navpos' => 1
			), sprintf("WHERE `id` = %d", $page->id));
			
			Session::setMessage(Language::string('The page is now visible in the navigation.'), 'success');
			
			return [
				'success' => true
			];
		}
		
		private static function action_plugin_edit() {
			$meta = @$_POST['_meta'];
			
			if(isset($meta['plugin'])) {
				$plugin = ucfirst($meta['plugin']);
				$class = 'Advitum\\Frontcms\\Plugins\\Plugin' . $plugin;
				
				if(is_file(PLUGINS_PATH . $plugin . DIRECTORY_SEPARATOR . 'Plugin' . $plugin . '.php')) {
					require_once(PLUGINS_PATH . $plugin . DIRECTORY_SEPARATOR . 'Plugin' . $plugin . '.php');
				}
				
				if(is_callable(array($class, 'edit'))) {
					return call_user_func(array($class, 'edit'), @$meta['content'], @$meta['name'], @$meta['attributes']);
				}
			}
			
			return [
				'success' => false,
				'error' => 'plugin'
			];
		}
		
		private static function action_user_add() {
			if(Form::sent('user-add')) {
				if(Validator::validate('user-add', [
					'username' => [
						'rules' => [
							'notEmpty',
							function($value) {
								return DB::selectValue(sprintf("SELECT count(*) FROM `users` WHERE `username` = '%s'", DB::escape($value))) == 0;
							}
						],
						'message' => Language::string('Please enter a unique username.')
					],
					'password' => Language::string('Please enter a password.'),
					'password_repeat' => [
						'rules' => function($value) {
							return $value === Form::value('user-add', 'password');
						},
						'message' => Language::string('You entered two different passwords. Try again!')
					]
				])) {
					User::create(Form::value('user-add', 'username'), Form::value('user-add', 'password'));
					
					Session::setMessage(sprintf(Language::string('The new user "%s" was created.'), htmlspecialchars(Form::value('user-add', 'username'))), 'success');
					
					return [
						'success' => true
					];
				}
			}
			
			$html = '';
			
			$html .= Form::create('#', 'user-add');
			
			$html .= Form::input('username', [
				'label' => Language::string('Username')
			]);
			$html .= Form::input('password', [
				'type' => 'password',
				'label' => Language::string('Password')
			]);
			$html .= Form::input('password_repeat', [
				'type' => 'password',
				'label' => Language::string('Password (repeat)')
			]);
			
			$html .= Form::end();
			
			if(Form::sent('user-add')) {
				return [
					'success' => false,
					'error' => 'validation',
					'content' => $html
				];
			}
			
			return [
				'success' => true,
				'content' => $html
			];
		}
	}
	
?>