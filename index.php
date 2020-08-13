<?php /*Author：小宇 Date: 2020-05-21 00:38*/
    define('ROOT', str_replace('\\','/',dirname(__file__)).'/');
    include('apidir/config.php');include('apidir/function.php');include('mtf/mtfMysql/mtfMysql.php');include('mtf/mtfSafety/mtfSafety.php');
    $mtfSafety = new mtfSafety();
    $mtfSafety -> safety();
    $db = new mtfMysql(array('host' => $c['db']['host'], 'user' => $c['db']['usr'], 'password' => $c['db']['psd'], 'database' => $c['db']['dbname']));
    header("Content-type: text/html; charset=utf-8");
    if (file_exists('.htaccess')) {
        $_tmp_ar = explode('mtfq=',@$_SERVER['REDIRECT_QUERY_STRING'] ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING']);
        $_tmp_ar = explode('?', end($_tmp_ar));
        $q = reset($_tmp_ar);

        if (stripos($_SERVER['SERVER_NAME'], 'api.') !== FALSE) {
            $q = 'api/' . $q;
        }

        $a = explode('/', $q);
        if ($a[0] === 'api' && !empty($a[1])){
            header("Access-Control-Allow-Origin: *");
            $api_name = $a[1];
            if ($api_name) {// 接口加载器
                $g = get_g(array(
                    'des' => '', //用途描述
                    'out_type' => '', // 输出结果类型
                    'jsonp_cb' => '', // JSONP 回调函数
                ));
                $out_type = trim($g['out_type']);
                if (!$out_type) {output('缺少参数 输出结果类型：out_type', 'json', -1, array('jsonp_cb' => $g['jsonp_cb']));}
                $des = trim($g['des']);
                if (!$des) {output('缺少参数 用途描述：des', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));}

                if (file_exists('apidir/' . $a[1] . '/' .$a[1]. '.php')) {
                    $api_action = isset($a[2]) ? $a[2] : '';
                    init_database($api_name, $db);
                    include('apidir/' . $api_name . '/' .$api_name. '.php');
                    output('接口方法未找到', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
                } else {
                    output('接口未找到', $out_type, -1, array('jsonp_cb' => $g['jsonp_cb']));
                }
            } else {mc_404();}
        } else if ($a[0]) {
            $api_action = 'redirect';
            include('apidir/short/short.php');
        } else {
            die('深圳市考拉超课科技股份有限公司 试卷和阅卷接口服务 粤ICP备12081495号 增值业务经营许可证：粤B2-20100246');
        }
    } else {
        file_put_contents('.htaccess', 'RewriteEngine On
        RewriteRule \.(js|css|svg|gif|png|jpg|jpeg|swf|ico|html|txt|webp)$ - [L]
        RewriteCond $1 !^(_) 
        RewriteRule ^(.*)$ index.php?mtfq=$1');
        die('安装成功！');
    }
?>