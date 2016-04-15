<?php
	
	namespace Advitum\Frontcms;
	
	session_start();
	
	define('DS', DIRECTORY_SEPARATOR);
	
	define('VERSION', '0.3.2');
	
	define('ROOT_PATH', dirname(dirname(__DIR__)) . DS);
		define('LAYOUTS_PATH', ROOT_PATH . 'layouts' . DS);
			define('PARTIALS_PATH', LAYOUTS_PATH . 'partials' . DS);
		define('LIB_PATH', ROOT_PATH . 'lib' . DS);
			define('CORE_PATH', LIB_PATH . 'core' . DS);
				define('ADMIN_PATH', CORE_PATH . 'admin' . DS);
				define('CLASSES_PATH', CORE_PATH . 'classes' . DS);
				define('LANGUAGES_PATH', CORE_PATH . 'languages' . DS);
			define('PLUGINS_PATH', LIB_PATH . 'plugins' . DS);
			define('VENDOR_PATH', LIB_PATH . 'vendor' . DS);
		define('TMP_PATH', ROOT_PATH . 'tmp' . DS);
		define('UPLOAD_PATH', ROOT_PATH . 'upload' . DS);
			define('MEDIA_PATH', UPLOAD_PATH . 'media' . DS);
			define('FILES_PATH', UPLOAD_PATH . 'files' . DS);
	
	spl_autoload_register(function($class) {
		if(substr($class, 0, strlen(__NAMESPACE__)) === __NAMESPACE__) {
			$file = CLASSES_PATH . str_replace('\\', DS, substr($class, strlen(__NAMESPACE__) + 1)) . '.php';
			if(is_file($file)) {
				require_once($file);
			}
		}
	});
	
	require_once(ROOT_PATH . 'config.php');
	
	if(!defined('DEBUG')) {
		define('DEBUG', false);
	}
	
	if(!defined('ROOT_URL')) {
		define('ROOT_URL', '/');
	}
	define('ADMIN_URL', ROOT_URL . 'lib/core/admin/');
	define('PLUGIN_URL', ROOT_URL . 'lib/plugins/');
	
	if(!class_exists('Advitum\Frontcms\PageOptions')) {
		class PageOptions
		{
			public static $PAGE_OPTIONS = [];
		}
	}
	
	if(!defined('LANGUAGE')) {
		define('LANGUAGE', 'en_us');
	}
	
	if(is_file(LANGUAGES_PATH . LANGUAGE . '.php')) {
		Language::add(require_once(LANGUAGES_PATH . LANGUAGE . '.php'));
	}
	
	Router::init();
	
?>