<?php
try { $pdo = new \PDO('sqlite:/home/12307444793/dev/phpolo/db.sqlite');
}
catch(\PDOException $e) {
    echo $e->getMessage();
}

include 'config.php';
include 'poloniex.php';

$poloniex = new Poloniex($api_key, $secret);

$returnAvailable = $poloniex->returnAvailableAccountBalances();

foreach($returnAvailable['exchange'] as $cur => $balance) {
    switch($cur) {
        case 'BTC':
            $tradeHistory =   null;
            break;
        case 'USDT':
            $tradeHistory[0] =   $poloniex->get_trade_history( $cur . '_BTC')[0];
            break;
        default:
            $tradeHistory =   $poloniex->get_my_trade_history(  'BTC_' . $cur );
    }

    if($tradeHistory) {
        foreach($tradeHistory as $history) {
            $sql = "INSERT INTO tradeHistory (
                  'globalTradeID',
                  'tradeID',
                  'date',
                  'rate',
                  'amount',
                  'total',
                  'fee',
                  'orderNumber',
                  'type',
                  'category',
                  'currency'
              ) VALUES (
                  ?, ?, ?, ?,
                  ?, ?, ?, ?,
                  ?, ?, ?
          )";

            $stm = $pdo->prepare($sql);

            $stm->bindParam(1, $history['globalTradeID']);
            $stm->bindParam(2, $history['tradeID']);
            $stm->bindParam(3, $history['date']);
            $stm->bindParam(4, $history['rate']);
            $stm->bindParam(5, $history['amount']);
            $stm->bindParam(6, $history['total']);
            $stm->bindParam(7, $history['fee']);
            $stm->bindParam(8, $history['orderNumber']);
            $stm->bindParam(9, $history['type']);
            $stm->bindParam(10, $history['category']);
            $stm->bindParam(11, $cur);

            $stm->execute();
        }
    }
}
