<?php
require_once("get_common.php");
/*
 * キー発行
 *   エラー時は空文字で返し、全体としての「キー不正」エラーとさせている
 */


session_start();

$req = $_POST;
//$req = $_GET;

// get系共通機能クラス
$obj_get_common = new Get_Common("error");

/*
 * セッションチェック
 */
$obj_get_common->check_session();

/*
 * リファラーチェック
 */
$obj_get_common->check_referer();

/*
 * ユーザーエージェントチェック
 */
$obj_get_common->check_user_agent();

/*
 * タイムアウトチェック
 */
$obj_get_common->check_time_out();

/*
 * Ajaxかどうか
 */
$obj_get_common->check_ajax();

/*
 * ブラウザ機能チェック
 *   チェックすべきかどうかの制御は呼び出し側で行っているのでここはこれでいい
 */
if ($req["browser_info"] === 'false') {
	error("browser_info");
}

$keys = "";
// 発行数分ループ
for ($i = 0; $i < $req["count"]; $i++) {
	// キーを発行
	do{
		$key = md5(rand() . time());
	} while (array_key_exists($key, $_SESSION["image-protector"]["key"]["publish"]));
	// 保持
	$_SESSION["image-protector"]["key"]["publish"][$key] = true;
	// 交付用
	$keys .= $req["split"] . $key;
}

// 交付
//echo substr($keys, 1);
echo "true" . $keys;

/*
 * エラーメッセージの出力
 */
function error($str_error) {
}
