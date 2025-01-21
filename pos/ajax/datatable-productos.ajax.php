<?php

require_once "../controladores/productos.controlador.php";
require_once "../modelos/productos.modelo.php";

require_once "../controladores/categorias.controlador.php";
require_once "../modelos/categorias.modelo.php";

class TablaProductos {

    /*=============================================
    MOSTRAR LA TABLA DE PRODUCTOS
    =============================================*/

    public function mostrarTablaProductos() {

        $item = null;
        $valor = null;
        $orden = "id";

        $productos = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);

        if (count($productos) == 0) {
            echo json_encode(["data" => []]);
            return;
        }

        $datosJson = [];
        
        foreach ($productos as $key => $producto) {

            /*=============================================
            TRAEMOS LA IMAGEN
            =============================================*/

            $imagen = "<img src='" . htmlspecialchars($producto["imagen"]) . "' width='40px'>";

            /*=============================================
            TRAEMOS LA CATEGORÍA
            =============================================*/

            $itemCategoria = "id";
            $valorCategoria = $producto["id_categoria"];

            $categorias = ControladorCategorias::ctrMostrarCategorias($itemCategoria, $valorCategoria);
            $nombreCategoria = $categorias["categoria"] ?? "Sin categoría";

            /*=============================================
            STOCK
            =============================================*/

            if ($producto["stock"] <= 10) {
                $stock = "<button class='btn btn-danger'>" . $producto["stock"] . "</button>";
            } elseif ($producto["stock"] <= 15) {
                $stock = "<button class='btn btn-warning'>" . $producto["stock"] . "</button>";
            } else {
                $stock = "<button class='btn btn-success'>" . $producto["stock"] . "</button>";
            }

            /*=============================================
            TRAEMOS LAS ACCIONES
            =============================================*/

            $botones = "<div class='btn-group'>
                            <button class='btn btn-warning btnEditarProducto' idProducto='" . $producto["id"] . "' data-toggle='modal' data-target='#modalEditarProducto'>
                                <i class='fa fa-pencil'></i>
                            </button>";

            if (!isset($_GET["perfilOculto"]) || $_GET["perfilOculto"] !== "Especial") {
                $botones .= "<button class='btn btn-danger btnEliminarProducto' 
                                idProducto='" . $producto["id"] . "' 
                                codigo='" . htmlspecialchars($producto["codigo"]) . "' 
                                imagen='" . htmlspecialchars($producto["imagen"]) . "'>
                                <i class='fa fa-times'></i>
                             </button>";
            }

            $botones .= "</div>";

            /*=============================================
            PREPARAR DATOS PARA EL JSON
            =============================================*/

            $datosJson[] = [
                $key + 1,
                $imagen,
                htmlspecialchars($producto["codigo"]),
                htmlspecialchars($producto["descripcion"]),
                htmlspecialchars($nombreCategoria),
                $stock,
                number_format($producto["precio_compra"], 2),
                number_format($producto["precio_venta"], 2),
                $producto["fecha"],
                $botones
            ];
        }

        echo json_encode(["data" => $datosJson]);
    }
}

/*=============================================
ACTIVAR TABLA DE PRODUCTOS
=============================================*/
$activarProductos = new TablaProductos();
$activarProductos->mostrarTablaProductos();
