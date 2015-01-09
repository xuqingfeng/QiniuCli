<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */

require_once __DIR__ . "/QiniuCli.php";

require_once __DIR__ . "/vendor/autoload.php";
use Sweet\Cli;

class RoboFile extends \Robo\Tasks {

    private static $qiniuCli;

    public function __construct() {

        self::$qiniuCli = QiniuCli::getInstance();
    }

    // define public methods as commands
    public function hi() {

        $this->say("hi");
    }

    public function upload($file = "") {

        if (empty($file)) {
            // 上传默认目录上的所有文件
            Cli::progress(0);
            $fileNames = self::$qiniuCli->getAllFilesInDir(DEFAULTDIR);
            Cli::progress(10);
            $outputs = self::$qiniuCli->uploadFiles($fileNames);
            Cli::progress(100);
            $this->say($outputs);
        } else {
            $output = self::$qiniuCli->uploadFile($file);
            $this->say($output);
        }
    }

    public function remove($file = "") {

        if (empty($file)) {
            $this->say(Cli::danger("Param is required!"));
        } else {
            $output = self::$qiniuCli->removeFile($file);
            $this->say($output);
        }
    }

    public function removeAll() {

        $fileNames = self::$qiniuCli->getAllFilesInDir(DEFAULTDIR);
        $outputs = self::$qiniuCli->removeAllFiles($fileNames);
        $this->say($outputs);
    }

    public function status($file = "") {

        if (empty($file)) {
            $fileNames = self::$qiniuCli->getAllFilesInDir(DEFAULTDIR);
            $outputs = self::$qiniuCli->getFilesStatus($fileNames);
            $this->say($outputs);
        } else {
            $output = self::$qiniuCli->getFileStatus($file);
            $this->say($output);
        }
    }

    public function refresh($file = "") {

        if(empty($file)){
            $this->say(Cli::danger("Param is required!"));
        }else{
            $output = self::$qiniuCli->refreshFile($file);
            $this->say($output);
        }
    }
}