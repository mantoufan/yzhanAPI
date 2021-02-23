<?php
    $g = array_merge(array(
        'out_sep' => '<br>',
        'ip_type' => 'all' // all anonymous available ssl
    ), $g);
    include(ROOT.'mtf/mtfProxy/mtfProxy.php');
    $mtfProxy = new mtfProxy();
    $mtfProxy->cacheTime = 360;
    $mtfProxy->dir['data'] = dirname(__FILE__) . '/cache';
    $ips = $mtfProxy->get($g['ip_type']);
    if ($out_type === 'txt') {
        echo implode($g['out_sep'], $ips);
        exit;
    } else if ($out_type === 'html') {
        date_default_timezone_set('Asia/ShangHai');
        $cache = $mtfProxy->dir['data'] . '/ips_' . $g['ip_type'] . '.php';
        $html = file_get_contents(dirname(__FILE__) . '/tpl/ip_proxy.html');
        $data = '';
        foreach($ips as $no => $ip) {
            list($ip, $port) = explode(':', $ip);
            $data .= '<tr' . ($no % 2 === 0 ? '' : ' class="pure-table-odd"') . '>';
            $data .= '<td>' . $no . '</td><td>' . $ip . '</td>' . '<td>' . $port . '</td>' . '<td>' . date('Y-m-d H:i:s', filemtime($cache)) . '</td>';
            $data .= '</tr>';
        }
        echo str_replace('{{data}}', $data, $html);
        exit;
    } else {

    }
?>