<?php
	
	class AutoImg
	{
		private
			$debug = false,
			$cacheDir = 'cache',
			$query,
			$cacheFile,
			$imageFile,
			$documentRoot,
			$params,
			$image,
			$sX,
			$sY;
		
		public function __construct() {
			$this->documentRoot = dirname(dirname(__FILE__));
			
			list($junk, $this->query) = explode('/autoimg/', $_SERVER["REQUEST_URI"], 2);
			unset($junk);
			
			$this->cacheFile = dirname(__FILE__) . '/' . $this->cacheDir . '/' . md5($this->query);
			
			$this->parseParams();
			
			if(substr($this->imageFile, 0, 4) != 'http' && !is_file($this->imageFile)) {
				header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
				exit();
			}
			
			$this->createCache();
		}
		
		public function output() {
			header('Content-Type: image/jpeg');
			echo file_get_contents($this->cacheFile);
			exit();
		}
		
		private function parseParams() {
			list($params, $this->imageFile) = explode('/', $this->query, 2);
			
			if(substr($this->imageFile, 0, 4) != 'http') {
				$this->imageFile = $this->documentRoot . '/' . $this->imageFile;
			}
			
			$params = explode('-', $params);
			foreach($params as $key => $param) {
				unset($params[$key]);
				if(strlen($param) > 1) {
					$params[substr($param, 0, 1)] = substr($param, 1);
				} else {
					$params[$param] = $param;
				}
			}
			$this->params = $params;
		}
		
		private function createCache() {
			if($this->debug || !is_file($this->cacheFile) || filemtime($this->cacheFile) < time() - 60 * 60 * 24 * 7 || (substr($this->imageFile, 0, 4) != 'http' && filemtime($this->cacheFile) < filemtime($this->imageFile))) {
				
				$this->image = imagecreatefromstring(file_get_contents($this->imageFile));
				$this->sX = imageSX($this->image);
				$this->sY = imageSY($this->image);
				
				if(isset($this->params['w']) && isset($this->params['h']) && isset($this->params['c'])) {
					$this->crop();
				} elseif(isset($this->params['w']) || isset($this->params['h'])) {
					$this->resize();
				}
				
				if(isset($this->params['g'])) {
					$this->grayscale();
				}
				
				if(isset($this->params['t'])) {
					$this->tint();
				}
				
				imagejpeg($this->image, $this->cacheFile);
				
				@imagedestroy($this->image);
			}
		}
		
		private function tint() {
			$color = sscanf($this->params['t'], '%02x%02x%02x');
			
			if($color[0] !== null && $color[1] !== null && $color[2] !== null) {
				if(!isset($this->params['g'])) {
					$this->grayscale();
				}
				
				if (imageistruecolor($this->image)) {
					imagetruecolortopalette($this->image, false, 256);
				}
				
				$stepR = $color['0']/255;
				$stepG = $color['1']/255;
				$stepB = $color['2']/255;

				$aColor = array();
				for ($i = 0; $i<=255; $i++){
					$aColor[$i] = array();
					
					$aColor[$i]['r'] = $color[0] - ($i*$stepR);
					$aColor[$i]['g'] = $color[1] - ($i*$stepG);
					$aColor[$i]['b'] = $color[2] - ($i*$stepB);
				}

				for ($c = 0; $c < imagecolorstotal($this->image); $c++){
					$currentColorRGB = imagecolorsforindex($this->image, $c);
					$gray = 255 - $currentColorRGB['red'];
					imagecolorset($this->image, $c, (int)$aColor[$gray]['r'], (int)$aColor[$gray]['g'], (int)$aColor[$gray]['b']);
				}
			}
		}
		
		private function grayscale() {
			imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		}
		
		private function crop() {
			$ratio = $this->params['w'] / $this->params['h'];
			
			$srcImage = $this->image;
			
			$this->image = imagecreatetruecolor($this->params['w'], $this->params['h']);
			if($ratio <= $this->sX / $this->sY) {
				imagecopyresampled($this->image, $srcImage, 0, 0, ($this->sX - $this->sY * $ratio) / 2, 0, $this->params['w'], $this->params['h'], $this->sY * $ratio, $this->sY);
			} else {
				imagecopyresampled($this->image, $srcImage, 0, 0, 0, ($this->sY - $this->sX / $ratio) / 2, $this->params['w'], $this->params['h'], $this->sX, $this->sX / $ratio);
			}
		}
		
		private function resize() {
			$ratio = $this->sX / $this->sY;
			
			if(isset($this->params['w']) && !isset($this->params['h'])) {
				$resizeDir = 'x';
				$size = $this->params['w'];
			} elseif(isset($this->params['h']) && !isset($this->params['w'])) {
				$resizeDir = 'y';
				$size = $this->params['h'];
			} else {
				if($ratio >= $this->params['w'] / $this->params['h']) {
					$resizeDir = 'x';
					$size = $this->params['w'];
				} else {
					$resizeDir = 'y';
					$size = $this->params['h'];
				}
			}
			
			if($resizeDir == 'x') {
				$newX = $size;
				$newY = $size / $ratio;
			} else {
				$newX = $size * $ratio;
				$newY = $size;
			}
			
			$srcImage = $this->image;
			$this->image = imagecreatetruecolor($newX, $newY);
			
			imagecopyresampled($this->image, $srcImage, 0, 0, 0, 0, $newX, $newY, $this->sX, $this->sY);
			imagedestroy($srcImage);
			
			$this->sX = $newX;
			$this->sY = $newY;
		}
	}
	
?>