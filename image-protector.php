<?php
/*
Plugin Name: Image Protector
Plugin URI: http://wordpress.1000sei.com/image-protector/
Description: This forbid visitor to download images.
Author: Hirofumi Ohta
Version: 1.0
Author URI: http://wordpress.1000sei.com
*/

	/*
	 * 画像保護クラス
	 */
	class Image_Protector {

		// バージョン
		private $str_varsion = "1.0";
		// 画像ディレクトリ
		private $str_image_url;
		
		/*
		 * コンストラクタ
		 */
		public function __construct() {
			// 画像ディレクトリ
			if (preg_match("/themes/", __FILE__)) {
				if (is_child_theme()) {
					// 画像ディレクトリ（子テーマでのローカルテスト用）
					//$this->str_image_url = get_stylesheet_directory_uri() . "/image-protector/images/";
					$this->str_image_url = plugins_url() . "/image-protector/images/";
				} else {
					// 画像ディレクトリ（親テーマでのローカルテスト用）
					//$this->str_image_url = get_template_directory_uri() . "/image-protector/images/";
					$this->str_image_url = plugins_url() . "/image-protector/images/";
				}
			} else {
				// 画像ディレクトリ（プラグイン用）
				$this->str_image_url = plugins_url() . "/image-protector/images/";
			}
		}
		/*
		 * toString
		 */
		public function __toString() {
		}

		/*
		 * セッションの使用準備
		 */
		public function init_sessions() {
			if (!session_id()) {
				session_start();
			}
			unset($_SESSION["image-protector"]);
			//$_SESSION["image-protector"] = $this->get_init();
			$_SESSION["image-protector"] = get_option('image-protector');
			$_SESSION["image-protector"]["time_out"]["start"] = time();
			$_SESSION["image-protector"]["key"]["publish"] = array();
		}
		/*
		 * 初期設定データ
		 */
		 private function get_init() {
			$init = array();
			/* バージョン */
			$init["varsion"] = "{$this->str_varsion}";
			/* htaccess */
			$init["htaccess"] = array(
				"do" => false,		// OnOff
				"cover" => array(	// カバー情報
					"uploads" => array(
						"display" => "アップロード",
						"cover" => true	// これはtrueで固定
					),
					"themes" => array(
						"display" => "テーマ",
						"cover" => false
					),
					"plugins" => array(
						"display" => "プラグイン",
						"cover" => false
					)
				)
			);
			/* リファラーチェック */
			$init["referer"] = array(
				"do" => true	// OnOff
			);
			/* ユーザーエージェント */
			$init["user_agent"] = array(
				"do" => true,	// OnOff
				"deny" => // 拒絶USER_AGENT（適当にめぼしいところをデフォルトで入れておいた）
					"PageDown" . PHP_EOL
					. "Website Explorer" . PHP_EOL
					. "Ninja" . PHP_EOL
					. "MSIECrawler" . PHP_EOL
					. "WWWC" . PHP_EOL
					. "DiffBrowser" . PHP_EOL
					. "HTML Get" . PHP_EOL
					. "Junshu" . PHP_EOL
					. "FLATARTS_FAVICO" . PHP_EOL
					. "Wget" . PHP_EOL
					. "WeBoX" . PHP_EOL
					. "WebAuto" . PHP_EOL
					. "Irvine" . PHP_EOL
					. "HTTrack" . PHP_EOL
					. "Getweb" . PHP_EOL
					. "Pockey-GetHTML" . PHP_EOL
					. "Arachmo" . PHP_EOL
					. "PerMan Surfer"
			);
			/* タイムアウト */
			$init["time_out"] = array(
				"do" => false,	// OnOff
				"limit" => 9,	// タイムアウト秒数（今から何秒以内に画像を読み込みに行かなければならないか）
				"start" => time()
			);
			/* キー */
			$init["key"] = array(
				"do" => true,			// OnOff
				"publish" => array()	// 発行キー
			);
			/* 分割 */
			$init["division"] = array(
				"do" => false,			// OnOff
				"prefix" => "p_",		// 分割対象画像のファイル名プリフィックス（classによる区別ではget_image.phpで対象かどうか区別がつかない）
				"cols" => 4,			// 縦分割数 （細かく分割しすぎると誤差がでる）
				"rows" => 3				// 横分割数 （細かく分割しすぎると誤差がでる）
			);
			/* ブラウザ機能チェック */
			$init["browser_function"] = array(
				"do" => true	// OnOff
			);
			/* 右クリック */
			$init["right_click"] = array(
				"do" => false	// OnOff
			);
			/* プリントスクリーン */
			$init["print_screen"] = array(
				"do" => false	// OnOff
			);
			/* 透明フィルター処理 */
			$init["transparent"] = array(
				"do" => true	// OnOff
			);
			/* 劣化処理 */
			$init["deterioration"] = array(
				"do" => true	// OnOff
			);
			/* 背景画像化 */
			$init["background"] = array(
				"do" => true	// OnOff
			);
			/* メディアリンク */
			$init["media_link"] = array(
				"do" => true	// OnOff
			);
			/* Javascript, CSS難読化 */
			$init["js_css_obfuscated"] = array(
				"do" => true	// OnOff
			);
			/* エラーメッセージタイプ */
			$init["error"] = array();
			$init["error"]["selected"] = "color_message";
			$init["error"]["message_types"] = array(
				0 => array(
					"input_value" => "now_loading",
					"display" => "NowLoading"
				),
				1 => array(
					"input_value" => "color_message",
					"display" => "カラーメッセージ"
				),
				2 => array(
					"input_value" => "japanese_message",
					"display" => "日本語メッセージ"
				),
				3 => array(
					"input_value" => "english_message",
					"display" => "英単語メッセージ"
				)
			);
			$init["error"]["error"] = array(
				"session" => array(
					"jp" => "セッション不正",
					"color" => array("r" => 0x00, "g" => 0x66, "b" => 0x00)
				),
				"referer" => array(
					"jp" => "リファラー不正",
					"color" => array("r" => 0x00, "g" => 0x00, "b" => 0x66)
				),
				"user_agent" => array(
					"jp" => "ユーザーエージェント不正",
					"color" => array("r" => 0x66, "g" => 0x66, "b" => 0x00)
				),
				"time_out" => array(
					"jp" => "タイムアウト",
					"color" => array("r" => 0x66, "g" => 0x00, "b" => 0x00)
				),
				"key" => array(
					"jp" => "キー不正",
					"color" => array("r" => 0x66, "g" => 0x00, "b" => 0x66)
				),
				"extension" => array(
					"jp" => "拡張子不正",
					"color" => array("r" => 0x00, "g" => 0x66, "b" => 0x66)
				),
				"exists" => array(
					"jp" => "データなし",
					"color" => array("r" => 0xCC, "g" => 0x00, "b" => 0xCC)
				),
				"division" => array(
					"jp" => "画像分割情報不正",
					"color" => array("r" => 0x00, "g" => 0xCC, "b" => 0x00)
				),
				"gd" => array(
					"jp" => "GDが使えない",
					"color" => array("r" => 0xCC, "g" => 0x00, "b" => 0x00)
				),
				"extension2" => array(
					"jp" => "分割未対応拡張子",
					"color" => array("r" => 0xCC, "g" => 0xCC, "b" => 0x00)
				)
			);
			//
			return $init;
		}

		/**
		 * メディア個別のページのリンクをはぎ取る
		 *   備考：wp-includes/post-template.phpの1200行目付近
		 */
		public function strip_media_link($p) {
			if ($_SESSION["image-protector"]["media_link"]["do"]) {
				return strip_tags($p, "<img>");
			} else {
				return $p;
			}
		}
		
		/*
		 * スタイルとJSの設定（共通画面）
		 */
		public function set_common_style() {
			ob_start(array($this, 'obfuscate_js_css'));
?>
<script type="text/javascript">
	// ブラウザ判別
	var _ua = (function(){
		return {
			ltIE6:typeof window.addEventListener == "undefined" && typeof document.documentElement.style.maxHeight == "undefined",
			ltIE7:typeof window.addEventListener == "undefined" && typeof document.querySelectorAll == "undefined",
			ltIE8:typeof window.addEventListener == "undefined" && typeof document.getElementsByClassName == "undefined",
			IE:document.uniqueID,
			Firefox:window.sidebar,
			Opera:window.opera,
			Webkit:!document.uniqueID && !window.opera && !window.sidebar && window.localStorage && typeof window.orientation == "undefined",
			Mobile:typeof window.orientation != "undefined"
		}
	})();
	// CSS3が使えるか
	function can_use_css3() {
		if (_ua.ltIE6 || _ua.ltIE7 || _ua.ltIE8) {
			return false;
		}
		return true;
	}
	// ブラウザ機能チェック
	function get_browser_info(){
<?php
		if ($_SESSION["image-protector"]["browser_function"]["do"]) {
?>
			if (_ua.ltIE6 || _ua.ltIE7 || _ua.ltIE8 || _ua.IE || _ua.Firefox || _ua.Opera || _ua.Webkit || _ua.Mobile) {
				return true;
			} else {
				return 'false';
			}
<?php
		} else {
?>
			return 'true';
<?php
		}
?>
	}
	// 使用済みキーの配列
	var lst_used_key = new Array();
	// 未使用キーの配列
	var lst_new_key = new Array();
	// 画像取得キーの取得
	function get_key() {
		// ステータス
		var str_status;
		// キー
		var str_key;
		var str_keys;
		// 分割文字
		var str_split = ",";
		//
		// php側でキーの重複チェックをしているのでこんなことをしなくてもいいはずなのだが、
		// なぜかこれをかませないとおかしくなることがある。
		do{
			// 取得済みキーがあるか？
			if (lst_new_key.length == 0) {
				// なければ取得
				str_keys = get_key_ajax(str_split);
				//alert(str_keys);
				str_status = str_keys.substring(0, 5);
				//alert(str_status);
				str_keys = str_keys.substring(5);
				//alert(str_keys);
				//alert(str_keys);
				if (str_status != "true,") {
					// 正常にキーを取得出来なかった場合
					return "";
				}
				lst_new_key = str_keys.split(str_split);
			}
			//alert(lst_new_key.length);
			// 1つ使用
			str_key = lst_new_key.shift();
		} while (lst_used_key.contains(str_key));
		// 使用済みキーの記録
		lst_used_key.push(str_key);
		//alert(str_key);
		return str_key;
	}
	function get_key_ajax(str_split) {
		// キー
		var str_key = "";
		//
		jQuery.ajax({
			async: false,// 同期
			url: '<?php echo plugins_url(); ?>/image-protector/get_key.php',
			type: 'POST',
			data: {
				count: '5',			// 一括取得数
				split: str_split,	// 区切り文字
				browser_info: get_browser_info()
			},
			dataType: 'text'
		})
		.done(function( data ) {
			// ...
			str_key = data;
		})
		.fail(function( data ) {
			// ...
			//alert("erroe");
		})
		.always(function( data ) {
			// ...
		});
		//
		return str_key;
	}
	//
	jQuery(function(){
		// 非imgタグの背景画像にキーを設定する
		jQuery("*").each(function(){
			// 背景画像が設定されているタグか
			if (jQuery(this).css("background-image") != "none") {
				// 編集前URL
				var str_before = jQuery(this).css("background-image");
				// 編集後URL
				var str_after;
				// キー（画像用）
				var str_key = get_key();
				//alert(str_before);
				// キーを添付
				str_after = str_before.replace(/\.(gif|png|jpe?g)\??/i, '.$1?key=' + str_key + '&');
				//alert(str_after);
				//alert(get_key());
				//str_after = str_after + '&key=' + get_key();
				//alert(str_after);
				// 設定しなおし
				jQuery(this).css("background-image", str_after);
				//
			}
		});
	});
	// 配列検索ライブラリ
	Array.prototype.contains = function(value) {
		for(var i in this) {
			if( this.hasOwnProperty(i) && this[i] === value) {
				return true;
			}
		}
		return false;
	}
</script>
<?php
			ob_end_flush();
		}
		
		/*
		 * スタイルとJSの設定（管理画面）
		 */
		public function set_admin_style() {
			ob_start(array($this, 'obfuscate_js_css'));
?>
<style type="text/css">
/* パラメータ設定エリア */
#image_protector .param_area {
	margin: 8px 0 0 0;
	padding:4px 2px 4px 16px;
	border: solid 1px #666666;
}
#image_protector .param_contents {
	float:left;
	width:85%;
}
#image_protector .submit_button {
	float:left;
	width:15px;
}
#image_protector .good {
	color:#009900;
	font-weight: 700;
}
#image_protector .bad {
	color:#990000;
	font-weight: 700;
}
#image_protector .dont_htaccess {
	background-color:#DDDDDD;
}
</style>
<script type="text/javascript">
	jQuery(function(){
		/*
		 * imgタグの画像を保護する
		 */
		jQuery("img").each(function(){
			// id
			var str_id = jQuery(this).attr('id') == null ? "" : jQuery(this).attr('id');
			// class
			var str_class = jQuery(this).attr('class') == null ? "" : jQuery(this).attr('class');
			// 画像URL
			var src = jQuery(this).attr('src');
			// キー（画像用）
			var str_key = get_key();
			str_rep = '<img class="' + str_class + '" id="' + str_id + '" src="' + src + '?key=' + str_key + '" />';
			// 置換
			jQuery(this).replaceWith(str_rep);
		});
		/*
		 * ビジュアルエディタ対策
		 *  うまくいかない
		 */
		/*
		jQuery("img", "<div>" + jQuery("textarea#content").text() + "</div>").each(function(){
			//alert(jQuery(this).attr('src'));
			//return;
			// id
			var str_id = jQuery(this).attr('id') == null ? "" : jQuery(this).attr('id');
			// class
			var str_class = jQuery(this).attr('class') == null ? "" : jQuery(this).attr('class');
			// 画像URL
			var src = jQuery(this).attr('src');
			// キー（画像用）
			var str_key = get_key();
			str_rep = '<img class="' + str_class + '" id="' + str_id + '" src="' + src + '?key=' + str_key + '" />';
			alert(str_rep);
			// 置換
			jQuery(this).replaceWith(str_rep);
		});
		*/
	});
