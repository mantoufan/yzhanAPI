<?php
  $g = array_merge(array(
    'url' => '' // url
  ), $g);
  $url = $g['url'];
  if (!$url) output('缺少参数 网址：url', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
  $whilteList = array('https://developers.google.com/static/search/apis/ipranges/googlebot.json');
  if (in_array($url, $whilteList) === false) output('非白名单网址', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
  $c = file_get_contents($url);
  foreach ($http_response_header as $header) header($header);
  exit($c);
?>