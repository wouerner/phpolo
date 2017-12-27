<?php

include_once('config/db.php');

include 'config/api.php';
include 'poloniex.php';

$comando = $_GET['comando'];
$currency = !empty($_GET['currency']) ? $_GET['currency'] : null;
$dateEnd = !empty($_GET['end']) ? $_GET['end'] : null;
$dateStart = !empty($_GET['start']) ? $_GET['start'] : null;

function load($pdo, $api_key, $secret, $currency, $dateStart = null, $dateEnd = null)
{
    //$dateStart = new DateTime('2016-01-01');
    //$dateEnd = new DateTime('now');
    //$dateStart = $dateStart->getTimestamp();
    //$dateEnd = $dateEnd->getTimestamp();

    $result = [];

    $poloniex = new Poloniex($api_key, $secret);
    //$returnAvailable = $poloniex->returnAvailableAccountBalances();
    //$currencies = (array_merge($returnAvailable['margin'], $returnAvailable['exchange']));
    //$currencies = ['XMR' => 1];
    //foreach($currencies as $cur => $balance) {
    //foreach($returnAvailable['exchange'] as $cur => $balance) {
        //switch($cur) {
            //case 'BTC':
                //$tradeHistory =   null;
                //break;
            //case 'USDT':
                //$tradeHistory[0] =   $poloniex->get_trade_history( $cur . '_BTC')[0];
                //break;
            //default:
                //$tradeHistory =   $poloniex->get_my_trade_history(  'BTC_' . $cur, $dateStart, $dateEnd);
        //}
/* $currency = 'ETH'; */
        $tradeHistory =   $poloniex->get_my_trade_history(  'BTC_' . $currency, $dateStart, $dateEnd);
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
                $stm->bindParam(11, $currency);

                $stm->execute();
                $result['error'] = $stm->errorInfo();
            }
            sleep(1);
        }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'error' => $result['error']]);
}

function eraseData($pdo, $api_key, $secret, $currency = null)
{
    $sql = "DELETE FROM tradeHistory";
    if ($currency) {
        $sql .= " WHERE currency = ?";
    }

    $stm = $pdo->prepare($sql);

    if ($currency) {
        $stm->bindParam(1, $currency);
    }

    $stm->execute();

    $result['error'] = $stm->errorInfo();

    header('Content-Type: application/json');
    echo json_encode($result);
}

$comando($pdo, $api_key, $secret, $currency, $dateStart, $dateEnd);
