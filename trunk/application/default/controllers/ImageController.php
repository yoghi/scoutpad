<?php


/**
 * @todo: cache support
 * @todo: tinteggiare anche immagini non riflesse
 */

class ImageController extends Zend_Controller_Action
{
	
	protected $params = null;
	
	private $image_name = '';
	
	private $image_dir = '';
	
	private $cache = false;
	
	private $height = null;
	
	private $rgb = array('r' => 127 ,'g' => 127 ,'b' => 127);
	
	private $alpha = array('s' => 80 , 'e' => 0);
	
	private $image = null;
	
	
	
	public function init(){
		
		$this->params = $this->_getAllParams();
		
	}
	
	public function indexAction(){
		
		if ( $this->_url() ){
			
			$filename = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->image_dir. $this->image_name;
			
			$size = getimagesize($filename);
			
			if ($size === false)
			{
				$this->_redirect('/errore/invalid/');
				//echo 'Not a valid image supplied, or this script does not have permissions to access it.';
			}
			
			$width = $size[0];
			$height = $size[1];
			$type = $size[2];
			$mime = $size['mime'];
			
			// quanto vale l'altezza dell'immagine
			$this->height = $height;
			
			//	Detect the source image format - only GIF, JPEG and PNG are supported. If you need more, extend this yourself.
			switch ($type)
			{
				case 1:
					//	GIF
					$source = imagecreatefromgif($filename);
					break;
			
				case 2:
					//	JPG
					$source = imagecreatefromjpeg($filename);
					break;
			
				case 3:
					//	PNG
					$source = imagecreatefrompng($filename);
					break;
			
				default:
					$this->_redirect('/errore/notsupported/');
			}
			
			// guardo se devo modificare il colore all'immagine
			if ( $this->_tint() ) {
				
				$alpha_final = 45;
				
				//	We'll store the final reflection in $output. $buffer is for internal use.
				$this->image = imagecreatetruecolor($width, $this->height);
		
				//  Save any alpha data that might have existed in the source image and disable blending
				imagesavealpha($source, true);
		
				imagesavealpha($this->image, true);
				imagealphablending($this->image, false);
		
				//	Copy the bottom-most part of the source image into the output
				imagecopy($this->image, $source, 0, 0, 0, $height - $this->height, $width, $this->height);
		
				//effetto fading
				imagelayereffect($this->image, IMG_EFFECT_OVERLAY);
				for ($y = 0; $y <= $this->height; $y++)
			    {
			        imagefilledrectangle($this->image, 0, $y, $width, $y, imagecolorallocatealpha($this->image, $this->rgb['r'], $this->rgb['g'], $this->rgb['b'], $alpha_final));
			    }
				
			    $type = 3;
			    
			} else {
				$this->image = $source;
			}
			
			/*
			----------------------------------------------------------------
			Output our final PNG
			----------------------------------------------------------------
			*/
	
			if (!$this->_response->canSendHeaders())
			{
				echo 'Headers already sent, I cannot display an image now. Have you got an extra line-feed in this file somewhere?';
				return;
			}
			else
			{
			    
				switch ($type)
				{
					case 1:
						//	GIF
						$this->_response->setHeader('Content-type','image/gif','true');
						imagegif($this->image);
						break;
				
					case 2:
						//	JPG
						$this->_response->setHeader('Content-type','image/jpg','true');
						imagejpeg($this->image);
						break;
				
					case 3:
						//	PNG
						$this->_response->setHeader('Content-type','image/png','true');
						imagepng($this->image);
						break;
				
					default:
						$this->_redirect('/errore/notsupported/');
				}
				
				imagedestroy($this->image);				
				return;
			}			
			
		} else {
			$this->_redirect('/errore/fourhundredfour/');
		}
		
	}
	
