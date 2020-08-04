<?php
    function get_root_domain($_domain) {// 获取根域名
        $a = explode('.', $_domain);
        $len = count($a);
        $res = array();
        if ($len > 1) {
            $res[2] = $a[$len - 2] . '.' . $a[$len - 1];
        } else {
            $res = array(
                2 => '',
                3 => ''
            );
        }
        if ($len > 2) {
            $res[3]  = $a[$len - 3] . '.' . $res[2];
        }
        return $res;
    }
?>