<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<title>天地图在路上</title>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=FFadb6823fe9ac2dc420330c4744e919"></script>
<script type="text/javascript" src="http://api.map.baidu.com/library/CurveLine/1.5/src/CurveLine.min.js"></script>
<style type="text/css">
    html,body{
        width:100%;
        height:100%;
        margin:0;
        overflow:hidden;
    }
</style>

</head>
<body>
<div style="width:100%;height:100%;border:1px solid gray" id="container">
</div>
</body>
</html>
<script type="text/javascript">
// 百度地图API功能
var map = new BMap.Map("container");
map.centerAndZoom(new BMap.Point(116.454, 39.955), 6);
map.enableScrollWheelZoom();
<?php>
 $user = $_GET['user'];
      $mysql = new SaeMysql();
  $sql = "SELECT * 
FROM  `location` 
WHERE weixinid =  '$user'";
  $info = $mysql->getData($sql);

if(!$info){
    return null;
  }
   $points = "var points = [";  
  foreach ($info as $key => $value) {
     echo "var lonlat".$key."=new BMap.Point(".$value['lat'].",".$value['lon'].");";
     $points.="lonlat".$key.",";

    //echo 'var opts'.$key.' = {
    //position : lonlat['.$key.'],    // 指定文本标注所在的地理位置
    //offset   : new BMap.Size(20, -20)    //设置文本偏移量
    //};';

  //echo 'var label'.$key.' = new BMap.Label("'.$value['currenttime'].'", opts'.$key.');';  // 创建文本标注对象

  //echo 'map.addOverlay(label'.$key.');';


     echo "var marker".$key." = new BMap.Marker(lonlat".$key.");";  // 创建标注
echo "map.addOverlay(marker".$key.");";  
echo '
var infoWindow'.$key.' = new BMap.InfoWindow("'.$value['currenttime'].$value['label'].'");
marker'.$key.'.addEventListener("click", function(){this.openInfoWindow(infoWindow'.$key.');});';
  }
  $points.="];";
  echo $points;

?>


var curve = new BMapLib.CurveLine(points, {strokeColor:"blue", strokeWeight:3, strokeOpacity:0.5}); //创建弧线对象
map.addOverlay(curve); //添加到地图中
//curve.enableEditing(); //开启编辑功能


</script>