<?php
/** 接口频率限制 */
class mtfSafety {
  private $_root;
	public function __construct()
  {	
    $_root = str_replace('\\','/',dirname(__file__)).'/';
    $this->_root = $_root;
  }
  public function safety() {
    // ini_set('max_execution_time','15');
    // //代理IP直接退出
    // empty($_SERVER['HTTP_VIA']) or exit('Access Denied');
    // //防止快速刷新
    // session_start();
    // $seconds = '1'; //时间段[秒]
    // $refresh = '3'; //刷新次数
    // //设置监控变量
    // $cur_time = time();
    // if(isset($_SESSION['last_time'])){
    //   $_SESSION['refresh_times'] += 1;
    // }else{
    //   $_SESSION['refresh_times'] = 1;
    //   $_SESSION['last_time'] = $cur_time;
    // }
    // //处理监控结果
    // if($cur_time - $_SESSION['last_time'] < $seconds){
    //   if($_SESSION['refresh_times'] >= $refresh){
    //       //跳转至攻击者服务器地址
    //       exit(json_encode(array(
    //         'code' => -1,
    //         'msg' => '超过请求频次'
    //       )));
    //   }
    // }else{
    //   $_SESSION['refresh_times'] = 0;
    //   $_SESSION['last_time'] = $cur_time;
    // }
  }
}
?>