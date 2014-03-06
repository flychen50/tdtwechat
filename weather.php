<?php //·¢ÆðPOSTÇëÇó
  $ch=curl_init();
  curl_setopt_array(
    $ch,
    array(
      CURLOPT_URL=>'http://tianditu.com/query.shtml',
      CURLOPT_RETURNTRANSFER=>true,
      CURLOPT_POST=>true,
      CURLOPT_POSTFIELDS=>'type=weather&postStr=101010100'
    )
  );
  $content=curl_exec($ch);
  if(curl_errno($ch)) echo curl_error($ch);
  else echo $content;
  curl_close($ch);
?>