<?php

/**
 * Class ServerUnica
 */
class ServerUnica{
    /**
     * [private] Variaveis privadas
     */
    public static $filial = null;
    private static $HOST = null;
    public static $code = 200;

    /**
     * @return mixed
     */
    public static function getHOST()
    {
        return self::$HOST;
    }

    /**
     * @param mixed $HOST
     */
    public static function setHOST($HOST)
    {
        self::$HOST = $HOST;
    }


    /**
     * @param string $postvars
     * @return mixed
     */
    public static function PostServer($postvars='', $host=null, $decode=true){
        if( !empty(self::$filial) ){
            if(empty($postvars)) $postvars = [];
            $postvars['filial'] = self::$filial;
        }

        if(is_array($postvars)){
            $postvars = http_build_query($postvars);
        }
	
		if( is_null($host) ){
			$host = self::$HOST;
		}
	
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, '3');
        
        $content = trim(curl_exec($ch));
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        self::$code = $httpcode;
        return $decode ? json_decode($content) : $content;
    }

    /**
     * @param $sql
     * @param null $dataPrepare
     * @return mixed
     */
    public static function query($sql, $dataPrepare=null, $host=null){
        if(is_array($dataPrepare)) {
            foreach ($dataPrepare as $var=>$value){
                $sql = preg_replace('/:' . $var . '/', $value, $sql);
            }
        }

        $data = self::PostServer(['sql'=>$sql], $host);
        return $data;
    }
	
	/**
     * @param $table
     * @param $data
	 * @param $where
     * @return mixed
     */
	public static function update($table, $data, $where, $host=null){
		$x = '';
		$sql = "UPDATE {$table} SET ";
		
		foreach($data as $column=>$value){
			$sql .= $x . "{$column}='{$value}'";
			$x = ', ';
		}
		
		$x = '';
		$sql .= " WHERE ";
		foreach($where as $column=>$value){
			$sql .= $x . "{$column}='{$value}'";
			$x = ' AND ';
		}
		
		$sql .= ' RETURNING ' . array_keys($where)[0];
		return count(self::query($sql)) > 0;
    }
    
    /**
     * Função responsável por verificar se existe 
    */
    public static function PrepareServerKey($key){
        $key = urlencode($key);
        if( isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION']) ){
            list($prefix, $key) = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);

            if( $prefix != 'APP-KEY' ) {
                $rt = [
                    'error' => true,
                    'msg' => 'Tem um erro na autorização'
                ];
    
                print_json($rt, true, 401);
            }

            $key = $key;
        }

        $server = Banco::query('SELECT * FROM empresas WHERE status = 1 AND key = "' . $key . '"');
        if( empty($server) ) {
            $rt = [
                'error' => true,
                'msg' => 'A chave informada é inválida!'
            ];

            print_json($rt, true, 401);
        } else {
            self::setHOST( $server[0]->server );
        }
    }
}

if(isset($_SESSION['__filial__'])){
    ServerUnica::$filial = $_SESSION['__filial__'];
}
