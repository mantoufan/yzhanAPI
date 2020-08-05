<?php
    $g = array_merge(array(
        'addon' => '', // 附加信息
        'engine' => 'pdfdwcmd', // 校正引擎：galfar / pdfdwcmd(默认) / imgmagic
        'crop' => 'auto' // 裁剪：auto 自动 / 0 不裁剪(默认)
    ), $g);
    $upload_path = dirname(__FILE__) . '/upload';
    include(ROOT.'apidir/com/img_base64_f_md5_id_res_data.php');
    foreach ($f_ar as $no => $f) {
        $img = end(explode('/', $f));
        $md5 = md5_file($f);
        $res = array(
            'data' => ''
        );
        $id = '';
        $r = $db->sql('s1', 'mtf_img_deskew', 'id, code, engine, results, msg', 'WHERE md5 = \'' . $md5 .'\' AND engine = \'' . $g['engine'] .'\'');
        if ($r && isset($r['id'])) {
            if ($r['code'] === '200') {
                $res['code'] = 200;
                $res['engine'] = $r['engine'];
                $res['data'] = $r['results'];
                $res['msg'] = $r['msg'];
            }
            $id = $r['id'];
        }
        if (!$res['data']) {
            $bin_com_dir = dirname(__file__) . '/../com/bin/';
            $bin_convert = $bin_com_dir . 'convert';
            $bin_dir = dirname(__file__) . '/bin/';
            $bin_deskew_pdfdwcmd = $bin_dir . 'deskew/pdfdwcmd';
            $bin_deskew_galfar = $bin_dir . 'deskew/galfar';
            $_tmp_ar = explode('.', $img);
            $f_name = reset($_tmp_ar);
            $dir_name = get_dir_name($f_name, $upload_path . '/');
            $f_tiff_name = $f_name . '_tmp.tiff';
            $f_tiff = $upload_path . '/' . $dir_name  . '/' . $f_tiff_name;
            $f_deskewed_name = $f_name . '_deskewed.jpg';
            $f_deskewed = $upload_path . '/' . $dir_name  . '/' . $f_deskewed_name;
            $_r = array();

            // 跳过倾斜校正
            $do_deskew = true;
            if ($des === '九点定位点不清晰') {
                $do_deskew = false;
            }
            if ($do_deskew) {
                if ($g['engine'] === 'galfar') {
                    $cmd_crop = '';
                    if ($g['crop'] === 'auto') {
                        $cmd_crop = ' -g c ';
                    }
                    $angle_crop = ' -a 45 ';
                    exec($bin_deskew_galfar . ' "' . $f . '" -o "' . $f_deskewed . '" -b FFFFFF ' . $cmd_crop . $angle_crop, $_r);
                    if(end($_r) === 'Done!') {
                        // 白边处理
                        // $_r = array();
                        // exec($bin_convert . ' "' . $f_deskewed . '"  -fuzz 80% -trim +repage "' . $f_deskewed . '"', $_r);
                        // if (!$_r[0]) {
                            $res = array('code'=>200, 'msg'=>'success', 'engine'=>'galfar', 'data'=>'https://'.$_SERVER['SERVER_NAME'].'/apidir/img/upload/' . $dir_name  . '/' . $f_deskewed_name);
                        // } else {
                        //     $res = array('code'=>-1, 'msg'=>'图片裁剪报错：'.implode(',', $_r));
                        // }
                    } else {
                        $res = array('code'=>-1, 'msg'=>'校正引擎报错：'.strtr(implode(',', $_r), array('http://galfar.vevb.net/deskew/'=>'', 'by Marek Mauder'=>'')));
                    }
                } else if ($g['engine'] === 'imgmagic') {
                    exec($bin_convert . ' "' . $f . '" -deskew 50% "' . $f_deskewed . '"', $_r);
                    if (!$_r[0]) {
                        $_r = array();
                        exec($bin_convert . ' "' . $f_deskewed . '" -deskew 50% "' . $f_deskewed . '"', $_r);
                        if (!$_r[0]) {
                            $res = array('code'=>200, 'msg'=>'success', 'engine'=>'imgmagic', 'data'=>'https://'.$_SERVER['SERVER_NAME'].'/apidir/img/upload/' . $dir_name  . '/' . $f_deskewed_name);
                        } else {
                            $res = array('code'=>-1, 'msg'=>'第二次校正报错：'.implode(',', $_r));
                        }
                    } else {
                        $res = array('code'=>-1, 'msg'=>'第一次校正报错：'.implode(',', $_r));
                    }
                } else {
                    exec($bin_convert . ' "' . $f . '" "' . $f_tiff . '"', $_r);
                    if (!$_r[0]) {
                        $_r = array();
                        exec($bin_deskew_pdfdwcmd . ' "' . $f_tiff . '" "' . $f_tiff . '" -A120 -Q90', $_r);
                        if(end($_r) === 'End') {
                            $_r = array();
                            exec($bin_convert . ' "' . $f_tiff . '" "' . $f_deskewed . '"', $_r);
                            if (!$_r[0]) {
                                unlink($f_tiff);
                                // 白边处理
                                // $_r = array();
                                // exec($bin_convert . ' "' . $f_deskewed . '"  -fuzz 80% -trim +repage "' . $f_deskewed . '"', $_r);
                                // if (!$_r[0]) {
                                    $res = array('code'=>200, 'msg'=>'success', 'engine'=>'pdfdwcmd', 'data'=>'https://'.$_SERVER['SERVER_NAME'].'/apidir/img/upload/' . $dir_name  . '/' . $f_deskewed_name);
                                // } else {
                                //     $res = array('code'=>-1, 'msg'=>'图片裁剪报错：'.implode(',', $_r));
                                // }
                            } else {
                                $res = array('code'=>-1, 'msg'=>'jpg生成失败：'.implode(',', $_r));
                            }
                        } else {
                            $res = array('code'=>-1, 'msg'=>'校正引擎报错：'.implode(',', $_r));
                        }
                    } else {
                        $res = array('code'=>-1, 'msg'=>'tiff生成失败：'.implode(',', $_r));
                    }
                }
            }

            // 图像清晰度增强处理
            $do_enhance = true;
            if (isset($res['code']) && $res['code'] === -1) {
                $do_enhance = false;
            }
            if ($do_enhance) {
                $engine = array(isset($res['engine']) ? $res['engine'] :'');
                $_r = array(); 
                exec($bin_convert . ' -brightness-contrast -50x50 -level 20%,80% "' . $f_deskewed . '" "' . $f_deskewed . '"', $_r);
                if (!$_r[0]) {
                    $res = array('code'=>200, 'msg'=>'success', 'engine'=>$engine, 'data'=>'https://'.$_SERVER['SERVER_NAME'].'/apidir/img/upload/' . $dir_name  . '/' . $f_deskewed_name);
                } else {
                    $res = array('code'=>-1, 'msg'=>'定位点清晰化报错：'.implode(',', $_r));
                }
            }
        }
        $value = array(
            'img'=>$img, 
            'code'=>$res['code'], 
            'engine'=>$res['engine'], 
            'results'=>$res['data'],
            'msg'=>$res['msg'],
            'des'=>$des,
            'addon'=>$g['addon']
        );
        if ($id) {
            $db->sql('u', 'mtf_img_deskew', array_merge($value, array('hits'=>'///hits+1')), 'WHERE id = \'' . $id .'\'');
        } else {
            $db->sql('i', 'mtf_img_deskew', array_merge($value, array('md5'=>$md5, 'add_time'=>date('Y-m-d H:i:s'))));
        }
        if ($res['code'] === 200) {
            $data_ar[] = $res['data'];
        } else {
            $data_ar[] = '';
            $msg_ar[] = ($_img_base64_is_array ? '第'. ($no + 1).'张' : '') . '图片校正失败，原因：'.$res['msg'];
            logger(($_img_base64_is_array ? '第'. ($no + 1).'张' : '') . '图片校正失败，原因：'.$res['msg'] . ' 图片：' . $f);
        }
    }
    output(count($msg_ar) > 0 ? implode(',', $msg_ar) : 'success', $out_type, $msg_ar_len === $_img_base64_ar_len ? -1 : 200, array('jsonp_cb' => $g['jsonp_cb'], 'data' => array('deskewed_img_path' => $_img_base64_is_array ?  $data_ar : $data_ar[0])));
?>