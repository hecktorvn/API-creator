<?php
class Usuario extends Model {
    protected $table = 'usuario';

    protected $primary = 'id';
    protected $notShow = ['senha'];
    protected $columns = [
        'id',
        'nome',
        'email',
        'telefone',
        'funcao',
        'lotacao',
        'apelido',
        'senha',
        'cpf',
        'ativo',
        'validade',
        'foto',
        'web',
        'ged'
    ];
}