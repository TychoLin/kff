<?php
require_once("common.inc.php");

function verify_checkmacvalue($post_data) {
	$checkmacvalue = $post_data["CheckMacValue"];
	unset($post_data["CheckMacValue"]);
	ksort($post_data);
	$query = "HashKey=5294y06JbISpM5x9&".urldecode(http_build_query($post_data))."&HashIV=v77hoKGq4kWxNNIS";
	
	return (strtoupper(md5(strtolower(urlencode($query)))) == $checkmacvalue);
}

$filtered_fields = array(
	"MerchantID",
	"MerchantTradeNo",
	"RtnCode",
	"RtnMsg",
	"TradeNo",
	"TradeAmt",
	"PaymentDate",
	"PaymentType",
	"PaymentTypeChargeFee",
	"TradeDate",
	"SimulatePaid",
	"CheckMacValue",
);
$post_data = array_intersect_key($_POST, array_fill_keys($filtered_fields, null));

if (count($post_data) == count($filtered_fields) && verify_checkmacvalue($post_data)) {
	try {
		$order = new Order();
		$order->dbHandler->beginTransaction();
		$order_info = $order->getOrderNoOrder($post_data["MerchantTradeNo"]);

		if (is_null($order_info)) {
			throw new Exception("illegal order ID");
		}

		$mwsn = new MovieWatchSN();
		$sn_type = 2;
		$sn_watch_code = $mwsn->generateNewSN($sn_type);
		$sn_id = $mwsn->createNewSN($sn_watch_code, $sn_type);
		$order->makeOrderPaid($order_info["order_id"], $sn_id);

		$trade = new Trade();
		$params = array(
			"order_id" => $order_info["order_id"],
			"trade_provider" => 1,
			"trade_no" => $post_data["TradeNo"],
			"trade_status" => $post_data["RtnCode"],
			"trade_msg" => $post_data["RtnMsg"],
			"trade_amount" => $post_data["TradeAmt"],
			"payment_type" => $post_data["PaymentType"],
			"payment_charge_fee" => $post_data["PaymentTypeChargeFee"],
			"payment_time" => str_replace("/", "-", $post_data["PaymentDate"]),
			"simulate_paid" => $post_data["SimulatePaid"],
		);
		$trade->createTrade($params);

		$order->dbHandler->commit();

		// mail kff sn to user email
		$to = $order_info["member_account"];
		$subject = "KFF SN";
		$message = "SN: $sn_watch_code";
		$header = "From: service@dcview.com\r\n";
		mail($to, $subject, $message, $header);

		// allpay response message
		echo "1|OK";
	} catch (Exception $e) {
		$order->dbHandler->rollBack();
		echo $e-getMessage();
	}
}
?>