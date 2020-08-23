<?php
    $g = array_merge(array(
        'url' => '' // long url
    ), $g);
    $short_url = $g['url'];
    if (!$short_url) {
        output('缺少参数 网址：url', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }
    $html = ''; $f = 0; $post = ''; $ua = ''; $url = ''; $headers = ''; $echohtml = false; $echoheader = true;
    //保留原始网址
    $orign_url = $short_url;
    //前置网址处理
    if (stripos($short_url,'tieba.baidu.com/mo/q/checkurl') !== FALSE){
        $a = parse_url($short_url);
        $q = $a['query'];
        parse_str($q, $a);
        $short_url = $a['url'];
        //https://tieba.baidu.com/mo/q/checkurl?url = https%3A%2F%2Fggddd2.com%2Fintr%2F51b1dafd6cccf772
    }
    //短网址
    if ( stripos($short_url,'dwz.cn') !== FALSE) {//国外需使用ssl
        //$short_url = str_replace('http://','https://', $short_url);
        $headers = array('Content-Type:application/json', 'Token:7dd52c989ca6e88dcaac7dbcb20fba0e');
        $post = json_encode(array('shortUrl' => $short_url));
        $echohtml = true;
        $echoheader = false;
        $short_url = 'https://dwz.cn/admin/v2/query';
    } elseif (stripos($short_url,'t.im')!== FALSE){
        $post = array('m' => 1);
    } elseif (stripos($short_url,'suo.im') !== FALSE) {//默认手机浏览器UA会检测安全性，应为电脑UA
        $ua = 'User-Agent,Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0;';
    }
    $true_url = get_redirect_url($short_url, $f, $post, $ua, $headers, $echohtml, $echoheader);
    if($true_url){
        if (stripos($short_url,'dwz.cn') !== FALSE) {
            $_data = json_decode($true_url,true);
            $url = $_data['LongUrl'];
        }else{
            $url = $true_url;
        }
    }else{
        if (stripos($short_url,'t.im') !== FALSE) {
            include_once('../../mtf/QueryList/autoload.php');
            $data = QL\QueryList::Query($html,array(
            'url' => array('.panel-body>p>a','text')
            ))->data;
            $url = $data[0]['url'];
        } elseif ($short_url!== $orign_url) {
            $url = $short_url;
        }
    }
    if ($url) {
        header("Cache-Control: static, max-age = 604800");//缓存7天，注意CDN中的配置，腾讯CDN会遵守此处的配置
        output('success', $out_type, 200, array('jsonp_cb'  => $g['jsonp_cb'], 'data'  => array('url'  => $url), 'txt'  => $url));
    }else{
        header('HTTP/1.1 500 Internal Server Error');
        output('还原网址失败', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
    }

    function get_redirect_url($url, $f  =  0, $post  =  '', $ua  =  '', $headers  =  '', $echohtml  =  false, $echoheader  =  true) {
        global $html;
        $ch  =  curl_init($url);
        //curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_HEADER, $echoheader?TRUE:FALSE); //输出header
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);//必须输出body（例如dwz.cn）
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);// 禁止自动输出内容
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $f?TRUE:FALSE);// 自动跳转 
        curl_setopt($ch, CURLOPT_MAXREDIRS, $f);
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);// 跳转时自动设置来源地址
        curl_setopt($ch, CURLOPT_URL, $url);// 设置URL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_REFERER, '');//设置来源
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);//5秒超时
        curl_setopt($ch,CURLOPT_USERAGENT, $ua?$ua:'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12A365 Safari/600.1.5');//（适应url.163.com）
        if($post){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        if($headers){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $html  =  curl_exec($ch);
        if($echohtml){
            return $html;
        }
        preg_match_all('/^Location:(.*)$/mi', $html, $matches);
        curl_close($ch);
        return !empty($matches[1]) ? trim($matches[1][0]) : '';
    }
?>