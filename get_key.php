<?php
require_once("get_common.php");
/*
 * �L�[���s
 *   �G���[���͋󕶎��ŕԂ��A�S�̂Ƃ��Ắu�L�[�s���v�G���[�Ƃ����Ă���
 */


session_start();

$req = $_POST;
//$req = $_GET;

// get�n���ʋ@�\�N���X
$obj_get_common = new Get_Common("error");

/*
 * �Z�b�V�����`�F�b�N
 */
$obj_get_common->check_session();

/*
 * ���t�@���[�`�F�b�N
 */
$obj_get_common->check_referer();

/*
 * ���[�U�[�G�[�W�F���g�`�F�b�N
 */
$obj_get_common->check_user_agent();

/*
 * �^�C���A�E�g�`�F�b�N
 */
$obj_get_common->check_time_out();

/*
 * Ajax���ǂ���
 */
$obj_get_common->check_ajax();

/*
 * �u���E�U�@�\�`�F�b�N
 *   �`�F�b�N���ׂ����ǂ����̐���͌Ăяo�����ōs���Ă���̂ł����͂���ł���
 */
if ($req["browser_info"] === 'false') {
	error("browser_info");
}

$keys = "";
// ���s�������[�v
for ($i = 0; $i < $req["count"]; $i++) {
	// �L�[�𔭍s
	do{
		$key = md5(rand() . time());
	} while (array_key_exists($key, $_SESSION["image-protector"]["key"]["publish"]));
	// �ێ�
	$_SESSION["image-protector"]["key"]["publish"][$key] = true;
	// ��t�p
	$keys .= $req["split"] . $key;
}

// ��t
//echo substr($keys, 1);
echo "true" . $keys;

/*
 * �G���[���b�Z�[�W�̏o��
 */
function error($str_error) {
}
