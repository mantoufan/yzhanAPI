<?php
  $g = array_merge(array(
    'url' => '',
    'width' => 720,
    'css_base64' => '',
    'crop' => '720x720',
    'disable-javascript' => '--disable-javascript'
  ), $g);
  if (empty( $g['url'])) {
    output('缺少GET参数，要转换的网址 url', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
  }
  $upload_path = dirname(__FILE__) . '/upload';
  $dir_name = get_dir_name(get_name(), $upload_path . '/');
  $md5 = md5($g['url'] . $g['css_base64']);
  $f_name = $dir_name . '/' . $md5 . '_' . $g['width'] . '.jpg';
  $f_path = $upload_path . '/' . $f_name;
  $bin_com_dir = dirname(__file__) . '/../com/bin/';
  $bin_wkhtmltoimage = $bin_com_dir . 'wkhtmltox/bin/wkhtmltoimage';
  
  $_r = array();
  if (!file_exists($f_path)) {
    $css_path = '';
    if (!empty($g['css_base64'])) {
      $css_path = $upload_path . '/' . $dir_name . '/' . $md5 . '.css';
      file_put_contents($css_path, base64_decode($g['css_base64']));
    }
    exec($bin_wkhtmltoimage . ' --width ' . $g['width'] .  ' --user-style-sheet "' . $css_path . '" ' . $g['disable-javascript'] . ' ' . $g['url'] . ' ' . $f_path, $_r);
  }
  if (!file_exists($f_path)) {
    output('网页转图片出错：' . implode(',', $_r), $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
  }
  $d_dir = $upload_path . '/' . $dir_name . '/' . $md5;
  mkdir($d_dir);
  $d_path = $d_dir . '/d.jpg';
  $bin_convert = $bin_com_dir . 'convert';
  exec($bin_convert . ' ' . $f_path . ' -crop ' . $g['crop'] . ' ' . $d_path, $r);
  if (!$_r[0]) {
    $bin_7z = $bin_com_dir . '7z/7za';
    $zip_path = $dir_name . '/' . $md5 . '.zip';
    exec($bin_7z . ' a -tzip ' . $upload_path . '/' . $zip_path . ' ' . $d_dir, $_r);
    if (!$_r[0]) {
      $output_zip_path = 'https://'.$_SERVER['SERVER_NAME'].'/apidir/img/upload/' . $zip_path ;
      output('success', $out_type, 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => array('output_zip_path' => $output_zip_path), 'txt' => $output_zip_path));
    } else {
      output('打包出错：' . implode(',', $_r), $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
  } else {
    output('裁剪图像出错：' . implode(',', $_r), $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
  }
?>