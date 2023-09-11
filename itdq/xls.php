<?php
namespace itdq;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

trait xls{

    static function writeResultSetToXls( $resultSet,Spreadsheet $spreadsheet,$withColumnHeadings=true,$columnIndex=1,$rowIndex=1){

        $rowsWritten = false;
        $headerRow=true;
        $columnCounter = $columnIndex;
        $rowCounter = $rowIndex;

        while (($rawRow = sqlsrv_fetch_array($resultSet))==true) {
            $rowsWritten = true;
            $row = array_map('trim', $rawRow);
            $row = static::preProcessRowForWriteToXls($row);
            if($headerRow && $withColumnHeadings){
                foreach ($row as $columnName => $value){
                    $columnHeading = ucwords(str_replace("_", " ", $columnName));
                    $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($columnCounter++, $rowCounter, $columnHeading);
                }
                $headerRow = false;
                $rowCounter++;
                $columnCounter=$columnIndex;
            }
            foreach ($row as $columnName => $value){
                $strValue = " " . $value . " ";
                $spreadsheet->getActiveSheet()->setCellValueByColumnAndRow($columnCounter++, $rowCounter, $strValue);
            }
            $rowCounter++;
            $columnCounter=$columnIndex;
        }
        return $rowsWritten;
    }
    
    static function preProcessRowForWriteToXls($row){
        return $row;
    }

    static function autoSizeColumns(Spreadsheet $spreadsheet){
        $sheet = $spreadsheet->getActiveSheet();
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
        }
    }

    static function autoFilter(Spreadsheet $spreadsheet){
        $spreadsheet->getActiveSheet()->setAutoFilter(
            $spreadsheet->getActiveSheet()
            ->calculateWorksheetDimension()
            );

    }



    static function setRowColor(Spreadsheet $spreadsheet,$color='80333333',$rowNumber=1){
        $sheet = $spreadsheet->getActiveSheet();
        $cellIterator = $sheet->getRowIterator($rowNumber)->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        foreach ($cellIterator as $cell) {
           $cell
            ->getStyle()
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB($color);




        }

    }




}