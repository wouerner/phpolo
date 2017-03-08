<?php

try {
    $pdo = new \PDO('sqlite:/var/www/phpolo/db.sqlite');
}
catch(\PDOException $e) {
    echo $e->getMessage();
}
