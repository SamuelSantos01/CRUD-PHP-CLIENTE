<?php
header('Access-Control-Allow-Origin: *');  
header('Content-type: application/json');

date_default_timezone_set('America/Sao_Paulo');


include_once "classes/autoload.class.php";
new Autoload();

$rota = new Rotas();

$rota->add('GET', '/clientes/listar', 'Clientes::listarTodos');    
$rota->add('GET', '/clientes/listar/[PARAM]', 'Clientes::listarUnico');
$rota->add('POST', '/clientes/adicionar', 'Clientes::adicionar');      
$rota->add('POST', '/clientes/atualizar/[PARAM]', 'Clientes::atualizar');
$rota->add('DELETE', '/clientes/deletar/[PARAM]', 'Clientes::deletar'); 

$rota->ir($_GET['path']);