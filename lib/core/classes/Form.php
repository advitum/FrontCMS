<?php
	
	namespace Advitum\Frontcms;
	
	class Form
	{
		private static $form = null;
		
		public static function create($action, $form) {
			self::$form = $form;
			
			$html = '<form ' . self::attrs(array(
				'action' => $action,
				'method' => 'POST',
				'id' => self::$form
			)) . '>';
			$html .= self::hidden('_form', self::$form);
			
			return $html;
		}
		
		public static function end($label = false) {
			if(self::$form === null) {
				return '';
			}
			
			$html = '';
			
			if($label !== false) {
				$html .= self::submit($label);
			}
			
			$html .= '</form>';
			
			self::$form = null;
			
			return $html;
		}
		
		public static function input($name, $options = array()) {
			if(self::$form === null) {
				return '';
			}
			
			$defaultOptions = array(
				'type' => ($name == 'password' ? 'password' : 'text'),
				'label' => ucfirst($name),
				'id' => self::$form . '_' . $name,
				'default' => '',
				'class' => '',
				'options' => array(),
				'div' => true
			);
			
			$options = array_merge($defaultOptions, $options);
			$error = Validator::error(self::$form, $name);
			$value = ($options['type'] == 'password' || $options['type'] == 'file' ? false : (self::value(self::$form, $name) ? self::value(self::$form, $name) : $options['default']));
			
			$html = '';
			
			if($options['div']) {
				$html .= '<div class="input ' . htmlspecialchars($options['type']) . ($error !== false ? ' error' : '') . '">';
			}
			
			if($error !== false) {
				$html .= '<div class="message error">' . htmlspecialchars($error) . '</div>';
				$options['class'] .= ' error';
			}
			
			$attributes = $options;
			unset($attributes['options']);
			unset($attributes['type']);
			unset($attributes['label']);
			unset($attributes['default']);
			unset($attributes['div']);
			$attributes['name'] = self::$form . '[' . $name . ']';
			
			switch($options['type']) {
				case 'select':
					if($options['label'] !== false) { 
						$html .= '<label for="' . htmlspecialchars($options['id']) . '">' . htmlspecialchars($options['label']) . '</label>';
					}
					
					$html .= '<select ' . self::attrs($attributes) . '>';
					
					foreach($options['options'] as $key => $option) {
						if(is_array($option)) {
							$key = $option[0];
							$option = $option[1];
						}
						
						$html .= '<option value="' . htmlspecialchars($key) . '"' . (htmlspecialchars($key) == $value ? ' selected="selected"' : '') . '>' . htmlspecialchars($option) . '</option>';
					}
					
					$html .= '</select>';
					
					break;
				case 'textarea':
					if($options['label'] !== false) { 
						$html .= '<label for="' . htmlspecialchars($options['id']) . '">' . htmlspecialchars($options['label']) . '</label>';
					}
					
					$html .= '<textarea ' . self::attrs($attributes) . '>' . htmlspecialchars($value) . '</textarea>';
					
					break;
				case 'checkbox':
				case 'radio':
					$attributes['type'] = $options['type'];
					$attributes['value'] = '1';
					if($value) {
						$attributes['checked'] = 'checked';
					}
					$html .= '<input ' . self::attrs($attributes) . ' />';
					
					if($options['label'] !== false) { 
						$html .= '<label for="' . htmlspecialchars($options['id']) . '">' . htmlspecialchars($options['label']) . '</label>';
					}
					
					break;
				default:
					if($options['label'] !== false) { 
						$html .= '<label for="' . htmlspecialchars($options['id']) . '">' . htmlspecialchars($options['label']) . '</label>';
					}
					
					$attributes['type'] = $options['type'];
					$attributes['value'] = $value;
					$html .= '<input ' . self::attrs($attributes) . ' />';
					
					break;
			}
			
			if($options['div']) {
				$html .= '</div>';
			}
			
			return $html;
		}
		
		public static function hidden($name, $value) {
			if(self::$form === null) {
				return '';
			}
			
			$html = '';
			
			$html .= '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '" />';
			
			return $html;
		}
		
		public static function submit($label, $options = array()) {
			if(self::$form === null) {
				return '';
			}
			
			$defaultOptions = array(
				'escape' => true,
				'div' => true
			);
			
			$options = array_merge($defaultOptions, $options);
			
			$html = '';
			
			if($options['div']) {
				$html .= '<div class="submit">';
			}
			$html .= '<button type="submit">' . ($options['escape'] ? htmlspecialchars($label) : $label) . '</button>';
			if($options['div']) {
				$html .= '</div>';
			}
			
			return $html;
		}
		
		public static function sent($name) {
			return isset($_POST['_form']) && $_POST['_form'] == $name;
		}
		
		public static function value($form, $name) {
			$value = false;
			
			if(isset($_POST[$form][$name])) {
				$value = $_POST[$form][$name];
			} elseif(isset($_GET[$form][$name])) {
				$value = $_GET[$form][$name];
			} elseif(isset($_FILES[$name])) {
				$value = $_FILES[$name];
			}
			
			return $value;
		}
		
		private static function attrs($attrs) {
			return Html::attributes($attrs);
		}
	}
	
?>