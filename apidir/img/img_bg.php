<?php
       $g = array_merge(array(
        'theme' => '', // 图片主题
        'order' => 'rand' // 返回顺序，随机 rand
    ), $g);
    if (empty($g['theme'])) {
        output('缺少GET参数，背景图片主题：img_urls', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    include(dirname(__FILE__) . '/data/bg.php');
    include(dirname(__FILE__) . '/../com/Mobile_Detect.php');
    $detect = new Mobile_Detect;
    $direciton = 'horizontal';
    if ($detect -> isMobile() && !$detect -> isTablet()) {
        $direciton = 'vertical';
    }
    $bg_img_path = '';
    if (isset($bgs[$g['theme']]) && $bgs[$g['theme']][$direciton]) {
        $bgs = $bgs[$g['theme']][$direciton];
        $bg = $bgs[array_rand($bgs)];
        $ext = strrchr($bg, '.');
        $bg_img_path = 'https://s1.cdn00.com/' . str_replace($ext, '', $bg) . '_c_w_1280'.($ext ? '_ext_' . substr($ext, 1) : '').'.jpg';
    }
    if ($bg_img_path) {
        output('success', $out_type, 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => array('bg_img_path' => $url), 'txt' => $bg_img_path, 'redirect' => $bg_img_path));
    } else {
        output('背景图片不存在', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
?>
