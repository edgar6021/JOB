<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

require_once 'tcpdf_include.php';

class ImprimirFacturaTicket {
    public $codigo;

    public function generarFactura() {
        $venta = $this->obtenerVenta();
        $cliente = $this->obtenerCliente($venta['id_cliente']);
        $vendedor = $this->obtenerVendedor($venta['id_vendedor']);
        $productos = $this->obtenerProductos($venta['productos']);
        $detallesTotales = $this->calcularTotales($venta);

        // Crear el PDF (para un formato de ticket más pequeño)
        $pdf = new TCPDF('P', 'mm', array(80, 150), true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Techeande');
        $pdf->SetTitle('Factura N. ' . $venta['codigo']);
        $pdf->SetSubject('Factura de Compra');
        
        // Configuración de márgenes y diseño para ticket
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(TRUE, 5);
        
        $pdf->AddPage();

        // Secciones del PDF
        $this->agregarEncabezado($pdf, $venta['codigo']);
        $this->agregarInformacionCliente($pdf, $cliente, $vendedor, $venta['fecha']);
        $this->agregarDetalleProductos($pdf, $productos);
        $this->agregarTotales($pdf, $detallesTotales);
        $this->agregarCodigoQR($pdf, $venta, $cliente, $detallesTotales);
        $this->agregarNotaFinal($pdf);

        // Salida del PDF
        $pdf->Output('factura_ticket_' . $venta['codigo'] . '.pdf', 'I');
    }

    private function obtenerVenta() {
        $item = "codigo";
        $valor = $this->codigo;
        return ControladorVentas::ctrMostrarVentas($item, $valor);
    }

    private function obtenerCliente($idCliente) {
        $item = "id";
        return ControladorClientes::ctrMostrarClientes($item, $idCliente);
    }

    private function obtenerVendedor($idVendedor) {
        $item = "id";
        return ControladorUsuarios::ctrMostrarUsuarios($item, $idVendedor);
    }

    private function obtenerProductos($productosJson) {
        $productos = json_decode($productosJson, true);
        foreach ($productos as &$producto) {
            $item = "descripcion";
            $valor = $producto['descripcion'];
            $orden = null;
            $datosProducto = ControladorProductos::ctrMostrarProductos($item, $valor, $orden);
            $producto['precio_unitario'] = number_format($datosProducto['precio_venta'], 2);
        }
        return $productos;
    }

    private function calcularTotales($venta) {
        return [
            'neto' => number_format($venta['neto'], 2),
            'impuesto' => number_format($venta['impuesto'], 2),
            'total' => number_format($venta['total'], 2),
        ];
    }

    private function agregarEncabezado($pdf, $numeroFactura) {
        $html = <<<HTML
        <table style="font-family: 'Arial', sans-serif; text-align: center; width: 100%;">
            <tr>
                <td>
                    <img src="./images/techeande.png" style="text-align:center; "width="50" alt="Techeande" />
                    <small style="color: #4CAF50;">Techeande</small><br>
                    <small>RNC: 71759963-9</small><br>
                    <small>La Guayiga Km20</small><br>
                    <small>Tel: 829-380-8296</small>
                </td>
            </tr>
        </table>
        <hr style="border: 1px solid #4CAF50; margin-top: 5px;">
        <h3 style="text-align: center; background-color: #f1f1f1; font-size: 14px; margin-top: 10px;">
            FACTURA N. $numeroFactura
        </h3>
HTML;
        $pdf->writeHTML($html, false, false, false, false, '');
    }

    private function agregarInformacionCliente($pdf, $cliente, $vendedor, $fecha) {
        $fechaFormateada = date('d-m-Y', strtotime($fecha));
        $html = <<<HTML
        <table style="font-size: 10px; width: 100%; margin-top: 10px; border-collapse: collapse;">
            <tr>
                <td>
                    <strong>Cliente:</strong> {$cliente['nombre']}<br>
                    <strong>RNC:</strong> {$cliente['documento']}
                </td>
                <td style="text-align: right;">
                    <strong>Vendedor:</strong> {$vendedor['nombre']}<br>
                    <strong>Fecha:</strong> $fechaFormateada
                </td>
            </tr>
        </table>
HTML;
        $pdf->writeHTML($html, false, false, false, false, '');
    }

    private function agregarDetalleProductos($pdf, $productos) {
        $html = <<<HTML
        <table style="font-size: 10px; width: 100%; margin-top: 10px; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #4CAF50; color: #fff;">
                    <th style="padding: 5px; text-align: left;">Producto</th>
                    <th style="padding: 5px; text-align: right;">Cantidad</th>
                    <th style="padding: 5px; text-align: right;">Precio</th>
                    <th style="padding: 5px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
HTML;
        foreach ($productos as $producto) {
            $html .= <<<HTML
                <tr>
                    <td style="padding: 5px;">{$producto['descripcion']}</td>
                    <td style="padding: 2px; text-align: center;">{$producto['cantidad']}</td>
                    <td style="padding: 1px; text-align: center"> {$producto['precio_unitario']}</td>
                    <td style="padding: 2px; text-align: center;">$ {$producto['total']}</td>
                </tr>
HTML;
        }
        $html .= <<<HTML
            </tbody>
        </table>
HTML;
        $pdf->writeHTML($html, false, false, false, false, '');
    }

    private function agregarTotales($pdf, $totales) {
        $html = <<<HTML
        <table style="font-size: 10px; width: 100%; margin-top: 10px; text-align: right;">
            <tr>
                <td style="padding: 5px;">Subtotal:</td>
                <td style="padding: 5px;">$ {$totales['neto']}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">Impuesto:</td>
                <td style="padding: 5px;">$ {$totales['impuesto']}</td>
            </tr>
            <tr style="background-color: #f7f7f7; font-weight: bold;">
                <td style="padding: 5px;">Total:</td>
                <td style="padding: 5px;">$ {$totales['total']}</td>
            </tr>
        </table>
HTML;
        $pdf->writeHTML($html, false, false, false, false, '');
    }

    private function agregarCodigoQR($pdf, $venta, $cliente, $totales) {
        $qrData = "Factura: {$venta['codigo']}\nCliente: {$cliente['nombre']}\nTotal: {$totales['total']}";
        $pdf->write2DBarcode($qrData, 'QRCODE,H', 32, 110, 15, 15, [], 'N');
    }

    private function agregarNotaFinal($pdf) {
        $html = <<<HTML
        <div style="text-align: center; font-size: 8px; margin-top: 10px;">
            <small>Techeande</small><br>
            <strong>Firma Digital Autorizada</strong><br>
            <em>¡Gracias por su compra!</em><br>
            Esta factura es válida como comprobante fiscal.
        </div>
HTML;
        $pdf->writeHTML($html, false, false, false, false, '');
    }
}

// Ejecutar la clase
$factura = new ImprimirFacturaTicket();
$factura->codigo = $_GET["codigo"];
$factura->generarFactura();
