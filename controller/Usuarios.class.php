<?php
require 'models/Usuario.model.php';

class Usuarios {
    /**
     * Função responsável pela listagem
     * de usuarios
     */
    public function index ($req) {
        $rt = ['AQUI É PRA ESTÁ A LISTAGEM DE USUARIOS'];

        // (new Usuario())->where(['CAMPO'=>'VALOR']); // ADICIONA WHERE AO SELECT
        // (new Usuario())->limit(5); // PERMITE QUE RETORNE APENAS 5 USUARIOS
        // (new Usuario())->offset(5); // PULA 5 USUARIOS NO RETORNO
        
        // (new Usuario())->find(); // RETORNA TODOS OS USUARIOS CADASTRADOS
        // (new Usuario())->findOne(); // RETORNA APENAS O PRIMEIRO DA LISTA
        // (new Usuario())->findById(5); // RETORNA O USUARIO COM O ID 5

        print_json($rt);
    }

    /**
     * Função responsável pelo cadastro
     * de usuarios
     */
    public function store ($req) {
        $rt = ['AQUI É PRA ESTÁ O CADASTRO DE USUARIOS'];
        print_json($rt);
    }

    /**
     * Função responsável pela alteração
     * de usuarios
     */
    public function update ($req) {
        $rt = ['AQUI É PRA ESTÁ A ALTERAÇÃO DE USUARIOS'];
        print_json($rt);
    }

    /**
     * Função responsável a autenticar
     * o usuário
     */
    public function auth($req) {
        $rt = ['error'=>false, 'message'=>null];

        try{
            if ( !isset($req->usuario) ) {
                throw new Exception("Favor informar o usuário.", 1);
            } else if ( !isset($req->senha) ) {
                throw new Exception("Favor informar a senha.", 1);
            }

            $where = [];
            $where[] = (new Where(['apelido'=>$req->usuario, 'id'=>$req->usuario]))->orWhere(true);
            $usuario = (new Usuario())->where($where)->select(['*'])->findOne();

            if ( !$usuario) {
                throw new Exception("Usuário não encontrado.", 1);
            } else if ($req->senha != $usuario[0]->senha && $req->senha !== Cryptografa($usuario[0]->senha)){
                throw new Exception("Senha inválida.", 1);
            }

            $token = jwt(['sub'=>$usuario[0]->id]);
            unset($usuario[0]->senha);

            $rt['token'] = $token;
            $rt['data'] = $usuario[0];
        } catch (Exception $e) {
            $rt = ['error'=>true, 'message'=>$e->getMessage()];
        }

        print_json($rt, true);
    }

    /**
     * Função responsável por atualizar o TOKEN
     */
    public function refresh() {
        $rt = ['error'=>false, 'message'=>null];

        try {
            $code = isAuth(true);
            if ( !$code || $code === 3 ) {
                $rt['token'] = jwt(['sub'=>$usuario[0]->id]);
            } else {
                throw new Exception("Acesso negado.", 1);
            }
        } catch (Exception $e) {
            $rt = ['error'=>true, 'message'=> $e->getMessage()];
        }

        print_json($rt, true);
    }
}