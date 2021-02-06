<?php
class Banco{
    /**
     * [HOST] Constantes de conexão com o banco de Dados
     * Para o funcionamento da classe deve ser preenchido
     * todos os campos.
     *
     * DRIVERS: mysql, firebird ou sqlite
     */
    const HOST = 'localhost';
    const USER = 'root';
    const PASS = '';
    const BANCO = 'aesprn';
    const DRIVER = 'mysql';

    // const BANCO = __DIR__ . '/../../../rastreamento/database/cachcontroll.db';
    // const BANCO = '/../../database/cachcontroll.db';
    // const DRIVER = 'sqlite';

    /**
     * [private] Variaveis privadas
     */
    public static $pdo = null;

    /**
     * [connect] Estabelece a conexão com o banco de Dados
     * Informado nas CONSTANTES acima.
     */
    private static function connect(){
        switch (strtolower(self::DRIVER)) {
            case 'firebird':
                $driver = self::DRIVER . ':dbname=' . self::HOST . ':' . self::BANCO . ';charset=utf8';
                break;
            case 'mysql':
                $driver = self::DRIVER . ':host=' . self::HOST . ';dbname=' . self::BANCO . ';charset=utf8';
                break;
            case 'sqlite':
                $driver = self::DRIVER . ':' . __DIR__ . self::BANCO;
                break;
        }

        if (is_null(self::$pdo)) {
            if (self::DRIVER != 'sqlite') {
                self::$pdo = new PDO($driver, self::USER, self::PASS);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            } else {
                self::$pdo = new PDO($driver);
            }
        }
    }


    public static function query_pdo($sql, $fetch=true) {
        if( is_null(self::$pdo) ) self::connect();
        
        $query = self::$pdo->query($sql);
        if ( $fetch ) $query = $query->fetch();
        
        self::verifyError();
        return true;
    }

    public static function exec($sql) {
        if( is_null(self::$pdo) ) self::connect();

        self::$pdo->exec($sql);
        self::verifyError();

        return true;
    }

    /**
     * [query] Executa uma pesquisa e retorna os dados
     * @param String $sql [sql a ser executado]
     * @param Object $dataPrepare [os dados para serem preenchidos no query]
     * @return array $data
     */
    public static function query($sql, $dataPrepare=null){
        if( is_null(self::$pdo) ) self::connect();

        $sth = self::$pdo->prepare($sql);
        $sth->execute($dataPrepare);
        $data = $sth->fetchAll(PDO::FETCH_CLASS);
        return $data;
    }

    public static function verifyError(){
        if(self::$pdo->errorInfo()[0] != '00000') throw new ErrorException(self::$pdo->errorInfo()[2]);
    }

    /**
     * @param $table
     * @param null $columns
     * @param bool $debug
     * @return bool|string
     * @throws ErrorException
     */
    public static function create($table, $columns=null, $debug=false){
        if( is_null(self::$pdo) ) self::connect();
        if( is_null($columns) && strrpos($table, 'CREATE ') > -1){
            $sql = $table;
        } else if(!is_null($columns)){
            if(is_array($columns)) $columns = implode(', ', $columns);
            $sql = "CREATE TABLE IF NOT EXISTS {$table} ({$columns})";
        } else {
            throw new ErrorException('Dados incorretos para o UPDATE!');
        }

        try {
            $create = self::$pdo->exec($sql);
            self::verifyError();

            if($debug) return 'Tabela criada com sucesso!';
            else return true;
        } catch (PDOException $e){
            if($debug) return $e->getMessage();
            else return false;
        }
    }


    /**
     * @param $table
     * @param null $values
     * @param null $where
     * @param bool $debug
     * @return bool|string
     * @throws ErrorException
     */
    public static function update($table, $aValues=null, $where=null, $debug=false){
        $values = '';
        if( is_null(self::$pdo) ) self::connect();
        if( is_null($aValues) && strrpos($table, 'UPDATE ') > -1){
            $sql = $table;
        } else if(!is_null($aValues)){
            if(is_array($aValues)){
                $spr = '';

                foreach ($aValues as $column=>$value){
                    $value = trim($value);
                    $value = strval($value) != '0' && empty($value) ? 'NULL' : "'{$value}'";
                    $values .= $spr . "{$column}={$value}";
                    $spr = ', ';
                }
            }

            $arWhere = [];
            if(is_array($where)){
                $spr = '';
                foreach ($where as $column=>$value){
                    $arWhere[] = "{$column}='{$value}'";
                }
            } else {
                throw new ErrorException('Favor informar um where em array! [COLUNA = VALOR]');
            }

            $where = implode(' AND ', $arWhere);
            $sql = "UPDATE {$table} SET {$values} WHERE {$where}";
        } else {
            throw new ErrorException('Dados incorretos para o UPDATE!');
        }

        try {           
            self::$pdo->exec($sql);
            self::verifyError();

            if($debug) return 'Item alterado com sucesso!';
            else return true;
        } catch (PDOException $e){
            if($debug) return $e->getMessage();
            else return false;
        }
    }

    /**
     * @param $table
     * @param null $values
     * @param bool $retid
     * @param bool $debug
     * @return bool|int
     * @throws ErrorException
     */
    public static function insert($table, $values=null, $retid=false, $debug=false){
        if( is_null(self::$pdo) ) self::connect();

        $retFirebird = self::DRIVER == 'firebird' && $retid !== false && is_string($retid);
        if( is_null($values) && strrpos($table, 'INSERT ') > -1){
            $sql = $table;
        } else if(!is_null($values)){
            if(is_array($values)){
                $columns = implode(', ', array_keys($values));
                // $values = "'" . implode("', '", array_values($values)) . "'";
                $values = array_map(function($var){
                    if ( strval($var) != '0' && (is_null($var) || empty($var) || strtoupper($var) === 'NULL') ) {
                        return 'NULL';
                    } else {
                        return "'{$var}'";
                    }
                }, $values);

                $values = implode(", ", array_values($values));
            }

            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
            if ( $retFirebird ) {
                $sql .= ' RETURNING ' . $retid;
            }
        } else {
            throw new ErrorException('Dados incorretos para o UPDATE!');
        }

        try {
            if ( !$retFirebird ){
                self::$pdo->exec($sql);
            } else {
                $ret = self::$pdo->query($sql)->fetch();
            }

            self::verifyError();

            if ($retFirebird){
                return $ret[ $retid ];
            } else if($retid) {
                return self::$pdo->lastInsertId();
            } else {
                return true;
            }
        } catch (PDOException $e){
            if($debug) die( $e->getMessage() );
            if($retid || $retFirebird) return 0;
            else return false;
        }
    }
}
