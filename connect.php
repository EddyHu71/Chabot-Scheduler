<?php
session_start();
$servername = "103.53.197.170";
$username = "iklcjadw_view";
$password = "cumalihatsaja"; //password server 
$dbname = "iklcjadw_jadwal";
if (isset($_SESSION['kodeA']))
{
    $username = "iklcjadw_admin";
    $password = "adminiklcsemangatmengajar";
}

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

