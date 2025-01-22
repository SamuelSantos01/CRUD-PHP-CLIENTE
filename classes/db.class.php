<?php

class DB {
    public static function connect() {
        $host = 'localhost';
        $user = 'root';
        $pass = 'umdianasestrelas';
        $base = "sistema_clientes";

        try {
            return new PDO("mysql:host=$host;dbname=$base;charset=utf8", $user, $pass);
        } catch (PDOException $e) {
            die("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }
}