</script>
<?php
			ob_end_flush();
		}
		
		/*
		 * スタイルとJSの設定（公開画面）
		 */
		public function set_public_style() {
			ob_start(array($this, 'obfuscate_js_css'));
?>
<style type="text/css">
/* 印刷禁止（そもそも背景画像化や被せフィルターで印刷は防がれているが念のため） */
@media print {
	span.image-protector {
		display:none!important;
	}
}
/* 保護画像　全体 */
span.image-protector {
	display:inline-block;
	position:relative;
	line-height: 1;
	font-size: 0%;
}
/* カバー */
span.image-protector .cover{
	/*display:inline-block;*/
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:100%;
	/* キーが必要なためここでは背景画像を指定しない */
	/*background-image:url(<?php echo $this->str_image_url ?>spacer.gif?col=1&row=1);*/
}
/* 劣化 */
span.image-protector .deterioration{
	/*display:inline-block;*/
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:100%;
	/*background-color: rgba(107, 74, 43, 0.35);*/
	background-color: rgb(107, 74, 43);
     filter: alpha(opacity=10);
    -moz-opacity:0.10;
    opacity:0.10;
}
span.image-protector .deterioration2{
	/*display:inline-block;*/
	position:absolute;
	top:0;
	left:0;
	width:100%;
	height:100%;
	box-shadow: inset 0 0 5px #fff, inset 0 0 5px #fff, inset 0 0 5px #fff, inset 0 0 5px #fff;
}
/* 画像 */
span.image-protector img {
	display:inline-block !important;
	border-style: none !important;
	padding:0px !important;
	margin:0px !important;
	max-width:100% !important;
	max-height:100% !important;
}
</style>
<script type="text/javascript">
	/*
	 * 画像読み込み後の調整
	 */
	function after_loaded(obj) {
		// 再帰読み込みを回避（念のため）
		//this.onload = "";
		/*
		// CSSでサイズを設定
		//alert(obj.style.width);
		if (obj.style.width == '') {
		//alert(1);
			obj.style.width = obj.width + "px";
		//alert(2);
		}else {
			// なぜかこうしないとダメ
			obj.style.width = obj.style.width;
		}
		if (obj.style.height == '') {
			obj.style.height = obj.height + "px";
		} else {
			// なぜかこうしないとダメ
			obj.style.heigh = obj.style.heigh;
		}
		// CSSの適用を外すならば画像を表示させない
		obj.width = 0;
		obj.height = 0;
		//
		//obj.src="";
		*/
		if (obj.style.width != '') {
			// なぜかこうしないとダメ
			obj.style.width = obj.style.width;
			// CSSの適用を外すならば画像を表示させない
			obj.width = 0;
		}
		if (obj.style.height != '') {
			// なぜかこうしないとダメ
			obj.style.height = obj.style.height;
			// CSSの適用を外すならば画像を表示させない
			obj.height = 0;
		}
	}

	jQuery(function(){
		/*
		 * imgタグの画像を保護する
		 */
		jQuery("img").each(function(){
			// 画像URL
			var src = jQuery(this).attr('src');
			//alert(src);

			/*
			 * 保護対象かどうかの判断
			 */
			var lst_cover_dir = new Array(
			<?php
				$str_cover_dir = "";
				foreach ($_SESSION["image-protector"]["htaccess"]["cover"] as $k => $v) {
					if ($v["cover"]) {
						$str_cover_dir .= ", '$k'";
					}
				}
				echo substr($str_cover_dir, 1);
			?>
			);
			var is_covered = false;
			for (var i = 0; i < lst_cover_dir.length; i++) {
				//alert(lst_cover_dir[i]);
				// 対象ディレクトリかどうか
				re = new RegExp("wp-content/" + lst_cover_dir[i], "i");
				if (src.match(re)) {
					//alert(lst_cover_dir[i]);
					is_covered = true;
					break;
				}
			}
			if (!is_covered) {
				// 保護しない
				return;
			}
			//alert(lst_cover_dir[i]);
			
			
			//jQuery(this).after("<img src='<?php echo $this->str_image_url ?>spacer.gif'>");
			// id
			var str_id = jQuery(this).attr('id') == null ? "" : jQuery(this).attr('id');
			var str_id_full = str_id == "" ? "" : ' id="' + str_id + '" ';
			// class
			var str_class = jQuery(this).attr('class') == null ? "" : jQuery(this).attr('class');
			//alert(jQuery(this).attr('style'));
			//
			//alert(i_width);
			// サイズ指定
			var str_size = "";
			var str_size_html = "";
			//var width_per = "";
			//var height_per = "";
			var str_per = "";
			var str_size_one_html = "";
			var str_size_one = "";
			//str_size2 = 'width:100px;height:100px;';
			if (jQuery.isNumeric(jQuery(this).attr('width')) && jQuery.isNumeric(jQuery(this).attr('height'))) {
				/* HTMLで指定されている場合 */
				str_size = 'width:' + jQuery(this).attr('width') + 'px;height:' + jQuery(this).attr('height') + 'px;';
				str_size_html = ' width="' + jQuery(this).attr('width') + '" height="' + jQuery(this).attr('height') + '" ';
				str_per = 'width:<?php echo (100 / $_SESSION["image-protector"]["division"]["cols"]) ?>%;height:<?php echo (100 / $_SESSION["image-protector"]["division"]["rows"]) ?>%';
				str_size_one_html = ' width="' + jQuery(this).attr('width') / <?php echo $_SESSION["image-protector"]["division"]["cols"]; ?> + '" height="' + jQuery(this).attr('height') / <?php echo $_SESSION["image-protector"]["division"]["rows"]; ?> + '" ';
				str_size_one = ' width:' + jQuery(this).attr('width') / <?php echo $_SESSION["image-protector"]["division"]["cols"]; ?> + 'px;height:' + jQuery(this).attr('height') / <?php echo $_SESSION["image-protector"]["division"]["rows"]; ?> + 'px; ';
			} else if (
				jQuery(this).get(0).style.width != null
				&& jQuery(this).get(0).style.height !=null
				&& jQuery(this).get(0).style.width != ''
				&& jQuery(this).get(0).style.height != ''
			) {
				//alert(jQuery(this).get(0).style.width);
				/* CSSで指定されている場合 */
				str_size = 'width:' + jQuery(this).get(0).style.width + ';height:' + jQuery(this).get(0).style.height + ';';
				str_per = 'width:<?php echo (100 / $_SESSION["image-protector"]["division"]["cols"]) ?>%;height:<?php echo (100 / $_SESSION["image-protector"]["division"]["rows"]) ?>%';
				//str_size2 = 'width="100%" height="100%"';
				var str_width_tmp = jQuery(this).get(0).style.width;
				str_width_tmp.match(/([0-9]+)([^0-9]+)/);
				str_width_tmp = (RegExp.$1 / <?php echo $_SESSION["image-protector"]["division"]["cols"]; ?>) + RegExp.$2;
				var str_height_tmp = jQuery(this).get(0).style.height;
				str_height_tmp.match(/([0-9]+)([^0-9]+)/);
				str_height_tmp = (RegExp.$1 / <?php echo $_SESSION["image-protector"]["division"]["rows"]; ?>) + RegExp.$2;
				str_size_one = ' width:' + str_width_tmp + ';height:' + str_height_tmp + '; ';
			}
			//alert(str_size2);
			//alert(str_size);
			//
			if (
				!src.match(/\.gif$/i)	// アニメーションgifとかあるのでgifは非対応
				&& !src.match(/\.bmp$/i)	// 標準ではGDが読み込みに対応していない
				&& src.match(/\/<?php echo $_SESSION["image-protector"]["division"]["prefix"] ?>/i)	// 対象画像名
				&& <?php echo var_export($_SESSION["image-protector"]["division"]["do"], true) ?>	// 分割On
				&& <?php echo var_export($_SESSION["image-protector"]["htaccess"]["do"], true) ?>	// .htaccess使用モード
			) {
				/* 
				 * 分割保護 
				 */
				//alert(str_size);
				// キー（カバー用）
				var str_key_cover = get_key();
				// キー（代理画像用）
				var str_key_substitution;
				// キー（画像用）
				var str_key;
				//
				var str_rep = "";
				str_rep += '<span class="image-protector ' + str_class + '" ' + str_id_full + ' style="' + str_size + '" ' + str_size_html + '>';
				// 透明カバー
				<?php if ($_SESSION["image-protector"]["transparent"]["do"]) { ?>
					str_rep += '<span class="cover" style="background:url(<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_cover + ');"></span>';
				<?php } ?>
				// 意図的退色
				<?php if ($_SESSION["image-protector"]["deterioration"]["do"]) { ?>
					str_rep += '<span class="deterioration"></span>';
					str_rep += '<span class="deterioration2"></span>';
				<?php } ?>
				for (y = 0; y < <?php echo $_SESSION["image-protector"]["division"]["rows"] ?>; y++) {
					//str_rep += '<span>';
					for (x = 0; x < <?php echo $_SESSION["image-protector"]["division"]["cols"] ?>; x++) {
						// キー（代理画像用）
						str_key_substitution = get_key();
						// キー（画像用）
						str_key = get_key();
						// １分割画像
						//if (true || (str_size === "" && str_size_html === "") || !can_use_css3()) {
						if (<?php echo var_export(!$_SESSION["image-protector"]["background"]["do"], true) ?>) {
							/* srcのまま */
							//
							//str_rep += '<img src="' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + '" style="border-style: none;width:' + width_per + '%;height:' + height_per + '%;" />';
							// 通常は問題ないが狭い領域で表示された場合imgのように自動的に縮小してくれないかも
							//str_rep += '<img src="' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key_substitution + '" style="border-style: none;background:url(' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + ');' + str_per + '" onload="after_loaded(this);" />';
							// 単なる印
							str_rep += '<span class="img_type_1"></span>';
							// 画像本体
							//str_rep += '<img src="' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + '" ' + str_size_one_html + ' onload="after_loaded(this);" />';
							//																											↑これはCSS3非対応用
							//alert(str_size_one);
							//str_rep += '<img src="' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + '" style="' + str_size_one + '" onload="after_loaded(this);" />';
							str_rep += '<img src="' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + '" style="' + str_size_one + '" ' + str_size_one_html + '/>';
						} else {
							/* 背景画像化 */
							//
							//str_rep += '<img src="' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + '" style="border-style: none;width:' + width_per + '%;height:' + height_per + '%;" />';
							//str_rep += '<img src="<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_substitution + '" style="border-style: none;background:url(' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + ');background-size:100% 100%;width:' + width_per + '%;height:' + height_per + '%;" />';
							// 単なる印
							str_rep += '<span class="img_type_2"></span>';
							// 画像本体
							//str_rep += '<img src="<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_substitution + '" style="background:url(' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + ');background-size:100% 100%;' + str_per + '" />';
							//str_rep += '<img src="<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_substitution + '" style="background:url(' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + ');background-size:100% 100%;" ' + str_size_one_html + '/>';
							str_rep += '<img src="<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_substitution + '" style="background:url(' + src + '?col=' + (x + 1) + '&row=' + (y + 1) + '&key=' + str_key + ');background-size:100% 100%;' + str_size_one + '" ' + str_size_one_html + '/>';
						}
					}
					//str_rep += '</span>';
					str_rep += '<br />';
				}
				str_rep += '</span>';
				//alert(str_rep);
			} else {
				/* 
				 * 非分割保護 
				 */
				// キー（カバー用）
				var str_key_cover = get_key();
				// キー（代理画像用）
				var str_key_substitution = get_key();
				// キー（画像用）
				var str_key = get_key();
				//alert(str_key_cover);
				//alert(str_key);
				var str_rep = "";
				str_rep += '<span class="image-protector ' + str_class + '" ' + str_id_full + ' style="' + str_size + '" ' + str_size_html + '>';
				// 透明カバー
				<?php if ($_SESSION["image-protector"]["transparent"]["do"]) { ?>
					str_rep += '<span class="cover" style="background:url(<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_cover + ');"></span>';
				<?php } ?>
				// 意図的退色
				<?php if ($_SESSION["image-protector"]["deterioration"]["do"]) { ?>
				str_rep += '<span class="deterioration"></span>';
				str_rep += '<span class="deterioration2"></span>';
				<?php } ?>
				// 画像本体
				//if (true || (str_size === "" && str_size_html === "") || !can_use_css3()) {//alert(1);
				if (<?php echo var_export(!$_SESSION["image-protector"]["background"]["do"], true) ?>) {
					/* srcのまま */
					//str_rep += '<img src="' + src + '?key=' + str_key + '" style="border-style: none;' + str_size + '" />';
					// サイズ未指定と伸縮を同時に満たすため二重に読み込む
					//str_rep += '<img src="' + src + '?key=' + str_key_substitution + '" style="border-style: none;background:url(' + src + '?key=' + str_key + ');' + str_size + '" onload="after_loaded(this);" />';
					// 単なる印
					str_rep += '<span class="img_type_3"></span>';
					// 画像本体
					//str_rep += '<img src="' + src + '?key=' + str_key + '" style="' + str_size + '" ' + str_size_html + ' onload="after_loaded(this);" />';
					str_rep += '<img src="' + src + '?key=' + str_key + '" style="' + str_size + '" ' + str_size_html + '  />';
				} else {
					/* 背景画像化 */
					// 単なる印
					str_rep += '<span class="img_type_4"></span>';
					// 画像本体
					//str_rep += '<img src="<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_substitution + '" style="background:url(' + src + '?key=' + str_key + ');background-size:100% 100%;width:100%;height:100%;" />';
					str_rep += '<img src="<?php echo $this->str_image_url ?>spacer.gif?key=' + str_key_substitution + '" style="background:url(' + src + '?key=' + str_key + ');background-size:100% 100%;' + str_size + '" ' + str_size_html + ' />';
				}
				str_rep += '</span>';
			}
			// 置換
			//var str_class_rand = "rep_" + parseInt(Math.random()*1000000000000);
			//alert(str_class_rand);
			//jQuery(this).replaceWith("<img>" + str_rep + "</img>");
			jQuery(this).replaceWith(str_rep);
			//jQuery(this).replaceWith("<span class='" + str_class_rand + "'></span>");
			//jQuery("." + str_class_rand).append(str_rep);
		});
		// 未使用キーの破棄
		delete lst_new_key;
		
		/*
		printscreenは一部のブラウザのupでしか捕捉できない
		document.onkeyup = function () {
			alert("aaa");
		};
		function keyCode(e){
			if(document.all)
				return e.keyCode;
			else if(document.getElementById)
				return (e.keyCode)? e.keyCode: e.charCode;
			else if(document.layers)
				return e.which;
		} 
		*/
		
<?php
		if ($_SESSION["image-protector"]["right_click"]["do"]) {
?>
			/*
			 * 右クリック禁止
			 */
			jQuery(document).bind("contextmenu", function(e){
				//alert("右クリック禁止");
				return false;
			});
<?php
		}
		if ($_SESSION["image-protector"]["print_screen"]["do"]) {
?>
			/*
			 * プリントスクリーンの無効化(IE限定)
			 *   いちいち確認を取られるのでうざいと思う
			 *   メモ：IE以外はzeroclipboardを使えばいいらしい
			 */
			jQuery(function(){
				setInterval(function(){
					if (window.clipboardData) {
						window.clipboardData.clearData("Image");
					}
					return true;
				},300);
			});
<?php
		}
?>
	});
