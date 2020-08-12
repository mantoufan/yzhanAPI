<?php
    $g = array_merge(array(
        'img_urls' => '',
        'img_direction' => 'vertical' // 方向竖直 vertical 、水平 horizontal
    ), $g);
    if (empty( $g['img_urls'])) {
        output('缺少GET参数，要合并的图片地址，用半角逗号,分隔：img_urls', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    $upload_path = dirname(__FILE__) . '/upload';
    $img_urls = explode(',http', $g['img_urls']);
    $dir_name = get_dir_name(get_name(), $upload_path . '/');

    $f_name = $dir_name . '/' . md5($g['img_url']) . '_' . $g['img_direction'] . '_merge.jpg';
    $f_path = $upload_path . '/' . $f_name;
    if (!file_exists($f_path)) {
        $f_ar = array();
        foreach($img_urls as $k => $f_url) {
            if ($k > 0) {
                $f_url = 'http' . $f_url;
            }
            $_f_name = get_name();
            $_f_ext_ar = explode('.', $f_url);
            $_f_ext = end($_f_ext_ar);
            $_f_path = $upload_path . '/' . $dir_name . '/' . $_f_name . '.' . $_f_ext;
            $f_ar[] = $_f_path;
            file_put_contents($_f_path, file_get_contents($f_url));
        }
        $bin_com_dir = dirname(__file__) . '/../com/bin/';
        $bin_convert = $bin_com_dir . 'convert';
        $_r = array();
        exec($bin_convert . ' ' . ($g['img_direction'] === 'vertical' ? '-' : '+') . 'append' . ' ' . implode(' ', $f_ar) . ' ' . $f_path, $_r);
        if (!$_r[0]) {
            
        } else {
            output('合并图像出错：' . implode(',', $_r), $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
        }
    }
    $merged_img_path = 'https://'.$_SERVER['SERVER_NAME'].'/apidir/img/upload/' . $f_name;
    if ($out_type === 'redirect') {
        header('Cache-Control: no-cache');
        header('Location:' . $merged_img_path);
    } else if ($out_type === 'img') {
        header('Content-type:image/jpeg');
        echo file_get_contents($f_path);
        exit;
    }else {
        output('success', $out_type, 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => array('merged_img_path' => $merged_img_path)));
    }
    
?>