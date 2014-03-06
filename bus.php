<?php //发起POST请求


include_once("wx_tpl.php");


  $key = $_GET['key'];
  $user = $_GET['user'];

function startsWith($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function tdtSearchRound($keyword,$fromUsername){

   $mysql = new SaeMysql();

  $user_info = "";
  $sql = "SELECT * 
FROM  `location` 
WHERE  `weixinid` =  '".$fromUsername."'
ORDER BY id DESC 
LIMIT 1";
  //echo $sql;
  $lon="";
  $lat = "";
  $locations = $mysql->getData( $sql );

  if(is_null($locations)){
    //return null;
      $lon="40.056036";
      $lat = "116.636540";
  }else{
      foreach ($locations as $location) {
    $lon = $location['lon'];
    $lat = $location['lat'];
  }
  }





  $cur_location = tdtgeocode($lat,$lon);
  $fin_content="当前位置：".$cur_location."\n更新当前位置,请先点击+之后选择位置发送\n---------------\n";
  $f = new SaeFetchurl();
   $url = 'http://www.tianditu.com/query.shtml?type=query&postStr={"keyWord":"'.$keyword.'","level":"16","mapBound":"116.62635,40.05899,116.64737,40.06587","queryType":"3","pointLonlat":"'.$lat.','.$lon.'","queryRadius":"1000","count":"20","start":"0","queryTerminal":"10000"}';
   $content = $f->fetch($url);
   //
   //echo $url;
   $res = json_decode($content,true);
   //var_dump($res);
   if(is_null($res['pois']))
   {
    return null;
   }

   foreach ($res['pois'] as $key => $info) {
      //if(is_null($info['phone'])||strlen($info['phone'])<4)
        //continue;
      $fin_content .=++$key.".".$info['name']." ".$info['address']."(".$info['distance']."米) ".$info["phone"]."\n"; 
     # code...
   }

  return $fin_content;
}

function tdtgeocode($lon,$lat){
  $fin_content="";
  $f = new SaeFetchurl();
   $url = 'http://www.tianditu.com/query.shtml?postStr={lon:'.$lon.',lat:'.$lat.',appkey:%27d05d576eb2115945ea61399f633249fa%27,ver:1}&type=geocode';
   $content = $f->fetch($url);
   //echo $url;
   $res = json_decode($content,true);
   //var_dump($res);
   return $res['result']['formatted_address'];
}

  function TiandituBusline($busline){

    $mmc=memcache_init();
    $mm_ret =  memcache_get($mmc,$busline);
    if($mm_ret!=""){

      return $mm_ret;
    }


  $url = 'http://www.tianditu.com/query.shtml?type=query&postStr={"keyWord":"'.$busline.'","level":"11","mapBound":"116.04448,39.81341,116.7174,40.03383","queryType":"1","count":"20","start":"0","queryTerminal":"10000"}';
   $f = new SaeFetchurl();
    $content = $f->fetch($url);
    if(!strpos($content, "lineData")){
       return null;
    }

     preg_match_all( "/uuid\"\:\"(\d{5})/", $content, $city );


     var_dump($url);
     var_dump($city);
    $uuid = $city[1][0];

    $bus_url = "http://www.tianditu.com/query.shtml?type=busline&postStr={'uuid':'".$uuid."'}";
    //echo $bus_url;
    $buses = $f->fetch($bus_url);

    $res = json_decode($buses,true);

    if(is_null($res)||is_null($res['starttime']))
    {
      return null;
    }

    $fin_content= $res['linename']."\n首末车时间".$res['starttime']."-". $res['endtime']."\n";
    foreach ( $res['station'] as $station )
      {
        $fin_content .= $station['name']."\n";
      }
       memcache_set($mmc,$busline,$fin_content);
      return $fin_content;
    }


    function tianditucontact($name){
          $mmc=memcache_init();
    $mm_ret =  memcache_get($mmc,$name);
    if($mm_ret!=""){
      
      return $mm_ret;
    }

      $mysql = new SaeMysql();

  $user_info = "";
  $sql = "SELECT * FROM `tdtcontact` WHERE name like '%".$name."%'";
  //echo $sql;
  $data = $mysql->getData( $sql );
  if(is_null($data)){
   // echo "null";
    return null;
  }
  //var_dump($data);
  foreach ($data as $user) {
    # code...
    $user_info.=$user['name']." ".$user['tel']."\n";
  }
   memcache_set($mmc,$name,$user_info);
  return $user_info;
    }

function updateUserInfo(){
  $mysql = new SaeMysql();
  $sql = "select id,name from tdtcontact";
  $user = $mysql->getData($sql);
  foreach($user as $uu){
    $username = $uu['name'];
    $username = str_replace(" ", "", $username);
    
    //echo $username."\n";
    $updatesql = "update tdtcontact set name='".$username."' where id='".$uu['id']."'";
    //echo $updatesql."\n";
    $mysql->runSql( $updatesql );

  }

}

function tdtlog(){
  $ret = "";
  $mysql = new SaeMysql();
  $sql = "select * from log where weixinid!='oRWRqtx8OpEBXCHNKFXSRb106-s0' order by id desc  limit 20";
  $info = $mysql->getData($sql);

if(!$info){
    return null;
  }

  foreach($info as $im){
    $ret .= $im['querytime']." ".$im['keyword']."\n";
  }
  return $ret;
}


function tdtgeocoding($fromUsername){
  $ret = "";
  $mysql = new SaeMysql();
  $sql = "SELECT * 
FROM  `location` 
WHERE weixinid =  '$fromUsername'";
  $info = $mysql->getData($sql);

if(!$info){
    return null;
  }
  $url = "http://1.tianditu.sinaapp.com/ttt.php?user=".$fromUsername;
  foreach($info as $im){
    $ret .= "*".$im['currenttime']."* ".$im['label']."\n";
  }
  return $ret."\n<a href='$url'>我的历史位置</a>";
}



function tdtURLInfo($keyword,$fromUsername,$toUsername){
  $ret = "";
  $mysql = new SaeMysql();
  $sql = "SELECT * FROM `information` WHERE info like '%".$keyword."%'";
  $info = $mysql->getData($sql);

if(!$info){
    return null;
  }

                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>1</ArticleCount>\n
                  <Articles>\n";                  
                  $return_arr = $info;
                  //数组循环转化
                  foreach($return_arr as $value)
                  {
                    //var_dump($value);
                    if($value['url']==''||$value['title']==''){
                      return null;
                    }
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$value['title']."]]></Title> \n
                    <Description><![CDATA[".$value['info']."]]></Description>\n
                    <PicUrl><![CDATA[".$value['picurl']."]]></PicUrl>\n
                    <Url><![CDATA[".$value['url']."]]></Url>\n
                    </item>\n";
                  }
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
                 // echo $resultStr;
  return $resultStr;
}


function infoModel($fromUsername,$toUsername,$title,$info,$url){

  if(is_null($url)||is_null($info)){
    return null;
  }


                  $resultStr="<xml>\n
                  <ToUserName><![CDATA[".$fromUsername."]]></ToUserName>\n
                  <FromUserName><![CDATA[".$toUsername."]]></FromUserName>\n
                  <CreateTime>".time()."</CreateTime>\n
                  <MsgType><![CDATA[news]]></MsgType>\n
                  <ArticleCount>1</ArticleCount>\n
                  <Articles>\n";                  
                    $resultStr.="<item>\n
                    <Title><![CDATA[".$title."]]></Title> \n
                    <Description><![CDATA[".$info."]]></Description>\n
                    <PicUrl><![CDATA[]]></PicUrl>\n
                    <Url><![CDATA[".$url."]]></Url>\n
                    </item>\n";
                  $resultStr.="</Articles>\n
                  <FuncFlag>0</FuncFlag>\n
                  </xml>";                
  return $resultStr;
}


function tdtindex(){
  $ret = "知识库目录\n搜索关键字(非序号)返回如下信息。如输入借阅，图书，公有等任意关键字，返回天地图公有图书借阅表\n";
  $mysql = new SaeMysql();
  $sql = "SELECT keyword
FROM `information` 
WHERE keyword != ''";
  $info = $mysql->getData($sql);

if(!$info){
    return null;
  }

$i=0;
  foreach($info as $key => $im){
    $ret .= ++$key.".".$im['keyword']."\n";
  }
  return $ret;
}

function tdtinfo($keyword){
  $ret = "";
  $mysql = new SaeMysql();
  $sql = "SELECT * FROM `information` WHERE info like '%".$keyword."%'";
  $info = $mysql->getData($sql);

if(!$info){
    return null;
  }

  foreach($info as $im){
    $ret .= $im['info']."\n------------------\n";
  }
  return $ret;
}

function updateinfo($information){
  $dt =  date("Y-m-d H:i:s" ,strtotime("now" ));
  $mysql = new SaeMysql();
              $sql = "INSERT INTO  `app_tianditu`.`information` (
`id` ,
`keyword` ,
`info`
)
VALUES (
NULL ,  '',  '$information'
);
";
  sae_debug($sql);
  $mysql->runSql( $sql );
  if( $mysql->errno() != 0 )
  {
    die( "Error:" . $mysql->errmsg() );
  }



}
function queryResponse($form_Content,$fromUsername,$toUsername){

if(startsWith($form_Content,"tdt")||endsWith($form_Content,"tdt")){
  //echo "start with blank";
  $form_Content = trim($form_Content,"tdt");
  updateinfo($form_Content);
  return "感谢参与天地图知识库的建设，您添加的信息已经加入数据库中";
}

  $all = "";
  $MulInfo = null;
   $all = tianditucontact($form_Content);
   //var_dump($all);
                
                if(is_null($all)){
                   $all = TiandituBusline($form_Content);
     //              var_dump($all);
                }

                if(is_null($all)&&$form_Content=="目录"){
                  $all = tdtindex();
                }


                if(is_null($all)){
                  $all = tdtSearchRound($form_Content,$fromUsername);
                  if(!is_null($all)){
                    $all.="\n<a href='http://123.125.87.71/down.myapp.com/android/30579/17116946/tianditu.com_16.apk?mkey=52e203914eccb3c8&f=e487&p=.apk'>天地图android版</a>";
                    $all.="\n<a href='https://itunes.apple.com/cn/app/tian-de-tu-shou-ji-ban/id458575713?mt=8'>天地图iOS版</a>";
                  }

                  //$MulInfo = infoModel($fromUsername,$toUsername,"天地图附近搜索",$all,"http://mobile.tianditu.com/mobile/index.html");
                }

                if(is_null($all)){
                  $all = tdtinfo($form_Content);
                  $MulInfo = tdtURLInfo($form_Content,$fromUsername,$toUsername);
       //           var_dump($all);
                }

                if($form_Content=="log"){
                  $all = tdtlog();
                }
                if($form_Content=="travel"||$form_Content=="在路上"){
                	$all = tdtgeocoding($fromUsername);
                }
                if(is_null($all)){
                  $all = "抱歉，还没有相关信息。\n------------------\n";
                  $all.='试试发送你的位置:
1. 点击左下方“小键盘”
2. 点击右下方“+号键”
3. 点击“位置”图标
4. 完成定位后点击“发送”';
                }

                $all.="\n------------------\n输入帮助，查看所有功能。\n输入目录，查看天地图知识库索引";

                $msgType = "text";
                $time = time();

$textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            <FuncFlag>0</FuncFlag>
            </xml>";   
                 $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $all);
                if(!is_null($MulInfo)){
                  return $MulInfo;
                }
                return  $resultStr;
                //var_dump($resultStr);

                
                //return $all;

}

//echo queryResponse($key,$user,"tttttt");
//echo sprintf($textTpl, "test", "test", "test", "test", "test");
//echo tdtSearchRound($key,$user);
//echo tdtgeocode( 116.30247724323,39.900564682311);
//echo tdtURLInfo($key,"","");
//echo tdtgeocoding("oRWRqtx8OpEBXCHNKFXSRb106-s0");
?>