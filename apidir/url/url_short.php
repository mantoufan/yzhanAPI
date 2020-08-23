<?php
    $g = array_merge(array(
        'url' => '', // 长连接
        'qrcode_size' => '', // 二维码 尺寸
        'addon' => '', // 附加信息
    ), $g);
    $url = trim($g['url']);
    if (!$url) {
        output('缺少参数 网址：url', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    $url = urldecode($url);
    $md5 = md5($url);
    $r = $db->sql('s1', 'mtf_url_short', 'code', 'WHERE md5 = \'' . $md5 .'\'');
    if ($r && isset($r['code'])) {
        $code = $r['code'];
    } else {
        $r = $db->sql('i', 'mtf_url_short', array('md5'=>$md5, 'url'=>$url, 'hits'=>0, 'add_time'=>date('Y-m-d H:i:s'), 'upd_time'=>date('Y-m-d H:i:s'), 'des'=>$des, 'addon'=>$g['addon']));
        if (!$r) {
            logger('新增网址失败：'."INSERT INTO `mtf_url_short` (`md5`, `url`, `hits`, `add_time`,`upd_time`, `des`) VALUES ('" . $md5 . "', '" . $url . "', '0', CURRENT_TIMESTAMP , CURRENT_TIMESTAMP , '" . $des . "');");
            output('新增网址失败，请重试', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
        }
        $maxId = 1;
        $lastId = '';
        $r = $db->sql('r', 'SELECT LAST_INSERT_ID()');
        if ($r) {
            $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
            $lastId = $row['LAST_INSERT_ID()'];
            $maxId = $lastId + 1;
        } else {
            logger('获取LAST_INSERT_ID失败');
            output('获取LAST_INSERT_ID失败，请重试');
        }
        $code = base_convert($lastId, 10, 36);
        $r = $db->sql('u', 'mtf_url_short', array('code'=>$code), 'WHERE id = \'' . $lastId .'\'');
        if (!$r) {
            logger('更新编码失败：'."UPDATE `mtf_url_short` SET `code` = '" . $code . "' WHERE id = '" . $lastId ."'");
            output('更新编码失败，请重试', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
        }
    }
    $url = 'http' . (isSsl() ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] . ($_SERVER[PHP_SELF] ? substr($_SERVER[PHP_SELF], 0, strrpos($_SERVER[PHP_SELF], '/')) : '') . '/' . $code;
    output('success', $out_type, 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => array('url' => $url), 'txt' => $url));
?>