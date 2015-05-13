<?php
	
	namespace Advitum\Frontcms;
	
	require_once(VENDOR_PATH . 'UploadHandler.php');
	
	class UploadHandler extends \UploadHandler
	{
		protected function upcount_name_callback($matches) {
		    $index = isset($matches[1]) ? ((int)$matches[1]) + 1 : 1;
		    $ext = isset($matches[2]) ? $matches[2] : '';
		    return '_'.$index.$ext;
		}

		protected function upcount_name($name) {
		    return preg_replace_callback(
		        '/(?:(?:_([\d]+))?(\.[^.]+))?$/',
		        array($this, 'upcount_name_callback'),
		        $name,
		        1
		    );
		}

		protected function get_unique_filename($file_path, $name, $size, $type, $error,
		        $index, $content_range) {
		    while(is_dir($this->get_upload_path($name))) {
		        $name = $this->upcount_name($name);
		    }
		    // Keep an existing filename if this is part of a chunked upload:
		    $uploaded_bytes = $this->fix_integer_overflow((int)$content_range[1]);
		    while(is_file($this->get_upload_path($name))) {
		        if ($uploaded_bytes === $this->get_file_size(
		                $this->get_upload_path($name))) {
		            break;
		        }
		        $name = $this->upcount_name($name);
		    }
		    return rawurlencode($name);
		}
	}
	
?>