<?php
	
	namespace Advitum\Frontcms;
	
	require_once('ValidationRules.php');
	
	class Validator
	{
		public static $errors = array();
		
		public static function validate($form, $fields) {
			self::$errors[$form] = array();
			
			$defaultOptions = array(
				'rules' => 'notEmpty',
				'message' => 'Please input something in field %s.'
			);
			
			foreach($fields as $field => $options) {
				if(is_numeric($field)) {
					$field = $options;
					$options = array();
				}
				
				$options = array_merge($defaultOptions, $options);
				$error = false;
				
				$value = Form::value($form, $field);
				
				if(!is_array($options['rules'])) {
					$options['rules'] = array($options['rules']);
				}
				
				foreach($options['rules'] as $rule) {
					if(is_string($rule)) {
						$rule = '\Advitum\Frontcms\ValidationRules::' . $rule;
					}
					if(is_callable($rule)) {
						$error = !call_user_func($rule, $value);
						if($error) {
							break;
						}
					}
				}
				
				if($error) {
					self::$errors[$form][$field] = sprintf($options['message'], ucfirst($field));
				}
			}
			
			return count(self::$errors[$form]) == 0;
		}
		
		public static function errors($form) {
			if(!isset(self::$errors[$form])) {
				self::$errors[$form] = array();
			}
			
			return self::$errors[$form];
		}
		
		public static function error($form, $field) {
			if(!isset(self::$errors[$form]) || !isset(self::$errors[$form][$field])) {
				return false;
			} else {
				return self::$errors[$form][$field];
			}
		}
	}
	
?>