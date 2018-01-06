<?php

class config
{
    //Database
    var $db_server = "localhost";
    var $db_database = "gallery";
    var $db_username = "root";
    var $db_password = "";

    //Fotos
    var $root_directory = "m:\gallery";
    var $temp_directory = "m:\gallery_temp"; //this must not be within webroot and not within $directory

    //Google
    var $google_vision_key = "";
    var $google_geolocation_key = "secret";

    public function __construct()
    {
        $this->root_directory = realpath($this->root_directory);

        if(!file_exists($this->temp_directory)){
            mkdir($this->temp_directory, 0777, true);
        }
        if(!file_exists($this->temp_directory."/mid")){
            mkdir($this->temp_directory."/mid", 0777, true);
        }
        if(!file_exists($this->temp_directory."/thumb")){
            mkdir($this->temp_directory."/thumb", 0777, true);
        }

        $this->temp_directory = realpath($this->temp_directory);
    }
}


