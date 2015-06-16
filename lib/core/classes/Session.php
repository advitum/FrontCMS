<?php
	
	namespace Advitum\Frontcms;
	
	class Session
	{
		public static function setMessage($text, $class = false) {
			$_SESSION['message'] = array(
				'text' => $text,
				'class' => $class
			);
		}
		
		public static function getMessage() {
			$html = '';
			
			if(isset($_SESSION['message']) && $_SESSION['message'] !== false) {
				$html .= '<div id="message"' . ($_SESSION['message']['class'] !== false ? ' class="' . htmlspecialchars($_SESSION['message']['class']) . '"' : '') . '>
					' . $_SESSION['message']['text'] . '
				</div>';
				
				$_SESSION['message'] = false;
			}
			
			return $html;
		}
	}
	
?>