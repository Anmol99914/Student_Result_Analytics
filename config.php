<?php
$host = "localhost";
$username = "root";
$password = "installationprocess12345";
$database = "sra";
$port = 3307;


$connection = mysqli_connect($host,$username,$password,$database,$port);

if(!$connection){
    echo "Database couldnot be connected!!!!";
}
?>