<?php
// calculo fibo: high-(high-low)*0.618 o 1.618, etc. si corresponde.
include 'config.php';
$base = $argv[1]; //"USDT"
$temp = $argv[2]; //"1m"
$offset = $argv[3]; // 0
$limit = $argv[4]; // 10
$periods = array(8,55);
$closes = array();
$ema_trend = array();
$exclude_pairs = "'USDSUSDT','USDCUSDT','TUSDUSDT','PAXUSDT','USDSBUSDT'";
$sql = "SELECT pair FROM `pairs_config` WHERE `base` = '".$base."' AND `pair` NOT IN (".$exclude_pairs.") AND `trading`=1 ORDER BY `pair` LIMIT ".$offset.",".$limit;
$btc_pairs = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
foreach ($btc_pairs as $pair) {
	$pairs[] = $pair["pair"];
	$exit[$pair["pair"]] = false;
}
///////////////////////////////////////////////////////////////////////////////////////////
$api->chart($pairs, $temp, function($api, $symbol, $chart) use (&$conn, &$temp, &$periods, &$trade, &$closes, &$emas, &$bullish, &$signal, &$exit, &$ema_trend, &$trend, &$diff_emas){
	if(!isset($closes[$symbol])){
		$aux = array_slice($chart, -151,149, TRUE);
		foreach ($aux as $key => $value) {
			$closes[$symbol][$key] = $value["close"]*100000000;
		} 
	}
	$pre_candle[$symbol] = array_slice($chart, -2,1, TRUE);
	$latest_key_closes = key(array_slice($closes[$symbol], -1,1, TRUE));
	$latest_key_chart = key($pre_candle[$symbol]);
	if($latest_key_closes != $latest_key_chart){
		// entro 1 vez por $temp
		$conn->ping();
		$closes[$symbol][$latest_key_chart] = $pre_candle[$symbol][$latest_key_chart]["close"]*100000000; 
		$emas[$symbol] = getEma ($closes[$symbol], $periods);
		$diff_emas[$symbol] = number_format(((1-($emas[$symbol][55]/$emas[$symbol][8]))*100),2);
		if($diff_emas[$symbol] > 0 && $diff_emas[$symbol] < 2){
			$bullish[$symbol] = 1; 
		}else{
			$bullish[$symbol] = 0; 
		}
		if($emas[$symbol][8] < $pre_candle[$symbol][$latest_key_chart]["close"]){
			$signal[$symbol] = 1; 
		}else{
			$signal[$symbol] = 0; 
		}
	}
	/*
	system("clear");
	print_r("#".$symbol."\n");
	print_r("bullish: ".$bullish[$symbol]."\n");
	print_r("signal: ".$signal[$symbol]."\n");
	print_r("pre_candle_close: ".$pre_candle[$symbol][$latest_key_chart]["close"]."\n");
	print_r("EMAS\n");
	print_r($emas[$symbol]);
	print_r("diff_emas: ".$diff_emas[$symbol]."\n");
	*/
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//WEBOSCKET LIVE
	$end_candle[$symbol] = end($chart);
	// condiciones para entrar en el trade
	if(!isset($trade[$symbol]) && 
		$bullish[$symbol] == 1 && 
		$signal[$symbol] == 1 && 
		$end_candle[$symbol]["low"] <= $emas[$symbol][8] && 
		$emas[$symbol]["trend"] == 1
	){
		$trade[$symbol] = array("buy" => $end_candle[$symbol]["close"], "date" => date("Y-m-d H:i"));
	}
	if(isset($trade[$symbol]) ){
		$profit = number_format(((1-($trade[$symbol]["buy"]/$end_candle[$symbol]["close"]))*100)-0.15,2);
		// condiciones para salir del trade
		if($bullish[$symbol] == 0 || $emas[$symbol]["trend"] == 0)
			$exit[$symbol] = true;
		if(($exit[$symbol] == true && $end_candle[$symbol]["high"] >= $emas[$symbol][55]) ||  $profit >= 1){
			$sql = "INSERT INTO `trades_log` (`pair`, `buy`, `buy_date`, `sell`, `sell_date`, `profit`) VALUES ('".$symbol."', ".$trade[$symbol]["buy"].", '".$trade[$symbol]["date"]."', ".$end_candle[$symbol]["close"].", '".date("Y-m-d H:i:s")."', ".$profit.");";
			if ($conn->query($sql) != TRUE) {echo "Error: " . $sql . "\n" . $conn->error; exit();}
			$message= '#'.$symbol.' 
			BUY: '.$trade[$symbol]["buy"].'
			BUY DATE: '.$trade[$symbol]["date"].'
			SELL: '.$end_candle[$symbol]["close"].'
			SELL DATE: '.date("Y-m-d H:i:s").'
			PROFIT: '.$profit;
			SendMessageTelegram($message);
			unset($trade[$symbol]);
			$exit[$symbol] = false;
		}
	}
});	

////////////////////////////////Emas///////////////////////////////////////////////////////////////
function getEma (array $closes, array $periods){
	foreach ($periods as $period) {
		$aux_ema= trader_ema($closes, $period);
		$ema[$period]= number_format(end($aux_ema)/100000000,8,".",""); 
		////////////////ema trend//////////////////////////////////////
		if($period == 55){
			$ema_trend = array_slice($aux_ema, -15,15);
			$aux_trend = number_format(((1-($ema_trend[0]/$ema_trend[14]))*100),2);
			if ($aux_trend > 0.05)
				$ema["trend"] = 1;
			else
				$ema["trend"] = 0;
		}
		//////////////////////////////////////////////////////////////
	}
	return $ema;
}
///////////////////////////////////////////////////////////////////////////////////////////////////

?>