<?php
namespace Api\Controller;
use Think\Controller;
class ImportPostmanController extends BaseController {


    public function import(){
        $login_user = $this->checkLogin();

        $json = file_get_contents($_FILES["file"]["tmp_name"]) ;

        //$json = file_get_contents("../Public/postmanpostman_collectionV2.json") ;//test
        $json_array = json_decode($json ,1 );
        unset($json);
        if ($json_array['id']) {
            $this->_fromPostmanV1($json_array);
            return ;
        }

        if ($json_array['info']) {
            $this->_fromPostmanV2($json_array);
            return ;
        }

        $this->sendError(10101);


    }

    //从postman导入(v1版本)

    private function _fromPostmanV1($json_array){
        $login_user = $this->checkLogin();

        // TODO 这里需要检查下合法性。比如关键字检查/黑名单检查/字符串过滤


        $item_array = array(
            "item_name" => $json_array['name'] ? $json_array['name'] : 'from postman' ,
            "item_type" => '1' ,
            "item_description" => $json_array['description'] ? $json_array['description'] :'',
            "password" => time().rand(),
            "members" => array(),
            "pages" =>array(
                    "pages" => array(),
                    "catalogs" => array()
                )
            ) ;
        $level = 2 ;

        foreach ($json_array['requests'] as $key => $value) {
            if (!$value['folder']) {
               $item_array['pages']['pages'][] = $this->_requestToDoc($value); 
            }
        }

        foreach ($json_array['folders'] as $key => $value) {
            //不存在父目录的话，那就是根目录了。
            if (!$value['folder']) {
                $cat_array = array(
                    "id" => $value['id'] ,
                    "cat_name" => $value['name'] ,
                    "level" => $level ,
                    "s_number" => 99 ,
                    );
                $cat_array['pages'] =  $this->_getPageByFolders($value['id'] , $json_array ) ;
                $cat_array['catalogs'] =  $this->_getSubByFolders($value['id'] ,$value['name'] , $level + 1 , $json_array )  ;

                $item_array['pages']['catalogs'][]  = $cat_array ;
            }


        }

        D("Item")->import( json_encode($item_array) , $login_user['uid'] );
        
        //echo D("Item")->export(196053901215026 );
        //echo json_encode($item_array);
        $this->sendResult(array());


    }

    //根据postman的folders获取子页面和子目录
    //参数id为父目录的id
    private function _getSubByFolders($id , $name , $level , $json_array ){
        $return = array() ;
        foreach($json_array['folders'] as $key => $value) {
            if ($value['folder'] && $value['folder'] == $id ) {
                $cat_array = array(
                    "id" => $value['id'] ,
                    "cat_name" => $value['name'] ,
                    "level" => $level ,
                    "s_number" => 99 ,
                    );
                $cat_array['pages'] = $this->_getPageByFolders($value['id'], $json_array ) ;
                $cat_array['catalogs'] = $this->_getSubByFolders($value['id'] , $value['name']  , $level + 1 , $json_array); 
               $return[] = $cat_array ;
            }
        }

        return $return ; 

    }


    //根据postman的folders获取页面
    private function _getPageByFolders($id , $json_array ){
        $return = array() ;
        foreach ($json_array['requests'] as $key => $value) {
            if ($value['folder'] == $id ) {
               $return[] = $this->_requestToDoc($value);
            }
        }

        return $return ; 

    }



    private function _requestToDoc($request){
        $return = array() ;
        $return['page_title'] = $request['name'] ;
        $return['id'] = $request['id'] ;
        $return['s_number'] = 99 ;
        $return['page_comments'] = '' ;
        //若$return['page_title'] 为很长的url，则做一些特殊处理
        $tmp_title_array = explode("/", $return['page_title']);
        if ($tmp_title_array) {
            $tmp_title_array = array_slice($tmp_title_array, -2);// 倒数2个
            if($tmp_title_array[1])$return['page_title'] = $tmp_title_array[0]."/".$tmp_title_array[1] ;
        }
        
        $content = '  
**简要描述：** 

- '.$request['description'].'

**请求URL：** 
- ` '.$request['url'].' `
  
**请求方式：**
- '.$request['method'].' ';

if ($request['headerData']) {
$content .='

**Header：** 

|Header名|是否必选|类型|说明|
|:----    |:---|:----- |-----   |'."\n";
    foreach ($request['headerData'] as $key => $value) {
         $content .= '|'.$value["key"].' |  | text | '.$value["value"].' |'."\n";
    }
}

if ($request['rawModeData']) {
$content .= '


 **请求参数示例**

``` 
'.$request['rawModeData'].'
```

';

}

if ($request['data']) {
$content .='

**参数：** 

|参数名|是否必选|类型|说明|
|:----    |:---|:----- |-----   |'."\n";
    foreach ($request['data'] as $key => $value) {
         $content .= '|'.$value["key"].' |   |'.$value["type"].' | 无 |'."\n";
    }
}


        $return['page_content'] = $content ;
        return $return ;

    }


