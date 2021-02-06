## Utilização do FrameWork
Para que uma rota funcione é necessário informalá no arquivo Route.php
Atualmente é apenas aceito os metodos (**GET** e **POST**).

------------------------------------------------------

 #### Configuração do banco
 
Para a configuração do banco de dados é necessário abrir o arquibo **Banco.class.php** encontrado em **controller\system\database**, e fazer as devidas configurações.

```php
<?php
class Banco{
    /**
     * [HOST] Constantes de conexão com o banco de Dados
     * Para o funcionamento da classe deve ser preenchido
     * todos os campos.
     *
     * DRIVERS: mysql, firebird ou sqlite
     */
    const HOST = 'localhost'; // CAMINHO DO SERVIDOR MYSQL
    const USER = 'root'; // USUÁRIO
    const PASS = ''; // SENHA DO USUÁRIO
    const BANCO = ''; // NOME DO BANCO DE DADOS
    const DRIVER = 'mysql';
...
```

------------------------------------------------------

 #### Rotas de exemplo

 
```php
	Request::GET('/usuario', 'Usuario@index');
	Request::POST('/usuario', 'Usuario@store');
	Request::POST('/usuario/{id}', 'Usuario@update');
	...
```

**Request::{METODO}(caminho, chamada, authentication)**
- caminho: String
- chamada: String
- authentication:  Boolean - Default: false

A rota é composta por caminho "/usuario" e chamada "Usuario@index".
A chamada é composta pelo nome da **class** "Usuario" e pelo nome da função "index",
classe essa que deve estar criada em ./controller com o nome **{Classe}.class.php**, no caso do exemplo acima **Usuario.class.php**.

----------------------------------

#### Exibição de JSON

Para que a função retorne um JSON é preciso utilizar a função **print_json**.

```php
public function index($req) {
		$rt = ['error'=>true, 'message'=>'Mensagem de erro'];
		print_json($rt);
}
```

O exemplo acima faz com que a rota retorne

```json
{
	"erro": true,
	"message": "Mensagem de erro"
}
```