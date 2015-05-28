<?php
	
	namespace Advitum\Frontcms;
	
	define('DATABASE_HOST', 'localhost');
	define('DATABASE_USER', 'root');
	define('DATABASE_PASSWORD', 'root');
	define('DATABASE_NAME', 'frontcms');
	
	class PageOptions
	{
		public static $PAGE_OPTIONS = [
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