<?php //·¢ÆðPOSTÇëÇó
include_once("bus.php");
include_once("wx_tpl.php");
  $f = new SaeFetchurl();
  $url = 'www.tianditu.com/query.shtml?type=query&postStr={"keyWord":"955","level":"16","mapBound":"116.6225,40.05845,116.64352,40.06533","queryType":"1","count":"20","start":"0","queryTerminal":"10000"}';
    $content = $f->fetch($url);

    echo strpos($content, "lineData");

    preg_match_all( "/uuid\"\:\"(\d{5})/", $content, $city );
    //var_dump($city);
    $uuid = $city[1][0];
    //echo $uuid;
    //
    //
    function HelloWorld(){
    	include_once("wx_tpl.php");
    	echo "1.".sprintf("hello %s","world");
    }
  
  HelloWorld();  
  echo "2.".sprintf($textTpl, "test", "test", "test", "test", "test");
?>