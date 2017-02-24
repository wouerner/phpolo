<?php

include 'config/api.php';
include 'poloniex.php';

$poloniex = new Poloniex($api_key, $secret);
$balances = (array_filter($poloniex->get_balances(), function($v){
    return (float)$v >0;
}));

$returnAvailable = $poloniex->returnAvailableAccountBalances();

?>
<html>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<script src="http://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<?php //var_dump( $returnAvailable ) ?>
    <?php foreach($returnAvailable['margin'] as $cur => $balance):?>
        <table class="table table-bordered">
            <caption><?php echo $cur ?></caption>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Rate(BTC)</th>
                    <th>Amount</th>
                    <th>Total</th>
                </tr>
            </thead>
                <?php
                    switch($cur){
                     case 'BTC':
                        $tradeHistory =   null;
                        break;
                     case 'USDT':
                        $tradeHistory[0] =   $poloniex->get_trade_history( $cur . '_BTC')[0];
                        break;
                    default:
                        $tradeHistory =   $poloniex->get_my_trade_history(  'BTC_' . $cur );
                   }
                ?>
            <?php if($tradeHistory ):?>
                <?php foreach($tradeHistory as $history):?>
                    <tr>
                        <td><?php //var_dump($history)?></td>
                        <td><?php echo $history['date']?></td>
                        <td><?php echo $history['type']?></td>
                        <td><?php echo $history['rate']?></td>
                        <td><?php echo $history['amount']?></td>
                        <td><?php echo $history['total']?></td>
                    </tr>
                <?php endforeach?>
            <?php endif?>
        </table>
    <?php endforeach?>
	<script type="text/javascript">
		jQuery.ajax({
			url: 'http://local.phpolo/api.php?comando=sumTotal&currency=DASH&type=buy&category=exchange&date=2016-06-28%2002:00:41',
		}).done(function(data) {
			console.log(data);
		});
	</script>
</body>
</html>
