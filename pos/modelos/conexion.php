<?php

class Conexion {

    static public function conectar() {

        try {
            $link = new PDO(
                "mysql:host=127.0.0.1;dbname=inventario", 
                "root", 
                "",
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );

            // Set the PDO error mode to exception
            $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $link;

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }
}
