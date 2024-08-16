<?php
require("Clases/Pedido.php");
require("Clases/Composicion.php");
require("Clases/Menu.php");
require("Clases/Usuario.php");

$BBDD_Cantina = [
    'BBDD' => 'mysql:host=mysql;port=3306;dbname=CantinAPP;',
    'Usuario' => 'cantinapp',
    'Password' => 'cantinapp',
];

setlocale(LC_ALL, "es_ES@euro", "es_ES", "esp");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);