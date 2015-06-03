<?php
	
	namespace Advitum\Frontcms\Plugins;
	
	use Advitum\Frontcms\Form;
	use Advitum\Frontcms\Html;
	use Advitum\Frontcms\Language;
	use Advitum\Frontcms\Plugin;
	use Advitum\Frontcms\Router;
	use Advitum\Frontcms\Validator;
	
	class PluginBoxes extends Plugin
	{
		private static $languageImported = false;
		
		public static function render($content, $name, $attributes = array()) {
			self::importLanguage();
			
			$html = '';
			
			if(Router::$user !== null) {
				$attributes = [
					'class' => 'fcmsEditablePlugin',
					'data-content' => $content,
					'data-attributes' => json_encode($attributes),
					'data-plugin' => 'boxes',
					'data-name' => $name,
					'data-global' => (int) (isset($attributes['global']) && $attributes['global'])
				];
				
				if($content !== '' && ($content = json_decode($content)) !== false && isset($content->boxes) && count($content->boxes)) {
					$innerHtml = '';
					
					foreach($content->boxes as $box) {
						$innerHtml .= '
		<li>
			<h2>' . htmlspecialchars($box->heading) . '</h2>
			<p>' . nl2br(htmlspecialchars($box->content)) . '</p>
		</li>';
					}
				} else {
					$attributes['class'] .= ' placeholder';
					$innerHtml = '
		<li>
			<h2>' . Language::string('Placeholder') . '</h2>
			<p>' . Language::string('This is a placeholder.') . '</p>
		</li>';
				}
				
				$html .= '
<div ' . Html::attributes($attributes) . '>
	<ul class="boxes">';
				$html .= $innerHtml;
				
				$html .= '
	</ul>
	<button type="button" class="fcmsButton"><i class="fa fa-pencil"></i></button>
</div>';
			} elseif($content !== '') {
				$content = json_decode($content);
				if(isset($content->boxes) && count($content->boxes)) {
					$html .= '
<ul class="boxes">';
					
					foreach($content->boxes as $box) {
						$html .= '
	<li>
		<h2>' . htmlspecialchars($box->heading) . '</h2>
		<p>' . nl2br(htmlspecialchars($box->content)) . '</p>
	</li>';
					}
					
					$html .= '
</ul>';
				}
			}
			
			return $html;
		}
		
		public static function edit($content, $name, $attributes = array()) {
			self::importLanguage();
			
			if(Form::sent('plugin')) {
				$boxIndizes = array_keys(Form::value('plugin', 'heading'));
				Validator::$errors['plugin'] = [];
				
				foreach($boxIndizes as $index) {
					if(
						(isset($_POST['plugin']['heading'][$index]) && !empty($_POST['plugin']['heading'][$index]))
						||
						(isset($_POST['plugin']['content'][$index]) && !empty($_POST['plugin']['content'][$index]))
					) {
						if(!isset($_POST['plugin']['heading'][$index]) || empty($_POST['plugin']['heading'][$index])) {
							Validator::$errors['plugin']['heading.' . $index] = Language::string('Please enter a title.');
						}
						if(!isset($_POST['plugin']['content'][$index]) || empty($_POST['plugin']['content'][$index])) {
							Validator::$errors['plugin']['content.' . $index] = Language::string('Please enter the content.');
						}
					}
				}
				
				if(count(Validator::$errors['plugin']) === 0) {
					$boxes = [];
					foreach($boxIndizes as $index) {
						if(
							(isset($_POST['plugin']['heading'][$index]) && !empty($_POST['plugin']['heading'][$index]))
							||
							(isset($_POST['plugin']['content'][$index]) && !empty($_POST['plugin']['content'][$index]))
						) {
							$boxes[] = [
								'heading' => $_POST['plugin']['heading'][$index],
								'content' => $_POST['plugin']['content'][$index]
							];
						}
					}
					
					$content = json_encode([
						'boxes' => $boxes
					]);
					
					$html = self::render($content, $name, $attributes);
					
					return [
						'success' => true,
						'content' => $html
					];
				}
			}
			
			$html = '';
			
			$html .= Form::create('#', 'plugin');
			
			$html .= '<ul class="repeatable">';
			
			if(isset($boxIndizes) && count($boxIndizes)) {
				foreach($boxIndizes as $index) {
					$html .= '<li>';
					$html .= Form::input('heading.' . $index, [
						'label' => Language::string('Title'),
						'id' => 'heading-' . $index
					]);
					$html .= Form::input('content.' . $index, [
						'label' => Language::string('Content'),
						'type' => 'textarea',
						'id' => 'content-' . $index
					]);
					$html .= '</li>';
				}
			} elseif(!isset($boxIndex) && $content !== false && isset($content->boxes) && count($content->boxes)) {
				foreach($content->boxes as $index => $box) {
					$html .= '<li>';
					$html .= Form::input('heading.', [
						'label' => Language::string('Title'),
						'id' => 'heading-' . $index,
						'default' => $box->heading
					]);
					$html .= Form::input('content.', [
						'label' => Language::string('Content'),
						'type' => 'textarea',
						'id' => 'content-' . $index,
						'default' => $box->content
					]);
					$html .= '</li>';
				}
			} else {
				$html .= '<li>';
				$html .= Form::input('heading.', [
					'label' => Language::string('Title'),
					'id' => 'heading-0'
				]);
				$html .= Form::input('content.', [
					'label' => Language::string('Content'),
					'type' => 'textarea',
					'id' => 'content-0'
				]);
				$html .= '</li>';
			}
			
			$html .= '</ul>';
			
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