<?php
	
	namespace Advitum\Frontcms;
	
	class Editable
	{
		private static $elements = array();
		
		public static function render($attributes) {
			if(isset($attributes['type'])) {
				$type = $attributes['type'];
			} else {
				$type = 'rich';
			}
			
			if(isset($attributes['name'])) {
				$name = str_replace('_', '-', $attributes['name']);
			} else {
				$name = 'element';
			}
			
			if(!isset(self::$elements[$name])) {
				self::$elements[$name] = 1;
			} else {
				$name .= '_' . self::$elements[$name]++;
			}
			
			$element = DB::selectSingle(sprintf("SELECT * FROM `elements` WHERE `page_id` = %d AND `name` = '%s' LIMIT 1", Router::$page->id, DB::escape($name)));
			
			$html = '';
			
			if(is_callable(array(get_class(), 'type_' . $type))) {
				$html .= call_user_func(array(get_class(), 'type_' . $type), $element, $name, $attributes);
			}
			
			return $html;
		}
		
		private static function type_rich($element, $name, $attributes) {
			$html = '';
			
			if(Router::$user !== null) {
				$html .= '<div class="fcmsEditable" data-id="' . htmlspecialchars($name) . '" data-type="rich">';
				
				if($element !== null) {
					$html .= $element->content;
				}
				
				$html .= '</div>';
			} elseif($element !== null) {
				$html .= $element->content;
			}
			
			return $html;
		}
		
		private static function type_plain($element, $name, $attributes) {
			$html = '';
			
			if(Router::$user !== null) {
				$html .= '<div class="fcmsEditable" data-id="' . htmlspecialchars($name) . '" data-type="plain">';
				
				if($element !== null) {
					$html .= htmlspecialchars($element->content);
				}
				
				$html .= '</div>';
			} elseif($element !== null) {
				$html .= htmlspecialchars($element->content);
			}
			
			return $html;
		}
		
		private static function type_image($element, $name, $attributes) {
			$html = '';
			
			$image = null;
			if($element !== null) {
				$image = json_decode($element->content);
			}
			
			if(Router::$user !== null) {
				$classes = array('fcmsEditableImage');
					
				if($element !== null) {
					$imageAttributes['src'] = $image->src;
					$imageAttributes['alt'] = $image->alt;
				} else {
					$imageAttributes['src'] = 'http://placehold.it/500x300';
					$classes[] = 'placeholder';
				}
				
				$html .= '<div class="' . implode(' ', $classes) . '" data-id="' . htmlspecialchars($name) . '"><img ' . Html::attributes($imageAttributes) . ' /><button class="fcmsButton"><i class="fa fa-pencil"></i></button></div>';
			} elseif($element !== null) {
				$html .= '<img src="' . htmlspecialchars($image->src) . '" alt="' . htmlspecialchars($image->alt) . '" />';
			}
			
			return $html;
		}
	}
	
?>