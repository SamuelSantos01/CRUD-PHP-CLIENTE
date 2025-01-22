<?php

class Clientes
{
    private $db;

    public function __construct()
    {
        $this->db = DB::connect();
    }

    function sendWhatsappMessage($phoneNumber)
    {
        $url = "https://api.positus.global/v2/sandbox/whatsapp/numbers/814e1f43-af0b-4ecb-a3ee-719c3481fabf/messages";
        $apiKey = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiNzFlZGI2ZGVjMWM4YjQyMTYyYmI0MTViZDk2M2E5YzFlMTQ4YTYzOWU0OGE5MmMyOWIzNGJjZTc5YjRiZjY5ZTczM2Q3ZDIzN2YwYjJlNmUiLCJpYXQiOjE3Mzc1NjU4NDAuODEyOTc0LCJuYmYiOjE3Mzc1NjU4NDAuODEyOTgsImV4cCI6MTc2OTEwMTg0MC44MDg5NTQsInN1YiI6IjMyODUzIiwic2NvcGVzIjpbXX0.iIOrViOSOTuqsoM_II4rOeGozroriwr6KCNWsVwosufvY_T8OM5CaeSGUH11H7CUmdn8pjgaraGJccUPHCJC7nKmc65XaJLMzWBH-Eh75uzENx7RX2Jmj3o7TdaT0M6cGTQve_nSmZdskeJ8CzCMFN2t-6b8aCIHhcLzEX-7BY99tkh0epBsLCvwx2i6p_MMo7lCRv7-jrFXaM5EpnJ2PbsCVzahLH6RxSPYrtfGL-O91GalUobYOVK7iiFLjQjo7n_lTn8yDBP78SKzeLrpe5kzSC6-I8L87lYex1x4v03NR4CbCDKItij9_nKVo0WrWV4PkXg5gVYHn2222kXg45MFHrAZkdgsXLOkN36huO3z0cog4HolxZ6KZ9BgqOPtRRunZTNwfTFmIDnw-6vM6YPCTFHgJwxtd7U5RzO4PmO5qH-Pj35MV5vw6mZuQqTk_62a2tjlAF9eS7l_ZKsASfxzDpRt9VvtBD45bqXnkbE8kFMvw1thd0pUvo0PU6AfeLuDcyRyjjEbWQ8WGv51pzw72hcr_x7EwoZ_HFvUiCyghVIGrsU0p9DoWwSE9Vib1XAOyZWHcrwiWLnvzfoXn_sb6Zv0xb2Cyz-TmKY0pKIg9-Th8iopejs6LwFCjQyRsFQ8Ri6b-7G0E9-FfbP2Lloo-hr3mgRqPEU5WssQalY";
        $postData = json_encode(array(
            "to" => $phoneNumber,
            "type" => "text",
            "text" => array(
                "body" => "Funcionou"
            )
        ));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer {$apiKey}"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function listarTodos()
    {
        try {
            $sql = "SELECT * FROM cliente";
            $stmt = $this->db->query($sql);
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['dados' => $dados]);
        } catch (PDOException $e) {
            $this->handleError('Erro ao listar clientes', $e);
        }
    }

    public function listarUnico($id)
    {
        try {
            if (!$this->validarId($id)) return;

            $sql = "SELECT * FROM cliente WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            echo $dados ? json_encode(['dados' => $dados]) : json_encode(['ERRO' => 'Cliente não encontrado']);
        } catch (PDOException $e) {
            $this->handleError('Erro ao listar cliente', $e);
        }
    }

    public function adicionar($dados)
    {
        try {
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$this->validarDados($dados)) return;

            $campos = implode(", ", array_keys($dados));
            $placeholders = ":" . implode(", :", array_keys($dados));

            $sql = "INSERT INTO cliente ($campos) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);

            foreach ($dados as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
            echo json_encode(['dados' => 'Cliente adicionado com sucesso']);

            $response = $this->sendWhatsappMessage($dados['telefone']);
        } catch (PDOException $e) {
            $this->handleError('Erro ao adicionar cliente', $e);
        }
    }

    public function atualizar($id, $dados)
    {
        try {
            $dados = json_decode(file_get_contents('php://input'), true);

            if (!$this->validarId($id) || !$this->validarDados($dados)) return;

            $sql = "UPDATE cliente SET " . $this->formatarCampos($dados) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            foreach ($dados as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            $stmt->execute();
            echo json_encode(['dados' => 'Cliente atualizado com sucesso']);
        } catch (PDOException $e) {
            $this->handleError('Erro ao atualizar cliente', $e);
        }
    }

    public function deletar($id)
    {
        try {
            if (!$this->validarId($id)) return;

            $sql = "DELETE FROM cliente WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();
            echo json_encode(['dados' => 'Cliente excluído com sucesso']);
        } catch (PDOException $e) {
            $this->handleError('Erro ao excluir cliente', $e);
        }
    }

    private function validarId($id)
    {
        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['ERRO' => 'É necessário informar um ID válido']);
            return false;
        }
        return true;
    }

    private function validarDados($dados)
    {
        if (empty($dados) || !is_array($dados)) {
            echo json_encode(['ERRO' => 'Dados inválidos fornecidos']);
            return false;
        }
        return true;
    }

    private function formatarCampos($dados)
    {
        $camposFormatados = [];
        foreach (array_keys($dados) as $key) {
            $camposFormatados[] = "$key = :$key";
        }
        return implode(", ", $camposFormatados);
    }

    private function handleError($mensagem, $exception)
    {
        echo json_encode(['ERRO' => $mensagem . ': ' . $exception->getMessage()]);
    }
}
