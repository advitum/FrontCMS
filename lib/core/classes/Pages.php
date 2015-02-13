<?php
	
	namespace Advitum\Frontcms;
	
	class Pages
	{
		public static function getRoot() {
			return DB::selectSingle("SELECT `pages`.*, IF(`pages`.`navtitle` IS NULL, `pages`.`title`, `pages`.`navtitle`) AS `navtitle_or_title` FROM `pages` WHERE `pages`.`parent_id` = 0 LIMIT 1");
		}
		
		public static function getByUrl($url) {
			$page = null;
			
			$path = Router::urlPath($url);
			
			$page = self::getRoot();
			if(count($path)) {
				while(count($path)) {
					$slug = array_shift($path);
					$page = DB::selectSingle(sprintf("SELECT `pages`.*, IF(`pages`.`navtitle` IS NULL, `pages`.`title`, `pages`.`navtitle`) AS `navtitle_or_title` FROM `pages` WHERE `pages`.`parent_id` = %d%s AND `pages`.`slug` = '%s' LIMIT 1", $page->id, (Router::$user !== null ? '' : " AND `hidden` = 0"), DB::escape($slug)));
					
					if($page === false) {
						$page = null;
						break;
					}
				}
			}
			
			return $page;
		}
		
		public static function getChildren($id, $hidden = false, $all = false) {
			return DB::selectArray(sprintf(
				"SELECT `pages`.*, IF(`pages`.`navtitle` IS NULL, `pages`.`title`, `pages`.`navtitle`) AS `navtitle_or_title`, IF(`pages`.`navpos` > 0, 1, 0) AS `in_navigation` FROM `pages` WHERE `pages`.`parent_id` = %d%s%s ORDER BY `in_navigation` DESC, `pages`.`navpos` ASC, `navtitle_or_title` ASC",
				$id,
				($hidden ? '' : ' AND `pages`.`hidden` = 0'),
				($all ? '' : ' AND `pages`.`navpos` > 0')
			));
		}
	}
	
?>