<?php

$userid = ''; # target id (victim)
$accesstoken = ''; # for access to the victim
$postid = ''; # target post id
$useragent = 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';

function doCurlRequest($url){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $data = curl_exec($ch);
  curl_close($ch);
  echo $data, PHP_EOL;
  return $data;
}

while ( true ) {
  ob_flush();
  flush();
  $userinfo = doCurlRequest('https://api.vk.com/method/users.get?user_id='.$userid.'&fields=online&v=5.45&access_token='.$accesstoken);
  $userinfo = json_decode($userinfo);
  if ( $userinfo->response[0]->online > 0 ) {
    doCurlRequest('https://api.vk.com/method/likes.add?owner_id='.$userid.'&item_id='.$postid.'&type=post&v=5.45&access_token='.$accesstoken);
    sleep(5);
    doCurlRequest('https://api.vk.com/method/likes.delete?owner_id='.$userid.'&item_id='.$postid.'&type=post&v=5.45&access_token='.$accesstoken);
  }
  sleep(15);
}

?>