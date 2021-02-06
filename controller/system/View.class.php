<?php
class View{
    /***/
    private $view = null;
    private $variables = [];

    const viewPath = '../../views/';
    protected $commands = [
        '@include_js\((.*)\)' => '<script src="@url($1).js"></script>',
        '@include_css\((.*)\)' => '<link rel="stylesheet" type="text/css" href="@url($1).css"></link>',

        '@php' => '<?php',
        '@endphp' => '?>',
        '@if\((.*)\)' => '<?php if($1): ?>',
        '@ifelse\((.*)\)' => '<?php ifelse($1): ?>',
        '@else' => '<?php else: ?>',
        '@endif' => '<?php endif; ?>',

        '@foreach\((.*)\)' => '<?php foreach($1): ?>',
        '@endforeach' => '<?php endforeach; ?>',

        '@for\((.*)\)' => '<?php for($1): ?>',
        '@endfor' => '<?php endfor; ?>',

        '@while\((.*)\)' => '<?php while($1): ?>',
        '@include\((.*), (.*)\)' => '<?php View($1, $2)->drawn(); ?>',
        '@include\((.*)\)' => '<?php View($1)->drawn(); ?>',
        '@endwhile' => '<?php endwhile; ?>',
        '@class\((.*)\)' => '<?php require_once(__DIR__ . "/../../" . $1 . ".class.php"); ?>',

        '({!--)(.*)(--!})' => '',
        '({!--)(.*)(!--})' => '',
        '@url\((.*)\)' => '{{url($1)}}',
        '\{\{(.*)\}\}' => '<?php View::e($1);?>',
    ];

    /**
     * View constructor.
     * @param null $view
     * @param array $variables
     */
    public function __construct($view, array $variables){
        $this->view = $view;
        $this->variables = $variables;
    }

    /**
     * Print view
     */
    public function drawn(){
        $varNames = '$' . implode(', $', array_keys($this->variables) );
        if($varNames != '$') eval('list(' . $varNames . ') = array_values($this->variables);');

        $variables = $this->variables;
        $fileView = __DIR__ . '/' . $this::viewPath . $this->view . '.view.php';
        if(!file_exists($fileView)){
            throw new ErrorException($this->view . ' Não foi encontrado!');
        } else {
            $content = file_get_contents($fileView);
            $content = $this->render($content);
            
            $fview = __DIR__ . '/tmp/' . time() . '_' . uniqid() . '_reder_view.uview';
            $fp = fopen($fview, 'w');
            fwrite($fp, $content);
            fclose($fp);

            if(file_exists($fview)){
                include $fview;
                if(file_exists($fview)) unlink($fview);
            } else {
                throw new ErrorException('Erro ao tentar renderizar o VIEW (' . $fileView . ')');
            }
        }
    }

    public function render($content){
        foreach($this->commands as $command=>$replace){
            $content = preg_replace('/' . $command . '/m', $replace, $content);
        } return $content;
    }

    public static function e($var){
        if(is_array($var)) print_r($var);
        else echo $var;
    }
}

function View($view, $vars=[]){
    return new View($view, $vars);
}