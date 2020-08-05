<?php
    // 日志
    function logger($logText) {
        $logSize = 100000; // 日志最大10M
        $logFile = 'log.txt';
        
        if (file_exists($logFile) && filesize($logFile) > $logSize) { // 如果日志超过大小，自动删除之前的一半记录
            $c = file_get_contents($logFile);
            $a = explode("\n", $c);
            $l = count($a);
            $a = array_slice($a, floor($l/2)); // 删除一半记录
            file_put_contents($logFile, implode("\n", $a));
        }
        file_put_contents($logFile, date('H:i:s') . ' ' . $logText . "\n", FILE_APPEND);
    }

    // 是否ssl
    function isSsl() {
        if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $_SERVER['REQUEST_SCHEME'] = 'https';
            $_SERVER['HTTPS'] = 'on';
        }
        if ( isset( $_SERVER['HTTPS'] ) ) {
            if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
                return true;
            }
     
            if ( '1' == $_SERVER['HTTPS'] ) {
                return true;
            }
        } elseif ( isset($_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            return true;
        }
        return true;
    }

    // 输出结果
    function output($msg, $type = 'json', $code = 200, $arv=array()) {
        $h = '';
        switch ($type) {
            case 'json':
                $h = json_encode(array(
                    'code' => $code,
                    'msg' => $msg,
                    'data' => !empty($arv['data']) ? $arv['data'] : ''
                ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            break;
            case 'jsonp':
                $h = $arv['jsonp_cb'] . '(' . json_encode(array(
                    'code' => $code,
                    'msg' => $msg,
                    'data' => !empty($arv['data']) ? $arv['data'] : ''
                ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ')';
            break;
            case 'txt':
                $h = !empty($arv['txt']) ? $arv['txt'] : $msg;
            break;
            case 'qrcode':
            
            break;
        }
        die($h);
    }

    // 获取 $_GET
    function get_g($_g = array()) {
        if (!empty($_SERVER['REQUEST_URI'])) {
            $p = parse_url($_SERVER['REQUEST_URI']);
            parse_str($p['query'], $g);
        }
        return array_merge($_g, $g);
    }

    // 生成文件名：
    function get_name() {
        return date('YmdHis', time()).rand(1000, 9999);
    }

    // Base64转图片
    function base64image_to_filepath($base64_image_content, $path = ''){
        $file_path = FALSE;
        preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result);
        $type = $result[2];
        if ($type) {
            $file_name = get_name() . '.' . $type;
            $file_path = $path. get_dir_name($file_name, $path) .'/'. $file_name;
            file_put_contents($file_path, base64_decode(str_replace($result[1], '', $base64_image_content)));
        }
        return $file_path;
    }

    // 根据文件名生成目录，并返回目录名称
    function get_dir_name($file_name = '000000', $path = '') {
        $dir_name = substr($file_name, 0, 6);
        if (!is_dir($path . $dir_name)) {
            mkdir($path . $dir_name);
        }
        return $dir_name;
    }

    // 404
    function mc_404() {
        header('HTTP/1.1 404 Not Found');
        header("status: 404 Not Found");
        die('404');
    }

    // 检查并创建数据库
    function init_database($api_name, $db) {
        $api_dir = 'apidir/' . $api_name .'/';
        if(!file_exists($api_dir .'z_install.lock')) {
            if(file_exists($api_dir .'z_install_db.txt')) {
                $r = $db->sql('r', file_get_contents($api_dir .'z_install_db.txt'));
                if (!$r) {
                    die('数据库创建失败！');
                } 
            }
            file_put_contents($api_dir. 'z_install.lock', 1);
        }
    }

    // 遍历文件夹中的图片
    function list_file($dir='', $cb) {
        $temp = scandir($dir);
        foreach($temp as $v){
           $a = $dir.'/'.$v;
           if(is_dir($a)){//如果是文件夹则执行
               if($v=='.' || $v=='..'){//判断是否为系统隐藏的文件.和..  如果是则跳过否则就继续往下走，防止无限循环再这里。
                   continue;
               }
               list_file($a, $cb);//因为是文件夹所以再次调用自己这个函数，把这个文件夹下的文件遍历出来
           }else{
               $cb($a);
           }
        }
    }

    // 清理过期文件
    function clear_outdate_file($file) {
        if (file_exists($file)) {
            $mtime = filemtime($file);
            if (time() - $mtime > 864000) { //超过10天自动清理
                unlink($file);
            }
        }
    }

    // 循环文件夹，清理过期文件
    function clear_outdate_file_in_dir($dir) {
        if (rand(1, 10) > 9) {// 十分之一的概率会触发检查 
            list_file($dir, function($file) {
                clear_outdate_file($file);
            });
        }
    }

    // 获取当前IP
    function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

    // 跨域限制策略
    function cors_domain_kocla() {
        $origin = ['http://onewx.kocla.com', 'http://beike.api.kocla.com', 'https://onewx.kocla.com', 'https://beike.api.kocla.com', 'http://localhost', 'http://127.0.0.1', 'http://localhost:8080', 'http://127.0.0.1:8080', 'http://localhost:8989', 'http://127.0.0.1:8989'];
        $AllowOrigin = 'http://localhost';
        if(in_array($_SERVER["HTTP_ORIGIN"], $origin))
        {
            $AllowOrigin = $_SERVER["HTTP_ORIGIN"];
        }
        header('Access-Control-Allow-Origin: ' . $AllowOrigin);
        header('Access-Control-Allow-Methods: POST,GET,OPTIONS,DELETE');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: token');
    }
?>