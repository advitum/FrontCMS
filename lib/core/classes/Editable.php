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
			
			$autoimg = array();
			if(isset($attributes['width'])) {
				$autoimg[] = 'w' . $attributes['width'];
			}
			if(isset($attributes['height'])) {
				$autoimg[] = 'h' . $attributes['height'];
			}
			if(isset($attributes['crop'])) {
				$autoimg[] = 'c';
			}
			
			if(Router::$user !== null) {
				$classes = array('fcmsEditableImage', 'img');
				
				$placeholderUrl = 'http://placehold.it/';
				if(isset($attributes['width'])) {
					$placeholderUrl .= $attributes['width'];
				} else {
					$placeholderUrl .= 200;
				}
				$placeholderUrl .= 'x';
				if(isset($attributes['height'])) {
					$placeholderUrl .= $attributes['height'];
				} else {
					$placeholderUrl .= 200;
				}
				$placeholderUrl .= '&text=';
				if(!isset($attributes['width']) && !isset($attributes['height'])) {
					$placeholderUrl .= 'Beliebige+Größe';
				} elseif(!isset($attributes['width'])) {
					$placeholderUrl .= $attributes['height'] . ' Höhe';
				} elseif(!isset($attributes['height'])) {
					$placeholderUrl .= $attributes['width'] . ' Breite';
				} else {
					$placeholderUrl .= $attributes['width'] . 'x' . $attributes['height'];
				}
				
				$divAttributes = array(
					'data-id' => $name,
					'data-placeholder-url' => $placeholderUrl
				);
				
				if(count($autoimg)) {
					$divAttributes['data-autoimg-params'] = implode('-', $autoimg);
				}
				
				if($element !== null) {
					if(isset($attributes['id'])) {
						$imageAttributes['id'] = $attributes['id'];
					}
					if(isset($attributes['class'])) {
						$imageAttributes['class'] = $attributes['class'];
					}
					
					if(count($autoimg)) {
						$imageAttributes['src'] = ROOT_URL . 'autoimg/' . implode('-', $autoimg) . $image->src;
						$imageAttributes['data-src'] = $image->src;
					} else {
						$imageAttributes['src'] = $image->src;
					}
					
					$imageAttributes['alt'] = $image->alt;
				} else {
					$imageAttributes['src'] = $placeholderUrl;
					$classes[] = 'placeholder';
				}
				
				$divAttributes['class'] = implode(' ', $classes);
				
				$html .= '<div ' . Html::attributes($divAttributes) . '><img ' . Html::attributes($imageAttributes) . ' /><button class="fcmsButton"><i class="fa fa-pencil"></i></button></div>';
			} elseif($element !== null) {
				$imageAttributes = array(
					'alt' => $image->alt
				);
				
				if(count($autoimg)) {
					$imageAttributes['src'] = ROOT_URL . 'autoimg/' . implode('-', $autoimg) . $image->src;
				} else {
					$imageAttributes['src'] = $image->src;
				}
				
				if(isset($attributes['id'])) {
					$imageAttributes['id'] = $attributes['id'];
				}
				if(isset($attributes['class'])) {
					$imageAttributes['class'] = $attributes['class'];
				}
				
				$html .= '<div class="img"><img ' . Html::attributes($imageAttributes) . ' /></div>';
			}
			
			return $html;
		}
	}
	
?>