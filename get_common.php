<?php
	/*
	 * get系共通機能クラス
	 */
	class Get_Common {
		// エラー出力コールバック関数
		private $_cb;
		/*
		 * コンストラクタ
		 */
		public function __construct($cb) {
			$this->_cb = $cb;
		}
		/*
		 * toString
		 */
		public function __toString() {
		}

		/*
		 * セッションチェック
		 */
		public function check_session() {
			if (!isset($_SESSION["image-protector"])) {
				//error("session");
				call_user_func($this->_cb, "session");
			}
		}

		/*
		 * リファラーチェック
		 */
		public function check_referer() {
			if ($_SESSION["image-protector"]["referer"]["do"]) {
				if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== "" && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
					// OK
				} else {
					//error("referer");
					call_user_func($this->_cb, "referer");
				}
			}
		}

		/*
		 * ユーザーエージェントチェック
		 */
		public function check_user_agent() {
			if ($_SESSION["image-protector"]["user_agent"]["do"]) {
				$str_user_agents = trim($_SESSION["image-protector"]["user_agent"]["deny"]);
				$lst_user_agents = preg_split("/[\r\n]+/", $str_user_agents);
				foreach ($lst_user_agents as $deny_user_agent) {
					if (strpos($_SERVER['HTTP_USER_AGENT'], $deny_user_agent) !== false) {
						//error("user_agent");
						call_user_func($this->_cb, "user_agent");
					}
				}
			}
		}

		/*
		 * タイムアウトチェック
		 */
		public function check_time_out() {
			if ($_SESSION["image-protector"]["time_out"]["do"]) {
				if ($_SESSION["image-protector"]["time_out"]["start"] + $_SESSION["image-protector"]["time_out"]["limit"] < time()) {
					//error("time_out");
					call_user_func($this->_cb, "time_out");
				}
			}
		}

		/*
		 * キーチェック
		 */
		public function check_key() {
			if ($_SESSION["image-protector"]["key"]["do"]) {
				if (!isset($_GET["key"]) || !isset($_SESSION["image-protector"]["key"]["publish"][$_GET["key"]])) {
					/* NG */
					//error("key");
					call_user_func($this->_cb, "key");
				} else {
					/* OK */
					// キーを無効化
					unset($_SESSION["image-protector"]["key"]["publish"][$_GET["key"]]);
				}
			}
		}

		/*
		 * Ajaxかどうか
		 */
		public function check_ajax() {
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			} else {
				//echo "ERROR: not ajax";
				//exit;
				call_user_func($this->_cb, "ajax");
			}
		}


		/*
		 * ログ出力（空ファイルを置かなければ動かないときもあるようだが？？）
		 */
		public function debug($msg) {
			//debug(var_export($lst_thread, true));
			if (!is_numeric($msg) && !is_string($msg)) {
				$msg = var_export($msg, true);
			}
			// ログファイル名
			$str_log_file_name = "debug.log";
			// １００M超えたらファイル名を変更
			if ( filesize( $str_log_file_name ) > 1000000) { // ファイルがないとここでWarningが出るが、まあ無視していい。
				rename ($str_log_file_name, $str_log_file_name . date("YmdHis"));
			}
			// ログ出力
			error_log(date("Y/m/d H:i:s") . ": " . $msg . "\r\n", 3, $str_log_file_name);
		}


	}
