<?php
if ($api_action === 'read') {
    $g = array_merge(array(
        'addon' => '', // 附加信息
    ), $g);
    $upload_path = dirname(__FILE__) . '/upload';
    include(ROOT.'apidir/com/img_base64_f_md5_id_res_data.php');
    foreach ($f_ar as $no => $f) {
        $img = end(explode('/', $f));
        $md5 = md5_file($f);
        $res = array(
            'data' => ''
        );
        $id = '';
        $r = $db->sql('s1', 'mtf_qrcode_read', 'id, code, engine, results, msg', 'WHERE md5 = \'' . $md5 .'\'');
        if ($r && isset($r['id'])) {
            if ($r['code'] === '200') {
                $res['code'] = 200;
                $res['engine'] = $r['engine'];
                $res['data'] = json_decode($r['results'], true);
                $res['msg'] = $r['msg'];
            }
            $id = $r['id'];
        }
        if (!$res['data']) {
            include(ROOT.'mtf/mtfCode/mtfCode.php');
            $mtfCode = new mtfCode();
            $res = $mtfCode -> deQRCode($f, $upload_path . '/');
        }
        $value = array(
            'img'=>$img, 
            'code'=>$res['code'], 
            'engine'=>$res['engine'], 
            'results'=>json_encode($res['data'], JSON_UNESCAPED_UNICODE),
            'msg'=>$res['msg'],
            'des'=>$des,
            'addon'=>$g['addon']
        );
        if ($id) {
            $db->sql('u', 'mtf_qrcode_read', array_merge($value, array('hits'=>'///hits+1')), 'WHERE id = \'' . $id .'\'');
        } else {
            $db->sql('i', 'mtf_qrcode_read', array_merge($value, array('md5'=>$md5, 'add_time'=>date('Y-m-d H:i:s'))));
        }
        if ($res['code'] === 200) {
            $data_ar[] = $res['data'];
        } else {
            $data_ar[] = '';
            $msg_ar[] = ($_img_base64_is_array ? '第'. ($no + 1).'张' : '') . '条码识别失败，原因：'.$res['msg'];
            logger(($_img_base64_is_array ? '第'. ($no + 1).'张' : '') . '条码识别失败，原因：'.$res['msg'] . ' 图片：' . $f);
        }
    }
    $msg_ar_len = count($msg_ar);
    output($msg_ar_len > 0 ? implode(',', $msg_ar) : 'success', $out_type, $msg_ar_len === $_img_base64_ar_len ? -1 : 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => array('scan_results' => $_img_base64_is_array ?  $data_ar : $data_ar[0])));
}
?>