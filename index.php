<?php
use app\Client;
require_once 'app/Client.php';

$cliente = new Client("http://maps.google.com/maps/geo");

var_dump($cliente->get(null, array('output' => 'json', 'q' => "gama deça")));