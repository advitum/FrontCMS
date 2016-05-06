<?php
	
	namespace Advitum\Frontcms;
	
	define('DATABASE_HOST', 'localhost');
	define('DATABASE_USER', 'root');
	define('DATABASE_PASSWORD', 'root');
	define('DATABASE_NAME', 'frontcms');
	
	//define('DEBUG', true);
	define('DEMO', true);
	
	//define('LANGUAGE', 'en_us');
	
	//define('ROOT_URL', '/subdirectory/');
	
	class PageOptions
	{
		public static $PAGE_OPTIONS = [
			/*'backgroundImage' => [
				'title' => 'Background image',
				'type' => 'image'
			],*/
			'keywords' => [
				'title' => 'Keywords',
				'type' => 'textarea'
			],
			'description' => [
				'title' => 'Description',
				'type' => 'textarea'
			]
		];
	}
	
?>