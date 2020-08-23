<?php
    $code = $a[0];
    $r = $db->sql('s1', 'mtf_url_short', 'url', 'WHERE code = \'' . $code .'\'');
    if ($r && isset($r['url'])) {
        $url = $r['url'];
        $db->sql('u', 'mtf_url_short', array('hits'=>'///hits+1'), 'WHERE code = \'' . $code .'\'');
        header('Location:' . $url);
    } else {
        mc_404();
    }
?>