<?php
	
	namespace Advitum\Frontcms;
	
	use Advitum\Frontcms\Form;
	
	abstract class Plugin
	{
		protected static function repeatable($formName, $entities, $callback) {
			$html = '<ul class="repeatable">';
			
			if(Form::sent($formName)) {
				$data = Form::values($formName);
				$firstInput = array_shift($data);
				foreach($firstInput as $index => $value) {
					$html .= '<li>';
					
					$html .= call_user_func($callback, $index, null);
					
					$html .= '</li>';
				}
			} elseif(count($entities)) {
				foreach($entities as $index => $entity) {
					$html .= '<li>';
					
					$html .= call_user_func($callback, $index, $entity);
					
					$html .= '</li>';
				}
			} else {
				$html .= '<li>';
				
				$html .= call_user_func($callback, '', null);
				
				$html .= '</li>';
			}
			
			$html .= '</ul>';
			
			return $html;
		}
	}
	
?>