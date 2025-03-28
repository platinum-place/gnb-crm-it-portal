<?php

namespace App\Libraries\Reporte;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteVida
{
    public function generar_reporte($desde, $hasta)
    {
        $libreria = new \App\Libraries\Cotizaciones();

        $emisiones = $libreria->emisiones("(Plan:equals:Vida)");

        if (empty($emisiones)) {
            return null;
        }

        // iniciar las librerias de la api para generar excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add a drawing to the worksheet
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');
        $drawing->setPath(FCPATH . 'img/nobe.png');
        $drawing->setCoordinates('A1');
        $drawing->setHeight(200);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        // celdas en negrita
        $sheet->getStyle('D1')
            ->getFont()
            ->setBold(true)
            ->setName('Arial')
            ->setSize(14);
        $sheet->getStyle('D2')
            ->getFont()
            ->setBold(true)
            ->setName('Arial')
            ->setSize(12);
        $sheet->getStyle('D4')
            ->getFont()
            ->setBold(true);
        $sheet->getStyle('D5')
            ->getFont()
            ->setBold(true);
        $sheet->getStyle('D6')
            ->getFont()
            ->setBold(true);
        $sheet->getStyle('D7')
            ->getFont()
            ->setBold(true);

        // titulos del reporte
        $sheet->setCellValue('D1', session("cuenta"));
        $sheet->setCellValue('D2', 'EMISIONES PLAN VIDA');
        $sheet->setCellValue('D4', 'Generado por:');
        $sheet->setCellValue('E4', session("usuario"));
        $sheet->setCellValue('D5', 'Desde:');
        $sheet->setCellValue('E5', $desde);
        $sheet->setCellValue('D6', 'Hasta:');
        $sheet->setCellValue('E6', $hasta);

        // elegir el contenido del encabezado de la tabla
        $sheet->setCellValue('A12', 'Num');
        $sheet->setCellValue('B12', 'Referidor');
        $sheet->setCellValue('C12', 'Plan');
        $sheet->setCellValue('D12', 'Aseguradora');
        $sheet->setCellValue('E12', 'Suma asegurada');
        $sheet->setCellValue('F12', 'Prima');
        $sheet->setCellValue('G12', 'Cliente');
        $sheet->setCellValue('H12', 'RNC/Cédula');
        $sheet->setCellValue('I12', 'Tel. Residencia');
        $sheet->setCellValue('J12', 'Fecha de nacimiento');
        $sheet->setCellValue('K12', 'Dirección');
        $sheet->setCellValue('L12', 'Plazos');
        $sheet->setCellValue('M12', 'Codeudor');
        $sheet->setCellValue('N12', 'Desde');
        $sheet->setCellValue('O12', 'Hasta');

        // cambiar el color de fondo de un rango de celdas
        $spreadsheet->getActiveSheet()
            ->getStyle('A12:O12')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('004F97');

        // cambiar el color de fuente de un rango de celdas
        $spreadsheet->getActiveSheet()
            ->getStyle('A12:O12')
            ->getFont()
            ->getColor()
            ->setARGB("FFFFFF");

        // inicializar contadores
        $cont = 1;
        $pos = 13;

        // inicializar contadores
        $cont = 1;
        $pos = 13;

        foreach ($emisiones as $emision) {
            if (
                date("Y-m-d", strtotime($emision->getFieldValue('Vigencia_desde'))) >= $desde
                and
                date("Y-m-d", strtotime($emision->getFieldValue('Vigencia_desde'))) <= $hasta
            ) {
                // valores de la tabla
                $sheet->setCellValue('A' . $pos, $cont);
                $sheet->setCellValue('B' . $pos, ($emision->getFieldValue('Contact_Name')) ? $emision->getFieldValue('Contact_Name')->getLookupLabel() : '');
                $sheet->setCellValue('C' . $pos, $emision->getFieldValue('Plan'));
                $sheet->setCellValue('D' . $pos, $emision->getFieldValue('Coberturas')
                    ->getLookupLabel());
                $sheet->setCellValue('E' . $pos, $emision->getFieldValue('Suma_asegurada'));
                $sheet->setCellValue('F' . $pos, $emision->getFieldValue('Prima'));

                // valores relacionados al cliente
                $sheet->setCellValue('G' . $pos, $emision->getFieldValue("Nombre") . " " . $emision->getFieldValue("Apellido"));
                $sheet->setCellValue('H' . $pos, $emision->getFieldValue('RNC_C_dula'));
                $sheet->setCellValue('I' . $pos, $emision->getFieldValue('Tel_Residencia'));
                $sheet->setCellValue('J' . $pos, $emision->getFieldValue('Fecha_de_nacimiento'));
                $sheet->setCellValue('K' . $pos, $emision->getFieldValue('Direcci_n'));

                // relacionados al vehiculo
                $sheet->setCellValue('L' . $pos, $emision->getFieldValue('Plazo'));
                $sheet->setCellValue('M' . $pos, $emision->getFieldValue('Nombre_codeudor') . " " . $emision->getFieldValue('Apellido_codeudor'));

                $sheet->setCellValue('N' . $pos, $emision->getFieldValue('Vigencia_desde'));
                $sheet->setCellValue('O' . $pos, $emision->getFieldValue('Valid_Till'));

                // contadores
                $cont++;
                $pos++;
            }
        }

        if ($cont == 1) {
            return null;
        }

        // ajustar tamaño de las columnas
        foreach (range('A', 'O') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // ruta del excel
        $doc = WRITEPATH . 'uploads/reporte.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($doc);

        return $doc;
    }
}
