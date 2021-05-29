<?php
include 'config.php';
$symbols = $api->exchangeInfo()["symbols"];
$ticker = $api->prices();
foreach ($symbols as $symbol) {
	if($symbol["status"]=="TRADING" )
		$trading = 1;
	else
		$trading = 0;
	$sql2 = "SELECT `id` FROM `pairs` WHERE `pair`='".$symbol["symbol"]."';";
	$check_exist = $conn->query($sql2)->fetch_all();
	if (sizeof($check_exist) == 0){
		$sql = "INSERT INTO `pairs_config` (`pair`,`base`,`minPrice`, `maxPrice`, `tickSize`, `minQty`, `maxQty`, `stepSize`, `i1_buy`, `i1_sell`, `i1_stoploss`, `i2_buy`, `i2_sell`, `i2_stoploss`, `i3_buy`, `i3_sell`, `i3_stoploss`, `i4_buy`, `i4_sell`, `i4_stoploss`, `i5_buy`, `i5_sell`, `i5_stoploss`, `i6_buy`, `i6_sell`, `i6_stoploss`, `trading`) VALUES ('".$symbol["symbol"]."', '".$symbol["quoteAsset"]."', ".$symbol["filters"][0]["minPrice"].", ".$symbol["filters"][0]["maxPrice"].", ".$symbol["filters"][0]["tickSize"].", ".$symbol["filters"][2]["minQty"].", ".$symbol["filters"][2]["maxQty"].", ".$symbol["filters"][2]["stepSize"].", 2.618, 3.618, 1.618, 2.618, 3.618, 1.618, 2.618, 3.618, 1.618, 2.618, 3.618, 1.618, 2.618, 3.618, 1.618, 2.618, 3.618, 1.618 ".$trading.");";
		if ($conn->query($sql) != TRUE) {echo "Error: " . $sql . "<br>" . $conn->error;}
	}else{
		// aca no actualizo los Intervals, por que me pisaria, si tengo configuraciones particulares.
		$sql = "UPDATE `pairs_config` SET `minPrice`=".$symbol["filters"][0]["minPrice"].",`maxPrice`=".$symbol["filters"][0]["maxPrice"].", `tickSize`=".$symbol["filters"][0]["tickSize"].", `minQty`=".$symbol["filters"][2]["minQty"].", `maxQty`=".$symbol["filters"][2]["maxQty"].", `stepSize`=".$symbol["filters"][2]["stepSize"].", `trading`=".$trading." WHERE id=".$check_exist[0][0].";"; 
		if ($conn->query($sql) != TRUE)
			echo "Error: " . $sql . "<br>" . $conn->error;
	}
}
CloseCon($conn);

