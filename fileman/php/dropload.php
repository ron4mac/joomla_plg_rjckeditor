<?php
/*
 *	added to Roxy Fileman for use in RJCKEditor
 *	handles file paste/drop on CKEditor instance
 *	also handles the 'upload' tab from insert dialogs 
 */
//$dump = print_r($_GET,true);
//$dump .= print_r($_POST,true);
//$dump .= print_r($_COOKIE,true);
//$dump .= print_r($_FILES,true);
//file_put_contents('drop.log',$dump,FILE_APPEND);

include '../system.inc.php';
include 'functions.inc.php';

define('CSRFT','ckCsrfToken');
if (!(isset($_POST[CSRFT]) && isset($_COOKIE[CSRFT])) || ($_POST[CSRFT] != $_COOKIE[CSRFT])) {
	header("HTTP/1.1 403 Forbidden" ); exit;
}

// !! should probably verify this
//verifyAction('DROPLOAD');

checkAccess('DROPLOAD');

$path = trim(empty($_POST['d'])?getFilesPath():$_POST['d']);
verifyPath($path);

$resp = array(
	'uploaded' => 0
	);
$ufv = 'upload';
if (is_dir(fixPath($path))) {
	if (!empty($_FILES[$ufv])) {
		$errors = $errorsExt = array();
		$filename = $_FILES[$ufv]['name'];
		$filename = RoxyFile::MakeUniqueFilename(fixPath($path), $filename);
		$filePath = fixPath($path).'/'.$filename;
		$isUploaded = true;
		if (!RoxyFile::CanUploadFile($filename)) {
			$errorsExt[] = $filename;
			$isUploaded = false;
		} elseif (!move_uploaded_file($_FILES[$ufv]['tmp_name'], $filePath)) {
			$errors[] = $filename; 
			$isUploaded = false;
		}
		if (is_file($filePath)) {
			@chmod ($filePath, octdec(FILEPERMISSIONS));
		}
		/* \\\\ RJCKEDITOR addition */
		if ($isUploaded && RoxyFile::IsImage($filename)) {
			require_once 'imager.php';
			orient_and_make_thumb($filePath);
		}
		/* //// */
		if ($isUploaded && RoxyFile::IsImage($filename) && (intval(MAX_IMAGE_WIDTH) > 0 || intval(MAX_IMAGE_HEIGHT) > 0)) {
			RoxyImage::Resize($filePath, $filePath, intval(MAX_IMAGE_WIDTH), intval(MAX_IMAGE_HEIGHT));
		}
		if ($isUploaded) {
			$tmp = getimagesize($filePath);
			$w = $tmp[0];
			$h = $tmp[1];
			if ($w < intval(MAX_IMAGE_WIDTH)) {
				$resp['width'] = $w;
				$resp['height'] = $h;
			} else {
				$resp['width'] = '100%';
				$resp['height'] = 'auto';
			}
		}
		if ($errors && $errorsExt)
			dropError('E_FileExtensionForbidden');
		elseif ($errorsExt)
			dropError('E_FileExtensionForbidden');
		elseif ($errors)
			dropError('E_UploadNotAll');
		else
			dropSuccess($filename, $path, '');
	} else
		dropError('E_UploadNoFiles');
} else
	dropError('E_UploadInvalidPath');

echo json_encode($resp);

function dropSuccess ($name, $path, $msg)
{
	global $resp;
	$url = $path.'/'.$name;

	if (isset($_GET['CKEditorFuncNum'])) {
		echo '<script>window.parent.CKEDITOR.tools.callFunction('.$_GET['CKEditorFuncNum'].', "'.$url.'")</script>';
		exit();
	}
	$resp['uploaded'] = 1;
	$resp['fileName'] = $name;
	$resp['url'] = $url;
	if ($msg) {
		$resp['error'] = array('message'=>t($msg));
	}
}

function dropError ($msg)
{
	global $resp;
	if (isset($_GET['CKEditorFuncNum'])) {
		echo '<script>alert("'.t($msg).'")</script>';
		exit();
	}
	$resp['error'] = array('message'=>t($msg));
}