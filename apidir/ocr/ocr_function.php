<?php
    use Grafika\Grafika;
    include_once('mtf/thirdParty/Grafika/autoloader.php');
    // 把点分布成两侧
    function dividePointSides($center_x, $rects = array()) {
        $_top_min = 0;
        $_top_max = 0;

        $_x_min = 0;
        $_x_max = 0;

        $_last_top_min = 0;
        $_last_top_max = 0;
        $_last_left_min = 0;
        $_last_left_max = 0;
        $_last_right_min = 0;
        $_last_right_max = 0;

        $_rects = array(
            0 => array(), // 原始数组
            1 => array(), // 左栏
            2 => array(), // 中栏（三栏） 右栏（两栏）
            3=> array() // 右栏（三栏）
        );

        foreach($rects as $k => $_rect) {
            $_rects[0][] = $_rect;
            $rect =  $_rect['layout_location'];
            $_is_avalible = true;
            
            $_x_min = getMaximum($rect, 'x', 'min');
            $_x_max = getMaximum($rect, 'x', 'max');
            
            if ($_x_max < $center_x) {
                if ($_rect['layout']) {
                    if (!isset($_rects[1][$_rect['layout']])){
                        $_rects[1][$_rect['layout']] = array();
                    }
                    $_rects[1][$_rect['layout']][] = $_rect;
                }

                if ($_last_left_min && $_last_left_max) {
                    $_last_left_min = min($_x_min, $_last_left_min);
                    $_last_left_max = max($_x_max, $_last_left_max);
                } else {
                    $_last_left_min = $_x_min;
                    $_last_left_max = $_x_max;
                }
            } else if ($_x_min > $center_x) {
                if ($_rect['layout']) {
                    if (!isset($_rects[2][$_rect['layout']])){
                        $_rects[2][$_rect['layout']] = array();
                    }
                    $_rects[2][$_rect['layout']][] = $_rect;
                }

                if ($_last_right_min && $_last_right_max) {
                    $_last_right_min = min($_x_min, $_last_right_min);
                    $_last_right_max = max($_x_max, $_last_right_max);
                } else {
                    $_last_right_min = $_x_min;
                    $_last_right_max = $_x_max;
                }
            } else {
                // 横跨左右页的区域，舍弃
                $_is_avalible = false;
            }

            if ($_is_avalible) {
                $_top_min = getMaximum($rect, 'y', 'min');
                $_top_max = getMaximum($rect, 'y', 'max');

                if ($_last_top_min && $_last_top_max) {
                    $_last_top_min = min($_top_min, $_last_top_min);
                    $_last_top_max = max($_top_max, $_last_top_max);
                } else {
                    $_last_top_min = $_top_min;
                    $_last_top_max = $_top_max;
                }
            }
        }
        return array(
            'left_min' => $_last_left_min,
            'left_max' => $_last_left_max,
            'right_min' => $_last_right_min,
            'right_max' => $_last_right_max,
            'top_min' => $_last_top_min,
            'top_max' => $_last_top_max,
            'rects' => $_rects
        );
    }

    // 取任意四个点的最值
    function getMaximum($rect, $xy, $minormax) {
        $queue = array(0, 1, 2, 3);
        $_queue = array();
        foreach ($queue as $k => $v) {
            $_queue[] = $rect[$v][$xy];
        }
        if ($minormax === 'min') {
            return min($_queue);
        } else {
            return max($_queue);
        }
    }

    // 寻找指定关键词的名称
    function searchByWord($_res_json, $_word = '', $_pattern = '', $multi = false) {
        $_index_word = '';
        $_index_pattern = '';
        $_index_words = array();
        $_index_patterns = array();
        $_res = $_res_json['results'];
        $_len = isset($_word['word']) ? strlen($_word['word']) : 0;
        foreach($_res as $_k => $_v) {
            $word = $_len ? substr($_v['words']['word'], 0, $_len) : $_v['words']['word'];
            if (!$_index_word || $multi) {
                if (isset($_word['word']) && $_word['word'] === $word) {
                    $_index_word = $_k + $_word['index'];
                    if (!$multi) {
                        break;
                    } else {
                        $_index_words[] = $_index_word;
                    }
                }
            }
            if (!$_index_pattern || $multi) {
                preg_match($_pattern['pattern'], $word, $matches);
                if ($matches[0]) {
                    $_index_pattern = $_k + $_pattern['index'];
                    if (!$multi) {
                        break;
                    } else {
                        $_index_patterns[] = $_index_pattern;
                    }
                }
            }
        }
        if ($multi) {
            return count($_index_words) ? $_index_words : (count($_index_patterns) ? $_index_patterns : FALSE);
        } else {
            return $_index_word ? $_index_word : ($_index_pattern ? $_index_pattern : FALSE);
        }
    }

    // Ocr模版解析
    function parseTpl($file_path, $_res_json, $_tpl_name) {
        global $upload_path;
        $editor = Grafika::createEditor();
        $editor->open($image, $file_path);
        $_w = $image->getWidth();
        $_h = $image->getHeight();

        $img = $image->getCore();

        // 获取中心坐标
        $_center_x = round($_w / 2);
        $_center_y = round($_h / 2);
        
        include_once('ocr_tpl.php');
        $tpl = $tpl[$_tpl_name];
        $layouts = dividePointSides($_center_x, $_res_json['layouts']);
        $res = array();
        foreach($tpl as $k => $v) {
            $source = $v['source'];
            $source_index = isset($source['index']) ? $source['index'] : FALSE;
            $source_word = $source['word'];
            $source_pattern = $source['pattern'];

            $points_points = isset($source['points']) ? $source['points'] : '';
            
            $outtype = isset($v['outtype']) ? $v['outtype'] : 'txt';

            if (isset($v['cb'])) {
                $cb = $v['cb'];
            } else {
                $cb = '';
            }

            switch($outtype) {
                case 'score':
                    $res[$k] = $layouts['rects'][$source['no']][$source['type']][$source['index']]['layout_location'];
                break;
                case 'crop':
                    $_indexs = searchByWord($_res_json, $source_word, $source_pattern, true);
                    if (!$_indexs) {
                        $res[$k] = '';
                    } else {
                        $res[$k] = array(
                            'layout_location' => array(),
                        );
                        foreach ($_indexs as $_k => $_index) {
                            $res[$k]['layout_location'][] = $_res_json['results'][$_index];
                        }
                        $res[$k]['layout_location'] = sortNumberByPoints($res[$k]['layout_location']);
                    }
                break;
                default:// 默认返回文本
                    $_index = $source_index !== FALSE? $source_index : searchByWord($_res_json, $source_word, $source_pattern);
                    if ($_index !== FALSE) {
                        $res[$k] = $_res_json['results'][$_index]['words']['word'];
                        if ($points_points) {
                            $points_total = count($points_points);
                            $points_black_num = 0;
                            $words_location = $_res_json['results'][$_index]['words']['words_location'];
                            foreach($points_points as $points_points_k => $points_points_v) {
                                if(isBlackByPoint($img, $words_location['left'] + $points_points_v[0], $words_location['top'] + $points_points_v[1])) {
                                    // 自动给一个点，生成矩形区域，判断该区域是否是黑色
                                    $points_black_num++;
                                }
                                $editor->draw($image, Grafika::createDrawingObject('Rectangle', 1, 1, array($words_location['left'] + $points_points_v[0], $words_location['top'] + $points_points_v[1]), 8, '#93c46b', null));
                            }
                            $res[$k] = round($points_black_num / $points_total, 2);
                        }
                    }
                    if ($cb) {
                        $res[$k] = $cb($res[$k]);
                    }
                break;
            }
        }

        $_a = explode('/', $file_path);
        $_n = end($_a);
        $_a = explode('.', $_n);
        $_type = $_a[1];
        $_n = $_a[0] . '_' . 'tpl.' . $_type; 
        $_f = get_dir_name($_n, $upload_path . '/')  . '/' . $_n;
        $editor->save($image, $upload_path . '/' . $_f, $_type === 'jpg' ? 'jpeg' : $_type);

        return $res;
    }

    // 判断某点是否为黑色
    function isBlackByPoint($res, $x, $y){//求某座标的灰度值
        $m255 = 200;
        $rgb = imagecolorat($res, $x, $y);
        $rgbarray = imagecolorsforindex($res, $rgb);
        $r = $rgbarray['red'] * 0.333;
        $g = $rgbarray['green'] * 0.333;
        $b = $rgbarray['blue'] * 0.333;
        $t = round(($r + $g + $b) /$m255);
        return $t == 0 ? true : false;
    }

    // 判断点是否在某矩形区域（四点组成）
    function isPointInRect($p = array('x' => '', 'y' => '', 'top' => '', 'left' => ''), $rect = array()) {
        $res = false;
        $_x_min = getMaximum($rect, 'x', 'min');
        $_x_max = getMaximum($rect, 'x', 'max');
        $_y_min = getMaximum($rect, 'y', 'min');
        $_y_max = getMaximum($rect, 'y', 'max');
        if ($p['x'] > $_x_min && $p['x'] < $_x_max && $p['y'] > $_y_min && $p['y'] < $_y_max) {
            $res = true;
        }
        return $res;
    }

    // 将题号根据坐标，按顺序排序
    function sortNumberByPoints($results = array()) {
        // $_ar_x = array();
        // $_ar_x_differ = array();
        // foreach($results as $k => $v) {
        //     $_ar_x[] = floor($v['words']['words_location']['left']);
        // }
        // sort($_ar_x);
        // foreach($_ar_x as $k => $v) {
        //     if (isset($_ar_x[$k + 1])) {
        //         $_ar_x_differ[$k] = $_ar_x[$k + 1] - $_ar_x[$k];
        //     } 
        // }
        // sort($_ar_x_differ);
        // $step = 36;
        // foreach($_ar_x_differ as $k => $v) {
        //     if ($v > 36) {
        //         $step = $v;
        //         break;
        //     }
        // }
        // $step = max(36, $step / 2);

        $step = 100;
        foreach($results as $k => $v) {
            $results[$k]['h'] = round($v['words']['words_location']['left'] / $step);
            $results[$k]['order'] = round($v['words']['words_location']['left'] / $step) * $step * 10 + floor($v['words']['words_location']['top']);
        }
        $orders = array_column($results,'order');
        array_multisort($orders, SORT_ASC, SORT_NUMERIC, $results);
        return $results;
    }
?>