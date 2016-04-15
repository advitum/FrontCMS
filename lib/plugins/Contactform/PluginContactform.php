<?php
	
	namespace Advitum\Frontcms\Plugins;
	
	use Advitum\Frontcms\Form;
	use Advitum\Frontcms\Html;
	use Advitum\Frontcms\Language;
	use Advitum\Frontcms\Plugin;
	use Advitum\Frontcms\Router;
	use Advitum\Frontcms\Session;
	use Advitum\Frontcms\Validator;
	
	class PluginContactform extends Plugin
	{
		private static $languageImported = false;
		
		public static function render($content, $name, $attributes = array()) {
			self::importLanguage();
			
			$html = '';
			
			$fields = [];
			if(isset($attributes['fields'])) {
				$fieldDefaults = [
					'type' => 'text',
					'required' => false
				];
				foreach(json_decode($attributes['fields'], true) as $field) {
					if(isset($field['label']) && !empty($field['label'])) {
						$fields[] = array_merge($fieldDefaults, $field);
					}
				}
			}
			
			if(Router::$user !== null) {
				$divAttributes = [
					'class' => 'fcmsEditablePlugin',
					'data-content' => $content,
					'data-attributes' => json_encode($attributes),
					'data-plugin' => 'contactform',
					'data-name' => $name,
					'data-global' => (int) (isset($attributes['global']) && $attributes['global'])
				];
				
				if($content === '' || ($content = json_decode($content)) === false || !isset($content->to)) {
					$divAttributes['class'] .= ' placeholder';
				}
				
				$html .= '
<div ' . Html::attributes($divAttributes) . '>';
				
				$html .= self::renderFields($name, $fields);
				
				$html .= '
	<button type="button" class="fcmsButton"><i class="fa fa-pencil"></i></button>
</div>';
			} elseif($content !== '' && ($content = json_decode($content)) !== false && isset($content->to)) {
				if(Form::sent($name)) {
					$validate = [];
					
					foreach($fields as $key => $field) {
						if($field['required']) {
							$validate['field' . $key] = [
								'rule' => 'notEmpty'
							];
							
							if(isset($field['error'])) {
								$validate['field' . $key]['message'] = $field['error'];
							}
						}
					}
					
					if(Validator::validate($name, $validate)) {
						Session::setMessage(Language::string('Thank you for your message.'), 'success');
						
						$headers = [
							'From: noreply@' . $_SERVER['HTTP_HOST'],
							'Content-Type: text/plain; charset=UTF-8'
						];
						
						$message = sprintf(Language::string('This message was sent trough your contact form on %s.'), $_SERVER['HTTP_HOST']) . "\n";
						
						foreach($fields as $key => $field) {
							$message .= sprintf("\n%s: %s", $field['label'], Form::value($name, 'field' . $key));
						}
						
						@mail($content->to, sprintf(Language::string('New message - %s'), $_SERVER['HTTP_HOST']), $message, implode("\r\n", $headers));
						
						Router::redirect(Router::here());
					} else {
						Session::setMessage(Language::string('Your message could not be sent.'), 'error');
					}
				}
				
				$html .= self::renderFields($name, $fields);
			}
			
			return $html;
		}
		
		public static function edit($content, $name, $attributes = array()) {
			self::importLanguage();
			
			if(Form::sent('plugin')) {
				$values = json_encode([
					'to' => Form::value('plugin', 'to')
				]);
				
				return [
					'success' => true,
					'content' => self::render($values, $name, $attributes)
				];
			}
			
			$content = json_decode($content);
			
			$html = '';
			
			$html .= Form::create('#', 'plugin');
			
			$html .= Form::input('to', [
				'label' => Language::string('Recipient'),
				'id' => 'to',
				'default' => ($content !== false && isset($content->to) ? $content->to : '')
			]);
			
			$html .= Form::end();
			
			return [
				'success' => true,
				'content' => $html
			];
		}
		
		private static function renderFields($name, $fields) {
			$html = '';
			
			$html .= Form::create(Router::here() . '#' . $name, $name);
			
			if(Router::$user === null) {
				$html .= Session::getMessage();
			}
			
			foreach($fields as $key => $field) {
				$options = [
					'label' => $field['label'],
					'type' => $field['type']
				];
				
				if(isset($field['placeholder'])) {
					$options['placeholder'] = $field['placeholder'];
				}
				
				$html .= Form::input('field' . $key, $options);
			}
			
			$options = [];
			if(Router::$user !== null) {
				$options['disabled'] = 'disabled';
			}
			
			$html .= Form::submit(Language::string('Send'), $options);
			
			$html .= Form::end();
			
			return $html;
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