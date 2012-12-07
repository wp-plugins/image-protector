<?php
require_once("get_common.php");
/*
 * 画像出力
 */


session_start();
//error("gd");
//exit;

// get系共通機能クラス
$obj_get_common = new Get_Common("error");

/*
 * セッションチェック
 */
//if (!isset($_SESSION["image-protector"])) {
//	error("session");
//}
$obj_get_common->check_session();

/*
 * リファラーチェック
 */
//if ($_SESSION["image-protector"]["referer"]["do"]) {
//	if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== "" && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
		// OK
//	} else {
//		error("referer");
//	}
//}
$obj_get_common->check_referer();

/*
 * ユーザーエージェントチェック
 */
//if ($_SESSION["image-protector"]["user_agent"]["do"]) {
//	$str_user_agents = trim($_SESSION["image-protector"]["user_agent"]["deny"]);
//	$lst_user_agents = preg_split("/[\r\n]+/", $str_user_agents);
//	foreach ($lst_user_agents as $deny_user_agent) {
//		if (strpos($_SERVER['HTTP_USER_AGENT'], $deny_user_agent) !== false) {
//			error("user_agent");
//		}
//	}
//}
$obj_get_common->check_user_agent();

/*
 * タイムアウトチェック
 */
//if ($_SESSION["image-protector"]["time_out"]["do"]) {
//	if ($_SESSION["image-protector"]["time_out"]["start"] + $_SESSION["image-protector"]["time_out"]["limit"] < time()) {
//		error("time_out");
//	}
//}
$obj_get_common->check_time_out();

/*
 * キーチェック
 */
//if ($_SESSION["image-protector"]["key"]["do"]) {
//	if (!isset($_GET["key"]) || !isset($_SESSION["image-protector"]["key"]["publish"][$_GET["key"]])) {
//		/* NG */
//		error("key");
//	} else {
//		/* OK */
//		// キーを無効化
//		unset($_SESSION["image-protector"]["key"]["publish"][$_GET["key"]]);
//	}
//}
$obj_get_common->check_key();

// 画像の相対パスの取得
//$img_path = '../../uploads/' . preg_replace('/.*\/wp-content\/uploads\/(.*)$/', '$1', $_SERVER['REQUEST_URI']);
//$img_path_soutai = '../../' . preg_replace('/.*\/wp-content\/(.*)$/', '$1', $_SERVER['REQUEST_URI']);
$lst_parse_url = parse_url($_SERVER['REQUEST_URI']);
$img_path_soutai = $lst_parse_url["path"];
$img_path_soutai = '../../' . preg_replace('/.*\/wp-content\/(.*)$/', '$1', $img_path_soutai);

// 画像パス
$img_path = $img_path_soutai;
//echo $img_path;
//echo "<br>";

// 拡張子(MINE TYPE)
//$str_ext = mime_content_type($img_path);// ←マルチサイトでなぜかエラー
$str_ext = strtolower(pathinfo($img_path, PATHINFO_EXTENSION));
if ($str_ext === "jpg")
	$str_ext = "jpeg";

if (!in_array($str_ext, array("png", "jpeg", "gif", "bmp"))) {
	/* NG */
	error("extension");
}

// 画像存在チェック
if (!file_exists($img_path) || is_dir($img_path)) {
	/* NG */
	error("exists");
}

/*
 * 画像分割
 */
