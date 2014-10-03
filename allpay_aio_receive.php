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
		$subject = "<2014高雄電影節>付款成功通知(*系統自動發送,請勿直接回覆!)";
		$message =
			"親愛的雄影之友,您好:<br>".
			"<br>".
			"感謝您購買《2014雄影雲端戲院》APP!<br>".
			"您的使用序號: $sn_watch_code<br>".
			"<br>".
			"此序號必須先下載《2014雄影雲端戲院》APP!,請至《<a href=\"http://www.kff.tw/app/\">2014雄影雲端戲院</a>》選<br>".
			"擇您手機對應的版本進行下載,開啟APP→至【主選單--輸入序號】→填寫帳號<br>".
			"/密碼登入→輸入序號,即可在2014/10/24-11/9期間內,於APP內線上觀看精選<br>".
			"百部影片。<br>".
			"<br>".
			"※此封信件為系統發出的信件,請勿直接回覆!若您仍有問題,詳情流程請參閱<br>".
			"2014高雄電影節《<a href=\"http://www.kff.tw/app/\">2014雄影雲端戲院</a>》專屬網頁,或洽請撥打客服專線0974-191-<br>".
			"501(09:00~21:00),謝謝!<br>".
			"<br>".
			"2014高雄電影節 敬上<br>";
		$header =
			"MIME-Version: 1.0\r\n".
			"Content-type: text/html; charset=utf-8\r\n".
			"From: kfa@kfa.gov.tw\r\n";
		mail($to, $subject, $message, $header);

		// allpay response message
		echo "1|OK";
	} catch (Exception $e) {
		$order->dbHandler->rollBack();
		echo $e-getMessage();
	}
}
?>