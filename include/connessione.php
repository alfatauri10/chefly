<?php
$conn = mysqli_connect("localhost","tuo_username","","my_chefly");

if(!$conn){
  die("Connessione fallitaa: " . mysqli_connect_error());
}
?>