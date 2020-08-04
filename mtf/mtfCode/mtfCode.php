<?php
require_once 'vendor/autoload.php';
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Ocr\V20181119\OcrClient;
use TencentCloud\Ocr\V20181119\Models\QrcodeOCRRequest;

class mtfCode{
	private $_root;
	public function __construct()
    {	
		$_root = str_replace('\\','/',dirname(__file__)).'/';
		$this->_root = $_root;
	}

	// 生成文件名：
    private function getName() {
        return date('YmdHis', time()).rand(1000, 9999);
	}

	// 根据文件名生成目录，并返回目录名称
    private function getDirName($file_name = '000000', $path = '') {
        $dir_name = substr($file_name, 0, 6);
        if (!is_dir($path . $dir_name)) {
            mkdir($path . $dir_name);
        }
        return $dir_name;
    }
	
	public function deQRCode($_f_p, $tmp_path = '')
	{
		if(file_exists($_f_p)){
			
			$source = imagecreatefromstring(file_get_contents($_f_p));

			if (!$source) {
				return array(
					'code'  =>  -1,
					'msg'  =>  '不支持的图片格式'
				);
			}

			$_pre = $this->getName() . '_tmp';
			$_dir = $this->getDirName($_pre, $tmp_path) . '/';
			$_dir_p = $_dir. $_pre . '.jpg';
			$tmp_dir = $tmp_path . $_dir;
			$_f_p = $tmp_dir . $_pre . '.jpg';
			
			imagejpeg($source, $_f_p, 70);
			$source = imagecreatefromstring(file_get_contents($_f_p));
			
			//保留颜色数目
			$_num = array(16);
			//将图片分成九等分，提高识别
			$w  =  5;
			$h  =  3;
			
			$_ar = array();
			$_ar[] = $_f_p;
			
			foreach($_num as $_k => $_v){
				
				imagetruecolortopalette($source, FALSE, $_v);//禁止抖动，避免颜色接近
				$_f_p = $tmp_dir . $_pre . '_' . $_v . '.png';
				imagepng($source, $_f_p);

				list($width, $height)  =  getimagesize($_f_p);
				$newwidth  =  floor($width / $w);
				$newheight  =  floor($height / $h);
				
				for( $i = 0 ; $i< $w; $i++ ){
					$_pw  =  $newwidth * $i;
					for( $j = 0 ; $j< $h; $j++ ){
						$_ph  =  $newheight * $j;
						$thumb  =  ImageCreateTrueColor($newwidth, $newheight);
						imagecopyresized( $thumb, $source, 0, 0, $_pw, $_ph, $width,  $height, $width, $height);
						$_p = $tmp_dir . $_pre  . '_' . $_v . '_' . $i . '_' . $j . '.jpg';
						imagejpeg( $thumb , $_p ,87);
						$_ar[] = $_p;
					}
				}
				unlink($_f_p);
			}
			
			$_is_have_qrcode = false;
			$_root = $this->_root;
			
			foreach ($_ar as $k => $_f_p) {
				exec('"'.$_root.'bin/Win32/Zbar/zbarimg.exe" -D "'.$_f_p.'" -q', $_s);
				if($_s){
					
					$_data = array();
					foreach ($_s as $_k => $_v) {
						$_tmp_ar = explode(':', $_v);
						$_tmp_type = strtoupper($_tmp_ar[0]);
						if ($_tmp_type === 'QR-CODE') {
							$_is_have_qrcode = true;
						}
						unset($_tmp_ar[0]);
						array_push($_data, array(
							'type' => $_tmp_type,
							'txt' => implode(':', $_tmp_ar)
						));
					}
					if ($_is_have_qrcode) {
						$this->_del($_ar);
						return array(
							'code' => 200,
							'msg' => 'success',
							'data' => $_data,
							'txt' => implode("\n", $_s),
							'engine' => 'zbar'
						);	
					}
					
				} elseif ($k === 0){
					/*暂停PHP扫码，效率太低，超过8秒
					
					*/
				}
			}

			if (!$_is_have_qrcode) {// 请求腾讯API
				try {
					$cred = new Credential("AKIDRcYEdrXPJx5bx1wgITQ7uj5B1vbsvnoO", "Vm8G9qHbfSyMjtpXmrjPFsSmsi5D5Thi");
					$httpProfile = new HttpProfile();
					$httpProfile->setEndpoint("ocr.tencentcloudapi.com");
					  
					$clientProfile = new ClientProfile();
					$clientProfile->setHttpProfile($httpProfile);
					$client = new OcrClient($cred, "ap-beijing", $clientProfile);
				
					$req = new QrcodeOCRRequest();
					
					$params = '{"ImageUrl":"https://'.$_SERVER['SERVER_NAME'].'/apidir/qrcode/upload/' . $_dir_p . '"}';
					$req->fromJsonString($params);
					$resp = $client->QrcodeOCR($req);
					$_res = json_decode($resp->toJsonString(), true);
					if ($_res) {
						if (isset($_res["CodeResults"]) && $_res["CodeResults"]) {
							$codes = $_res["CodeResults"];
							if (count($codes) > 0) {
								$_data = array();
								foreach($codes as $k => $v) {
									array_push($_data, array(
										'type' => str_replace('_', '-', $v['TypeName']),
										'txt' => $v['Url']
									));
								}
								$this->_del($_ar);
								return array(
									'code' => 200,
									'msg' => 'success',
									'data' => $_data,
									'txt' => '',
									'engine' => 'qqcloud'
								);
							}
						}
					}
				} catch(TencentCloudSDKException $e) {
					$this->_del($_ar);
					return array(
						'code' => -1,
						'msg' => $e
					);
				}
			}
			$this->_del($_ar);
			return array(
				'code'  =>  -1,
				'msg'  =>  '未能找到条码'
			);
		} else {
			return array(
				'code'  =>  -1,
				'msg'  =>  '图片存储失败'
			);
		}
	}
	
	public function enQRCode($_s)
	{
		include_once($_root.'QRcode/En/qrcode.class.php');
		// 纠错级别：L、M、Q、H
		$errorCorrectionLevel  =  'H';  
		// 点的大小：1到10
		$matrixPointSize  =  4;  
		//创建一个二维码文件
		QRcode::png($_s, false, $errorCorrectionLevel, $matrixPointSize, 2);
	}
	
	private function _del($_ar){
		foreach($_ar as $k => $_f_p){
			unlink($_f_p);
		}
	}

	private function object_to_array($obj) {
		$obj = (array)$obj;
		foreach ($obj as $k => $v) {
			if (gettype($v) == 'resource') {
				return;
			}
			if (gettype($v) == 'object' || gettype($v) == 'array') {
				$obj[$k] = (array)object_to_array($v);
			}
		}
	 
		return $obj;
	}
}
?>