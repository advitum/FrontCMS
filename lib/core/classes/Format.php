<?php
	
	namespace Advitum\Frontcms;
	
	class Format
	{
		public static function fileSize($size) {
			$fileSizePrefixes = array('k', 'M', 'G', 'T', 'P');
			
			$size = intval($size);
			
			$prefix = -1;
			while($size > 1024 / 5 && $prefix < count($fileSizePrefixes) - 1) {
				$size /= 1024;
				$prefix++;
			}
			
			$rounded = round($size * 10) / 10;
			$rounded = str_replace('.', Language::string('.'), $rounded);
			
			return $rounded . ' ' . ($prefix >= 0 ? $fileSizePrefixes[$prefix] : '') . 'B';
		}
	}
	
?>