<?php
	
	namespace Advitum\Frontcms;
	
	class Editable
	{
		private static $elements = array();
		private static $flexElements = array();
		
		public static function renderFlexlist($attributes, $items) {
			$html = '';
			
			$attributes['name'] = str_replace('_', '-', $attributes['name']);
			
			if(!isset(self::$elements[$attributes['name']])) {
				self::$elements[$attributes['name']] = 1;
			} else {
				$attributes['name'] .= '_' . self::$elements[$attributes['name']]++;
			}
			
			if(isset($attributes['global']) && $attributes['global']) {
				$elementPageId = 0;
			} else {
				$elementPageId = Router::$page->id;
			}
			$element = DB::selectSingle(sprintf("SELECT * FROM `elements` WHERE `page_id` = %d AND `name` = '%s' LIMIT 1", $elementPageId, DB::escape($attributes['name'])));
			if($element) {
				$content = json_decode($element->content);
			}
			
			if(Router::$user !== null) {
				$html .= '
<div ' . Html::attributes([
	'class' => 'fcmsFlexlist',
	'data-name' => $attributes['name'],
	'data-global' => (int) (isset($attributes['global']) && $attributes['global'])
]) . '>';
				
				foreach($items as $item) {
					$html .= '
	<div ' . Html::attributes([
		'class' => 'fcmsFlexitem empty',
		'data-title' => $item['attributes']['title'],
		'data-name' => $item['attributes']['name']
	]) . '>';
					
					self::$flexElements = [];
					$html .= Router::parseTags($item['content'], 'flex_' . $attributes['name'] . '_x_');
					
					$html .= '
	</div>';
				}
				
				if($element && $content !== false) {
					foreach($content->items as $index => $name) {
						if(isset($items[$name])) {
							$item = $items[$name];
							
							$html .= '
	<div ' . Html::attributes([
		'class' => 'fcmsFlexitem',
		'data-title' => $item['attributes']['title'],
		'data-name' => $item['attributes']['name']
	]) . '>';
							
							self::$flexElements = [];
							$html .= Router::parseTags($item['content'], 'flex_' . $attributes['name'] . '_' . $index . '_');
							
							$html .= '
	</div>';
						}
					}
				}
				
				$html .= '
