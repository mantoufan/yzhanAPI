<?php
if($api_action === 'verify') {
    $_domain = isset($_POST['domain']) ? $_POST['domain'] : (isset($g['domain']) ? $g['domain'] : '');
    $_name = isset($_POST['name']) ? $_POST['name'] : (isset($g['name']) ? $g['name'] : 'shopxoplugin_thirdpartylogin');
    if (!$_domain) {
        output('缺少domain参数', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    include('data.php');include('wga_function.php');
    $_domain = get_root_domain($_domain);
    $_res = array(
        'qq' => '',
        'level' => ''
    );
    $msg = 'success';$code = 200;
    if ($_domain[2] && $data[$_name][$_domain[2]]) {
        $_res = array(
            'qq' => $data[$_name][$_domain[2]],
            'level' => 1
        );
    } else if ($_domain[3] && $data[$_name][$_domain[3]]) {
        $_res = array(
            'qq' => $data[$_name][$_domain[3]],
            'level' => 1
        );
    }else {
        $msg = '未授权，请联系QQ：978572783';
        $code = -1;
    }
    output($msg, $out_type, $code, array('jsonp_cb' => $g['jsonp_cb'], 'data' => $_res));
}
?>