</script>
<?php
			ob_end_flush();
		}

		/*
		 * JS, CSS難読化
		 */
		private function obfuscate_js_css($str) {
			// 難読化しない場合
			if (!$_SESSION["image-protector"]["js_css_obfuscated"]["do"]) {
				return $str;
			}
			// 難読化する場合
			$lst = preg_split("/[\r\n]+/", $str);
			$str2 = "";
			foreach ($lst as $str){
				// 一行コメントを削除
				$str2 .= preg_replace("/([^\:]+?)\/\/.*/", "\\1", $str);// . PHP_EOL;
			}
			//return $str2;
 			// 複数行コメントを削除
			$str = preg_replace('/\/\*.*?\*\//s', '', $str2);
			// タブ削除
			$str = str_replace("\t", "", $str); 
			//$str = str_replace("\r", "", $str); 
			//$str = str_replace("\n", "", $str); 
			return $str;
		}


		/*
		 * 管理ページ
		 */
		//   basename(__FILE__)だとfunctions.phpがページのURLになる
		public function add_menu_page_cb() {
			// トップレベルメニュー 
			add_menu_page(
				'画像保護', // メニューが有効になった時に表示されるHTMLのページタイトル用テキスト。 
				'画像保護', // 管理画面のメニュー上での表示名。 
				'edit_themes', // このメニューページを閲覧・使用するために最低限必要なユーザーレベルまたはユーザーの種類と権限 。管理能力名（edit_themes等）で指定。
				__FILE__, // メニューページのコンテンツを表示するPHPファイル。とマニュアルにあるが実態は単なるslug。 
				array($this, 'add_menu_page_html') // メニューページにコンテンツを表示する関数。
			);
		}
		public function add_menu_page_html() {
?>
<div class="wrap">
	<?php screen_icon('upload'); ?>
	<h2>画像保護 image-protector ver.<?php echo $this->str_varsion ?></h2>
	<h3>マニュアル</h3>
	<div>
		<a href="http://wordpress.1000sei.com/image-protector/">http://wordpress.1000sei.com/image-protector/</a>
	</div>
<?php
			if (isset($_POST['image-protector'])) {
				/* 送信した場合 */
				/* 不正送信防止（wp_nonce_fieldとセット） */
				check_admin_referer('my_param_action', 'my_param_nonce');
				//
				switch ($_POST['image-protector']) {
					case 'init':
						/* 初期化 */
						// .htaccessの削除
						//$this->delete_htaccess();
						// 初期化
						//$_SESSION["image-protector"] = $this->get_init();
						// 保存
						//update_option('image-protector', $_SESSION["image-protector"]);
						//
						$this->do_init();
						break;
					case 'htaccess':
						/* .htaccessで制限 */
						// OnOff
						$_SESSION["image-protector"]["htaccess"]["do"] = ($_POST['do_check'] === 'on');
						// apache_get_modulesがsakuraで定義されていない
						/*
						if(!in_array('mod_rewrite', apache_get_modules())) {// Apacheモジュール(rewrite)チェック
							$_SESSION["image-protector"]["htaccess"]["do"] = false;
						} else {
							$_SESSION["image-protector"]["htaccess"]["do"] = ($_POST['do_check'] === 'on');
						}
						*/
						// マルチサイトでは機能させない
						if ( is_multisite() ) {
							$_SESSION["image-protector"]["htaccess"]["do"] = false;
						} else {
							$_SESSION["image-protector"]["htaccess"]["do"] = ($_POST['do_check'] === 'on');
						}
						// .htaccessの処理
						if ($_POST['do_check'] === 'on') {
							/* ON */
							// .htaccessの削除
							//$this->delete_htaccess();
							// .htaccessがなければ生成
							if (!file_exists("../wp-content/.htaccess")) {
								// ワードプレス設置ディレクトリ
								$str_wp_base_dir = str_replace("/wp-admin/admin.php", "", $_SERVER['PHP_SELF']);
								// .htaccessの構築
								$str_htaccess = "";
								$str_htaccess .= "<IfModule mod_rewrite.c>" . PHP_EOL;
								$str_htaccess .= "RewriteEngine On" . PHP_EOL;
								if ($_SESSION["image-protector"]["htaccess"]["cover"]["themes"]["cover"]) {
									$str_htaccess .= "RewriteCond %{REQUEST_URI} /themes/(.*)$ [OR]" . PHP_EOL;
								}
								if ($_SESSION["image-protector"]["htaccess"]["cover"]["plugins"]["cover"]) {
									$str_htaccess .= "RewriteCond %{REQUEST_URI} /plugins/(.*)$ [OR]" . PHP_EOL;
								}
								$str_htaccess .= "RewriteCond %{REQUEST_URI} /uploads/(.*)$" . PHP_EOL;
								$str_htaccess .= "RewriteRule ^(.*)\.(png|jpe?g|gif|bmp) {$str_wp_base_dir}/wp-content/plugins/image-protector/get_image.php [L]" . PHP_EOL;
								$str_htaccess .= "</IfModule>" . PHP_EOL;
								//echo "<pre>";
								//echo $str_htaccess;
								//echo "</pre>";
								if (!$fp = fopen("../wp-content/.htaccess", "w")) {
									echo "書き込み失敗";
								}
								fwrite($fp, $str_htaccess);
								fclose($fp);
							}
						} else {
							/* OFF */
							// .htaccessの削除
							$this->delete_htaccess();
						}
						// 制限範囲
						foreach ($_SESSION["image-protector"]["htaccess"]["cover"] as $k => $v) {
							if (in_array($k, $_POST['cover'])) {
								$_SESSION["image-protector"]["htaccess"]["cover"][$k]["cover"] = true;
							} else {
								$_SESSION["image-protector"]["htaccess"]["cover"][$k]["cover"] = false;
							}
						}
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'referer':
						/* リファラーチェック */
						// OnOff
						$_SESSION["image-protector"]["referer"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'user_agent':
						/* ユーザーエージェントチェック */
						// OnOff
						$_SESSION["image-protector"]["user_agent"]["do"] = ($_POST['do_check'] === 'on');
						// はじくユーザーエージェント
						$_SESSION["image-protector"]["user_agent"]["deny"] = $_POST['deny'];
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'time_out':
						/* タイムアウトチェック */
						// OnOff
						$_SESSION["image-protector"]["time_out"]["do"] = ($_POST['do_check'] === 'on');
						// タイムアウト秒数
						$_SESSION["image-protector"]["time_out"]["limit"] = $_POST['limit'];
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'key':
						/* キーチェック */
						// OnOff
						$_SESSION["image-protector"]["key"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'division':
						/* 分割 */
						// OnOff
						if(!extension_loaded('gd')) {// GD拡張モジュールチェック
							$_SESSION["image-protector"]["division"]["do"] = false;
						} else {
							$_SESSION["image-protector"]["division"]["do"] = ($_POST['do_check'] === 'on');
						}
						// プリフィックス
						$_SESSION["image-protector"]["division"]["prefix"] = $_POST['prefix'];
						// 列
						$_SESSION["image-protector"]["division"]["cols"] = $_POST['cols'];
						// 行
						$_SESSION["image-protector"]["division"]["rows"] = $_POST['rows'];
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'browser_function':
						/* ブラウザ機能チェック */
						// OnOff
						$_SESSION["image-protector"]["browser_function"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'right_click':
						/* 右クリック禁止 */
						// OnOff
						$_SESSION["image-protector"]["right_click"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'print_screen':
						/* プリントスクリーン防止 */
						// OnOff
						$_SESSION["image-protector"]["print_screen"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'transparent':
						/* 透明フィルター */
						// OnOff
						$_SESSION["image-protector"]["transparent"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'deterioration':
						/* 劣化処理 */
						// OnOff
						$_SESSION["image-protector"]["deterioration"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'background':
						/* 背景画像化 */
						// OnOff
						$_SESSION["image-protector"]["background"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'media_link':
						/* メディアリンク */
						// OnOff
						$_SESSION["image-protector"]["media_link"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'js_css_obfuscated':
						/* Javascript, CSS難読化 */
						// OnOff
						$_SESSION["image-protector"]["js_css_obfuscated"]["do"] = ($_POST['do_check'] === 'on');
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					case 'error':
						/* エラー表示 */
						// エラーメッセージタイプ
						$_SESSION["image-protector"]["error"]["selected"] = $_POST['error_message_type'];
						//echo $_POST['error_message_type'];
						// 保存
						update_option('image-protector', $_SESSION["image-protector"]);
						break;
					default:
						break;
				}
			}
?>
	<div id="image_protector">
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>.htaccessで制限</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="htaccess">
					<?php
						// Apacheモジュール(rewrite)チェック
						//   apache_get_modulesがsakuraでは定義されていない
						/*
						if (!in_array('mod_rewrite', apache_get_modules())) {
							echo "<span class='bad'>！Apacheモジュール(rewrite)が使えないので有効化できません。</span><br />";
						}
						*/
						if ( is_multisite() ) {
							echo "<span class='bad'>！マルチサイトでは機能しません。</span><br />";
						}
					?>
					<?php $this->add_on_off_html($_SESSION["image-protector"]["htaccess"]["do"]) ?>
					<p>
						<span class="good">セッションチェックやその他以下のチェックを可能にします。</span>
						<span class="bad">ですが有効化時は管理画面のビジュアルエディタなどが使えません。</span>
						WordPressのインストールディレクトリではなく、その中のwp-contentディレクトリに作られます。
					</p>
					<h4>制限範囲</h4>
					<?php
						echo "<input type='hidden' name='cover[]' value='uploads'>";
						foreach ($_SESSION["image-protector"]["htaccess"]["cover"] as $k => $v) {
							$str_checked = ($v["cover"])?"checked":"";
							$str_disabled = ($k == "uploads")?"disabled":"";	// uploadsの保護は必須とする
							echo "<input type='checkbox' name='cover[]' value='{$k}' $str_checked $str_disabled> {$v["display"]} ({$k}ディレクトリ) ";
						}
					?>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
			<?php
				if (!$_SESSION["image-protector"]["htaccess"]["do"]) {
					$str_dont_htaccess_class = "dont_htaccess";
				}
			?>
			<div class="param_area <?php echo $str_dont_htaccess_class ?>">
				<form action="" method="post">
					<div class="param_contents">
						<h3>リファラーチェック</h3>
						<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
						<input type="hidden" name="image-protector" value="referer">
						<?php $this->add_on_off_html($_SESSION["image-protector"]["referer"]["do"]) ?>
						<p>
							<span class="good">画像へのブックマーク、URL直打ち、直リンクなどを防ぎます。</span>
							<span class="bad">ですがリファラーは容易に偽装されます。</span>
						</p>
					</div>
					<div class="submit_button">
						<?php submit_button(null, 'primary', 'submit'); ?>
					</div>
					<br style="clear:both;" />
				</form>
			</div>
			<div class="param_area <?php echo $str_dont_htaccess_class ?>">
				<form action="" method="post">
					<div class="param_contents">
						<h3>ユーザーエージェントチェック</h3>
						<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
						<input type="hidden" name="image-protector" value="user_agent">
						<?php $this->add_on_off_html($_SESSION["image-protector"]["user_agent"]["do"]) ?>
						<p>
							<span class="good">ホームページダウンロードソフトの使用などを防ぎます。</span>
							<span class="bad">ですがユーザーエージェントは容易に偽装されます。</span>
						</p>
						<h4>はじくユーザーエージェントに含まれる文字（改行区切り）</h4>
						<?php
							$str_user_agents = trim($_SESSION["image-protector"]["user_agent"]["deny"]);
						?>
						<textarea name="deny" style="width:90%;height:100px;"><?php echo $str_user_agents; ?></textarea>
					</div>
					<div class="submit_button">
						<?php submit_button(); ?>
					</div>
					<br style="clear:both;" />
				</form>
			</div>
			<div class="param_area <?php echo $str_dont_htaccess_class ?>">
				<form action="" method="post">
					<div class="param_contents">
						<h3>タイムアウトチェック</h3>
						<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
						<input type="hidden" name="image-protector" value="time_out">
						<?php $this->add_on_off_html($_SESSION["image-protector"]["time_out"]["do"]) ?>
						<p>
							<span class="good">偽装工作を許す時間を制限できます。</span>
							<span class="bad">ですがブラウザの戻るボタンの操作時に表示がおかしくなる事があります。低速回線では悪意がない方の場合でも画像を表示しきる前にタイムアウトしてしまう恐れがあります。</span>
						</p>
						<h4>タイムアウト秒数</h4>
						<select name="limit" style="width:50px">
							<?php $this->add_09option_html($_SESSION["image-protector"]["time_out"]["limit"]) ?>
						</select>
						秒
					</div>
					<div class="submit_button">
						<?php submit_button(); ?>
					</div>
					<br style="clear:both;" />
				</form>
			</div>
			<div class="param_area <?php echo $str_dont_htaccess_class ?>">
				<form action="" method="post">
					<div class="param_contents">
						<h3>キーチェック</h3>
						<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
						<input type="hidden" name="image-protector" value="key">
						<?php $this->add_on_off_html($_SESSION["image-protector"]["key"]["do"]) ?>
						<p>
							<span class="good">画像にワンタイムパスワードもどきを設定します。</span>
							<span class="bad">ですがoperaでは正しく機能しません。</span>
						</p>
					</div>
					<div class="submit_button">
						<?php submit_button(); ?>
					</div>
					<br style="clear:both;" />
				</form>
			</div>
			<div class="param_area <?php echo $str_dont_htaccess_class ?>">
				<form action="" method="post">
					<div class="param_contents">
						<h3>分割</h3>
						<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
						<input type="hidden" name="image-protector" value="division">
						<?php
							// GD拡張モジュールチェック
							if(!extension_loaded('gd')) {
								echo "<span class='bad'>！GD拡張モジュールが使えないので有効化できません。</span><br />";
							}
						?>
						<?php $this->add_on_off_html($_SESSION["image-protector"]["division"]["do"]) ?>
						<p>
							<span class="good">画像を分割表示し、全体の画像をダウンロードする手間をかけさせることができます。</span>
							<span class="bad">ですがサーバーの負荷が高まります。GIF, BMPには未対応です。</span>
						</p>
						<h4>処理画像ファイル名のプリフィックス</h4>
						この文字で始まる画像ファイルを分割表示対象とします。全ての画像を対象にする場合は空欄にしてください。<br />
						<input type="text" name="prefix" value="<?php echo $_SESSION["image-protector"]["division"]["prefix"]; ?>" />
						<h4>分割数</h4>
						<select name="cols" style="width:50px">
							<?php $this->add_09option_html($_SESSION["image-protector"]["division"]["cols"]) ?>
						</select>
						列
						<select name="rows" style="width:50px">
							<?php $this->add_09option_html($_SESSION["image-protector"]["division"]["rows"]) ?>
						</select>
						行
					</div>
					<div class="submit_button">
						<?php submit_button(); ?>
					</div>
					<br style="clear:both;" />
				</form>
			</div>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>ブラウザ機能チェック</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="browser_function">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["browser_function"]["do"]) ?>
					<p>
						<span class="good">ユーザーエージェントではなくブラウザの機能をみて有効なクライアントかを判別することによりユーザーエージェント偽装を困難にさせます。</span>
						<span class="bad">ですが将来出てくる新しいブラウザを正しく判別できるかがわかりません。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>右クリック禁止</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="right_click">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["right_click"]["do"]) ?>
					<p>
						<span class="good">右クリックを禁止し、画像ダウンロードに繋がる操作を困難にさせます。</span>
						<span class="bad">ですがその他の便利な右クリックメニューも使えなくなり不便です。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>プリントスクリーン防止</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="print_screen">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["print_screen"]["do"]) ?>
					<p>
						<span class="good">プリントスクリーンボタン[PrtSc]を無意味なものとします。</span>
						<span class="bad">ですがIEでしか効果が無く、確認ウィンドウも出てきてしまうようです。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>透明フィルター</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="transparent">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["transparent"]["do"]) ?>
					<p>
						<span class="good">透明な画像でかぶせて対象の画像の選択を困難なものにします。</span>
						<span class="bad">ですがソースを覗かれたら意味がありません。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>劣化処理</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="deterioration">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["deterioration"]["do"]) ?>
					<p>
						<span class="good">縁をぼかしたり退色フィルターをかけてアナログ撮影したような効果を画像に与え、プリントスクリーンでのキャプチャに手間をかけさせます。</span>
						<span class="bad">ですがCSSをOffにされたりCSS3未対応ブラウザだと意味がありません。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>背景画像化</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="background">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["background"]["do"]) ?>
					<p>
						<span class="good">imgタグのsrcからimgタグのbackgroundに置き換えて画像ダウンロードを困難にします。</span>
						<span class="bad">ですが幅と高さを明示して指定する必要があり、かつ伸縮に非対応です。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>メディアリンク</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="media_link">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["media_link"]["do"]) ?>
					<p>
						<span class="good">添付ファイル投稿ページからの画像へのリンクを剥ぎ取ります。</span>
						<span class="bad">ですがURL直打ちをされたら意味がありません。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>Javascript, CSS難読化</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="js_css_obfuscated">
					<?php $this->add_on_off_html($_SESSION["image-protector"]["js_css_obfuscated"]["do"]) ?>
					<p>
						<span class="good">当プラグインに関するものだけを難読化し解析されにくくします。</span>
						<span class="bad">ですが当プラグイン自体が公開されているのが難点です。</span>
					</p>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>エラー表示</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="error">
					<?php
						echo $this->add_error_message_type_html(0);
						echo "<br />";
						echo "<span class='good'>推奨</span>";
						echo "<br />";
						echo "<img src='{$this->str_image_url}/loading.gif' />";
						echo "<br />";
						echo "<br />";
						echo $this->add_error_message_type_html(1);
						echo $this->add_error_message_type_html(2);
						echo $this->add_error_message_type_html(3);
						echo "<br />";
						echo "<span class='bad'>障害原因追求時等のみに使用すべきです。画像が表示されない理由をわざわざ他人に教えてやる必要はありません。</span>";
						echo "<br />";
						foreach ($_SESSION["image-protector"]["error"]["error"] as $k => $v) {
							//echo $v["jp"];
							$str_color =
							str_pad(dechex($v["color"]["r"]), 2, "0", STR_PAD_LEFT)
							. str_pad(dechex($v["color"]["g"]), 2, "0", STR_PAD_LEFT)
							. str_pad(dechex($v["color"]["b"]), 2, "0", STR_PAD_LEFT);
							//echo $str_color;
							echo "<span style='background-color:#{$str_color};'>　　　</span> ";
							echo "{$v["jp"]} ({$k})";
							echo "<br />";
						}
					?>
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>設定保持情報</h3>
					<textarea><?php echo esc_html(var_export($_SESSION["image-protector"], true)); ?></textarea>
				</div>
				<div class="submit_button">
				</div>
				<br style="clear:both;" />
			</form>
		</div>
		<div class="param_area">
			<form action="" method="post">
				<div class="param_contents">
					<h3>設定初期化</h3>
					<?php wp_nonce_field('my_param_action', 'my_param_nonce'); ?>
					<input type="hidden" name="image-protector" value="init">
				</div>
				<div class="submit_button">
					<?php submit_button(); ?>
				</div>
				<br style="clear:both;" />
			</form>
		</div>
	</div>
</div>
<?php
		}
		private function add_on_off_html($is_on) {
?>
			<div>
				<label style="border:solid 1px #FF9999;<?php echo (!$is_on)?'background-color:#FF9999;':''; ?>">
					<input type="radio" name="do_check" value="off" <?php echo (!$is_on)?'checked':''; ?> />
					停止
				</label>
				<label style="border:solid 1px #9999FF;<?php echo ($is_on)?'background-color:#9999FF;':''; ?>">
					<input type="radio" name="do_check" value="on" <?php echo ($is_on)?'checked':''; ?> />
					有効化
				</label>
			</div>
<?php
		}
		private function add_09option_html($selected_num) {
			$str_options = '';
			for ($i = 1; $i < 10; $i++) {
				$selected = (intval($selected_num) === $i)?"selected":"";
				$str_options .= "<option value='$i' $selected >$i</option>";
			}
			echo $str_options;
		}
		private function add_error_message_type_html($i_index) {
			$ipe = $_SESSION["image-protector"]["error"];
			if ($ipe["message_types"][$i_index]["input_value"] === $ipe["selected"]) {
				$checked = 'checked';
			} else {
				$checked = '';
			}
?>
			<label>
				<input type="radio" name="error_message_type" value="<?php echo $ipe["message_types"][$i_index]["input_value"]; ?>" <?php echo $checked; ?> />
				<?php echo $ipe["message_types"][$i_index]["display"]?>
			</label>
<?php
		}
		public function delete_htaccess() {
			// 存在するなら
			if (file_exists("../wp-content/.htaccess")) {
				// バックアップ
				if (!copy("../wp-content/.htaccess", "../wp-content/plugins/image-protector/htaccess/" . time() . ".htaccess.php")) {
					echo ".htaccessバックアップ失敗";
				}
				// 既存のhtaccessを削除
				if (!unlink("../wp-content/.htaccess")) {
					echo ".htaccess削除失敗";
				} 
			}
		}
		public function do_init() {
			// .htaccessの削除
			$this->delete_htaccess();
			// 初期化
			$_SESSION["image-protector"] = $this->get_init();
			// 保存
			update_option('image-protector', $_SESSION["image-protector"]);
		}
	}


//エラー表示
//ini_set( 'display_errors', "1" );
//ini_set('error_reporting', E_ALL);

// マルチサイトだとなぜか読み込まれないので一応明示的に指定
wp_enqueue_script('jquery');

$obj_image_protector = new Image_Protector();

// セッションの使用、設定データ読み込み
add_action('init', array($obj_image_protector, 'init_sessions'));


if (is_admin()) {
	// 管理ページ
	
	// 管理メニュー
	add_action('admin_menu', array($obj_image_protector, 'add_menu_page_cb'));
	
	// スタイルの設定（管理画面用）
	add_action('admin_head', array($obj_image_protector, 'set_common_style'));
	add_action('admin_head', array($obj_image_protector, 'set_admin_style'));

	// 有効化するときのフック用関数（データ初期化）
	register_activation_hook(__FILE__, array($obj_image_protector, 'do_init'));
	// 停止するときのフック用関数（.htaccess削除）
	register_deactivation_hook(__FILE__, array($obj_image_protector, 'delete_htaccess'));
	// 削除するときのフック用関数（.htaccess削除）（マルチサイトではいきなり削除しうるためこれを記述しておく）
	register_uninstall_hook(__FILE__, 'delete_htaccess');
	
} else {
	// 表のページ
	
	// スタイルの設定（公開画面用）
	add_action('wp_head', array($obj_image_protector, 'set_common_style'));
	add_action('wp_head', array($obj_image_protector, 'set_public_style'));

	// メディア個別のページのリンクをはぎ取る
	add_filter( 'prepend_attachment', array($obj_image_protector, 'strip_media_link') );
}
/*
add_action('wp_loaded', "aaa");
function aaa() {
	wp_die("err");
}
*/
?>