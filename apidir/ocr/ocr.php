<?php
use Grafika\Grafika;
use Grafika\Color;

$upload_path = dirname(__FILE__) . '/upload';
if (!is_dir($upload_path)) {
    mkdir($upload_path);
} else {
    clear_outdate_file_in_dir($upload_path);
}
if($api_action === 'answersheet') {// 答题卡识别
    $g = array_merge(array(
        'addon' => '', // 附加信息
        'engine' => 'pdfdwcmd', // 识别引擎：baidu
    ), $g);
    $_img_base64 = $_POST['img_base64'];
    if (!$_img_base64) {
        output('缺少POST参数 BASE64编码的图片：img_base64', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    include(ROOT.'apidir/com/ocr_baidu.php');
    $file_path = base64image_to_filepath($_img_base64, $upload_path.'/');
    $bin_com_dir = dirname(__file__) . '/../com/bin/';
    $bin_convert = $bin_com_dir . 'convert';

    $_tmp_ar = explode('/', $file_path);$_n = end($_tmp_ar);
    $_tmp_ar = explode('.', $_n);$_n = reset($_tmp_ar);
    $f_enhanced_name = $_n . '_enhanced.jpg';
    $dir_name = get_dir_name($f_enhanced_name, $upload_path . '/');
    $f_enhanced = $upload_path . '/' . $dir_name  . '/' . $f_enhanced_name;
    $_f_web = 'https://' . $_SERVER['SERVER_NAME'] . '/apidir/ocr/upload/' . $dir_name  . '/' . $f_enhanced_name;

    exec($bin_convert . ' "' . $file_path . '" -deskew 50% -resize 2000x2000 -brightness-contrast -50x50 -quality 75 -deskew 40% -trim +repage -level 20%,80% -verbose "' . $f_enhanced . '"', $_r);
    if ($_r) {
        if (isset($_r[0])) {
            preg_match_all('/=>(\d+)x(\d+)/', $_r[0], $_matches);
            if ($_matches && isset($_matches[1][0]) && isset($_matches[2][0])) {
                $_width = $_matches[1][0];
                $_height = $_matches[2][0];
                // 获取中心坐标
                $_center_x = round($_width / 2);
                $_center_y = round($_height / 2);
            } else {
                $res = array('code'=>-1, 'msg'=>'图像增强引擎报错：获取图像宽度和高度失败');
            }
        }
        $res = array('code'=>200, 'msg'=>'success', 'engine'=>'', 'data'=>$_f_web); 
    } else {
        $res = array('code'=>-1, 'msg'=>'图像增强引擎超时');
    }

    // 图片转base64编码
    $_base64 = '';
    if($fp = fopen($f_enhanced, 'rb', 0))
    {
        $gambar = fread($fp,filesize($f_enhanced));
        fclose($fp);
        $_base64 = chunk_split(base64_encode($gambar));
    }

    if (!$_base64) {
        output('图片转base64失败：' . $_f_web, $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    } else {
        $_h = $mtfHTTP->curl(array(
            'u' => 'https://aip.baidubce.com/rest/2.0/ocr/v1/doc_analysis?access_token=' . $access_token,
            'h' => array('Content-Type:application/x-www-form-urlencoded'),
            'p' => array(
                'image' => $_base64,
                'language_type' => 'CHN_ENG',
                'result_type' => 'big',
                'detect_direction' => 'true',
                'line_probability' => 'true',
                'words_type' => 'handprint_mix',
                'layout_analysis' => 'true'
            )
        ));
    }
    echo json_encode(array('img_path'=> $_f_web, 'baidu_json' => $_h));
    exit;
} elseif ($api_action === 'visual') {// 数据结果可视化
    $_res_json_str = str_replace('\"', '"', $_POST['res_json_str']);
    if (!$_res_json_str) {
        output('缺少POST参数 JSON序列化后的字符串：res_json_str', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    $_img_base64 = $_POST['img_base64'];
    if (!$_img_base64) {
        output('缺少POST参数 BASE64编码的图片：img_base64', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    $file_path = base64image_to_filepath($_img_base64, $upload_path.'/');

    $_res_json = json_decode($_res_json_str, true);
    
    include('ocr_function.php');

    $editor = Grafika::createEditor();
    $editor->open($image, $file_path);

    // 文字结果
    if ($_res_json['results']) {
        $_res = $_res_json['results'];
        foreach($_res as $_k => $_v) {
            $word = $_v['words']['word'];
            $words_location = $_v['words']['words_location'];
            $_x = $words_location['left'];
            $_y = $words_location['top'];
            $_w = $words_location['width'];
            $_h = $words_location['height'];
            // $editor->draw($image, Grafika::createDrawingObject('Rectangle', $_w, $_h, array($_x, $_y), 3, '#FF0000', null));
        }
    }

    // 区域结果
    if ($_res_json['layouts']) {
        $_layouts = $_res_json['layouts'];
        foreach($_layouts as $_k => $_v) {
            $_p = $_v['layout_location'];
            $_x_0 = $_p[0]['x'];
            $_y_0 = $_p[0]['y'];

            $_x_1 = $_p[1]['x'];
            $_y_1 = $_p[1]['y'];

            $_x_2 = $_p[2]['x'];
            $_y_2 = $_p[2]['y'];

            $_x_3 = $_p[3]['x'];
            $_y_3 = $_p[3]['y'];  
            
            // $editor->draw($image, Grafika::createDrawingObject('Polygon', array(array($_p[0]['x'], $_p[0]['y']), array($_p[1]['x'], $_p[1]['y']), array($_p[2]['x'], $_p[2]['y']), array($_p[3]['x'], $_p[3]['y'])), 5, '#85ff00', null));
            // $editor->text($image, $_v['layout'] , 12, $_p[0]['x'], $_p[0]['y'], new Color('#d14'), '', 0);
        }
    }

    // 模版解析
    $res = parseTpl($file_path, $_res_json, 'changde');

    // 客观题和主观题画框
    $layouts = array();
    if ($res['subject_single']) {
        $layouts[] = $res['subject_single'];
    }
    if ($res['subject_subjective']) {
        $layouts = array_merge($layouts, $res['subject_subjective']);
    }
    foreach($layouts as $k => $v) {
        $layout = $v['layout'];
        $editor->draw($image, Grafika::createDrawingObject('Rectangle', $layout['width'], $layout['height'], array($layout['left'], $layout['top']), 6, '#FF0000', null));
    }

    // 根据识别类型切割结果
    $_a = explode('/', $file_path);
    $_n = end($_a);
    $_a = explode('.', $_n);
    $_type = $_a[1];
    $_n = $_a[0] . '_' . 'visual.' . $_type; 
    $_f = get_dir_name($_n, $upload_path . '/')  . '/' . $_n;
    $editor->save($image, $upload_path . '/' . $_f, $_type === 'jpg' ? 'jpeg' : $_type);
    $res['img_visual'] = 'https://'.$_SERVER['SERVER_NAME'].'/apidir/ocr/upload/' . $_f;
    output('success', $out_type, 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => $res));
}
include('ocr_function.php');
?>