<?php
/**
 * Created by PhpStorm.
 * User: Guido
 * Date: 23.12.2017
 * Time: 12:15
 */

class database
{
    var $CFG;
    var $conn;

    public function __construct()
    {
        $this->CFG = new Config();
        //$this->mysqli = new mysqli($this->CFG->db_server, $this->CFG->db_username, $this->CFG->db_password, $this->CFG->db_database);
        //$this->mysqli = new mysqli("localhost","root","","gallery");
    }

    function connect(){

        if(!isset($this->conn) || !$this->conn->ping()){
            $this->conn = new mysqli($this->CFG->db_server, $this->CFG->db_username, $this->CFG->db_password, $this->CFG->db_database);
        }

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        return $this->conn;
    }

    function saveImage($hash, $exifjson){
        $conn = $this->connect();

        $stmt = $conn->prepare("INSERT INTO images (hash, views, exiftags) VALUES (?, ?, ?)");
        $stmt->bind_param('sds', $hash, $views, $exifjson);

        $views = 0;

        if ($stmt->execute() === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    function incrementCounter($hash){
        $conn = $this->connect();

        $stmt = $conn->prepare("UPDATE images SET views = views + 1 WHERE hash=?");
        $stmt->bind_param('s', $hash);

        if ($stmt->execute() === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    function addRating($hash, $value){
        $conn = $this->connect();

        $stmt = $conn->prepare("UPDATE images SET ratingcount = ratingcount + 1, ratingsum = ratingsum + ? WHERE hash=?");
        $stmt->bind_param('ds', $value, $hash);

        if ($stmt->execute() === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    function getImage($hash){
        $conn = $this->connect();

        $query = "SELECT * FROM images where hash='$hash'";
        if ($result = $conn->query($query)) {
            $resultobject = array();

            while ($row = $result->fetch_assoc()) {
                $resultobject['exif'] = json_decode($row['exiftags'],true);
                $resultobject['views'] = $row['views'];
                $resultobject['ratingsum'] = $row['ratingsum'];
                $resultobject['ratingcount'] = $row['ratingcount'];
                $resultobject['keywords'] = $row['keywords'];

                return $resultobject;
            }
            /* free result set */
            $result->close();
        } else {
            return false;
        }
    }

}