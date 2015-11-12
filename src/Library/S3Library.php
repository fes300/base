<?php

namespace Appzero\Library;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\S3\Enum\CannedAcl;

class S3Library {
    public function __construct() {
        // Instantiate the S3 client with your AWS credentials
        $this->client = S3Client::factory(array(
            'credentials' => array(
                'key'    => 'AKIAJ7PSZZXATXOFC6CA',
                'secret' => 'WEuUme+94Vt86vp4/Zn08rJVLpXcxnfBXAR0Ahul'
            ),
            'version' => '2006-03-01',
            'region' => 'eu-central-1'
            
        ));
    }

    public function upload($bucket, $destination, $content) {
        $destination = str_replace(' ', '-', $destination);
        try {
            return $this->client->putObject(array(
                'Bucket' => $bucket,
                'Key'    => $destination,
                'Body'   => $content,
                'ACL'    => 'public-read'
            ));
        } catch (S3Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function uploadPDF($bucket, $destination, $content) {
        $destination = str_replace(' ', '-', $destination);
        try {
            return $this->client->putObject(array(
                'Bucket' => $bucket,
                'Key'    => $destination,
                'Body'   => $content,
                'ContentType' => 'application/pdf',
                'ACL'    => 'public-read',
                'Metadata' => array(
                    'Content-Type' => 'application/pdf',
                )
            ));
        } catch (S3Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function delete($bucket, $destination) {
        try {
            return $this->client->deleteObject(array(
                'Bucket' => $bucket,
                'Key'    => $destination
            ));
        } catch (S3Exception $e) {
            echo $e->getMessage();
            return false;
        }

    }

    public function getObjects($bucket, $prefix) {
        try {
            $result = $this->client->listObjects(array(
                'Bucket' => $bucket,
                'Prefix' => $prefix
            ));
            return $result;
        } catch (S3Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getExtension($string) {
        $validFormats = array(
            "jpg",
            "png",
            "gif",
            "bmp",
            "jpeg",
            "PNG",
            "JPG",
            "JPEG",
            "GIF",
            "BMP"
        );
        $iii = strrpos($string,".");
        if (!$iii) {
            return false;
        }
        $length = strlen($string) - $iii;
        $extension = substr($string, $iii + 1, $length);
        if (!in_array($extension, $validFormats)) {
            return false;
        }
        return $extension;
    }
}
