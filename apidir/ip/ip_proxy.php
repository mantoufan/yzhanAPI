<?php
    $g = array_merge(array(
        'out_sep' => '<br>',
        'ip_type' => 'all' // all anonymous available ssl
    ), $g);
    include(ROOT.'mtf/mtfProxy/mtfProxy.php');
    $mtfProxy = new mtfProxy();
    $mtfProxy->cacheTime = 360;
    $mtfProxy->dir['data'] = dirname(__FILE__) . '/cache';
    if (!is_dir($mtfProxy->dir['data'])) {
        mkdir($mtfProxy->dir['data']);
    }
    $ips = $mtfProxy->get($g['ip_type']);
    if ($out_type === 'txt') {
        echo implode($g['out_sep'], $ips);
        exit;
    } else {

    }
?>