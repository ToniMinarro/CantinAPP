<?php
require("Clases/Pedido.php");
require("Clases/Composicion.php");
require("Clases/Menu.php");
require("Clases/Usuario.php");
// PARA LOCALHOST
// $BBDD_Cantina = array('BBDD'=>'mysql:host=localhost;port=3306;dbname=Cantina;', 'Usuario'=>'root', 'Password'=>'', 'Host'=>'localhost', 'DBName'=>'Cantina', 'Port'=>'3306');

// PARA RPI
$BBDD_Cantina = array('BBDD'=>'mysql:host=localhost;port=3306;dbname=CantinAPP;', 'Usuario'=>'CantinAPP', 'Password'=>'xuWoG325WA7i', 'Host'=>'localhost', 'DBName'=>'CantinAPP', 'Port'=>'3306');

setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);