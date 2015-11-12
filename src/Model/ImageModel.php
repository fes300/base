<?php

namespace app-zero\Model;

use Exception;

class ImageModel {

    const MAX_WIDTH = 1600;
    const MAX_HEIGHT = 1600; 

    public $origName;
    public $newName;
    public $path;
    public $extension;
    public $origContent;
    public $newContent;
    public $origWidth;
    public $origHeight;
    public $origOrientation;
    public $origRatio;
    public $resizeWidth;
    public $resizeHeight;

    function __construct($fileName) {
        if (@get_headers($fileName)[0] == 'HTTP/1.0 404 Not Found') {
            throw new Exception('Image ' . $fileName . ' can not be found.');
        } else  if (@get_headers($fileName)[0] == 'HTTP/1.0 302 Found' && @get_headers($fileName)[7] == 'HTTP/1.0 404 Not Found') {
            throw new Exception('Image ' . $fileName . ' can not be found, redirect to a custom 404 page.');
        } else {
            $this->setImage($fileName);
        }
    }

    private function setImage($fileName) {
        $this->origName = $fileName;
        $this->path = $fileName;//name could be changed later and differ from path
        $size = getimagesize($fileName);
        $this->extension = substr(strrchr($size['mime'], "/"), 1);

        switch($this->extension) {
            case 'jpg':
            case 'jpeg':
                $this->origContent = imagecreatefromjpeg($fileName);
                break;

            case 'gif':
                $this->origContent = imagecreatefromgif($fileName);
                break;

            case 'png':
                $this->origContent = imagecreatefrompng($fileName);
                break;

            default:
                throw new Exception("File is not an image, please use another file type.", 1);
        }

        $this->origWidth = imagesx($this->origContent);
        $this->origHeight = imagesy($this->origContent);
        $this->origRatio = $this->origWidth/$this->origHeight;
        $this->setOrientation($this->origRatio);
    }

    public function resizeTo($width, $height, $resizeOption = 'default') {
        switch (strtolower($resizeOption)) {
            case 'exact':
                $this->resizeWidth = $width;
                $this->resizeHeight = $height;
                break;

            case 'maxwidth':
                $this->resizeWidth  = $width;
                $this->resizeHeight = $this->resizeHeightByWidth($width);
                break;

            case 'maxheight':
                $this->resizeWidth  = $this->resizeWidthByHeight($height);
                $this->resizeHeight = $height;
                break;

            default:
                if($this->origWidth > $width || $this->origHeight > $height)
                {
                    if ( $this->origWidth > $this->origHeight ) {
                         $this->resizeHeight = $this->resizeHeightByWidth($width);
                         $this->resizeWidth  = $width;
                    } else if( $this->origWidth < $this->origHeight ) {
                        $this->resizeWidth  = $this->resizeWidthByHeight($height);
                        $this->resizeHeight = $height;
                    } else {
                        $this->resizeWidth = $width;
                        $this->resizeHeight = $height;  
                    }
                } else {
                    $this->resizeWidth = $width;
                    $this->resizeHeight = $height;
                }
                break;
        }

        $this->newContent = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);
        imagecopyresampled($this->newContent, $this->origContent, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->origWidth, $this->origHeight);

    }

    private function cropAndResize($toCropArray, $newWidth, $newHeight) {
        $this->origContent = imagecrop($this->origContent, $toCropArray);
        $this->origWidth = $toCropArray['width'];
        $this->origHeight = $toCropArray['height'];
        $this->resizeTo($newWidth, $newHeight, 'exact');
    }

    public function process($ratio, $suggWidth, $suggHeight) {
        $toCropArray = array();
        if ($this->origRatio < $ratio) {
            $toCropArray = array(
                'x' => 0,
                'y' => ($this->origHeight - $this->origWidth / $ratio) / 2,
                'width' => $this->origWidth,
                'height' => $this->origWidth / $ratio
            );
            $this->cropAndResize($toCropArray, $suggWidth, $suggHeight);

        } else if ($this->origRatio == $ratio) {
            $this->resizeTo($suggWidth, $suggHeight, 'exact');

        } else if ($this->origRatio > $ratio) {
            $toCropArray = array(
                'x' => ($this->origWidth - $ratio * $this->origHeight) / 2,
                'y' => 0,
                'width' => $ratio * $this->origHeight,
                'height' => $this->origHeight
            );
            $this->cropAndResize($toCropArray, $suggWidth, $suggHeight);
        }
    }

    private function resizeHeightByWidth($width) {
        return floor(($this->origHeight / $this->origWidth) * $width);
    }

    private function resizeWidthByHeight($height) {
        return floor(($this->origWidth / $this->origHeight) * $height);
    }

    private function setOrientation($ratio) {
        if ($ratio > 1) {
            $this->origOrientation = "horizontal";
        } else if ($ratio < 1) {
            $this->origOrientation = "vertical";
        } else if ($ratio == 1){
            $this->origOrientation = "square";
        } else {
            return false;
        }
    }

}