if (
	$str_ext !== "gif"	// アニメーションgifとかあるのでgifは非対応
	&& $str_ext !== "bmp"	// 標準ではGDが読み込みに対応していない
	&& strpos($img_path, "/" . $_SESSION["image-protector"]["division"]["prefix"]) !== false
	&& $_SESSION["image-protector"]["division"]["do"]
) {
	/* 分割画像の場合 */
	// GET値チェック
	if (!isset($_GET["col"]) || !isset($_GET["row"])) {
		/* NG */
		//error("GET情報不正", "invalid GET", array(0x00, 0x00, 0xCC));
		$_GET["col"] = 1;
		$_GET["row"] = 1;
	}
	// 画像名の先頭に_は付けられないみたい
	$i_x = $_GET["col"];
	$i_xs = $_SESSION["image-protector"]["division"]["cols"];
	$i_y = $_GET["row"];
	$i_ys = $_SESSION["image-protector"]["division"]["rows"];
	if (!is_numeric($i_x) || !is_numeric($i_y) || $i_x <= 0 || $i_y <= 0 || $i_x > $i_xs || $i_y > $i_ys) {
		/* NG */
		error("division");
	}
	// GD拡張モジュールチェック
	if(!extension_loaded('gd')) {
		error("gd");
	}
	// 画像読み込み
	if ($str_ext === "png") {
		$image_base = ImageCreateFromPNG($img_path);
	} elseif ($str_ext === "jpeg") {
		$image_base = ImageCreateFromJPEG($img_path);
	}// else {
		/* NG */
	//	error("extension2");
	//}
	// 画像サイズ
	list($i_width, $i_height, $type, $attr) = getimagesize($img_path);
	// 透明保護
	//imagecolortransparent($image_base);
	//imageAlphaBlending($image_base, true);
	//imageSaveAlpha($image_base, true);
	
	// 分割画像サイズ（小数点切り捨てによる誤差を防ぐため少々めんどくさい計算式にしている）
	//$i_width_divided = floor($i_width / $i_xs);
	//$i_height_divided = floor($i_height / $i_ys);
	$i_width_divided = floor(($i_x) * $i_width / $i_xs) - floor(($i_x - 1) * $i_width / $i_xs);
	$i_height_divided = floor(($i_y) * $i_height / $i_ys) - floor(($i_y - 1) * $i_height / $i_ys);

	// 生成画像イメージ
	$image_new = imagecreatetruecolor($i_width_divided, $i_height_divided);
	// 透過の準備
	imagealphablending($image_new, false);  // アルファブレンディングをoffにする
	imagesavealpha($image_new, true);       // 完全なアルファチャネル情報を保存するフラグをonにする
	// コピー
	imagecopyresampled(
		$image_new,		// コピー先
		$image_base,	// コピー元
		0,				// コピー先のX座標
		0,				// コピー先のY座標
		($i_x - 1) * $i_width / $i_xs,				// コピー元のX座標
		($i_y - 1) * $i_height / $i_ys,				// コピー元のY座標
		$i_width_divided,				// コピー先の幅
		$i_height_divided,				// コピー先の高さ
		$i_width_divided,				// コピー元の幅
		$i_height_divided				// コピー元の高さ
	);
	// 加工して出力
	//header('Content-type: image/' . $str_ext);
	image_header($img_path, $str_ext);
	if ($str_ext === "png") {
		ImagePNG($image_new);
	} elseif ($str_ext === "jpeg") {
		ImageJPEG($image_new);
	}
	// 破棄
	ImageDestroy($image_new);
} else {
	// そのまま出力
	//header('Content-type: image/' . $str_ext);
	image_header($img_path, $str_ext);
	readfile($img_path);
}

/*
 * ヘッダ出力
 */
function image_header($img_path, $str_ext) {
	header('Content-type: image/' . $str_ext);
	$last_modified = gmdate('D, d M Y H:i:s', filemtime($img_path));
	$etag = '"' . md5( $last_modified ) . '"';
	header( "Last-Modified: $last_modified GMT" );
	header( 'ETag: ' . $etag );
	header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );
}

//echo $_SERVER['HTTP_REFERER'];
//exit;
/*
 * エラーメッセージの出力
 */
function error($str_error) {
	$ipe = $_SESSION["image-protector"]["error"];
	
	$str_en = $str_error;
	$str_jp = $ipe["error"][$str_error]["jp"];
	$lst_color = $ipe["error"][$str_error]["color"];
	
	//debug($_SERVER['REQUEST_URI']);
	//debug($img_path);
	//debug($_GET);
	
	// firebugでネットワークエラーと出てしまうのでやめとく
	//header('HTTP/1.0 403 Forbidden', FALSE);
	
	switch ($ipe["selected"]) {
		case $ipe["message_types"][0]["input_value"]:
			/* Loading... */
			header('Content-type: image/gif');
			readfile("images/loading.gif");
			break;
		case $ipe["message_types"][2]["input_value"]:
			/* 日本語メッセージ */
			header('Content-type: text/plain');
			echo $str_jp;
			break;
		case $ipe["message_types"][3]["input_value"]:
			/* 英語メッセージ */
			header('Content-type: text/plain');
			echo $str_en;
			break;
		default:
		//case $ipe["message_types"][1]["input_value"]:
			/* カラーメッセージ */
			$img = ImageCreate(100, 100);
			$black = ImageColorAllocate($img, $lst_color["r"], $lst_color["g"], $lst_color["b"]);
			ImageFilledRectangle($img, 0,0, 100,100, $black);
			header('Content-Type: image/png');
			ImagePNG($img);
			break;
//		default:
//			echo "iは0,1,2に等しくない";
	}
	exit;
}


