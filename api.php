<?php

var_dump($_GET['comando']);
var_dump($_GET['params']);


try {
    $pdo = new \PDO('sqlite:/home/12307444793/dev/phpolo/db.sqlite');
}
catch(\PDOException $e) {
    echo $e->getMessage();
}

//$sql = 'SELECT * FROM tradeHistory';
//$stm = $pdo->prepare($sql);

//$stm->execute();

//var_dump($stm->fetchAll());
