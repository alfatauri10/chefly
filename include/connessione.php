<?php
$conn = mysqli_connect("localhost","chefly","","my_chefly");

if(!$conn){
  die("Connessione fallitaa: " . mysqli_connect_error());
}
?>