</div>';
			} elseif($element && $content !== false) {
				foreach($content->items as $index => $name) {
					if(isset($items[$name])) {
						$item = $items[$name];
						
						self::$flexElements = [];
						$html .= Router::parseTags($item['content'], 'flex_' . $attributes['name'] . '_' . $index . '_');
					}
				}
			}
			
			return $html;
		}
		
		public static function render($attributes, $namePrefix = '') {
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
			
			if($namePrefix !== '') {
				if(!isset(self::$flexElements[$name])) {
					self::$flexElements[$name] = 1;
				} else {
					$name .= '_' . self::$flexElements[$name]++;
				}
				
				$name = $namePrefix . $name;
			} else {
				if(!isset(self::$elements[$name])) {
					self::$elements[$name] = 1;
				} else {
					$name .= '_' . self::$elements[$name]++;
				}
			}
			
			if(isset($attributes['global']) && $attributes['global']) {
				$elementPageId = 0;
			} else {
				$elementPageId = Router::$page->id;
			}
			$element = DB::selectSingle(sprintf("SELECT * FROM `elements` WHERE `page_id` = %d AND `name` = '%s' LIMIT 1", $elementPageId, DB::escape($name)));
			$content = '';
			if($element) {
				$content = $element->content;
				$content = str_replace('intern://', ROOT_URL, $content);
			}
			
			$html = '';
			
			if(is_callable(array(get_class(), 'type_' . $type))) {
				$html .= call_user_func(array(get_class(), 'type_' . $type), $content, $name, $attributes);
			}
			
			return $html;
		}
		
		private static function type_rich($content, $name, $attributes) {
			$html = '';
			
			if(Router::$user !== null) {
				$html .= '<div ' . Html::attributes([
					'class' => 'fcmsEditable',
					'data-name' => $name,
					'data-type' => 'rich',
					'data-global' => (int) (isset($attributes['global']) && $attributes['global'])
				]) . '>';
				
				if($content !== '') {
					$html .= $content;
				}
				
				$html .= '</div>';
			} elseif($content !== '') {
				$html .= $content;
			}
			
			return $html;
		}
		
		private static function type_plain($content, $name, $attributes) {
			$html = '';
			
			if(Router::$user !== null) {
				$html .= '<div ' . Html::attributes([
					'class' => 'fcmsEditable',
					'data-name' => $name,
					'data-type' => 'plain',
					'data-global' => (int) (isset($attributes['global']) && $attributes['global'])
				]) . '>';
				
				if($content !== '') {
					$html .= nl2br(htmlspecialchars($content));
				}
				
				$html .= '</div>';
			} elseif($content !== '') {
				$html .= nl2br(htmlspecialchars($content));
			}
			
			return $html;
		}
		
		private static function type_image($content, $name, $attributes) {
			$html = '';
			
			$image = null;
			if($content !== '') {
				$image = json_decode($content);
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
					$placeholderUrl .= Language::string('Arbitrary+dimensions');
				} elseif(!isset($attributes['width'])) {
					$placeholderUrl .= $attributes['height'] . ' ' . Language::string('Height');
				} elseif(!isset($attributes['height'])) {
					$placeholderUrl .= $attributes['width'] . ' ' . Language::string('Width');
				} else {
					$placeholderUrl .= $attributes['width'] . 'x' . $attributes['height'];
				}
				
				$divAttributes = array(
					'data-name' => $name,
					'data-placeholder-url' => $placeholderUrl,
					'data-global' => (int) (isset($attributes['global']) && $attributes['global'])
				);
				
				if(count($autoimg)) {
					$divAttributes['data-autoimg-params'] = implode('-', $autoimg);
				}
				
				if($content !== '') {
					$imageAttributes['data-src'] = $image->src;
					
					if(isset($attributes['id'])) {
						$imageAttributes['id'] = $attributes['id'];
					}
					if(isset($attributes['class'])) {
						$imageAttributes['class'] = $attributes['class'];
					}
					
					if(count($autoimg)) {
						$imageAttributes['src'] = ROOT_URL . 'autoimg/' . implode('-', $autoimg) . 'upload/media/' . $image->src;
					} else {
						$imageAttributes['src'] = ROOT_URL . 'upload/media/' . $image->src;
					}
					
					$imageAttributes['alt'] = $image->alt;
				} else {
					$imageAttributes['src'] = $placeholderUrl;
					$classes[] = 'placeholder';
				}
				
				$divAttributes['class'] = implode(' ', $classes);
				
				$html .= '<div ' . Html::attributes($divAttributes) . '><img ' . Html::attributes($imageAttributes) . ' /><button type="button" class="fcmsButton"><i class="fa fa-pencil"></i></button></div>';
			} elseif($content !== '') {
				$imageAttributes = array(
					'alt' => $image->alt
				);
				
				if(count($autoimg)) {
					$imageAttributes['src'] = ROOT_URL . 'autoimg/' . implode('-', $autoimg) . 'upload/media/' . $image->src;
				} else {
					$imageAttributes['src'] = ROOT_URL . 'upload/media/' . $image->src;
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
		
		private static function type_plugin($content, $name, $attributes) {
			$html = '';
			
			if(isset($attributes['plugin'])) {
				$plugin = ucfirst($attributes['plugin']);
				$class = 'Advitum\\Frontcms\\Plugins\\Plugin' . $plugin;
				
				if(is_file(PLUGINS_PATH . $plugin . DIRECTORY_SEPARATOR . 'Plugin' . $plugin . '.php')) {
					require_once(PLUGINS_PATH . $plugin . DIRECTORY_SEPARATOR . 'Plugin' . $plugin . '.php');
				}
				
				if(is_callable(array($class, 'render'))) {
					$html .= call_user_func(array($class, 'render'), $content, $name, $attributes);
				}
			}
			
			return $html;
		}
	}
	
?>