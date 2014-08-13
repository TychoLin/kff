<?php
require_once("common.inc.php");

if (!isset($_POST["account"], $_POST["payment_type"]) || !in_array($_POST["payment_type"], array("Credit", "WebATM"))) {
	exit();
}

try {
	$post_url = "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut";
	
	$order = new Order();
	$order_id = $order->createOrder($_POST["account"]);
	$order_info = $order->getOrder($order_id);

	$data = array(
		"MerchantID" => "2000132",
		"MerchantTradeNo" => $order_info["order_no"],
		"MerchantTradeDate" => date("Y/m/d H:i:s"),
		"PaymentType" => "aio",
		"TotalAmount" => "180",
		"TradeDesc" => "高雄電影節",
		"ItemName" => "電影節期間手機無限觀看",
		"ReturnURL" => "http://www.dcview.com/kff/allpay_aio_receive.php",
		"ClientBackURL" => "http://www.dcview.com/kff",
		"ChoosePayment" => $_POST["payment_type"],
	);

	ksort($data);
	$query = "HashKey=5294y06JbISpM5x9&".urldecode(http_build_query($data))."&HashIV=v77hoKGq4kWxNNIS";
	$data["CheckMacValue"] = md5(strtolower(urlencode($query)));
} catch (Exception $e) {
	echo $e-getMessage();
	exit();
}
?>
<!DOCTYPE html>
<html>
<head>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
</head>
<body>
<form action="<?php echo $post_url; ?>" method="post" style="display: none;">
	<?php foreach ($data as $key => $value) { ?>
	<input type="text" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
	<?php } ?>
	<input type="submit" value="submit">
</form>
<script type="text/javascript">
$("form").submit();
</script>
</body>
</html>