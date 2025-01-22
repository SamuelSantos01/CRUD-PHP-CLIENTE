<?php

class Rotas
{
    private $listaRotas = [];
    private $listaCallback = [];

    public function add($metodo, $rota, $callback)
    {
        $this->listaRotas[] = strtoupper($metodo) . ':' . $rota;
        $this->listaCallback[] = $callback;

        return $this;
    }

    public function ir($rota)
    {
        $param = '';
        $callback = '';
        $methodServer = $_SERVER['REQUEST_METHOD'];
        
        $methodServer = isset($_POST['_method']) ? $_POST['_method'] : $methodServer;
        $rota = strtoupper($methodServer) . ":/" . $rota;

        if (substr_count($rota, "/") >= 3) {
            $param = substr($rota, strrpos($rota, "/") + 1);
            $rota = substr($rota, 0, strrpos($rota, "/")) . "/[PARAM]";
        }

        $indice = array_search($rota, $this->listaRotas);
        if ($indice !== false) {
            $callback = explode("::", $this->listaCallback[$indice]);
        }

        $class = isset($callback[0]) ? $callback[0] : '';
        $method = isset($callback[1]) ? $callback[1] : '';

        if (class_exists($class)) {
            if (method_exists($class, $method)) {
                $instanciaClass = new $class();

                if ($method === 'enviarMensagemWhatsapp') {
                    $dados = json_decode(file_get_contents('php://input'), true);
                    if ($dados === null) {
                        echo json_encode(['erro' => 'Dados inválidos no corpo da requisição']);
                        exit;
                    }

                    return call_user_func_array(
                        array($instanciaClass, $method),
                        array($dados)
                    );
                }

                if ($method === 'atualizar') {
                    $dados = json_decode(file_get_contents('php://input'), true);
                    if ($dados === null) {
                        echo json_encode(['erro' => 'Dados inválidos no corpo da requisição']);
                        exit;
                    }

                    return call_user_func_array(
                        array($instanciaClass, $method),
                        array($param, $dados)
                    );
                }

                return call_user_func_array(
                    array($instanciaClass, $method),
                    array($param)
                );
            } else {
                $this->naoExiste();
            }
        } else {
            $this->naoExiste();
        }
    }

    public function naoExiste()
    {
        http_response_code(404);
        echo json_encode(['erro' => 'Rota não encontrada.']);
    }
}