<?php
/**
 * Author: xuqingfeng
 * Date: 15/1/9
 * Time: 下午2:15
 */

require_once __DIR__ . "/qiniu/io.php";
require_once __DIR__ . "/qiniu/rs.php";
require_once __DIR__ . "/config.php";

require_once __DIR__ . "/vendor/autoload.php";
use Sweet\Cli;

class QiniuCli {

    private static $qiniuCli;
    private static $client;
    private static $putPolicy;
    private static $upToken;
    private static $putExtra;
    private static $minGap;

    private function __construct() {

        Qiniu_SetKeys(ACCESSKEY, SECRETKEY);
        self::$client = new Qiniu_MacHttpClient(null);
        self::$putPolicy = new Qiniu_RS_PutPolicy(BUCKET);
        self::$upToken = self::$putPolicy->Token(null);
        self::$putExtra = new Qiniu_PutExtra();
        self::$putExtra->Crc32 = 1;

        self::$minGap = 1;
    }

    public static function getInstance() {

        if (!isset(self::$qiniuCli)) {
            self::$qiniuCli = new QiniuCli();
        }

        return self::$qiniuCli;
    }

    public function uploadFile($fileName) {

        if (is_file($fileName)) {

            list($ret, $err) = Qiniu_PutFile(self::$upToken, $fileName, $fileName, self::$putExtra);
            if (!empty($err)) {
                $response = array(
                    'success'  => 'error',
                    'filename' => $fileName,
                    'message'  => ''
                );
                $output = $this->formatOutput($response);
            } else {
                $response = array(
                    'success'  => 'success',
                    'filename' => $fileName,
                    'message'  => DOMAIN . $fileName
                );
                $output = $this->formatOutput($response);
            }
        } else {
            $response = array(
                'success'  => 'error',
                'filename' => $fileName,
                'message'  => '404'
            );
            $output = $this->formatOutput($response);
        }

        return $output;
    }

    //
    public function uploadFiles(array $fileNames) {

        if (!empty($fileNames)) {
            $count = count($fileNames) - 1;
            $outputs = "";
            foreach ($fileNames as $no => $file) {
                if ($count == $no) {
                    $outputs .= $this->uploadFile($file);
                    Cli::progress(80);
                } else {
                    $outputs .= $this->uploadFile($file) . "\n";
                }
            }
        } else {
            $response = array(
                'success'  => 'error',
                'filename' => 'DEFAULTDIR',
                'message'  => '404'
            );
            $outputs = $this->formatOutput($response);
        }

        return $outputs;
    }

    public function getAllFilesInDir($dir) {

        if (!empty($dir)) {
            $directory = new RecursiveDirectoryIterator($dir);
            $iterator = new RecursiveIteratorIterator($directory);
            $files = array();
            foreach ($iterator as $file) {
                if (is_file($file)) {
                    $files[] = $file->getPathName();
                }
            }

            // 返回 pathName
            return $files;
        }

        return array();
    }

    public function removeFile($fileName) {

        if (is_file($fileName)) {
            $err = Qiniu_RS_Delete(self::$client, BUCKET, $fileName);
            if (!empty($err)) {
                $response = array(
                    'success'  => 'error',
                    'filename' => $fileName,
                    'message'  => ''
                );
                $output = $this->formatOutput($response);
            } else {
                $response = array(
                    'success'  => 'success',
                    'filename' => $fileName,
                    'message'  => 'Removed from qiniu!'
                );
                $output = $this->formatOutput($response);
            }
        } else {
            $response = array(
                'success'  => 'error',
                'filename' => $fileName,
                'message'  => '404'
            );
            $output = $this->formatOutput($response);
        }

        return $output;
    }

    public function removeAllFiles(array $fileNames) {

        if (!empty($fileNames)) {
            $count = count($fileNames) - 1;
            $outputs = "";
            foreach ($fileNames as $no => $file) {
                if ($count == $no) {
                    $outputs .= $this->removeFile($file);
                } else {
                    $outputs .= $this->removeFile($file) . "\n";
                }
            }
        } else {
            $response = array(
                'success'  => 'error',
                'filename' => 'DEFAULTDIR',
                'message'  => '404'
            );
            $outputs = $this->formatOutput($response);
        }

        return $outputs;
    }

    public function getFileStatus($fileName) {

        if (is_file($fileName)) {
            list($ret, $err) = Qiniu_RS_Stat(self::$client, BUCKET, $fileName);
            if (!empty($err)) {
                $response = array(
                    'success'  => 'error',
                    'filename' => $fileName,
                    'message'  => ''
                );
                $output = $this->formatOutput($response);
            } else {
                $response = array(
                    'success'  => 'success',
                    'filename' => $fileName,
                    'message'  => DOMAIN . $fileName
                );
                $output = $this->formatOutput($response);
            }
        } else {
            $response = array(
                'success'  => 'error',
                'filename' => $fileName,
                'message'  => '404'
            );
            $output = $this->formatOutput($response);
        }

        return $output;
    }

    public function getFilesStatus($fileNames) {

        if (!empty($fileNames)) {
            $count = count($fileNames) - 1;
            $outputs = "";
            foreach ($fileNames as $no => $file) {
                if ($count == $no) {
                    $outputs .= $this->getFileStatus($file);
                } else {
                    $outputs .= $this->getFileStatus($file) . "\n";
                }
            }
        } else {
            $response = array(
                'success'  => 'error',
                'filename' => 'DEFAULTDIR',
                'message'  => '404'
            );
            $outputs = $this->formatOutput($response);
        }

        return $outputs;
    }

    public function refreshFile($fileName){

        if(is_file($fileName)){
            $removeOutput = $this->removeFile($fileName);
            $uploadOutput = $this->uploadFile($fileName);
            $output = $removeOutput."\n".$uploadOutput;
        }else{
            $response = array(
                'success'  => 'error',
                'filename' => $fileName,
                'message'  => '404'
            );
            $output = $this->formatOutput($response);
        }

        return $output;
    }

    public function formatOutput(array $response) {

        if (!empty($response)) {
            if ('success' == $response['success']) {
                return Cli::success($response['filename']) . str_repeat(" ", self::$minGap) . Cli::info($response['message']);
            } else {
                return Cli::danger($response['filename']) . str_repeat(" ", self::$minGap) . Cli::info($response['message']);
            }
        } else {
            return Cli::danger("NULL");
        }
    }

}