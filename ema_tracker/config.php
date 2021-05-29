<?php
date_default_timezone_set("America/Buenos_Aires");
require 'vendor/autoload.php';
$api = new Binance\API(,);
$conn = Opencon();
$conn->query('SET GLOBAL connect_timeout=86400');
$conn->query('SET GLOBAL wait_timeout=86400');
$conn->query('SET GLOBAL interactive_timeout=86400');
function OpenCon(){
	$dbhost = "localhost";
	$dbuser = "root";
	$dbpass = "root";
	$db = "ema_tracker";
	$conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
	return $conn;
}

function CloseCon($conn){
	$conn -> close();
}

function SendMessageTelegram($message){
	$chat_id = ;
	$TelegramToken = ;
	$data_tel = ['chat_id' => $chat_id,'text' => $message];
	$response = file_get_contents("https://api.telegram.org/bot$TelegramToken/sendMessage?" . http_build_query($data_tel) );
} 


?>
