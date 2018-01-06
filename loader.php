<?php
/**
 * Created by PhpStorm.
 * User: Guido
 * Date: 22.12.2017
 * Time: 22:13
 */

//include_once("imagelib.php");
include_once("config.php");
include_once("database.php");
$CFG = new config();
$DB = new database();

$size = 'mid';
if (isset($_GET["size"])) {
    $size = $_GET["size"];
    if($size != "mid" && $size != "thumb"){
        $size = 'mid';
    }
}

$hash = false;
if (isset($_GET["hash"])) {
    $hash = $_GET["hash"];
}

$path = false;
if ( isset($_GET['path'])){
    $path = $_GET['path'];
}

if($path !== false){
    $path = realpath($CFG->root_directory.$path);

    $directory = realpath($CFG->root_directory);
    if (substr($path, 0, strlen($CFG->root_directory)) !== $directory){
        http_response_code(401);
        print json_encode('Noooo');
        exit(1);
    }

    $hash = md5_file($fullpath);
    $DB->incrementCounter($hash);


    header('Content-type: image/jpeg');
    readfile($path);
    exit;
}

if($hash !== false){

    $path = realpath($CFG->temp_directory."/".$size."/".$hash.".jpg");

    if($size == 'mid'){
        $DB->incrementCounter($hash);
    }

    header('Content-type: image/jpeg');
    readfile($path);
    exit;
}

