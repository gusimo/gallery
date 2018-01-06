<?php
/**
 * Created by PhpStorm.
 * User: Guido
 * Date: 23.12.2017
 * Time: 11:26
 */
//include_once("config.php");

class fileslib
{
    var $CFG;

    public function __construct()
    {
        $this->CFG = new Config();
    }

    function getValidFullpath($path){
        $fullpath = realpath( $this->CFG->root_directory.$path); //append to root path

        $rootpath = realpath($this->CFG->root_directory);
        if (substr($fullpath, 0, strlen($rootpath)) !== $rootpath){
            http_response_code(401);
            print json_encode('Noooo');
            exit(1);
        }

        return $fullpath;
    }

    function getRelativePath($fullpath){
        $relativepath = str_replace($this->CFG->root_directory,"",$fullpath);
        if(strlen($relativepath) < 1) $relativepath = "/";
        return $relativepath;
    }

    function getRelativeParent($fullpath){
        $parentPath = dirname($fullpath);
        if(strlen($parentPath) < strlen($this->CFG->root_directory)){
            $parentPath = false; //its outside of our root
        }
        else {
            //cut off root
            $parentPath = str_replace($this->CFG->root_directory, "", $parentPath);
            if(strlen($parentPath) < 1) $parentPath = "/";
        }
        return $parentPath;
    }


}