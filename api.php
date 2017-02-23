<?php

$comando = $_GET['comando'];
$currency = $_GET['currency'];
$category = $_GET['category'];
$type = $_GET['type'];

include_once('config/db.php');

function tradeHistory($pdo, $currency, $type, $category)
{
    $sql = "
        SELECT  *
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

function sumTotal($pdo, $currency, $type, $category)
{
    $sql = "
        SELECT  sum(total) as total
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

$comando($pdo, $currency, $type, $category);

