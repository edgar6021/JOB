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

class ImprimirFactura {
    public $codigo;

    public function generarFactura() {
        $venta = $this->obtenerVenta();
        $cliente = $this->obtenerCliente($venta['id_cliente']);
        $vendedor = $this->obtenerVendedor($venta['id_vendedor']);
        $productos = $this->obtenerProductos($venta['productos']);
        $detallesTotales = $this->calcularTotales($venta);

        // Crear el PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Techeande');
        $pdf->SetTitle('Factura N. ' . $venta['codigo']);
        $pdf->SetSubject('Factura de Compra');
        
        // Configuración de márgenes y diseño
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        $pdf->AddPage();

        // Secciones del PDF
        $this->agregarEncabezado($pdf, $venta['codigo']);
        $this->agregarInformacionCliente($pdf, $cliente, $vendedor, $venta['fecha']);
        $this->agregarDetalleProductos($pdf, $productos);
        $this->agregarTotales($pdf, $detallesTotales);
        $this->agregarCodigoQR($pdf, $venta, $cliente, $detallesTotales);
        $this->agregarNotaFinal($pdf);

        // Salida del PDF
        $pdf->Output('factura_' . $venta['codigo'] . '.pdf', 'I');
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
        <table style=" font-family: 'Arial', sans-serif;">
            <tr>
                <td style="width: 150px;">
                    <img src="./images/techeande.png" width="150" alt="Techeande" />
                </td>
                <td style="text-align: center; font-size: 18px;">
                    <small><h1 style="color: #4CAF50;">Techeande</h1>
                    <small style="font-size: 12px;">RNC: 71759963-9</small><br>
                    <small style="font-size: 12px;">La Guayiga Km20 Autosmallista Duarte Vieja</small>
                    <small style="font-size: 12px;">Teléfono: 829-380-8296</small><br>
                    <small style="font-size: 12px;">techeande@gmail.com</small>
                </small>
             </td>
            </tr>
        </table>
        <hr style="border: 2px solid #4CAF50; margin-top: 10px;">
        <h3 style="text-align: center; background-color: #f1f1f1; padding: 10px; font-size: 16px; color: #333;">
            FACTURA N. $numeroFactura
        </h3>
HTML;
        $pdf->writeHTML($html, false, false, false, false, '');
    }

    private function agregarInformacionCliente($pdf, $cliente, $vendedor, $fecha) {
        $fechaFormateada = date('d-m-Y', strtotime($fecha));
        $html = <<<HTML
        <table style="font-size: 12px; width: 100%; margin-top: 20px; border-collapse: collapse;">
            <tr>
                <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9;">
                    <strong>Cliente:</strong> {$cliente['nombre']}<br>
                    <strong>RNC:</strong> {$cliente['documento']}<br>
                    <strong>Dirección:</strong> {$cliente['direccion']}
                </td>
                <td style="border: 1px solid #ddd; padding: 8px; background-color: #f9f9f9;">
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
        <table style="font-size: 12px; width: 100%; margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #4CAF50; color: #fff; text-align: center;">
                    <th style="padding: 10px;">Producto</th>
                    <th style="padding: 10px;">Cantidad</th>
                    <th style="padding: 10px;">Precio Unitario</th>
                    <th style="padding: 10px;">Total</th>
                </tr>
            </thead>
            <tbody>
HTML;
        foreach ($productos as $producto) {
            $html .= <<<HTML
                <tr style="text-align: center;">
                    <td style="border: 1px solid #ddd; padding: 8px;">{$producto['descripcion']}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{$producto['cantidad']}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">$ {$producto['precio_unitario']}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">$ {$producto['total']}</td>
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
        <table style="font-size: 12px; width: 100%; margin-top: 20px; text-align: right;">
            <tr>
                <td style="padding: 8px;">Subtotal:</td>
                <td style="padding: 8px;">$ {$totales['neto']}</td>
            </tr>
            <tr>
                <td style="padding: 8px;">Impuesto:</td>
                <td style="padding: 8px;">$ {$totales['impuesto']}</td>
            </tr>
            <tr style="background-color: #f7f7f7; font-weight: bold;">
                <td style="padding: 8px;">Total:</td>
                <td style="padding: 8px;">$ {$totales['total']}</td>
            </tr>
        </table>
HTML;
        $pdf->writeHTML($html, false, false, false, false, '');
    }

    private function agregarCodigoQR($pdf, $venta, $cliente, $totales) {
        $qrData = "Factura: {$venta['codigo']}\nCliente: {$cliente['nombre']}\nTotal: {$totales['total']}";
        // Se ajusta la posición para asegurarse de que el QR esté más cerca de la nota final
        $pdf->write2DBarcode($qrData, 'QRCODE,H', 150, 200, 20, 20, [], 'N');
    }

    private function agregarNotaFinal($pdf) {
        $html = <<<HTML
        <div style="text-align: center; font-size: 10px; margin-top: 10px;">
            <small style="font-family: 'cursive'">Techeande</small><br>
            <strong>Firma Digital Autorizada</strong><br>
            <em>¡Gracias por su compra! Estamos comprometidos con su satisfacción.</em><br>
            Esta factura es válida como comprobante fiscal.
        </div>
HTML;
        // Se agrega después del QR
        $pdf->writeHTML($html, false, false, false, false, '');
    }
}

// Ejecutar la clase
$factura = new ImprimirFactura();
$factura->codigo = $_GET["codigo"];
$factura->generarFactura();
