<?php
    include('IpLocation.class.php');
    $IpLocation = new IpLocation('qqwry.dat');
    $addr = array(
        'ip' => get_client_ip(),
        'beginip' => '',
        'endip' => '',
        'country' => '',
        'area' => '',
        'province' => '',
        'city' => ''
    );
    $addr = array_merge($addr, $IpLocation->getlocation($addr['ip']));
    cors_domain_kocla();
    output('success', $out_type, 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => $addr));
?>