<?php
/*
 *	added to Roxy Fileman for use in RJCKEditor
 *	provides for automatic image orientation
 */

function getMimeType ($filename)
{
	$mimetype = 'application/octet-stream';
	if (function_exists('finfo_fopen')) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);
	} elseif (function_exists('getimagesize')) {
		$size = @getimagesize($filename);
		$mimetype = $size['mime'];
	} elseif (function_exists('mime_content_type')) {
		$mimetype = mime_content_type($filename);
	}
	return $mimetype;
}

function orient_and_make_thumb ($fpath, $dothm=false)
{
	$fpath = realpath($fpath);	//loggit($fpath);
	$isize = getimagesize($fpath);
	if (!$isize) return;
	$tool = getImgTool($fpath);
	$exif = @exif_read_data($fpath);
	if ($exif && isset($exif['Orientation'])) {
		$ort = $exif['Orientation'];
		if ($ort !== 1) {
			$tool->orientImage($ort);
		}
	}
	if ($dothm) {
		@mkdir(dirname($fpath).'/.thumbs', 0777, true);
		$tool->makeThumb(dirname($fpath).'/.thumbs/'.basename($fpath));
	}
}

function getImgTool ($fpath)
{
	if (extension_loaded('imagick')) return new Img_imx(dirname($fpath), basename($fpath));
	else return new Img_gd(dirname($fpath), basename($fpath));
}

function loggit ($v)
{
	file_put_contents('log.txt', print_r($v, true)."\n", FILE_APPEND);
}

class ImageTool {

	// image information
	protected $imginfo;

	// needed actions to correctly orient an image based on its current orientation
	// array(<rotate angle>, <mirror>)
	protected $orientAction = array(
		1 => array(0, false),
		2 => array(0, true),
		3 => array(180, false),
		4 => array(180, true),
		5 => array(-90, true),
		6 => array(-90, false),
		7 => array(90, true),
		8 => array(90, false)
	);

}
 
class Img_imx extends ImageTool
{
	// image resource
	var $imgRes;

	// px
	var $height = 0;
	var $width = 0;

	// for img height/width tags
	var $string;

	// output report or error message
	protected $message;

	// file + dir
	var $directory;
	var $filename;
	protected $srcPath;

	// output quality, 0 - 100
	var $quality;

	//constructor
	public function __construct ($directory, $filename)
	{	//loggit(array($directory, $filename));
		if (!$filename) {
			$this->directory = dirname($directory);
			if ($this->directory == '.') $this->directory = '';
			$this->filename	 = basename($directory);
		} else {
			$this->directory = $directory;
			$this->filename	 = $filename;
		}
		$this->srcPath = $this->directory .'/'. $this->filename;

		if (file_exists($this->srcPath)) {

			$this->filesize = round(filesize($this->srcPath) / 1000);

			if ($this->filesize > 0) {

				$this->imginfo = getimagesize($this->srcPath);

				if ($this->imginfo && !$this->imgRes) {
					$this->imgRes = new Imagick($this->srcPath);
				}

				$this->width  = $this->imginfo[0];
				$this->height = $this->imginfo[1];
				$this->string = $this->imginfo[3];
			} //else loggit($this);
		} //else loggit($this);
		//loggit($this);
	}

	//destructor
	public function __destruct ()
	{
		if ($this->imgRes) {
			$this->imgRes->clear();
			$this->imgRes->destroy();
		}
	}


	public function resizeImage ($new_w = 0, $new_h = 0)
	{
		$fit = ($new_w === 0 || $new_h === 0) ? 1 : 0;
		$this->imgRes->resizeImage($new_w, $new_h, Imagick::FILTER_LANCZOS, 1/*, $fit*/);
		$this->imgRes->writeImage($this->srcPath);

		// Clear current resources
		$this->imgRes->clear();

		// Call the constructor again to repopulate the dimensions etc
		$this->__construct($this->directory, $this->filename);

		return $this;
	}


	public function orientImage ($from)
	{
		$oAct = $this->orientAction[$from];
		if ($oAct[0] !==  0) $this->imgRes->rotateimage("#000", -$oAct[0]); 
		if ($oAct[1]) $this->imgRes->flopImage();

		$this->imgRes->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
		$this->imgRes->writeImage($this->srcPath);

		// Clear current resources
//		$this->imgRes->clear();

		// Call the constructor again to repopulate the dimensions etc
		$this->__construct($this->directory, $this->filename);
	}


