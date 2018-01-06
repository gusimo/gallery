<?php
/**
 * Created by PhpStorm.
 * User: Guido
 * Date: 23.12.2017
 * Time: 11:15
 */


class imagelib
{
    var $DB;
    var $CONFIG;

    public function __construct()
    {
        $this->DB = new database();
        $this->CONFIG = new config();
    }

    function treatImage($fullpath){
        $resultobject = array();

        //hash the file
        $resultobject["hash"] = md5_file($fullpath);

        //db-lookup
        //found in DB
        $dbresult = $this->DB->getImage($resultobject["hash"]);

        if(is_array($dbresult)){
            $resultobject = array_merge($resultobject, $dbresult);
        }
        else {
            //not found in db
            //extract exif & geolocate
            $exif = $this->extractExif($fullpath);
            $resultobject["exif"] = $exif;

            $this->DB->saveImage($resultobject["hash"],json_encode($exif));

            //save to db
        }

        //create thumb
        if(!file_exists( $this->CONFIG->temp_directory . "/mid/" . $resultobject["hash"] . ".jpg" )){
            $this->resizeAndRotate($fullpath, $resultobject["hash"], $resultobject["exif"]["orientation"],"mid");
        }
        if(!file_exists( $this->CONFIG->temp_directory . "/thumb/" . $resultobject["hash"] . ".jpg" )){
            $this->resizeAndRotate($fullpath, $resultobject["hash"], $resultobject["exif"]["orientation"],"thumb");
        }
        //return

        return $resultobject;
    }

    function extractExif($path)
    {
        $result = array();
        $exif = exif_read_data($path);

        if (isset($exif["Make"]) && isset($exif["Model"]))
            $result["camera"] = $exif["Make"] . " " . $exif["Model"];
        if (isset($exif["Orientation"]))
            $result["orientation"] = $exif["Orientation"];

        /*
         * FileDateTime
            MimeType
            Make
            Model
            Orientation
            DateTime
            ExifImageWidth
            ExifImageLength
         */
        if (isset($exif["GPSLatitude"]) && isset($exif["GPSLongitude"]))
        {
            $lat = $exif["GPSLatitude"];
            $long = $exif["GPSLongitude"];

            $lat = $this->getGps($lat);
            $long = $this->getGps($long);

            $result["latitude"] = $lat;
            $result["longitude"] = $long;

            $result["place"] = $this->queryGeoLocation($lat,$long);
        };

        return $result;
    }

    function queryGeoLocation($lat, $lon){
        //https://maps.googleapis.com/maps/api/geocode/json?latlng=44.7061609,10.5948236&key=
        //$string = str_replace (" ", "+", urlencode($string));
        $details_url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&key=".$this->CONFIG->google_geolocation_key;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = json_decode(curl_exec($ch), true);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            //echo $error;
        }


        // If Status Code is ZERO_RESULTS, OVER_QUERY_LIMIT, REQUEST_DENIED or INVALID_REQUEST
        if ($response['status'] != 'OK') {
            return null;
        }

        //print_r($response);
        /*$geometry = $response['results'][0]['geometry'];

        $longitude = $geometry['location']['lat'];
        $latitude = $geometry['location']['lng'];

        $array = array(
            'latitude' => $geometry['location']['lng'],
            'longitude' => $geometry['location']['lat'],
            'location_type' => $geometry['location_type'],
        );
        */

        $array = array(
            'formatted' => $response['results'][0]['formatted_address'],
            'place' => $response['results'][0]['place_id'],
        );

        return $array;
    }

    function queryVisionApi($path){

    }

    function _mirrorImage ( $imgsrc)
    {
        $width = imagesx ( $imgsrc );
        $height = imagesy ( $imgsrc );

        $src_x = $width -1;
        $src_y = 0;
        $src_width = -$width;
        $src_height = $height;

        $imgdest = imagecreatetruecolor ( $width, $height );

        if ( imagecopyresampled ( $imgdest, $imgsrc, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height ) )
        {
            return $imgdest;
        }

        return $imgsrc;
    }

    function resizeAndRotate($path, $hash, $orientation, $size){
        $resize = false;
        $width = 0;
        $height = 0;
        if ($size == 'thumb'){
            $resize = true;
            $width = 640;
            $height = 480;
        }
        if ($size == 'mid') {
            $resize = true;
            $width = 1632;
            $height = 1224;
        }

        $image = imagecreatefromjpeg($path);
        //$image = WideImage::load($path);

        $mirror = false;
        $rotation = false;

        if(isset($orientation)){
                switch ($orientation) {
                    case 2:
                        $mirror = true;
                        break;

                    case 3:
                        $rotation = 180;
                        break;

                    case 4:
                        $rotation = 180;
                        $mirror = true;
                        break;

                    case 5:
                        $rotation = 90;
                        $mirror = true;
                        break;

                    case 6:
                        $rotation = 270;
                        break;

                    case 7:
                        $rotation = 270;
                        $mirror = true;
                        break;

                    case 8:
                        $rotation = 90;
                        break;
                }
        }

        if ($resize){

            //$image = imagescale ( $image , $width, $height );
            $image = imagescale ( $image , $width );

            if($rotation !== false){
                $image = imagerotate($image, $rotation, 0);
            }

            if($mirror){
                $image = $this->_mirrorImage($image);
            }

            imagejpeg($image,$this->CONFIG->temp_directory . "/$size/$hash.jpg",70);
        }
    }

    function getGps($exifCoord)
    {
        $degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;

        //normalize
        $minutes += 60 * ($degrees - floor($degrees));
        $degrees = floor($degrees);

        $seconds += 60 * ($minutes - floor($minutes));
        $minutes = floor($minutes);

        //extra normalization, probably not necessary unless you get weird data
        if($seconds >= 60)
        {
            $minutes += floor($seconds/60.0);
            $seconds -= 60*floor($seconds/60.0);
        }

        if($minutes >= 60)
        {
            $degrees += floor($minutes/60.0);
            $minutes -= 60*floor($minutes/60.0);
        }

        //return array('degrees' => $degrees, 'minutes' => $minutes, 'seconds' => $seconds);
        return $this->DMStoDEC($degrees,$minutes,$seconds);
    }

    function gps2Num($coordPart)
    {
        $parts = explode('/', $coordPart);

        if(count($parts) <= 0)// jic
            return 0;
        if(count($parts) == 1)
            return $parts[0];

        return floatval($parts[0]) / floatval($parts[1]);
    }

    function DMStoDEC($deg,$min,$sec)
    {

            // Converts DMS ( Degrees / minutes / seconds )
        // to decimal format longitude / latitude

        return $deg+((($min*60)+($sec))/3600);
    }
}