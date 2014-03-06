<!DOCTYPE html> 
<html> 
<head> 
<meta name="keywords" content="天地图"/> 
<title>天地图－地图API－范例－画线</title> 
<script language="javascript" src="http://api.tianditu.com/js/maps.js"></script> 
<script language="javascript"> 
	var map,zoom = 12,points; 
	function onLoad() 
	{ 
		//初始化地图对象 
	   	map=new TMap("mapDiv"); 
	   	//设置显示地图的中心点和级别 
		map.centerAndZoom(new TLngLat(116.40969,39.94940),zoom); 
		//允许鼠标滚轮缩放地图 
		map.enableHandleMouseScroll(); 
		 
		points = []; 
		points.push(new TLngLat(116.41136,39.97569)); 
   		points.push(new TLngLat(116.411794,39.9068)); 
    	points.push(new TLngLat(116.32969,39.92940)); 
    	points.push(new TLngLat(116.385438,39.90610)); 
    	//创建线对象 
	    var line = new TPolyline(points,{strokeColor:"red", strokeWeight:6, strokeOpacity:1}); 
	    //向地图上添加线 
	    map.addOverLay(line); 
	} 
</script> 
</head> 
<body onLoad="onLoad()"> 
	<div id="mapDiv" ></div> 
</body> 
</html>