<?php
require_once("common.inc.php");
require_once("PHPExcel_1.8.0/Classes/PHPExcel.php");

$mwsn = new MovieWatchSN();
$data = $mwsn->getActivatedSNReport();

$objPHPExcel = new PHPExcel();
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()
			->setCellValue("A1", "member account")
			->setCellValue("B1", "sn")
			->setCellValue("C1", "activate time");

$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);

$row_index = 2;
foreach ($data as $row_data) {
	foreach ($row_data as $key => $value) {
		$col_name = chr(ord("A") + $key).$row_index;
		$objPHPExcel->getActiveSheet()->setCellValue($col_name, $value);
	}

	$row_index++;
}

$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="sn_stat.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit();
?>