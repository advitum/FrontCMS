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
		
		public function post($print_response = true) {
		    if ($this->get_query_param('_method') === 'DELETE') {
		        return $this->delete($print_response);
		    }
		    $upload = $this->get_upload_data($this->options['param_name']);
		    // Parse the Content-Disposition header, if available:
		    $content_disposition_header = $this->get_server_var('HTTP_CONTENT_DISPOSITION');
		    $file_name = $content_disposition_header ?
		        rawurldecode(preg_replace(
		            '/(^[^"]+")|("$)/',
		            '',
		            $content_disposition_header
		        )) : null;
		    // Parse the Content-Range header, which has the following form:
		    // Content-Range: bytes 0-524287/2000000
		    $content_range_header = $this->get_server_var('HTTP_CONTENT_RANGE');
		    $content_range = $content_range_header ?
		        preg_split('/[^0-9]+/', $content_range_header) : null;
		    $size =  $content_range ? $content_range[3] : null;
		    $files = array();
		    if ($upload) {
		        if (is_array($upload['tmp_name'])) {
		            // param_name is an array identifier like "files[]",
		            // $upload is a multi-dimensional array:
		            foreach ($upload['tmp_name'] as $index => $value) {
		                $files[] = $this->handle_file_upload(
		                    $upload['tmp_name'][$index],
		                    $file_name ? $file_name : $upload['name'][$index],
		                    $size ? $size : $upload['size'][$index],
		                    $upload['type'][$index],
		                    $upload['error'][$index],
		                    $index,
		                    $content_range
		                );
		            }
		        } else {
		            // param_name is a single object identifier like "file",
		            // $upload is a one-dimensional array:
		            $files[] = $this->handle_file_upload(
		                isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
		                $file_name ? $file_name : (isset($upload['name']) ?
		                        $upload['name'] : null),
		                $size ? $size : (isset($upload['size']) ?
		                        $upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
		                isset($upload['type']) ?
		                        $upload['type'] : $this->get_server_var('CONTENT_TYPE'),
		                isset($upload['error']) ? $upload['error'] : null,
		                null,
		                $content_range
		            );
		        }
		        
		        $files = array_map(function($file) {
		            $file->size = [
		                $file->size,
		                Format::fileSize($file->size)
		            ];
		            return $file;
		        }, $files);
		    }
		    $response = array($this->options['param_name'] => $files);
		    return $this->generate_response($response, $print_response);
		}
	}
	
?>