	/**
	 * Rifletto un'immagine		
		img		        required	The source image to reflect
		height	        optional	Height of the reflection (% or pixel value)
        fade_start      optional    Start the alpha fade from whch value? (% value)
        fade_end        optional    End the alpha fade from whch value? (% value)
        cache           optional    Save reflection image to the cache? (boolean)
        tint            optional    Tint the reflection with this colour (hex)
	*/
	public function reflectAction(){
		
		//    GD Version check
		$gd_info = gd_info();

		if ($gd_info['PNG Support'] == false)
		{
			echo 'This version of the GD extension cannot output PNG images.';
			return;
		}

		if (ereg_replace('[[:alpha:][:space:]()]+', '', $gd_info['GD Version']) < '2.0.1')
		{
			echo 'GD library is too old. Version 2.0.1 or later is required, and 2.0.28 is strongly recommended.';
			return;
		}

		//  To cache or not to cache? that is the question
		if ( array_key_exists('cache',$this->params) )
		{
			if ((int) $this->params['cache'] == 1)
			{
				$this->cache = true;
			}
			else
			{
				$this->cache = false;
			}
		}

		
		//	img (the image to reflect)
		if ( array_key_exists('url',$this->params) )
		{
			
			$source_image = $this->params['url'];

			$dir = 'images/';
			
			$r = explode('.',$source_image);

			if ( $r > 2 ){
				//c'è una o piu direttori
				for ( $i=0; $i < count($r)-2; $i++ ) {
					$dir .= $r[$i].DIRECTORY_SEPARATOR;
				}
			}
			
			$image_name = $r[$i].'.'.$r[$i+1];
			
			$source_image = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $dir. $image_name;

			if (file_exists($source_image))
			{
				if ($this->cache)
				{
					$cache_dir = dirname($source_image);
					$cache_base = basename($source_image);
					$cache_file = 'refl_' . md5($image_name) . '_' . $cache_base;
					$cache_path = $cache_dir . DIRECTORY_SEPARATOR . $cache_file;

					if (file_exists($cache_path) && filemtime($cache_path) >= filemtime($source_image))
					{
						// Use cached image
						$image_info = getimagesize($cache_path);
						
						$this->_response->setHeader('Content-type',$image_info['mime'],'true');
						
						readfile($cache_path);
						
						return;
					}
				}
			}
			else
			{
				$this->_redirect('/errore/fourhundredfour/');
			}
		}
		else
		{
			$this->_redirect('/errore/fourhundredfour/');
		}

		//	dimensioni immagine
		$image_details = getimagesize($source_image);

		if ($image_details === false)
		{
			$this->_redirect('/errore/invalid/');
			//echo 'Not a valid image supplied, or this script does not have permissions to access it.';
		}
		
		$width = $image_details[0];
		$height = $image_details[1];
		$type = $image_details[2];
		$mime = $image_details['mime'];

		// quanto vale l'altezza dell'immagine
		$this->_calculateHeight($height);

		//	Detect the source image format - only GIF, JPEG and PNG are supported. If you need more, extend this yourself.
		switch ($type)
		{
			case 1:
				//	GIF
				$source = imagecreatefromgif($source_image);
				break;
		
			case 2:
				//	JPG
				$source = imagecreatefromjpeg($source_image);
				break;
		
			case 3:
				//	PNG
				$source = imagecreatefrompng($source_image);
				break;
		
			default:
				$this->_redirect('/errore/notsupported/');
				echo 'Unsupported image file format.';
				exit();
		}
		
		// rifletto l'immagine
		
		//	We'll store the final reflection in $output. $buffer is for internal use.
		$this->image = imagecreatetruecolor($width, $this->height);
		$buffer = imagecreatetruecolor($width, $this->height);

		//  Save any alpha data that might have existed in the source image and disable blending
		imagesavealpha($source, true);

		imagesavealpha($this->image, true);
		imagealphablending($this->image, false);

		imagesavealpha($buffer, true);
		imagealphablending($buffer, false);

		//	Copy the bottom-most part of the source image into the output
		imagecopy($this->image, $source, 0, 0, 0, $height - $this->height, $width, $this->height);

		//effetto fading
		$this->_fade($width);
		
		//	Rotate and flip it (strip flip method)
		for ($y = 0; $y < $this->height; $y++)
		{
			imagecopy($buffer, $this->image, 0, $y, 0, $this->height - $y - 1, $width, 1);
		}

		$this->image = $buffer;

		/*
		----------------------------------------------------------------
		Output our final PNG
		----------------------------------------------------------------
		*/

		if (!$this->_response->canSendHeaders())
		{
			echo 'Headers already sent, I cannot display an image now. Have you got an extra line-feed in this file somewhere?';
			return;
		}
		else
		{
		    //	PNG
		    $this->_response->setHeader('Content-type','image/png','true');
		    imagepng($this->image);
	
	        // Save cached file
	        if ($this->cache)
	        {
	            imagepng($this->image, $cache_path);
	        }
	
			imagedestroy($this->image);
			return;
		}
	}

	/**
	 * Upload an image
	 */
	public function uploadAction(){
		$this->view = new Sigma_View_TemplateLite();
		$this->view->title = "Upload Image - Campetti Specialit&agrave;";
		$this->view->stylesheet = '<link rel="stylesheet" type="text/css" media="screen" href="/styles/double.css" />';
		$this->view->actionTemplate = 'forms/_uploadImage.tpl';
		$this->view->buttonText = 'Upload';
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	}
	
	/**
	 * calcolo quanto alta deve essere l'immagine da prendere in considerazione per la riflessione
	 */
	protected function _calculateHeight($height){
		
		if (array_key_exists('height',$this->params) )
		{
			$output_height = $this->params['height'];

			//	uso %
			if (substr($output_height, -1) == '%')
			{
				$output_height = (int) substr($output_height, 0, -1);

				if ($output_height == 100)
				{
					$output_height = "0.99";
				}
				elseif ($output_height < 10)
				{
					$output_height = "0.0$output_height";
				}
				else
				{
					$output_height = "0.$output_height";
				}
				
				$this->height = $height * $output_height;
				
			}
			else
			{				
				$this->height = (int) $output_height;
			}
		}
		else
		{
			// default : 50%
			$this->height = $height * 0.50;
		}
	}
	