    //从postman导入(v2版本)
    private function _fromPostmanV2($json_array){

        $login_user = $this->checkLogin();

        // TODO 这里需要检查下合法性。比如关键字检查/黑名单检查/字符串过滤


        $item_array = array(
            "item_name" => $json_array['info']['name'] ? $json_array['info']['name']  : 'from postman' ,
            "item_type" => '1' ,
            "item_description" => $json_array['info']['description'] ? $json_array['info']['description'] :'',
            "password" => time().rand(),
            "members" => array(),
            "pages" =>array(
                    "pages" => array(),
                    "catalogs" => array()
                )
            ) ;
        $level = 2 ;
        $item_array['pages']['pages'] = $this->_getPageByItem($json_array['item'] );
        $item_array['pages']['catalogs'] = $this->_getItemByItem($json_array['item'] , 2 );
        D("Item")->import( json_encode($item_array) , $login_user['uid'] );
        
        //echo D("Item")->export(196053901215026 );
        //echo json_encode($item_array);
        $this->sendResult(array());


    }

    //获取某个目录下的所有页面
    private function _getPageByItem($item_array ){
        $return = array();
        foreach ($item_array as $key => $value) {

            //含有request，则这是一个子页面
            if ($value['request']) {
                $return[] = $this->_requestToDocV2($value['name'] ,$value['request']);
            }

        }
        return $return ;
    }

    //获取某个目录下的所有子目录
    private function _getItemByItem($item_array ,$level ){
        $return = array();
        foreach ($item_array as $key => $value) {

            //含有item，则这是一个子目录
            if ($value['item']) {
                $one_ary = array(
                    "cat_name" => $value['name'] ,
                    "level" => $level ,
                    "s_number" => 99 ,
                    "pages" => $this->_getPageByItem($value['item'], $level + 1 ), //递归
                    "catalogs" => $this->_getItemByItem($value['item'], $level + 1 ) //递归
                    );
                $return[] = $one_ary ;
            }

        }
        return $return ;
    }

    private function _requestToDocV2($name , $request){
        $return = array() ;
        $return['page_title'] = $name ;
        $return['s_number'] = 99 ;
        $return['page_comments'] = '' ;
        //若$return['page_title'] 为很长的url，则做一些特殊处理
        $tmp_title_array = explode("/", $return['page_title']);
        if ($tmp_title_array) {
            $tmp_title_array = array_slice($tmp_title_array, -2);// 倒数2个
            if($tmp_title_array[1])$return['page_title'] = $tmp_title_array[0]."/".$tmp_title_array[1] ;
        }

        $url = is_array($request['url']) ? $request['url']['raw'] : $request['url'] ;
        $rawModeData = $request['body']['mode'] == 'raw' ? $request['body']['raw']  : $request['rawModeData'] ;

        $content = '  
**简要描述：** 

- '.$request['description'].'

**请求URL：** 
- ` '.$url.' `
  
**请求方式：**
- '.$request['method'].' ';

if ($request['header']) {
$content .='

**Header：** 

|Header名|是否必选|类型|说明|
|:----    |:---|:----- |-----   |'."\n";
    foreach ($request['header'] as $key => $value) {
         $content .= '|'.$value["key"].' |  |  | '.$value["value"].' |'."\n";
    }
}

if ($rawModeData) {
$content .= '


 **请求参数示例**

``` 
'.$rawModeData.'
```

';

}

if ($request['body']['formdata']) {
$content .='

**参数：** 

|参数名|是否必选|类型|说明|
|:----    |:---|:----- |-----   |'."\n";
    foreach ($request['body']['formdata'] as $key => $value) {
         $content .= '|'.$value["key"].' |   |'.$value["type"].' | 无 |'."\n";
    }
}


        $return['page_content'] = $content ;
        return $return ;

    }

}