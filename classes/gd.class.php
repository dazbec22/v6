<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

class GD
{
    private $_abort = false;

    private $_gdImageMax;
    private $_gdJpegQuality;
    private $_gdTargetDir;
    private $_gdWebpSupport;

    private $_gdImageData;
    private $_gdImageExif = array();
    private $_gdImageType;

    private $_gdImageSource;
    private $_gdImageOutput;

    private $_gdImageArray = array();

    ##############################################

    public function __construct($targetDir, $maxImage = false, $jpegQuality = 100)
    {
        if (substr($targetDir, -1) != '/') {
            $targetDir .= '/';
        }
        $this->_gdTargetDir  = $targetDir;
        $this->_gdImageMax  = $maxImage;
        $this->_gdJpegQuality = $jpegQuality;
        $this->_gdWebpSupport = function_exists('imagecreatefromwebp');
    }

    //=====[ Public ]=======================================

    /**
     * Set GD params false
     */
    public function gdClear()
    {
        $this->_gdImageOutput = false;
        $this->_gdImageSource = false;
        $this->_gdImageData  = false;
        $this->_gdImageExif = false;
    }

    /**
     * Crop image
     *
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     */
    public function gdCrop($x, $y, $w, $h)
    {
        if ($im = $this->gdGetCurrentData()) {
            $oh = imagesy($im);
            $ow = imagesx($im);
            $h = ($oh < $h) ? $oh : $h;
            $w = ($ow < $w) ? $ow : $w;
            $this->_gdImageOutput = imagecreatetruecolor($w, $h);
            imagecopyresampled($this->_gdImageOutput, $im, 0, 0, $x, $y, $w, $h, $w, $h);
            imagesavealpha($this->_gdImageOutput, true);
            $this->_gdImageArray[0] = $w;
            $this->_gdImageArray[1] = $h;
        }
    }

    /**
     * Return image output
     *
     * @return data
     */
    private function gdGetCurrentData()
    {
        // Detect what data source we should be using
        // If output is empty, use the source
        return (!empty($this->_gdImageOutput)) ? $this->_gdImageOutput : $this->_gdImageSource;
    }

