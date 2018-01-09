<?php
include(dirname(__FILE__) . '/HyApiClient.php');
//
$cl = new HyApiClient("access_key_id", 'access_key_secret');
$ret = $cl->SendSms('手机号', '短信签名', '短信模板Code', '模板参数,JSON字符串,没有就留空');
echo "test SendSms \n";
var_dump($ret);


$ret = $cl->QuerySendDetails('手机号', '短信业务流水id', '发送时间', '每页数据数量');
echo "test  QuerySendDetails\n";
var_dump($ret);




