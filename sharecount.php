<?php
if(isset($_REQUEST['sharecount']))
{
    function multiRequest($data, $options = array()) {

  // array of curl handles
  $curly = array();
  // data to be returned
  $result = array();

  // multi handle
  $mh = curl_multi_init();

  // loop through $data and create curl handles
  // then add them to the multi-handle
  foreach ($data as $id => $d) {

    $curly[$id] = curl_init();

    $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
    curl_setopt($curly[$id], CURLOPT_URL,            $url);
    curl_setopt($curly[$id], CURLOPT_HEADER,         0);
    curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);

    // post?
    if (is_array($d)) {
      if (!empty($d['post'])) {
        curl_setopt($curly[$id], CURLOPT_POST,       1);
        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
      }
    }

    // extra options?
    if (!empty($options)) {
      curl_setopt_array($curly[$id], $options);
    }

    curl_multi_add_handle($mh, $curly[$id]);
  }

  // execute the handles
  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while($running > 0);


  // get content and remove handles
  foreach($curly as $id => $c) {
    $result[$id] = curl_multi_getcontent($c);
    curl_multi_remove_handle($mh, $c);
  }

  // all done
  curl_multi_close($mh);

  return $result;
}
$videoID=($_REQUEST['sharecount']);
$URI =$videoID;
$data=array("http://graph.facebook.com/?id=".$URI,
"https://api.pinterest.com/v1/urls/count.json?callback=receiveCount&url=".$URI,
"https://www.linkedin.com/countserv/count/share?url=".$URI."&format=json");
$r=multiRequest($data);
$pinterest=str_replace("receiveCount(","",$r[1]);
$pinterest=str_replace(")","",$pinterest);
$result=array(
    "fb"=>json_decode($r[0]),
    "pinterest"=>($pinterest),
    "linkedin"=>json_decode($r[2])
    );
$encoded=json_encode($result);
header("Content-type: application/json");
exit($encoded);
}
