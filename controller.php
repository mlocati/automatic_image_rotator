<?php

defined('C5_EXECUTE') or die('Access denied.');

class AutomaticImageRotatorPackage extends Package {

	const JPEG_QUALITY = 90;

	protected $pkgHandle = 'automatic_image_rotator';

	protected $appVersionRequired = '5.6';

	protected $pkgVersion = '1.0.0';

	public function getPackageName()
	{
		return t('Automatic Image Rotator');
	}

	public function getPackageDescription() {
		return t('Automatically rotate newly added files accordingly to the picture metadata.');
	}

	public function on_start()
	{
		Events::extend('on_file_version_add', __CLASS__, 'processFileVersion');
		Events::extend('on_file_version_replaced', __CLASS__, 'processFileVersion');
		// jl_front_end_uploader package
		Events::extend('on_fefu_upload_started', __CLASS__, 'processFilePath');
	}

	public static function processFileVersion($fileVersion)
	{
		if (
			(!$fileVersion instanceof Concrete5_Model_FileVersion)
			||
			(!function_exists('getimagesize'))
			||
			(!function_exists('exif_read_data'))
		) {
			return;
		}
		$path = $fileVersion->getPath();
		self::processFilePath($path);
	}

	public static function processFilePath($path) {
		if (!is_file($path)) {
			return;
		}
		$imageSize = @getimagesize($path);
		if (!$imageSize) {
			return;
		}
		if ($imageSize[2] !== IMAGETYPE_JPEG) {
			return;
		}
		$exif = @exif_read_data($path);
		if (!is_array($exif)) {
			return;
		}
		$exif = array_change_key_case($exif, CASE_LOWER);
		$orientation = null;
		if ($orientation === null && isset($exif['orientation']) && is_scalar($exif['orientation'])) {
			$orientation = $exif['orientation'];
		}
		if ($orientation === null && isset($exif['ifd0']) && is_array($exif['ifd0'])) {
			$exif['ifd0'] = array_change_key_case($exif['ifd0'], CASE_LOWER);
			if (isset($exif['ifd0']['orientation']) && is_scalar($exif['ifd0']['orientation'])) {
				$orientation = $exif['ifd0']['orientation'];
			}
		}
		if ($orientation === null) {
			return;
		}
		$orientationMap = array(
			2 => array(null, IMG_FLIP_HORIZONTAL),
			3 => array(180, null),
			4 => array(null, IMG_FLIP_VERTICAL),
			5 => array(-90, IMG_FLIP_HORIZONTAL),
			6 => array(-90, null),
			7 => array(90, IMG_FLIP_HORIZONTAL),
			8 => array(90, null),
		);
		if (!isset($orientationMap[$orientation])) {
			return;
		}
		list($angle, $flip) = $orientationMap[$orientation];
		$image = @imagecreatefromjpeg($path);
		if (!$image) {
			return;
		}
		if ($angle) {
			$rotated = @imagerotate($image, $angle, 0);
			@imagedestroy($image);
			if (!$rotated) {
				return;
			}
			$image = $rotated;
		}
		if ($flip !== null) {
			if (function_exists('imageflip')) {
				$flipped = @imageflip($image, $flip);
			} else {
				$flipped = false;
			}
			if (!$flipped) {
				@imagedestroy($image);
				return;
			}
		}
		@imagejpeg($image, $path, self::JPEG_QUALITY);
		@chmod($path, FILE_PERMISSIONS_MODE);
		@imagedestroy($image);
	}
}