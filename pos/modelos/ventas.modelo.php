<?php

require_once "conexion.php";

class ModeloVentas {

    /*=============================================
    MOSTRAR VENTAS
    =============================================*/
    static public function mdlMostrarVentas($tabla, $item, $valor) {
        try {
            if ($item != null) {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla WHERE $item = :$item ORDER BY id ASC");
                $stmt->bindParam(":" . $item, $valor, PDO::PARAM_STR);
            } else {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id ASC");
            }

            $stmt->execute();

            return $item != null ? $stmt->fetch() : $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarVentas: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    REGISTRO DE VENTA
    =============================================*/
    static public function mdlIngresarVenta($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO $tabla(codigo, id_cliente, id_vendedor, productos, impuesto, neto, total, metodo_pago) 
                VALUES (:codigo, :id_cliente, :id_vendedor, :productos, :impuesto, :neto, :total, :metodo_pago)"
            );

            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
            $stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
            $stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
            $stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlIngresarVenta: " . $e->getMessage());
            return "error";
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    EDITAR VENTA
    =============================================*/
    static public function mdlEditarVenta($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "UPDATE $tabla SET 
                    id_cliente = :id_cliente, 
                    id_vendedor = :id_vendedor, 
                    productos = :productos, 
                    impuesto = :impuesto, 
                    neto = :neto, 
                    total = :total, 
                    metodo_pago = :metodo_pago 
                WHERE codigo = :codigo"
            );

            $stmt->bindParam(":codigo", $datos["codigo"], PDO::PARAM_INT);
            $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_INT);
            $stmt->bindParam(":id_vendedor", $datos["id_vendedor"], PDO::PARAM_INT);
            $stmt->bindParam(":productos", $datos["productos"], PDO::PARAM_STR);
            $stmt->bindParam(":impuesto", $datos["impuesto"], PDO::PARAM_STR);
            $stmt->bindParam(":neto", $datos["neto"], PDO::PARAM_STR);
            $stmt->bindParam(":total", $datos["total"], PDO::PARAM_STR);
            $stmt->bindParam(":metodo_pago", $datos["metodo_pago"], PDO::PARAM_STR);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEditarVenta: " . $e->getMessage());
            return "error";
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    ELIMINAR VENTA
    =============================================*/
    static public function mdlEliminarVenta($tabla, $datos) {
        try {
            $stmt = Conexion::conectar()->prepare("DELETE FROM $tabla WHERE id = :id");
            $stmt->bindParam(":id", $datos, PDO::PARAM_INT);

            return $stmt->execute() ? "ok" : "error";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarVenta: " . $e->getMessage());
            return "error";
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    RANGO FECHAS
    =============================================*/
    static public function mdlRangoFechasVentas($tabla, $fechaInicial, $fechaFinal) {
        try {
            if ($fechaInicial && $fechaFinal) {
                $stmt = Conexion::conectar()->prepare(
                    "SELECT * FROM $tabla WHERE fecha BETWEEN :fechaInicial AND :fechaFinal"
                );
                $stmt->bindParam(":fechaInicial", $fechaInicial, PDO::PARAM_STR);
                $stmt->bindParam(":fechaFinal", $fechaFinal, PDO::PARAM_STR);
            } else {
                $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla ORDER BY id DESC");
            }

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en mdlRangoFechasVentas: " . $e->getMessage());
            return [];
        } finally {
            $stmt = null;
        }
    }

    /*=============================================
    SUMAR EL TOTAL DE VENTAS
    =============================================*/
    static public function mdlSumaTotalVentas($tabla) {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT SUM(neto) as total FROM $tabla");
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en mdlSumaTotalVentas: " . $e->getMessage());
            return null;
        } finally {
            $stmt = null;
        }
    }
}
