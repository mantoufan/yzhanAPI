<?php 
if($api_action === 'verify') {
    $_domain = isset($_POST['domain']) ? $_POST['domain'] : (isset($g['domain']) ? $g['domain'] : '');
    $_name = isset($_POST['name']) ? $_POST['name'] : (isset($g['name']) ? $g['name'] : 'shopxoplugin_thirdpartylogin');
    $_version = isset($_POST['version']) ? $_POST['version'] : (isset($g['version']) ? $g['version'] : ''); 
    if (!$_domain) {
        output('缺少domain参数', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    include('data.php');include('wga_function.php');
    $_domain = get_root_domain($_domain);
    $_res = array(
        'qq' => '',
        'level' => ''
    );
    $msg = 'success';$code = 200;$_version_new = '';
    if (isset($data[$_name]['version'])) {
        $_version_new = $data[$_name]['version'];
    }
    if ($_domain[2] && $data[$_name]['domain'][$_domain[2]]) {
        $_info = $data[$_name]['domain'][$_domain[2]];
    } else if ($_domain[3] && $data[$_name]['domain'][$_domain[3]]) {
        $_info = $data[$_name]['domain'][$_domain[3]];
    }

    if ($_info) {
        if (is_array($_info)) {
            $_qq = $_info['qq'];
            $_version_max = $_info['version'];
            if ($_version && versionCompare($_version_max, $_version)) {
                $msg = '无此版本授权，请联系QQ：978572783';
                $code = -1;
            }
           
        } else {
            $_qq = $_info;
        }

        $_res = array(
            'qq' => $_qq,
            'level' => 1
        );

        if ($_version && $_version_new) {
            if (versionCompare($_version, $_version_new)) {
                $_res = array_merge($_res, array(
                    'tip' => '有新版本啦，版本号是：' . $_version_new . ' 升级请联系QQ：978572783'
                ));
            }
        }
    } else {
        $msg = '未授权，请联系QQ：978572783';
        $code = -1;
    }

    output($msg, $out_type, $code, array('jsonp_cb' => $g['jsonp_cb'], 'data' => $_res));
}
function versionCompare($_version, $version_new) {
    $_ar = explode('.', $_version);
    $_ar_new = explode('.', $version_new);
    $_len = max(count($_ar_new), count($_ar));
    for ($i = 0; $i < $_len; $i++) {
        if (!isset($_ar_new[$i])) {
            $_ar_new[$i] = 0;
        }
        if (!isset($_ar[$i])) {
            $_ar[$i] = 0;
        }
        if ($_ar_new[$i] > $_ar[$i]) {
            return true;
        } else if ($_ar_new[$i] < $_ar[$i]) {
            break;
        }
    }
}
?>