    /**
     * Load file
     *
     * @param string $file
     * @return bool
     */
    public function gdLoadFile($file)
    {
        if (file_exists($file)) {
            $this->_gdImageData = getimagesize($file);
            $this->_gdImageExif = function_exists('exif_read_data') ? exif_read_data($file) : array();
            $this->_gdImageType = $this->_gdImageData[2];

            switch ($this->_gdImageType) {
                case IMAGETYPE_GIF:
                    $this->_gdImageSource = imagecreatefromgif($file);
                    break;
                case IMAGETYPE_JPEG:
                    $this->_allocateMemory();
                    if ($this->_abort) {
                        return false;
                    }
                    $this->_gdImageSource = imagecreatefromjpeg($file);
                    break;
                case IMAGETYPE_PNG:
                    $this->_gdImageSource = imagecreatefrompng($file);
                    imagesavealpha($this->_gdImageSource, true);
                    break;
                case IMAGETYPE_WEBP:
                    if($this->_gdWebpSupport) {
                        $this->_gdImageSource = imagecreatefromwebp($file);
                    } else {
                        return false;
                    }
                    break;
                default:
                    return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Orientate image based on EXIF data
     *
     * @return $im object
     */
    private function gdOrientate($im)
    {
        if (isset($this->_gdImageExif['Orientation']) && !empty($this->_gdImageExif['Orientation'])) {
            switch ($this->_gdImageExif['Orientation']) {
                case 3:
                    return imagerotate($im, 180, 0);
                break;
                case 6:
                    return imagerotate($im, -90, 0);
                break;
                case 8:
                    return imagerotate($im, 90, 0);
                break;
            }
        }
        return $im;
    }

    /**
     * Resize image
     * @param int $resize
     * @return bool
     */
    private function gdResize($resize)
    {
        // Resize the image, while maintaining the proportions
        $im = $this->gdGetCurrentData();
        if ($im) {
            // Get the existing image details
            $width = imagesx($im);
            $height = imagesy($im);
            // Calculate the resized dimensions
            $x_ratio = $resize / $width;
            $y_ratio = $resize / $height;
            // Perform a few calculations to work out the new (constrained) dimensions
            $proceed = true;
            if (($width <= $resize) && ($height <= $resize)) {
                // no resize needed
                $out_width = $width;
                $out_height = $height;
                $proceed = false;
            } elseif (($x_ratio * $height) < $resize) {
                $out_height = ceil($x_ratio * $height);
                $out_width = $resize;
            } else {
                $out_width = ceil($y_ratio * $width);
                $out_height = $resize;
            }
            if ($proceed) {
                // Create the output file and resample
                $this->_gdImageOutput = imagecreatetruecolor($out_width, $out_height);
                imagealphablending($this->_gdImageOutput, false);
                imagesavealpha($this->_gdImageOutput, true);
                imagecopyresampled($this->_gdImageOutput, $im, 0, 0, 0, 0, $out_width, $out_height, $width, $height);
                return true;
            }
        }
        return false;
    }

    /**
     * Save modified file
     *
     * @param string $filename
     * @param bool $resize
     * @return bool
     */
    public function gdSave($filename, $resize = false)
    {
        if ($this->_abort) {
            return false;
        }

        // Do we need to resize the file before saving?
        if ($resize || $this->_gdImageMax) {
            $this->gdResize(($resize) ? $resize : $this->_gdImageMax);
        }
        $im = $this->gdGetCurrentData();
        if ($im) {
            $file = $this->_gdTargetDir.$filename;
            $im = $this->gdOrientate($im);
            imageinterlace($im, true);
            switch ($this->_gdImageType) {
                case IMAGETYPE_GIF:
                    $this->_gdImageSource = imagegif($im, $file);
                    break;
                case IMAGETYPE_JPEG:
                    $this->_gdImageSource = imagejpeg($im, $file, $this->_gdJpegQuality);
                    break;
                case IMAGETYPE_PNG:
                    imagesavealpha($im, true);
                    $this->_gdImageSource = imagepng($im, $file);
                    break;
                case IMAGETYPE_WEBP:
                    if($this->_gdWebpSupport) {
                        $this->_gdImageSource = imagewebp($im, $file, $this->_gdJpegQuality);
                    } else {
                        return false;
                    }
                    break;
                default:
                    trigger_error(__METHOD__.' - Unknown file type', E_USER_NOTICE);
                    return false;
            }
            
            return true;
        }
        return false;
    }

    //=====[ Private ]=======================================

    /**
     * Calculate and set memory for jpeg
     * Credit to Karolis Tamutis karolis.t_AT_gmail.com
     *
     * @return false
     */
    private function _allocateMemory()
    {
        $this->_abort = false;
        
        $memoryNeeded = round(($this->_gdImageData[0] * $this->_gdImageData[1] * $this->_gdImageData['bits'] * $this->_gdImageData['channels'] / 8 + Pow(2, 16)) * 1.65);
        
        $memLimit = ini_get('memory_limit');
        $memLimit = substr($memLimit, -1) == 'G' ? (int)$memLimit * 1024 : (int)$memLimit;
    
        if (function_exists('memory_get_usage') && memory_get_usage() + $memoryNeeded > $memLimit * pow(1024, 2)) {
            $new_memory_limit = $memLimit + ceil(((memory_get_usage() + $memoryNeeded) - $memLimit * pow(1024, 2)) / pow(1024, 2)) . 'M';
            // ini_set may be a disabled function
            if (!function_exists('ini_set')) {
                $this->_abort = true;
                $this->gdClear();
                return false;
            }
            // check ini_set works
            if (!ini_set('memory_limit', $new_memory_limit)) {
                $this->_abort = true;
                $this->gdClear();
                return false;
            } else {
                return true;
            }
        }
    }
}
