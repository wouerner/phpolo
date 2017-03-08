<?php

$comando = $_GET['comando'];
$currency = !empty($_GET['currency']) ? $_GET['currency'] : null;
$category = !empty($_GET['category']) ? $_GET['category'] : null;
$type = !empty($_GET['type']) ? $_GET['type'] : null;
$date = !empty($_GET['date']) ? $_GET['date'] : null;

include_once('config/db.php');
include 'config/api.php';
include 'poloniex.php';

function tradeHistory($pdo, $currency, $type, $category)
{
    $sql = "
        SELECT
            date,
            category,
            currency,
            printf('%.8f', total) as total,
            printf('%.8f', amount) as amount ,
            printf('%.8f', rate) as rate,
            printf('%.8f',  fee) as fee
        from tradeHistory
        where
            category = ?
            and type = ?
            and currency = ?
        ";

    $stm = $pdo->prepare($sql);

    $stm->bindParam(1, $category);
    $stm->bindParam(2, $type);
    $stm->bindParam(3, $currency);

    $stm->execute();

    $result = ($stm->fetchAll(\PDO::FETCH_ASSOC));

    header('Content-Type: application/json');
    echo json_encode($result);
}

function sumTotal($pdo, $currency, $type, $category, $date = null)
{
    $sql = "
        SELECT date,
        printf('%.8f', sum(total)) as total,
        printf('%.8f', sum(amount)) as amount ,
        printf('%.8f', avg(rate)) as rate,
        printf('%.8f', sum(amount* fee)) as fee
        from tradeHistory
        where
            category = ?
            and
            type= ?
            and currency = ?
         and  strftime('%s', date)  >=  strftime('%s', ?)
        ";

    $stm = $pdo->prepare($sql);

    $stm->bindParam(1, $category);
    $stm->bindParam(2, $type);
    $stm->bindParam(3, $currency);
    $stm->bindParam(4, $date);

    $stm->execute();

    $result = ($stm->fetchAll(\PDO::FETCH_ASSOC));

    header('Content-Type: application/json');
    echo json_encode($result);
}

function currencies()
{
    include 'config/api.php';
    $poloniex = new Poloniex($api_key, $secret);

    $returnAvailable = $poloniex->returnAvailableAccountBalances();
    $currencies = (array_merge($returnAvailable['margin'], $returnAvailable['exchange']));

    header('Content-Type: application/json');
    echo json_encode($currencies);
}

function getTicket($t, $currency)
{
    include 'config/api.php';
    $poloniex = new Poloniex($api_key, $secret);

    $value = $poloniex->get_ticker($currency);

    header('Content-Type: application/json');
    echo json_encode($value);
}

$comando($pdo, $currency, $type, $category, $date);