	public function saveImage ($destPath, $type='jpg')
	{
		$this->imgRes->writeImage("{$type}:{$destPath}");
	}


	public function send ($maxS=0, $type='jpeg')
	{
		if ($type) {
			$this->imgRes->setImageFormat($type);
		}
		if ($maxS) {
			if ($this->width > $this->height) {
				$this->imgRes->resizeImage($maxS, 0, Imagick::FILTER_LANCZOS, 1);
			} else {
				$this->imgRes->resizeImage(0, $maxS, Imagick::FILTER_LANCZOS, 1);
			}
		}
		
		header('Content-type: image/' . $this->imgRes->getImageFormat());
		echo $this->imgRes->getImageBlob();
	}


	public function makeThumb ($dest, $maxd=200)
	{
		$ratio = max($this->width, $this->height) / $maxd;
		$destWidth = (int)($this->width / $ratio);
		$destHeight = (int)($this->height / $ratio);
		$this->imgRes->setImageFormat('PNG8');
		$this->imgRes->thumbnailImage($destWidth, $destHeight);
	//	$this->imgRes->resizeImage($destWidth, $destHeight, Imagick::FILTER_LANCZOS, 1);

//		$this->imgRes->setImageCompression(Imagick::COMPRESSION_JPEG);
		// Set compression level (1 lowest quality, 100 highest quality)
//		$this->imgRes->setCompressionQuality(09);
		// Strip out unneeded meta data
//		$this->imgRes->stripImage();

	//	if ($sharpen==1 && $CONFIG['enable_unsharp']==1) {
	//		$this->imgRes->sharpenImage(0, $CONFIG['unsharp_amount']/100);
	//	}

		$this->imgRes->writeImage($dest);
/*
		for ($compression = 0; $compression <= 9; $compression++) {
			for ($filter = 0; $filter <= 9; $filter++) {
				$output = clone $this->imgRes;
				$compressionType = intval($compression . $filter);

				//Use this for ImageMagick releases after 6.8.7-5
				$output->setCompressionQuality($compressionType);

				//Use this for ImageMagick releases before 6.8.7-5 
				$output->setImageCompressionQuality($compressionType);

				$outputName = $dest . "$compression$filter.jpg";
				$output->writeImage($outputName);
			}
		}
*/
	}

}

class Img_gd extends ImageTool
{
	// image resource
	var $imgRes;

	// px
	var $height = 0;
	var $width = 0;

	// for img height/width tags
	var $string;

	// output report or error message
	protected $message;

	// file + dir
	var $directory;
	var $filename;

	// output quality, 0 - 100
	var $quality;

	// truecolor available, boolean
	protected $truecolor;

	//constructor
	public function __construct ($directory, $filename, $previous = null)
	{
		if (!$filename) {
			$this->directory = dirname($directory);
			if ($this->directory == '.') $this->directory = '';
			$this->filename	 = basename($directory);
		} else {
			$this->directory = $directory;
			$this->filename	 = $filename;
		}
		$this->previous	 = $previous;
		$this->imgRes	 = $previous ? $previous->imgRes : null;

		$this->srcPath = $this->directory .'/'. $this->filename;

		if (file_exists($this->srcPath)) {

			$this->filesize = round(filesize($this->srcPath) / 1000);

			if ($this->filesize > 0) {

				$this->imginfo = getimagesize($this->srcPath);

				if ($this->imginfo && !$this->imgRes) {
					$this->imgRes = $this->getimgRes($this->srcPath, $this->imginfo[2]);
				}

				if (function_exists('imagecreatetruecolor')) {
					$this->truecolor = true;
				}

				$this->width  = $this->imginfo[0];
				$this->height = $this->imginfo[1];
				$this->string = $this->imginfo[3];
			}
		}
	}


	// private methods
	private function getimgRes ($name, $type)
	{
		switch ($type) {

		case 1:
			$im = imagecreatefromgif($name);
			break;

		case 2:
			$im = imagecreatefromjpeg($name);
			break;

		case 3:
			$im = imagecreatefrompng($name);
			break;
		}

		return $im;
	}


