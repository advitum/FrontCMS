<?php
	
	namespace Advitum\Frontcms\Plugins;
	
	use Advitum\Frontcms\Form;
	use Advitum\Frontcms\Html;
	use Advitum\Frontcms\Language;
	use Advitum\Frontcms\Plugin;
	use Advitum\Frontcms\Router;
	use Advitum\Frontcms\Validator;
	
	class PluginSlideshow extends Plugin
	{
		private static $languageImported = false;
		
		public static function render($content, $name, $attributes = array()) {
			$html = '';
			
			if(Router::$user !== null) {
				$divAttributes = [
					'class' => 'fcmsEditablePlugin',
					'data-content' => $content,
					'data-attributes' => json_encode($attributes),
					'data-plugin' => 'slideshow',
					'data-name' => $name,
					'data-global' => (int) (isset($attributes['global']) && $attributes['global'])
				];
				
				if($content !== '' && ($content = json_decode($content)) !== false && isset($content->slides) && count($content->slides)) {
					$html .= '<div ' . Html::attributes($divAttributes) . '>';
					
					foreach($content->slides as $slide) {
						$html .= '
						<div class="slide">
							<figure>
								<img src="' . ROOT_URL . 'autoimg/w300/upload/media/' . $slide->image . '" />
								<figcaption>' . htmlspecialchars($slide->caption) . '</figcaption>
							</figure>
						</div>';
					}
				} else {
					$divAttributes['class'] .= ' placeholder';
					
					$html .= '<div ' . Html::attributes($divAttributes) . '>';
				}
				
				$html .= '<button type="button" class="fcmsButton" style="display: block;"><i class="fa fa-pencil"></i></button>';
				$html .= '</div>';
			} elseif($content !== '' && ($content = json_decode($content)) !== false && isset($content->slides) && count($content->slides)) {
				$html .= '<div class="slideshow">';
				
				foreach($content->slides as $slide) {
					$html .= '
					<div class="slide">
						<figure>
							<img src="' . ROOT_URL . 'upload/media/' . $slide->image . '" />
							<figcaption>' . htmlspecialchars($slide->caption) . '</figcaption>
						</figure>
					</div>';
				}
				
				$html .= '</div>';
				
				Router::enqueueScript('slideshow', ROOT_URL . 'lib/plugins/Slideshow/js/slideshow.js');
				Router::enqueueStyle('slideshow', ROOT_URL . 'lib/plugins/Slideshow/css/slideshow.css');
			}
			
			return $html;
		}
		
		public static function edit($content, $name, $attributes = array()) {
			self::importLanguage();
			
			if(Form::sent('plugin')) {
				Validator::$errors['plugin'] = [];
				
				$extras = [];
				
				foreach(Form::value('plugin', 'image') as $index => $img) {
					$slide = [
						'image' => Form::value('plugin', 'image.' . $index),
						'caption' => Form::value('plugin', 'caption.' . $index)
					];
					
					if(($slide['image'] && !empty($slide['image'])) || ($slide['caption'] && !empty($slide['caption']))) {
						$slides[] = $slide;
						
						if(!$slide['image'] || empty($slide['image'])) {
							Validator::$errors['plugin']['image.' . $index] = Language::string('Select an image!');
						}
					}
				}
				
				if(count(Validator::$errors['plugin']) === 0) {
					$values = json_encode([
						'slides' => $slides
					]);
					return [
						'success' => true,
						'content' => self::render($values, $name, $attributes)
					];
				}
			}
			
			$content = json_decode($content);
			
			$html = '';
			
			$html .= Form::create('#', 'plugin');
			
			$slides = $content !== false && isset($content->slides) ? $content->slides : array();
			
			$html .= self::repeatable('plugin', $slides, function($index, $slide) {
				$html = '';
				
				$html .= Form::input('image.' . $index, [
					'id' => 'image-' . $index,
					'label' => Language::string('Image'),
					'class' => 'imageSelect',
					'default' => $slide !== null ? $slide->image : ''
				]);
				$html .= Form::input('caption.' . $index, [
					'id' => 'caption-' . $index,
					'label' => Language::string('Caption'),
					'default' => $slide !== null ? $slide->caption : ''
				]);
				
				return $html;
			});
			
			$html .= Form::end();
			
			if(Form::sent('plugin')) {
				return [
					'success' => false,
					'error' => 'validation',
					'content' => $html
				];
			}
			
			return [
				'success' => true,
				'content' => $html
			];
		}
		
		private static function importLanguage() {
			if(!self::$languageImported) {
				self::$languageImported = true;
				if(is_file(__DIR__ . DS . 'languages' . DS . LANGUAGE . '.php')) {
					Language::add(require_once(__DIR__ . DS . 'languages' . DS . LANGUAGE . '.php'));
				}
			}
		}
	}
	
?>