<?php
    ini_set('memory_limit', '256M');
    if (!isset($_POST['img_base64'])) {
        $query_str = file_get_contents('php://input'); 
        if ($query_str) {
            $query_ar = json_decode($query_str, true);
            if (!$query_ar['img_base64']) {
                if (isset($query_ar['img_base64']) && !empty($query_ar['img_base64'])) {
                    $_img_base64 = $query_ar['img_base64'];
                } else {
                    $_img_base64 = '';
                }
            }
        } else {
            $_img_base64 = '';
        }
    } else {
        $_img_base64 = $_POST['img_base64'];
    }
    if (!$_img_base64) {
        output('缺少POST参数 BASE64编码的图片：img_base64', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    if (!is_dir($upload_path)) {
        mkdir($upload_path);
    } else {
        clear_outdate_file_in_dir($upload_path);
    }
    if (is_array($_img_base64)) {
        $_img_base64_ar = $_img_base64;
        $_img_base64_is_array = true;
    } else {
        $_img_base64_ar = array($_img_base64);
        $_img_base64_is_array = false;
    }
    $_img_base64_ar_len = count($_img_base64_ar);
    $f_ar = array();
    $msg_ar = array();
    $data_ar = array();
    foreach($_img_base64_ar as $k => $_img_base64) {
        $f = base64image_to_filepath($_img_base64, $upload_path . '/');
        $f_ar[] = $f;
    }
?>