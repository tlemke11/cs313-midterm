<?php
//PDO connection to the Audible DB


$server = 'localhost';
$username = 'top1xpuz_audappu';
$password = '8O1-eu18z$QXN';
$database = 'top1xpuz_audapp1';

try {
  $connection = new PDO("mysql:host=$server;dbname=$database", $username, $password);
  }
  
  catch (PDOException $ex){
    echo "Error connecting to the Database: " . $ex->getMessage();
  }
?>