<?php

try {
    $pdo = new \PDO('sqlite:/home/wouerner/dev/phpolo/db.sqlite');
}
catch(\PDOException $e) {
    echo $e->getMessage();
}