	/**
	 * Calcolo l'intensità del fade
	 */
	protected function _fadeX(){
		
		if (array_key_exists('fade_start',$this->params) )
			{
				if (strpos($this->params['fade_start'], '%') !== false)
				{
					$alpha_start = str_replace('%', '', $this->params['fade_start']);
					$this->alpha['s'] = (int) (127 * $alpha_start / 100);
				}
				else
				{
					$alpha_start = (int) $params['fade_start'];
	
					if ($alpha_start < 1 || $alpha_start > 127)
					{
						$alpha_start = 80;
					}
					
					$this->alpha['s'] = $alpha_start;
				}
			}
	
			if (array_key_exists('fade_end',$this->params))
			{
				if (strpos($params['fade_end'], '%') !== false)
				{
					$alpha_end = str_replace('%', '', $this->params['fade_end']);
					$alpha_end = (int) (127 * $alpha_end / 100);
				}
				else
				{
					$alpha_end = (int) $this->params['fade_end'];
	
					if ($alpha_end < 1 || $alpha_end > 0)
					{
						$alpha_end = 0;
					}
				}
				
				$this->alpha['e'] = $alpha_end;
			}

	}
	
	/**
	 * Applico il fade all'immagine
	 * 
	 * This is quite simple really. There are 127 available levels of alpha, so we just
	 * step-through the reflected image, drawing a box over the top, with a set alpha level.
	 * The end result? A cool fade.
	 * There are a maximum of 127 alpha fade steps we can use, so work out the alpha step rate
	 * 
	 */
	protected function _fade($width){
		
		// guardo se devo modificare il colore all'immagine
		$this->_tint();
		
		$this->_fadeX();
		
		$alpha_length = abs($this->alpha['s'] - $this->alpha['e']);

	    imagelayereffect($this->image, IMG_EFFECT_OVERLAY);
	
	    for ($y = 0; $y <= $this->height; $y++)
	    {
	        //  Get % of reflection height
	        $pct = $y / $this->height;
	
	        //  Get % of alpha
	        if ($this->alpha['s'] > $this->alpha['e'] )
	        {
	            $alpha = (int) ($this->alpha['s'] - ($pct * $alpha_length));
	        }
	        else
	        {
	            $alpha = (int) ($this->alpha['s'] + ($pct * $alpha_length));
	        }
	        
	        //  Rejig it because of the way in which the image effect overlay works
	        $final_alpha = 127 - $alpha;
	
	        //imagefilledrectangle($output, 0, $y, $width, $y, imagecolorallocatealpha($output, 127, 127, 127, $final_alpha));
	        imagefilledrectangle($this->image, 0, $y, $width, $y, imagecolorallocatealpha($this->image, $this->rgb['r'], $this->rgb['g'], $this->rgb['b'], $final_alpha));
	    }
	}

	/**
	 * Ricoloro l'immagine
	 */
	protected function _tint(){

		if ( array_key_exists('tint',$this->params) )
		{
			//    Extract the hex colour
			$hex_bgc = $this->params['tint'];

			//    Does it start with a hash? If so then strip it
			$hex_bgc = str_replace('#', '', $hex_bgc);

			switch (strlen($hex_bgc))
			{
				case 6:
					$this->rgb['r'] = hexdec(substr($hex_bgc, 0, 2));
					$this->rgb['g'] = hexdec(substr($hex_bgc, 2, 2));
					$this->rgb['b'] = hexdec(substr($hex_bgc, 4, 2));
					break;

				case 3:
					$this->rgb['r'] = substr($hex_bgc, 0, 1);
					$this->rgb['g'] = substr($hex_bgc, 1, 1);
					$this->rgb['b'] = substr($hex_bgc, 2, 1);
					$this->rgb['r'] = hexdec($this->rgb['r'] . $this->rgb['r']);
					$this->rgb['g'] = hexdec($this->rgb['g'] . $this->rgb['g']);
					$this->rgb['b'] = hexdec($this->rgb['b'] . $this->rgb['b']);
					break;

				default:
					//    Wrong values passed, default to white
					$this->rgb['r'] = 127;
					$this->rgb['g'] = 127;
					$this->rgb['b'] = 127;
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Estrapolo l'url del file richiesto
	 */
	protected function _url(){
		
		if ( array_key_exists('url',$this->params) ){
			
			// verifico se è una sottodirectory 
			
			$source_image = $this->params['url'];

			$dir = 'images/';
			
			$r = explode('.',$source_image);

			if ( $r > 2 ){
				//c'è una o piu direttori
				for ( $i=0; $i < count($r)-2; $i++ ) {
					$dir .= $r[$i].DIRECTORY_SEPARATOR;
				}
			}
			
			$this->image_name = $r[$i].'.'.$r[$i+1];
			
			$this->image_dir = $dir;
			
			return true;
		} else {
			return false;
		}
		
	}
}

?>