<?php
/* Ocr 识别模版 */
$tpl = array(
    'changde' => array(
        'title' => array(
            'source' => array(
                'type' => 'handwriting',
                'index' => 0
            )
        ),
        'grade' => array(
            'source' => array(
                'type' => 'text',
                'index' => 1
            ),
            'cb' => function($source) {
                $ar = explode('年级', $source);
                return $ar[0] . '年级';
            } 
        ),
        'subject' => array(
            'source' => array(
                'type' => 'text',
                'index' => 1
            ),
            'cb' => function($source) {
                $ar = explode('年级', $source);
                $ar = explode('(', $ar[1]);
                return $ar[0];
            } 
        ),
        'name' => array(
            'source' => array(
                'type' => 'text',
                'word' => array(
                    'word' => '姓名',
                    'index' => 1
                )
            )
        ),
        'class' => array(
            'source' => array(
                'type' => 'text',
                'word' => array(
                    'word' => '班级',
                    'index' => 1
                ),
                'pattern'  => array(
                    'pattern' => '/.*?班/',
                    'index' => 0
                )
            )
        ),
        'room' => array(
            'source' => array(
                'type' => 'text',
                'word' => array(
                    'word' => '考场/座位号',
                    'index' => 1
                ),
                'pattern'  => array(
                    'pattern' => '/.*?号/',
                    'index' => 0
                )
            )
        ),
        'number' => array(
            'source' => array(
                'type' => 'text',
                'word' => array(
                    'word' => '准考证号',
                    'index' => 1
                )
            )
        ),
        'absent' => array(
            'source' => array(
                'type' => 'text',
                'word' => array(
                    'word' => '缺考标记',
                    'index' => 0
                ),
                'points' => array(
                    array(159, 7),
                    array(165, 7),
                    array(171, 7),
                    array(159, 9),
                    array(165, 9),
                    array(171, 9),
                    array(159, 11),
                    array(165, 11),
                    array(171, 11)
                )
            )
        ),
        'subject_single' => array(
            'source' => array(
                'type' => 'table',
                'no' => 1,
                'index' => 2
            ),
            'outtype' => 'score'
        ),
        'subject_subjective' => array(
            'source' => array(
                'type' => 'text',
                'pattern'  => array(
                    'pattern' => '/\d+.*?分/',
                    'index' => 0
                )
            ),
            'outtype' => 'crop'
        )
    )
);
?>