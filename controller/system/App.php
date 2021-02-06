<?php
session_start();
header("Access-Control-Allow-Origin: *");

require_once(__DIR__ . '/Util.php');

require_once(__DIR__ . '/database/Raw.class.php');
require_once(__DIR__ . '/database/Where.class.php');
require_once(__DIR__ . '/database/Model.class.php');
require_once(__DIR__ . '/database/Join.class.php');
require_once(__DIR__ . '/database/Banco.class.php');
require_once(__DIR__ . '/ServerUnica.class.php');
require_once(__DIR__ . '/Request.class.php');
require_once(__DIR__ . '/View.class.php');
require_once(__DIR__ . '/../../Route.php');

function DateSystem($date) {
    if ( strlen(preg_replace("/[^\/]/", '', $date)) > 1 ) {
        $format = strlen($date) > 8 ? 'd/m/Y' : 'd/m/y';
        return DateTime::createFromFormat($format, $date);
    } else {
        return new DateTime($date);
    }
}


if(isset($_SESSION['server'])) ServerUnica::setHOST( gzdecode($_SESSION['server']) );
if(isset($_SESSION['__filial__'])) ServerUnica::$filial = $_SESSION['__filial__'];
$route = isset($_GET['__route__']) ? $_GET['__route__'] : '/';
Request::call( $route );