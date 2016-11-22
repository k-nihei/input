<?php
//LineBusinessCenter
//https://business.line.me/
$accessToken = 'biRBWFDIRC/y7mMI0IEPnthGKJ0xufOoS5UMEmO6rM5YmyG0aQprchXg/pOeCDg6gnXwyidzKalt4kE7bAbelkRh4koRxXZ3ZsyTG+0vYONTpmiICBnwdBBj+egv6YXF2YF5mAd3Ysm1drBWWIhUmgdB04t89/1O/w1cDnyilFU=';

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);

$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
//メッセージ取得
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

//メッセージ以外のときは何も返さず終了
if($type != "text"){
    exit;
}

//docomo返信
$response = chat($text);

//返信データ作成
$response_format_text = [
    "type" => "text",
    "text" => $response
    ];

$post_data = [
    "replyToken" => $replyToken,
    "messages" => [$response_format_text]
    ];

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
    ));
$result = curl_exec($ch);
curl_close($ch);



//ドコモの雑談APIから雑談データを取得
function chat($text) {
    // docomo chatAPI
	//docomo APIをapi_keyに記入
    $api_key = '7956507a37786c33686149635a2f3070424b7a464f355a64652f6f555154784b3479354a62397031745439';
    $api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY=%s', $api_key);
    $req_body = array('utt' => $text);

    $headers = array(
        'Content-Type: application/json; charset=UTF-8',
    );
    $options = array(
        'http'=>array(
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => json_encode($req_body),
            )
        );
    $stream = stream_context_create($options);
    $res = json_decode(file_get_contents($api_url, false, $stream));

    return $res->utt;
}