	private function createUnique ($imgnew)
	{
		$unique_str = 'temp_' . md5(rand(0, 999999)) . '.jpg';

		imagejpeg($imgnew, $this->directory . $unique_str, $this->quality);
		imagedestroy($this->imgRes);

		// Don't clutter with old images
		@unlink($this->srcPath);

		// Create a new ImageObject
		return new imageObject($this->directory, $unique_str, $imgnew);
	}


	private function createImage ($new_w, $new_h)
	{
		if (function_exists('imagecreatetruecolor')) {
			$retval = imagecreatetruecolor($new_w, $new_h);
		}

		if (!$retval) {
			$retval = imagecreate($new_w, $new_h);
		}

		return $retval;
	}


	public function rotateImage ($angle)
	{
		if ($angle == 180){
			$dst_img = imagerotate($this->imgRes, $angle, 0);
		} else {
			$width = imagesx($this->imgRes);
			$height = imagesy($this->imgRes);

			if ($width > $height) {
				$size = $width;
			} else {
				$size = $height;
			}

			$dst_img = $this->createImage($size, $size);
			imagecopy($dst_img, $this->imgRes, 0, 0, 0, 0, $width, $height);
			$dst_img = imagerotate($dst_img, $angle, 0);
			$this->imgRes = $dst_img;
			$dst_img = $this->createImage($height, $width);

			if ((($angle == 90) && ($width > $height)) || (($angle == 270) && ($width < $height))) {
				imagecopy($dst_img, $this->imgRes, 0, 0, 0, 0, $size, $size);
			}

			if ((($angle == 270) && ($width > $height)) || (($angle == 90) && ($width < $height))){
				imagecopy($dst_img, $this->imgRes, 0, 0, $size - $height, $size - $width, $size, $size);
			}
		}

		return $this->createUnique($dst_img);
	}


	public function resizeImage ($new_w = 0, $new_h = 0)
	{
		$dst_img = $this->createImage($new_w, $new_h);

		$result = imagecopyresampled($dst_img, $this->imgRes, 0, 0, 0, 0, $new_w, $new_h, $this->width, $this->height);

		if (!$result) {
			$result = @imagecopyresized($dst_img, $this->imgRes, 0, 0, 0, 0, $new_w, $new_h, $this->width,$this->height);
		}

		return $dst_img;
	}


	private function _mirrorImage ($img)
	{
		$width = imagesx ($img);
		$height = imagesy ($img);

		$src_x = $width -1;
		$src_y = 0;
		$src_width = -$width;
		$src_height = $height;

		$imgdest = imagecreatetruecolor($width, $height);

		if (imagecopyresampled($imgdest, $img, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height)) {
			return $imgdest;
		}

		return $img;
	}


	public function orientImage ($from=1)
	{
		$dest = $this->srcPath;

		$oAct = $this->orientAction[$from];
		if ($oAct[0] !==  0) $this->imgRes = imagerotate($this->imgRes, $oAct[0], 0); 
		if ($oAct[1]) $this->imgRes = $this->_mirrorImage($this->imgRes);

		switch ($this->imginfo[2])
		{
			case 1:
				imagegif($this->imgRes, $dest);
				break;
			case 2:
				imagejpeg($this->imgRes, $dest, 100);
				break;
			case 3:
				imagepng($this->imgRes, $dest, 0);
				break;
		}
		$this->width = imagesx($this->imgRes);
		$this->height = imagesy($this->imgRes);
	}

	public function makeThumb ($dest, $maxd=200)
	{
		$ratio = max($this->width, $this->height) / $maxd;
		$destWidth = (int)($this->width / $ratio);
		$destHeight = (int)($this->height / $ratio);
		$dst_img = $this->resizeImage($destWidth, $destHeight);

	//	if ($sharpen==1 && $CONFIG['enable_unsharp']==1) {
	//		$this->imgRes->sharpenImage(0, $CONFIG['unsharp_amount']/100);
	//	}

		if ($this->imginfo[2] == IMG_PNG) {
			imagesavealpha($dst_img, true);
			imagepng($dst_img, $dest, 2);
		} else {
			imagejpeg($dst_img, $dest, 80);
		}
	}

}
