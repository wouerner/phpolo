<?php
	// FINAL TESTED CODE - Created by Compcentral

	// NOTE: currency pairs are reverse of what most exchanges use...
	//       For instance, instead of XPM_BTC, use BTC_XPM

class poloniex {
    protected $api_key;
    protected $api_secret;
    protected $trading_url = "https://poloniex.com/tradingApi";
    protected $public_url = "https://poloniex.com/public";

    public function __construct($api_key, $api_secret) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    private function query(array $req = array()) {
        // API settings
        $key = $this->api_key;
        $secret = $this->api_secret;

        // generate a nonce to avoid problems with 32bit systems
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1].substr($mt[0], 2, 6);

        // generate the POST data string
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('sha512', $post_data, $secret);

        // generate the extra headers
        $headers = array(
            'Key: '.$key,
            'Sign: '.$sign,
        );

        // curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; Poloniex PHP bot; '.php_uname('a').'; PHP/'.phpversion().')'
            );
        }
        curl_setopt($ch, CURLOPT_URL, $this->trading_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // run the query
        $res = curl_exec($ch);

        if ($res === false) throw new Exception('Curl error: '.curl_error($ch));
        //echo $res;
        $dec = json_decode($res, true);
        if (!$dec){
            //throw new Exception('Invalid data: '.$res);
            return false;
        }else{
            return $dec;
        }
    }

    protected function retrieveJSON($URL) {
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'timeout' => 10
            )
        );
        $context = stream_context_create($opts);
        $feed = file_get_contents($URL, false, $context);
        $json = json_decode($feed, true);
        return $json;
    }

    public function get_balances() {
        return $this->query(
            array(
                'command' => 'returnBalances'
            )
        );
    }

    //public function get_open_orders($pair) {
        //return $this->query(
            //array(
                //'command' => 'returnOpenOrders',
                //'currencyPair' => strtoupper($pair)
            //)
        //);
    //}

	public function get_my_trade_history($pair) {
		return $this->query(
			array(
				'command' => 'returnTradeHistory',
				'currencyPair' => strtoupper($pair)
			)
		);
	}

    //public function buy($pair, $rate, $amount) {
        //return $this->query(
            //array(
                //'command' => 'buy',
                //'currencyPair' => strtoupper($pair),
                //'rate' => $rate,
                //'amount' => $amount
            //)
        //);
    //}

    //public function sell($pair, $rate, $amount) {
        //return $this->query(
            //array(
                //'command' => 'sell',
                //'currencyPair' => strtoupper($pair),
                //'rate' => $rate,
                //'amount' => $amount
            //)
        //);
    //}

    //public function cancel_order($pair, $order_number) {
        //return $this->query(
            //array(
                //'command' => 'cancelOrder',
                //'currencyPair' => strtoupper($pair),
                //'orderNumber' => $order_number
            //)
        //);
    //}

    //public function withdraw($currency, $amount, $address) {
        //return $this->query(
            //array(
                //'command' => 'withdraw',
                //'currency' => strtoupper($currency),
                //'amount' => $amount,
                //'address' => $address
            //)
        //);
    //}

    public function get_trade_history($pair) {
        $trades = $this->retrieveJSON($this->public_url.'?command=returnTradeHistory&currencyPair='.strtoupper($pair));
        return $trades;
    }

    //public function get_order_book($pair) {
        //$orders = $this->retrieveJSON($this->public_url.'?command=returnOrderBook&currencyPair='.strtoupper($pair));
        //return $orders;
    //}

    //public function get_volume() {
        //$volume = $this->retrieveJSON($this->public_url.'?command=return24hVolume');
        //return $volume;
    //}

    //public function get_ticker($pair = "ALL") {
        //$pair = strtoupper($pair);
        //$prices = $this->retrieveJSON($this->public_url.'?command=returnTicker');
        //if($pair == "ALL"){
            //return $prices;
        //}else{
            //$pair = strtoupper($pair);
            //if(isset($prices[$pair])){
                //return $prices[$pair];
            //}else{
                //return array();
            //}
        //}
    //}

    //public function get_trading_pairs() {
        //$tickers = $this->retrieveJSON($this->public_url.'?command=returnTicker');
        //return array_keys($tickers);
    //}

	public function get_total_btc_balance() {
		$balances = $this->get_balances();
		$prices = $this->get_ticker();

		$tot_btc = 0;

		foreach($balances as $coin => $amount){
			$pair = "BTC_".strtoupper($coin);

			// convert coin balances to btc value
			if($amount > 0){
				if($coin != "BTC"){
					$tot_btc += $amount * $prices[$pair];
				}else{
					$tot_btc += $amount;
				}
			}

			// process open orders as well
			if($coin != "BTC"){
				$open_orders = $this->get_open_orders($pair);
				foreach($open_orders as $order){
					if($order['type'] == 'buy'){
						$tot_btc += $order['total'];
					}elseif($order['type'] == 'sell'){
						$tot_btc += $order['amount'] * $prices[$pair];
					}
				}
			}
		}

		return $tot_btc;
	}

    public function returnAvailableAccountBalances() {
        return $this->query(
            array(
                'command' => 'returnAvailableAccountBalances'
            )
        );
    }
}

include 'config.php';
$poloniex = new Poloniex($api_key, $secret);
$balances = (array_filter($poloniex->get_balances(), function($v){
    return (float)$v >0;
}));

$returnAvailable=$poloniex->returnAvailableAccountBalances();

?>
<html>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<?php var_dump( $returnAvailable ) ?>
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
                        //$tradeHistory[0] =   $poloniex->get_trade_history(  'BTC_' . $cur )[0];
                        //$tradeHistory[1] =   $poloniex->get_trade_history(  'BTC_' . $cur )[1];
                        //$tradeHistory[2] =   $poloniex->get_trade_history(  'BTC_' . $cur )[2];
                   }
                ?>
            <?php if($tradeHistory ):?>
                <?php foreach($tradeHistory as $history):?>
                    <tr>
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
</body>
</html>
