<?php
// require_once("common.inc.php");
// require_once("../PHPExcel_1.8.0/Classes/PHPExcel/IOFactory.php");

// $tssn = new TblSpecialSN();
// $data = $tssn->getAllSpecialSN();

// $objPHPExcel = new PHPExcel();
// $objPHPExcel->setActiveSheetIndex(0);

// foreach ($data as $key => $value) {
// 	$objPHPExcel->getActiveSheet()->setCellValue("A".($key + 1), $value["special_sn"]);
// }

// $objPHPExcel->setActiveSheetIndex(0);

// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
// $objWriter->save("test.xlsx");

// $post_url = "192.168.2.83:9999/kff/app_api_sn.php";
$post_url = "www.dcview.com/kff/app/app_api_sn.php";
$data = array(
	"apikey" => "9dcba708e91abe2f1ef6b087a2c57fac",
	"method_name" => "report",
	"account" => "user3",
	"sn" => "CVJVCNJP",
	"type" => 3,
	"provider" => 1,
);

// $post_url .= "?".http_build_query($data);

// $post_url = "www.dcview.com/kff/allpay_aio_create_order.php";
// $data = array(
// 	"account" => "tycho.lin@gmail.com",
// 	"payment_type" => "Credit",
// );

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $post_url);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);

if (curl_errno($ch)) {
	echo 'Curl error: ' . curl_error($ch);
} else {
	var_dump(json_decode($output, true));
	// echo $output;
}

curl_close($ch);
?>