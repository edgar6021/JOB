<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";
require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";
require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";
require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class ImprimirFactura {

    public $codigo;

    public function traerImpresionFactura() {

        // TRAER INFORMACIÓN DE LA VENTA
        $itemVenta = "codigo";
        $valorVenta = $this->codigo;
        $respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

        // Formateo de fecha y totales
        $fecha = substr($respuestaVenta["fecha"], 0, -8);
        $productos = json_decode($respuestaVenta["productos"], true);
        $neto = number_format($respuestaVenta["neto"], 2);
        $impuesto = number_format($respuestaVenta["impuesto"], 2);
        $total = number_format($respuestaVenta["total"], 2);

        // INFORMACIÓN DEL CLIENTE
        $itemCliente = "id";
        $valorCliente = $respuestaVenta["id_cliente"];
        $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

        // INFORMACIÓN DEL VENDEDOR
        $itemVendedor = "id";
        $valorVendedor = $respuestaVenta["id_vendedor"];
        $respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

        // CARGA TCPDF
        require_once('tcpdf_include.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->AddPage();

        // ENCABEZADO CON INFORMACIÓN DE LA EMPRESA
        $bloque1 = <<<EOF
            <table>
                <tr>
                    <td style="width:150px"><img src="images/techeande.png"></td>
                    <td style="background-color:white; width:270px; font-size:10px; text-align:right;">
                        <b>RNC:</b> 71759963-9<br>
                        <b>Dirección:</b> La Guayiga Km20 Autopista Duarte Vieja<br>
                        <b>Teléfono:</b> 829-380-8296<br>
                        <b>Email:</b> techeande@gmail.com
                    </td>
                    <td style="background-color:white; width:110px; text-align:center; color:red; font-size:14px">
                        <b>FACTURA N.<br>$valorVenta</b><br><br>
                        <b>NCF:</b> B010000001
                    </td>
                </tr>
            </table>
EOF;

        $pdf->writeHTML($bloque1, false, false, false, false, '');

        // INFORMACIÓN DEL CLIENTE Y FECHA
        $bloque2 = <<<EOF
            <table style="font-size:10px; padding:5px 10px;">
                <tr>
                    <td style="border: 1px solid #666; width:390px">
                        <b>Cliente:</b> {$respuestaCliente['nombre']}<br>
                        <b>RNC/Cédula:</b> {$respuestaCliente['rnc']}<br>
                        <b>Dirección:</b> {$respuestaCliente['direccion']}
                    </td>
                    <td style="border: 1px solid #666; width:150px; text-align:right">
                        <b>Fecha:</b> $fecha
                    </td>
                </tr>
            </table>
EOF;

        $pdf->writeHTML($bloque2, false, false, false, false, '');

        // DETALLES DE PRODUCTOS
        $pdf->writeHTML("<br><b>Detalle de Productos</b>", false, false, false, false, '');
        
        $bloque3 = <<<EOF
            <table style="font-size:10px; padding:5px 10px;">
                <tr>
                    <td style="border: 1px solid #666; background-color:#eee; width:250px; text-align:center">Producto</td>
                    <td style="border: 1px solid #666; background-color:#eee; width:70px; text-align:center">Cantidad</td>
                    <td style="border: 1px solid #666; background-color:#eee; width:100px; text-align:center">Precio Unit.</td>
                    <td style="border: 1px solid #666; background-color:#eee; width:100px; text-align:center">Total</td>
                </tr>
EOF;

        foreach ($productos as $item) {
            $descripcion = $item["descripcion"];
            $cantidad = $item["cantidad"];
            $precioUnitario = number_format($item["precio"], 2);
            $precioTotal = number_format($item["total"], 2);

            $bloque3 .= <<<EOF
                <tr>
                    <td style="border: 1px solid #666; width:250px; text-align:left">{$descripcion}</td>
                    <td style="border: 1px solid #666; width:70px; text-align:center">{$cantidad}</td>
                    <td style="border: 1px solid #666; width:100px; text-align:right">{$precioUnitario}</td>
                    <td style="border: 1px solid #666; width:100px; text-align:right">{$precioTotal}</td>
                </tr>
EOF;
        }

        $bloque3 .= "</table>";
        $pdf->writeHTML($bloque3, false, false, false, false, '');

        // RESUMEN DE TOTALES
        $bloque4 = <<<EOF
            <table style="font-size:10px; padding:5px 10px;">
                <tr>
                    <td style="width:420px;"></td>
                    <td style="width:100px; text-align:right;"><b>Subtotal:</b></td>
                    <td style="width:100px; text-align:right;">$neto</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align:right;"><b>ITBIS (18%):</b></td>
                    <td style="text-align:right;">$impuesto</td>
                </tr>
                <tr>
                    <td></td>
                    <td style="text-align:right;"><b>Total General:</b></td>
                    <td style="text-align:right;">$total</td>
                </tr>
            </table>
EOF;

        $pdf->writeHTML($bloque4, false, false, false, false, '');

        // SALIDA DEL ARCHIVO
        $pdf->Output('factura.pdf');
    }
}

// INSTANCIAR LA FACTURA
$factura = new ImprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->traerImpresionFactura();

?>
