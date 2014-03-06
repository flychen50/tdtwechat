<?php
//装载模板文件
include_once("wx_tpl.php");
include_once("bus.php");
//获取微信发送数据
$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

  //返回回复数据
if (!empty($postStr)){
          
        //解析数据
          $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        //发送消息方ID
          $fromUsername = $postObj->FromUserName;
        //接收消息方ID
          $toUsername = $postObj->ToUserName;
     //消息类型
          $form_MsgType = $postObj->MsgType;
          
          //地理位置,本地天气
          if($form_MsgType=="location")
          {
            //获取地理消息信息，经纬度，地图缩放比例，地址
            $from_Location_X=$postObj->Location_X;
            $from_Location_Y=$postObj->Location_Y;
            $from_Location_Scale=$postObj->Scale;
            $from_Location_Label=$postObj->Label;
           
           $dt =  date("Y-m-d H:i:s" ,strtotime("now" ));
 	$mysql = new SaeMysql();
            	$sql = "INSERT INTO  `app_tianditu`.`location` (
`weixinid` ,
`lon` ,
`lat` ,
`Scale` ,
`label`,
`currenttime` 
)
VALUES (
'$fromUsername',  '$from_Location_X',  '$from_Location_Y',  '$from_Location_Scale',  '$from_Location_Label','$dt'
);";
	sae_debug($sql);
	$mysql->runSql( $sql );
	if( $mysql->errno() != 0 )
	{
		die( "Error:" . $mysql->errmsg() );
	}
            //地址解析使用百度地图API的链接
            $map_api_url="http://api.map.baidu.com/geocoder?";
            //坐标类型
            $map_coord_type="&coord_type=wgs84";
            //建立抓取对象
            $f = new SaeFetchurl();
            //抓取百度地址解析
            $geocoder = $f->fetch($map_api_url.$map_coord_type."&location=".$from_Location_X.",".$from_Location_Y);
            //如果抓取地址解析成功
            if($f->errno() == 0)
            {
              //匹配出城市
                preg_match_all( "/\<city\>(.*?)\<\/city\>/", $geocoder, $city ); 
                $city=str_replace(array("市","县","区"),array("","",""),$city[1][0]);
              //通过新浪天气接口查询天气的链接
                $weather_api_url="http://php.weather.sina.com.cn/xml.php?password=DJOYnieT8234jlsK";
              //城市名转字符编码
                $city="&city=".urlencode(iconv("UTF-8","GBK",$city));
              //查询当天
                $day="&day=0";
              //抓取天气
                $weather = $f->fetch($weather_api_url.$city.$day);
              //如果抓取到天气
               if($f->errno() == 0 && strstr($weather,"Weather"))
              {
                //用正则表达式获取数据
                preg_match_all( "/\<city\>(.*?)\<\/city\>/", $weather, $w_city);
                preg_match_all( "/\<status2\>(.*?)\<\/status2\>/", $weather, $w_status2);
                preg_match_all( "/\<status1\>(.*?)\<\/status1\>/", $weather, $w_status1);
                preg_match_all( "/\<temperature2\>(.*?)\<\/temperature2\>/", $weather, $w_temperature2);
                preg_match_all( "/\<temperature1\>(.*?)\<\/temperature1\>/", $weather, $w_temperature1);
                preg_match_all( "/\<direction2\>(.*?)\<\/direction2\>/", $weather, $w_direction2);
                preg_match_all( "/\<power2\>(.*?)\<\/power2\>/", $weather, $w_power2);
                preg_match_all( "/\<chy_shuoming\>(.*?)\<\/chy_shuoming\>/", $weather, $w_chy_shuoming);
                preg_match_all( "/\<savedate_weather\>(.*?)\<\/savedate_weather\>/", $weather, $w_savedate_weather);
                //如果天气变化一致
                if($w_status2==$w_status1)
                {
                        $w_status=$w_status2[1][0];
                }
                else
                {
                        $w_status=$w_status2[1][0]."转".$w_status1[1][0];
                }
                $current_locat = tdtgeocode($from_Location_Y,$from_Location_X);
                $url = "http://1.tianditu.sinaapp.com/ttt.php?user=".$fromUsername;
                //将获取到的数据拼接起来
                $weather_res=array(
                	"当前位置：".$current_locat,
                  "------------------\n",
                $w_city[1][0]."天气预报",
                "发布：".$w_savedate_weather[1][0],
                "气候：".$w_status,
                "气温：".$w_temperature2[1][0]."-".$w_temperature1[1][0],
                //"风向：".$w_direction2[1][0],
                //"风力：".$w_power2[1][0],
                //"穿衣：".$w_chy_shuoming[1][0],
                "------------------\n天地图周边搜索，请输入超市，酒店,饭馆等关键词",
                "-------------------\n\n<a href='$url'>我的历史位置</a>"
                );
                $weather_res=implode("\n",$weather_res);
                //$weather_res.="\n 附近搜索，请输入超市，酒店等搜索关键词"；
                
               
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $weather_res);
                echo $resultStr;
              }
              else
              {
                //失败提示
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "天气获取失败");
                echo $resultStr;
              }
            }
            else
            {
              //失败提示
              $msgType = "text";
              $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "无法获取地理位置");
              echo $resultStr;
            }
            exit;
          }

    //图片消息
          if($form_MsgType=="image")
          {
            //获取发送的图片URL
            $from_PicUrl=$postObj->PicUrl;
             //创建新图片的名称
            $filename=$fromUsername.date("YmdHis").".jpg";
            //建立抓取图片类
            $f = new SaeFetchurl();
            //抓取图片
            $res = $f->fetch($from_PicUrl);
            //如果抓取到图片
            if($f->errno() == 0) 
            {
              //新建存储类
              $s = new SaeStorage();
              //写入图片
          $s->write( 'weixincourse' , $filename , $res );
              if($s->errno()==0)
              {
                //成功提示
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "图片上传成功");
                echo $resultStr;
              }
              else
              {
               //失败提示
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "图片保存失败");
                echo $resultStr;
              }
            }
            else
            {
              //失败提示
              $msgType = "text";
              $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "图片上传失败");
              echo $resultStr;
            }
            exit;                                   
          }
    //文字消息
          if($form_MsgType=="text")
          {
              
           //获取用户发送的文字内容
            $form_Content = trim($postObj->Content);

              
              
       //如果发送内容不是空白回复用户相同的文字内容
        if(!empty($form_Content))
            {
            
                $mysql = new SaeMysql();
                 $dt =  date("Y-m-d H:i:s" ,strtotime("now" ));
              $sql = "INSERT INTO  `app_tianditu`.`log` (
`weixinid` ,
`keyword` ,
`querytime` 

)
VALUES (
'$fromUsername',  '$form_Content','$dt'
);";
  sae_debug($sql);
  $mysql->runSql( $sql );
  if( $mysql->errno() != 0 )
  {
    die( "Error:" . $mysql->errmsg() );
  }


              //回复菜谱类别
                if($form_Content=="菜谱")
                {
                    
                  $return_str="请输入字母编码浏览相应菜品：\n\n";
                  $return_arr=array("lc.冷菜\n","hb.杭帮菜\n","sk.烧烤\n","wp.外婆烧\n","ml.麻辣\n","rc.热菜\n","tp.甜品");
                  $return_str.=implode("",$return_arr);
                  $msgType = "text";
                  $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $return_str);
                  echo $resultStr;
                  exit;                                   
                
                }
 

                
              //表情回复歌曲
              if($form_Content=="/::)")
              {
                
        $msgType = "music";
                $resultStr = sprintf(
                     $musicTpl, 
                         $fromUsername, 
                         $toUsername, 
                         $time, 
                         $msgType, 
                         "我的歌声里",
                         "曲婉婷",
                         "http://weixincourse-weixincourse.stor.sinaapp.com/mysongs.aac",
                         "http://weixincourse-weixincourse.stor.sinaapp.com/mysongs.mp3");
                echo $resultStr;
                exit;
                                        
              }


                  //$resultStr = tdtURLInfo($form_Content,$fromUsername,$toUsername);
                  //if(!is_null($resultStr)){
                    //echo $resultStr;

                    //exit;
                  //}


              //默认回复
                $msgType = "text";
               $all = queryResponse($form_Content,$fromUsername, $toUsername);
               echo $all;
                //$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $all);
                //echo $resultStr;
                exit;                                   
            }
            //否则提示输入
            else
            {
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, "请输入些什么吧……");
                echo $resultStr;
                exit;                                   
            }          
          }
        
          
        //事件消息
          if($form_MsgType=="event")
          {
            //获取事件类型
            $form_Event = $postObj->Event;
            //退订事件
            
             if($form_Event=="unsubscribe")
            {
              //新建存储类
              $s = new SaeStorage();
              //写入文件
          $s->write( 'weixincourse' , $fromUsername.".txt" , date("Y/m/d H:i:s")."退订！" );
              exit;
            }
            
            //订阅事件
            if($form_Event=="subscribe")
            {
 
 
              //回复欢迎文字消息
              $msgType = "text";
              $contentStr = "感谢您关注天地图食堂！[愉快]\n\n最新增加公交查询功能，直接输入955，顺22等返回公交信息！[玫瑰]\n------------------\n输入帮助，查看所有功能。\n输入目录，查看天地图知识库索引";
              $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $contentStr);
          
              
              echo $resultStr;
              exit;
          
            }
          
          }
          
  }
  else 
  {
          echo "";
          exit;
  }

?>