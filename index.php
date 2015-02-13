<?php
	
	namespace Advitum\Frontcms;
	
	session_start();
	
	define('DEBUG', true);
	
	define('DS', DIRECTORY_SEPARATOR);
	
	define('ROOT_PATH', __DIR__ . DS);
		define('LAYOUTS_PATH', ROOT_PATH . 'layouts' . DS);
			define('PARTIALS_PATH', LAYOUTS_PATH . 'partials' . DS);
		define('LIB_PATH', ROOT_PATH . 'lib' . DS);
			define('CORE_PATH', LIB_PATH . 'core' . DS);
				define('CLASSES_PATH', CORE_PATH . 'classes' . DS);
			define('VENDOR_PATH', LIB_PATH . 'vendor' . DS);
		define('TMP_PATH', ROOT_PATH . 'tmp' . DS);
	
	define('ROOT_URL', '/');
	
	spl_autoload_register(function($class) {
		if(substr($class, 0, strlen(__NAMESPACE__)) === __NAMESPACE__) {
			$file = CLASSES_PATH . str_replace('\\', DS, substr($class, strlen(__NAMESPACE__) + 1)) . '.php';
			if(is_file($file)) {
				require_once($file);
			}
		}
	});
	
	require_once(ROOT_PATH . 'config.php');
	Router::init();
	
?>