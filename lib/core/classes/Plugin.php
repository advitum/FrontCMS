<?php
	
	namespace Advitum\Frontcms;
	
	abstract class Plugin
	{
		abstract public static function render($attributes = array(), $content = '');
	}
	
?>