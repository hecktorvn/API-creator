<?php
require_once('Req.class.php');
class Request{
    /**
     * @var array
     */
    protected static $auth = ['POST'=>[], 'GET'=>[], 'REQUEST'=>[]];
    protected static $route_REQUEST = [];
    protected static $route_POST = [];
    protected static $route_GET = [];

    /**
     * @param $route
     * @param $func
     * @throws Exception
     */
    public static function POST($route, $func, $auth=false){
        if( $auth ) self::$auth['POST'][] = $route;
        self::$route_POST[$route] = $func;
    }

    /**
     * GET method
     * @param $route
     * @param $func
     * @throws Exception
     */
    public static function GET($route, $func, $auth=false){
        if( $auth ) self::$auth['GET'][] = $route;
        self::$route_GET[$route] = $func;
    }

    /**
     * @param $func
     * @throws Exception
     */
    public static function Inicio($func){
        self::$route_GET['/'] = $func;
    }

    /**
     * @param $route
     * @param $func
     * @throws Exception
     */
    public static function _REQUEST($route, $func, $auth=false){
        if( $auth ) self::$auth['REQUEST'][] = $route;
        self::$route_REQUEST[$route] = $func;
    }


    /**
     * @param $controller
     * @param $data
     * @return mixed
     * @throws ErrorException
     */
    private static function callController($controller, $data){
        $method = $_SERVER['REQUEST_METHOD'];
        list($class_, $function_) = explode('@', $controller);
        if(!class_exists($class_)){
            $file_path = __DIR__ . '/../' . $class_ . '.class.php';
            if(!file_exists($file_path)) throw new ErrorException("A classe \"{$class_}\" não foi encontrada!");
            else require_once($file_path);
        }

        $data = [ new Req($data[0], $data[1]), $method ];
        $content = @call_user_func_array([$class_, $function_], $data);
        return $content;
    }

    public static function ignore($route){
        /*list($route, $data) = self::getPage($route);
        if( is_null($route) ){
            echo 'testando';
        }

        die;*/
    }

    /**
     * @param $route
     */
    public static function call($route){
        $method = $_SERVER['REQUEST_METHOD'];

        $request = null;
        $content = ob_get_clean();

        if(substr($route, 0, 1) != '/') $route = '/' . $route;
        list($request, $data) = self::getPage( $route );
        
        if( empty($request) ){
            header(utf8_decode('HTTP/1.1 404 ROTA NÃO ENCONTRADA'));
            die;
        }
        
        if(isset($data['__route__'])) unset($data['__route__']);
        if(is_callable($request)) $request = $request((object) $data, $method);
        else $request = self::callController($request, [$data, $method]);

        if($request instanceof View){
            $request->drawn();
        } else {
            if(is_array($request)) self::print_json($request);
            else echo $request;
        }
    }

    /**
     * @param $route
     */
    public static function getPage($route){
        $method = $_SERVER['REQUEST_METHOD'];
        list($request, $data) = self::inRoutes($route, self::$route_REQUEST, $_REQUEST);

        if( is_null($request) && $method == 'GET' ){
            list($request, $data) = self::inRoutes($route, self::$route_GET, $_GET);
        } else if( is_null($request) && $method == 'POST' ){
            list($request, $data) = self::inRoutes($route, self::$route_POST, $_POST);
        }

        if ( in_array($route, self::$auth[$method]) ) {
            if ( !isAuth() ) {
                self::headerError('401', 'conexão não autorizada.');
            }
        }

        return [$request, $data];
    }

    /**
     * @param $routes
     */
    public static function inRoutes($route, $DT, $_obj){
        $data = [];
        $request = null;

        foreach($DT as $route_=>$vRoute){
            $rArr = explode('/', $route);
            if($route == $route_){
                $request = $vRoute;
                $data = $_obj;
            } else {
                $nRoute = explode('/', $route_);
                $data = [];

                foreach($nRoute as $iR=>$vR){
                    if( strrpos($vR, '{') > -1 ){
                        $var = preg_replace('/[{}]/', '', $vR);

                        if( isset($rArr[ $iR ]) ){
                            $nRoute[ $iR ] = $rArr[ $iR ];
                            $data[ $var ] = $rArr[ $iR ];
                        } else {
                            $nRoute[ $iR ] = '';
                            $data[ $var ] = null;
                        }
                    }
                }
                
                if($route == implode('/', $nRoute)){
                    $data = array_merge($_obj, $data);
                    $request = $vRoute;
                }
            }

            if( !empty($request) ){
                break;
            }
        }

        $json = json_decode(file_get_contents('php://input'), true);
        if( is_array($json) ){
            $data = array_merge($data, $json);
        }
        
        $data['userId'] = AuthGetUser();
        return [$request, $data];
    }
    

    /**
     * @param $code
     * @param $menssage
     */
    public static function headerError($code, $menssage){
        header( utf8_decode("HTTP/1.1 {$code} {$menssage}") );
        die;
    }

    /**
     * @param $url
     */
    public static function reload($url){
        ob_get_clean();
        header('Refresh: 0; url=' . $url);
        exit;
    }

    public static function back(){
        ob_get_clean();
        if(isset($_SERVER['HTTP_REFERER'])) header("Location: {$_SERVER['HTTP_REFERER']}");
		else  header("Location: javascript:history.go(-1)");
        exit;
    }

    public static function print_json($arr){
        header('Content-type: application/json;');
        echo json_encode($arr, true);
        die;
    }
}

function url($url){
    $path = preg_replace('/^(.*)\/(.*)/', '$1', $_SERVER['SCRIPT_NAME']);
    $path = preg_replace('/\/(.*)/', '$1', $path);

    $ssl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    return $ssl . '://' . $_SERVER['SERVER_NAME'] . '/' . $path . '/' . $url;
}

if( !function_exists('apache_request_headers') ) {
    function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach($_SERVER as $key => $val) {
            if( preg_match($rx_http, $key) ) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();

                $rx_matches = explode('_', $arh_key);
                if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
                    foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return( $arh );
    }
}