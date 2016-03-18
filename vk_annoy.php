<?php

$userid = ''; # target id (victim)
$accesstoken = ''; # for access to the victim
$postid = ''; # target post id
$message = '@id'.$userid; # victims mention
$useragent = 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';

$log = 'log/vk_annoy_log';

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

function logMessage($log, $message) {
  if (filesize($log) > 1000000) {
	  $log += 'o';
	  logMessage($message);
  }
  $log_message = '['.date('Y-m-d H:i:s T', time()).'] ' . $message . ' <br>' . PHP_EOL;
  echo $log_message;
  file_put_contents($log, $log_message, FILE_APPEND);
}

set_time_limit(0);
date_default_timezone_set('Europe/Moscow');

while (true) {
  $userinfo = doCurlRequest('https://api.vk.com/method/users.get?user_id='.$userid.'&fields=online&v=5.50&access_token='.$accesstoken);
  if (!is_null($userinfo)) {	
    $userinfo = json_decode($userinfo);
    if ($userinfo->response[0]->online > 0) {
      logMessage($log, 'Victim ' . $userinfo->response[0]->first_name . ' is online'); 
      $lastmessage = doCurlRequest('https://api.vk.com/method/messages.getHistory?user_id='.$userid.'&count=1&v=5.50&access_token='.$accesstoken);
	  $lastmessage = json_decode($lastmessage);
	  if ($lastmessage -> response -> items[0] -> read_state == 0) {
	    logMessage($log, 'Last message is unread');	  
        # echo 'Like post with id = ' . $postid . PHP_EOL;
        # doCurlRequest('https://api.vk.com/method/likes.add?owner_id='.$userid.'&item_id='.$postid.'&type=post&v=5.50&access_token='.$accesstoken);
        # sleep(5);
        # doCurlRequest('https://api.vk.com/method/likes.delete?owner_id='.$userid.'&item_id='.$postid.'&type=post&v=5.50&access_token='.$accesstoken);  
        logMessage($log, 'Add victim mention to post with id = ' . $postid);               
        $commentid = doCurlRequest('https://api.vk.com/method/wall.addComment?owner_id='.$userid.'&post_id='.$postid.'&text='.$message.'&v=5.50&access_token='.$accesstoken);
        $commentid = json_decode($commentid);
        $commentid = $commentid->response->comment_id;
        sleep(3);
        doCurlRequest('https://api.vk.com/method/wall.deleteComment?owner_id='.$userid.'&comment_id='.$commentid.'&v=5.50&access_token='.$accesstoken);
        logMessage($log, 'Remove mention. Comment id = ' . $commentid);
	  } else {
		  logMessage($log, 'Last message is read');
	  }	  
    } else {
      logMessage($log, 'Victim ' . $userinfo->response[0]->first_name . ' is offline');          
    }
    flush();
    ob_flush();
  }
  sleep(15);
}

?>
