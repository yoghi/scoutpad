<?php


/**
 * @todo: cache support
 * @todo: tinteggiare anche immagini non riflesse
 */

class ImageController extends Sigma_Controller_Action
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
		try {
			Zend_Loader::loadClass('Files','/home/workspace/Scout/ScoutPad/application/default/models/tables/');
			$config = Zend_Registry::get('config');
			// uso il db come cache sytem
			if ( !is_null($config->db->adapter) ){
				$this->cache = true;
			}
		}
		catch (Zend_Exception $e) {
			var_dump($e);
		}
		
		
		
	}
	
	/**
	 * Visualizza l'immagine richiesta 
	 */
	public function indexAction(){
		
		if ( $this->_url() ){
						
			if ( $this->cache ) {
				
				$files = new Files();
				
				/*
				 * Immagine con modifiche gia applicate
				 */
				$where = $files->getAdapter()->quoteInto('uri = ?', $this->getRequest()->getRequestUri());
				$row = $files->fetchAll($where);
				
				if ( $row !== null && count($row) > 0  ){

					$r = $row->current();
					$type = $r->mimeType;
					
					$this->image = imagecreatefromstring(base64_decode($r->object));
					
					/*
					 * fix problem of alpha channel
					 */
					imagesavealpha($this->image,true);
					imagealphablending($this->image, false);
				
				} else {
				
					/*
					 * Immagine naturale
					 */
					$where = $files->getAdapter()->quoteInto('uri = ?', '/image/index/url/'.$this->params['url']);
					$row = $files->fetchAll($where);
					
					if ( $row !== null && count($row) > 0  ){
						
						$r = $row->current();
						$type = $r->mimeType;
						$source = imagecreatefromstring(base64_decode($r->object));
						
						$width = imagesx ( $source );
    					$height = imagesy ( $source );

						// quanto vale l'altezza dell'immagine
						$this->height = $height;
						
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
						
						//la salvo nel db per il futuro
						$rand = rand(1000,5000);
						while(file_exists('/tmp/'.$rand)) {
							$rand = rand(1000,5000);	
						}
						
						switch ($type)
						{
							case 'image/gif':
							case 1:
								//	GIF
								imagegif($this->image,'/tmp/'.$rand);
								$handle = fopen('/tmp/'.$rand, "r");
								$da = base64_encode(fread($handle, filesize('/tmp/'.$rand)));
								fclose($handle);
								unlink('/tmp/'.$rand);
								$type = 'image/gif';
								break;
							case 'image/jpeg':
							case 'image/jpg':
							case 2:
								//	JPG
								imagejpeg($this->image,'/tmp/'.$rand);
								$handle = fopen('/tmp/'.$rand, "r");
								$da = base64_encode(fread($handle, filesize('/tmp/'.$rand)));
								fclose($handle);
								unlink('/tmp/'.$rand);
								$type = 'image/jpeg';
								break;
								
							case 'image/png':
							case 3:
								//	PNG
								imagesavealpha($this->image,true);
								imagealphablending($this->image, false);
								imagepng($this->image,'/tmp/'.$rand);
								$handle = fopen('/tmp/'.$rand, "r");
								$da = base64_encode(fread($handle, filesize('/tmp/'.$rand)));
								fclose($handle);
								unlink('/tmp/'.$rand);
								$type = 'image/png';
								break;
							default:
								$this->_redirect('/errore/notsupported/');
						}
						
						$data = array(
							'uri' => $this->getRequest()->getRequestUri(),
							'object' => $da,
							'mimeType' => $type
						);
						
						try {
							$files = new Files();
							$files->insert($data);
						}
						catch( Zend_Exception $e){
							Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR );
						}
						
					}
						
				}
				
			} 
			
			// non ho ancora trovato
			if ( $this->image === null ) {
				
				//devo caricare l'immagine dal filesystem
		
				$filename = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->image_dir. $this->image_name;

				if ( !file_exists($filename) ){
					$this->_redirect('/errore/fourhundredfour/');
				}
				
				$size = getimagesize($filename);
		
				if ($size === false)
				{
					$this->_redirect('/errore/invalid/');
					//echo 'Not a valid image supplied, or this script does not have permissions to access it.';
				}
		
				$width = $size[0];
				$height = $size[1];
				$type = $size[2];
				//$mime = $size['mime'];

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
					case 'image/gif':
					case 1:
						//	GIF
						$this->_response->setHeader('Content-type','image/gif','true');
						imagegif($this->image);
						break;
				
					case 'image/jpeg':
					case 'image/jpg':	
					case 2:
						//	JPG
						$this->_response->setHeader('Content-type','image/jpg','true');
						imagejpeg($this->image);
						break;
				
					case 'image/png':
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
			
			if ($this->cache)
			{
				$files = new Files();
				
				/*
				 * Immagine con modifiche gia applicate
				 */
				$where = $files->getAdapter()->quoteInto('uri = ?', $this->getRequest()->getRequestUri());
				$row = $files->fetchAll($where);
				
				if ( $row !== null && count($row) > 0  ){

					$r = $row->current();
					$type = $r->mimeType;
					
					$this->_response->setHeader('Content-type','image/png','true');
					
					$this->image = imagecreatefromstring(base64_decode($r->object));
					
					imagesavealpha($this->image, true);
					imagealphablending($this->image, false);
					
					imagepng($this->image);
					
					return;
				
				} 
				
				/*
				 * Immagine naturale
				 */
				$where = $files->getAdapter()->quoteInto('uri = ?', '/image/index/url/'.$this->params['url']);
				$row = $files->fetchAll($where);
				
				if ( $row !== null && count($row) > 0  ){
					
					$r = $row->current();
					$type = $r->mimeType;
					$source = imagecreatefromstring(base64_decode($r->object));
					
					$width = imagesx($source);
					$height = imagesy($source);
					
					// quanto vale l'altezza dell'immagine
					$this->_calculateHeight($height);
					
				} else {
					
					$source_image = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $dir. $image_name;
	
					if (!file_exists($source_image)) {
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
					//$mime = $image_details['mime'];
			
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
					
				}
				
			}

		}
		else
		{
			$this->_redirect('/errore/fourhundredfour/');
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
	        	$rand = rand(1000,5000);
	            //imagepng($this->image, $cache_path);
	            imagesavealpha($this->image,true);
				imagealphablending($this->image, false);
				imagepng($this->image,'/tmp/'.$rand);
				$handle = fopen('/tmp/'.$rand, "r");
				$da = base64_encode(fread($handle, filesize('/tmp/'.$rand)));
				fclose($handle);
				unlink('/tmp/'.$rand);
	        	$data = array(
							'uri' => $this->getRequest()->getRequestUri(),
							'object' => $da,
							'mimeType' => 'image/png'
						);
				
				try {
					$files = new Files();
					$files->insert($data);
				}
				catch( Zend_Exception $e){
					Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR );
				}
	        }
	
			imagedestroy($this->image);
			return;
		}
	}

	/**
	 * Upload an image
	 */
	public function addAction(){
		$this->view = new Sigma_View_TemplateLite();
		$this->view->title = "Upload Image - Campetti Specialit&agrave;";
		$this->view->stylesheet = '<link rel="stylesheet" type="text/css" media="screen" href="/styles/double.css" />';
		$this->view->actionTemplate = 'forms/_uploadImage.tpl';
		$this->view->buttonText = 'Upload';
		$this->view->action = 'upload';
		$this->getResponse()->setBody( $this->view->render('site2c.tpl') );
	}
	
	/**
	 * Carico un'immagine in un database o nel filesystem
	 */
	public function uploadAction(){
		
		//$this->view->title = "Uploading";

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			//$filter = Zend_Registry::get('filter');
			
			Zend_Loader::loadClass('Zend_Filter_Int');
			
			$filter = new Zend_Filter_Int();
			
			$max_size = $filter->filter($_POST['MAX_FILE_SIZE']);
            
			/*
				Array
				(
				    [ufile] => Array
				        (
				            [name] => p1240001.jpg
				            [type] => image/jpeg
				            [tmp_name] => /tmp/phpZpDhbc
				            [error] => 0
				            [size] => 953847
				        )
				
				)
				Array
				(
				    [MAX_FILE_SIZE] => 2097152
				    [submit] => Upload
					[type] => emoticons
				)
			 */

			/*
			 * Verifico di essere riuscito a caricare l'immagine senno gestisco l'errore
			 */
			if ( $_FILES['ufile']['error'] != 0 ){
				
				switch ($_FILES['ufile']['error']) {
					
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$this->_redirect('/errore/toobig/maxsize/'.$max_size);
						break;
					case UPLOAD_ERR_NO_FILE:
						$this->_redirect('/errore/missing/');
						break;
					case UPLOAD_ERR_NO_TMP_DIR:
						$this->_redirect('/errore/notavaible/');
						break;
					default:
						break;
				}

			}
			
			
			$filter = new Zend_Filter();
			
			Zend_Loader::loadClass('Zend_Filter_Alpha');
			Zend_Loader::loadClass('Zend_Filter_StringToLower');
			Zend_Loader::loadClass('Zend_Filter_StringTrim');
			
			$filter->addFilter(new Zend_Filter_Alpha());
			
			$filter->addFilter(new Zend_Filter_StringToLower());
			
			$filter->addFilter(new Zend_Filter_StringTrim());
			
			$yy = date("Y");
			$mm = date("m");
			$dd = date("d"); 
			
			/*
			 * In base al tipo di immagine lo inserisco nella opportuna cartella
			 */			
			$type = $filter->filter($_POST['type']);
			
			switch ($type) {
				case 'emoticons':
				case 'icons':
					$dir = 'sources'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR;
					$uri = '/image/index/url/'.$type.'.'.$_FILES['ufile']['name'];
					break;
				case 'foto':
					$dir = 'sources'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'foto'.DIRECTORY_SEPARATOR.$yy.DIRECTORY_SEPARATOR.$mm.DIRECTORY_SEPARATOR.$dd.DIRECTORY_SEPARATOR;
					$uri = '/image/index/url/foto.'."$yy.$mm.$dd.".$_FILES['ufile']['name'];
					break;
				default:
					$dir = 'sources'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR;
					$uri = '/image/index/url/'.$_FILES['ufile']['name'];
					break;
			}
			
			if ( $this->cache ) {
				
				$files = new Files();
				$where = $files->getAdapter()->quoteInto('uri = ?', $uri);
				$row = $files->fetchAll($where);
				
				if ( $row !== null && count($row) > 0  ){
					 //l'immagine già esiste
					 $this->_redirect('/errore/exist/');		 
				} else {
					//posso inserirla
					
					$handle = fopen($_FILES["ufile"]["tmp_name"], "r");
					$da = base64_encode(fread($handle, filesize($_FILES["ufile"]["tmp_name"])));
					fclose($handle);
					
					$data = array(
						'uri' => $uri,
						'object' => $da,
						'mimeType' => $_FILES['ufile']['type']
					);
					
					try {
						$files->insert($data);
					}
					catch( Zend_Exception $e){
						Zend_Registry::get('log')->log($e->getMessage(),Zend_Log::ERR );
					}
					
					$this->_redirect($uri);
				}
				
			} else {
				
				if ( !is_dir($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$dir) ){
					mkdir($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$dir,0770,true);
					chmod($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$dir,0770);
				}
				
				if ( move_uploaded_file($_FILES["ufile"]["tmp_name"],$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$dir.$_FILES['ufile']['name']) ) {
					$this->_redirect($uri);
				} else {
					$this->_redirect('/errore/');
				}
				
			}

		}
		else {
			$this->_redirect('/image/add');
		}
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

			$dir = 'sources'.DIRECTORY_SEPARATOR.'images/';
			
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