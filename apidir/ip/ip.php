<?php
    $file_name = dirname(__FILE__) . '/ip_' . $api_action . '.php';
    if (file_exists($file_name)) {
        include($file_name);
    }
?>