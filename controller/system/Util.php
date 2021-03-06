<?php

function print_json($arr, $die=false, $code=200){
    $headers = $_SERVER;
    if( isset($headers['HTTP_ACCEPT']) && $headers['HTTP_ACCEPT'] == 'application/json' ) {
        if( isset($arr['error']) && $arr['error'] && $code == 200 ){
            $code = 400;
        }

        http_response_code($code);
    }

    header('Content-type: application/json');
    echo json_encode($arr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if($die) die;
}

/**
 * CRIANDO JSON WEB TOKEN
 */
$key = md5('API-KEY-TOKEN123456789@');

function jwt($dataload) {
    global $key;

    //Header Token
    $header = [
        'typ' => 'JWT',
        'alg' => 'HS256'
    ];

    //Payload - Content
    $payload = array_merge([
        'iat' => (new DateTime("now"))->getTimestamp(),
        'exp' => (new DateTime("now +7 day"))->getTimestamp(),
        'iss' => 'UNICA_MEDICO_API'
    ], $dataload);

    //JSON
    $header = json_encode($header);
    $payload = json_encode($payload);

    //Base 64
    $header = base64_encode($header);
    $payload = base64_encode($payload);

    //Sign
    $sign = hash_hmac('sha256', $header . "." . $payload, $key, true);
    $sign = base64_encode($sign);

    //Token
    $token = $header . '.' . $payload . '.' . $sign;

    return $token;
}

function isAuth($errorCode = false) {
    global $key;

    if( isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION']) ){
        $token = explode(' ', $_SERVER['HTTP_AUTHORIZATION'])[1];
        list($header, $payload, $signature) = explode('.', $token);

        $checkSignature = hash_hmac('sha256', $header . "." . $payload, $key, true);
        $header = json_decode(base64_decode($header), true);
        $payload = json_decode(base64_decode($payload), true);

        if (base64_encode($checkSignature) != $signature) {
            return $errorCode ? 2 : false;
        }

        // CEHCK DATE
        $exp = new DateTime('@' . $payload['exp']);
        $nowTime = new DateTIme('now');
        $diff = (object) $nowTime->diff($exp);

        $diffTime = $diff->days > 0 || $diff->h > 0 || $diff->i > 0 || $diff->s > 0;
        if ($diffTime && $diff->invert) {
            return $errorCode ? 3 : false;
        }

    } else {
        return $errorCode ? 1 : false;
    }

    return $errorCode ? 0 : true;
}

function AuthGetUser(){
    if ( !isAuth() ){
        return null;
    } else {
        $token = explode(' ', $_SERVER['HTTP_AUTHORIZATION'])[1];
        list($header, $payload, $signature) = explode('.', $token);
        $payload = json_decode(base64_decode($payload), true);

        return $payload['sub'];
    }
}


/**
 * Função responsável por validar o CPF
 */
function validaCPF($cpf = null) {

	// Verifica se um número foi informado
	if(empty($cpf)) {
		return false;
	}

	// Elimina possivel mascara
	$cpf = preg_replace("/[^0-9]/", "", $cpf);
	$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
	
	// Verifica se o numero de digitos informados é igual a 11 
	if (strlen($cpf) != 11) {
		return false;
	}
	// Verifica se nenhuma das sequências invalidas abaixo 
	// foi digitada. Caso afirmativo, retorna falso
	else if ($cpf == '00000000000' || 
		$cpf == '11111111111' || 
		$cpf == '22222222222' || 
		$cpf == '33333333333' || 
		$cpf == '44444444444' || 
		$cpf == '55555555555' || 
		$cpf == '66666666666' || 
		$cpf == '77777777777' || 
		$cpf == '88888888888' || 
		$cpf == '99999999999') {
		return false;
	 // Calcula os digitos verificadores para verificar se o
	 // CPF é válido
	 } else {   
		
		for ($t = 9; $t < 11; $t++) {
			
			for ($d = 0, $c = 0; $c < $t; $c++) {
				$d += $cpf{$c} * (($t + 1) - $c);
			}
			$d = ((10 * $d) % 11) % 10;
			if ($cpf{$c} != $d) {
				return false;
			}
		}

		return true;
	}
}