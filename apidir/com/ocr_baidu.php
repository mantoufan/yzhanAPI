<?php
include('mtf/mtfHTTP/mtfHTTP.php');
$mtfHTTP = new mtfHTTP();
$_h = $mtfHTTP->curl(array(
    'u' => 'https://aip.baidubce.com/oauth/2.0/token',
    'p' => array(
        'grant_type' => 'client_credentials',
        'client_id' => '1Xz1eG6OQpud8p3xwXeBhkiy',
        'client_secret' => 'rptK6WztVwXry94Ec2BEYR8KpMQ5FRA7'
    )
));
$_j = json_decode($_h, true);
if (isset($_j['access_token']) && !empty($_j['access_token'])) {
    $access_token = $_j['access_token'];
} else {
    output('access_token获取失败', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
}
?>