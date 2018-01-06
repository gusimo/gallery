<?php
/**
 * Created by PhpStorm.
 * User: Guido
 * Date: 22.12.2017
 * Time: 20:53
 */

include_once("../config.php");
include_once("../fileslib.php");
include_once("../database.php");
include_once("../imagelib.php");

$CFG = new config();
$FILES = new fileslib();
$IMAGES = new imagelib();

header('Content-type: application/json');

//check if path is valid
$path = "";
if ( isset($_GET['dir'])){
    $path = $_GET['dir'];
}

//validate folder and receive absolute path to work with!
$path = $FILES->getValidFullpath($path);

//List all files
$fileList = scandir($path);
$resultFolders = array();
$resultFiles = array();
foreach($fileList as $file){
    if($file === '.' || $file === '..'){
        continue;
    }

    $filepath = realpath( $path . "/" . $file );

    $resultobject = array();
    $resultobject["name"] = $file;
    $resultobject["type"] = filetype($filepath);
    $resultobject["fullpath"] = $FILES->getRelativePath($filepath);

    if($resultobject["type"] === "file" ){
        $ext = pathinfo($filepath, PATHINFO_EXTENSION);
        //echo $ext;
        if($ext !== 'JPG' && $ext !== 'JPEG') continue;
        $resultobject = array_merge($resultobject, $IMAGES->treatImage($filepath));
        $resultobject["ext"] = $ext;
        $resultobject["hash"] = md5_file($filepath);
        $resultFiles[] = $resultobject;
    }
    else {
        $resultFolders[] = $resultobject;
    }


}

//create all thumbs

//extract exif

//fetch google vision

//store to db

//send to user



$relativepath = $FILES->getRelativePath($path);
$parentPath = $FILES->getRelativeParent($path);

echo json_encode(array("files" => $resultFiles, "folders" => $resultFolders, "path" => $relativepath, "parent" => $parentPath ));