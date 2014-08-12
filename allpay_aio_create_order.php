<?php
function get_trade_no() {
	$post_url = "http://www.dcview.com/kff/app/app_api_sn.php";
	$data = array(
		"apikey" => "9dcba708e91abe2f1ef6b087a2c57fac",
		"method_name" => "create",
		"account" => "user1",
		"type" => 2,
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $post_url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);

	if (curl_errno($ch)) {
		echo "Curl error: ".curl_error($ch);
	} else {
		$output = json_decode($output, true);
	}

	curl_close($ch);

	return $output["sn_id"].$output["sn_watch_code"];
}

$post_url = "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut";

$data = array(
	"MerchantID" => "2000132",
	"MerchantTradeNo" => get_trade_no(),
	"MerchantTradeDate" => date("Y/m/d H:i:s"),
	"PaymentType" => "aio",
	"TotalAmount" => "180",
	"TradeDesc" => "高雄電影節",
	"ItemName" => "電影節期間手機無限觀看",
	"ReturnURL" => "",
	"ClientBackURL" => "",
	"ChoosePayment" => "Credit",
);

ksort($data);
$query = "HashKey=5294y06JbISpM5x9&".urldecode(http_build_query($data))."&HashIV=v77hoKGq4kWxNNIS";
$data["CheckMacValue"] = md5(strtolower(urlencode($query)));
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