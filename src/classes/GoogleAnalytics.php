<?php
namespace MyApiCore\System;

/**
 * Class GoogleAnalytics
 * @package MyApiCore\System
 *
 * Send uri and responses to google analytics
 *
 * @package     App\Components
 * @subpackage  Controllers
 * @since       v0.1.0
 *
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
 *
 */
class GoogleAnalytics {

	const GA_URL = 'https://stats.g.doubleclick.net/__utm.gif';

	private static $requests_for_this_session = 0;

	private $data = array(
		'utmac' => null,
		'utmcc' => null,
		'utmcn' => null,
		'utmcr' => null,
		'utmcs' => null,
		'utmdt' => '-',
		'utmfl' => '-',
		'utme' => null,
		'utmni' => null,
		'utmhn' => null,
		'utmipc' => null,
		'utmipn' => null,
		'utmipr' => null,
		'utmiqt' => null,
		'utmiva' => null,
		'utmje' => 0,
		'utmn' => null,
		'utmp' => null,
		'utmr' => null,
		'utmsc' => '-',
		'utmvp' => '-',
		'utmsr' => '-',
		'utmt' => null,
		'utmtci' => null,
		'utmtco' => null,
		'utmtid' => null,
		'utmtrg' => null,
		'utmtsp' => null,
		'utmtst' => null,
		'utmtto' => null,
		'utmttx' => null,
		'utmul' => '-',
		'utmhid' => null,
		'utmht' => null,
		'utmwv' => '5.6.4dc' );


	private $tracking;


	/**
	 * GoogleAnalytics constructor.
	 *
	 * @param null $UA
	 * @param null $domain
	 */
	public function __construct( $UA = null, $domain = null ) {
		$this->data['utmac'] = $UA;
		$this->data['utmhn'] = isset( $domain ) ? $domain : $_SERVER['SERVER_NAME'];
		$this->data['utmp'] = $_SERVER['PHP_SELF'];
		$this->data['utmn'] = rand( 1000000000, PHP_INT_MAX );
		$this->data['utmr'] = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
		$this->data['utmcc'] = $this->create_cookie();
		$this->data['utmhid'] = rand( 1000000000, PHP_INT_MAX );
		$this->data['utmht'] = time() * 1000;
	}

	/**
	 * Create the GA callback url, aka the gif
	 *
	 * @return string
	 */
	public function create_gif() {
		$data = array();
		foreach ( $this->data as $key => $item ) {
			if ( $item !== null ) {
				$data[$key] = $item;
			}
		}
		return $this->tracking = self::GA_URL . '?' . http_build_query( $data );
	}

	/**
	 * Send tracking code/gif to GB
	 *
	 * @return
	 */
	public function send() {
		if ( !isset( $this->tracking ) )
			$this->create_gif();
		return $this->remote_call();
	}

	/**
	 * Use WP's HTTP class or CURL or fopen
	 * @return void
	 */
	private function remote_call() {
		if ( function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $this->tracking );
			curl_setopt( $ch, CURLOPT_HEADER, false );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false); //@nczz Fixed HTTPS GET method
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			curl_exec( $ch );
			curl_close( $ch );
		} else {
			$handle = fopen( $this->tracking, "r" );
			fclose( $handle );
		}
		return;
	}

	/**
	 * Reset Defaults
	 * @return null
	 */
	public function reset() {
		$data = array(
			'utmac' => null,
			'utmcc' => $this->create_cookie(),
			'utmcn' => null,
			'utmcr' => null,
			'utmcs' => null,
			'utmdt' => '-',
			'utmfl' => '-',
			'utme' => null,
			'utmni' => null,
			'utmje' => '0',
			'utmn' => rand( 1000000000, PHP_INT_MAX ),
			'utmp' => isset( $_SERVER['PHP_SELF'] ) ? $_SERVER['PHP_SELF'] : '',
			'utmr' => isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
			'utmsc' => '-',
			'utmsr' => '-',
			'utmt' => null,
			'utme' => null,
			'utmttx' => null,
			'utmul' => 'php',
			'utmht' => time() * 1000,
			'utmwv' => '5.6.4dc' );
		$this->tracking = null;
		return $this->data = $data;
	}

	/**
	 * Create unique cookie
	 * @return string
	 */
	private function create_cookie() {
		$rand_id = rand( 10000000, PHP_INT_MAX );
		$random = rand( 1000000000, PHP_INT_MAX );
		$var = '-';
		$time = time();
		$cookie = '';
		$cookie .= '__utma=' . $rand_id . '.' . $random . '.' . $time . '.' . $time . '.' . $time . '.2;+';
		$cookie .= '__utmb=' . $rand_id . ';+';
		$cookie .= '__utmc=' . $rand_id . ';+';
		$cookie .= '__utmz=' . $rand_id . '.' . $time . '.2.2.utmccn=(direct)|utmcsr=(direct)|utmcmd=(none);+';
		$cookie .= '__utmv=' . $rand_id . '.' . $var . ';';
		return $cookie;
	}


	//////////
	// Misc //
	//////////
	public function set_java( $var = null ) {
		return $this->data['utmje'] = $var;
	}
	public function set_encode_type( $var = null ) {
		return $this->data['utmcs'] = $var;
	}
	public function set_flash_version( $var = null ) {
		return $this->data['utmfl'] = $var;
	}
	public function set_host( $var = null ) {
		return $this->data['utmhn'] = $var;
	}
	public function set_screen_depth( $var = null ) {
		return $this->data['utmsc'] = $var;
	}
	public function set_screen_resolution( $var = null ) {
		return $this->data['utmsr'] = $var;
	}
	public function set_lang( $var = null ) {
		return $this->data['utmul'] = $var;
	}
	public function set_ga_version( $var = null ) {
		return $this->data['utmwv'] = isset( $var ) ? $var : $this->data['utmwv'];
	}
	//////////
	// Page //
	//////////
	public function set_page( $var = null ) {
		return $this->data['utmp'] = $var;
	}
	public function set_page_title( $var = null ) {
		return $this->data['utmdt'] = $var;
	}
	public function set_campaign( $var=null ) {
		return $this->data['utmcn'] = $var;
	}
	public function clone_campaign( $var=null ) {
		return $this->data['utmcr'] = $var;
	}
	public function set_referal( $var = null ) {
		return $this->data['utmr'] = $var;
	}
	////////////
	// Events //
	////////////
	public function set_event( $category, $action, $label = '', $value = '', $opt_noninteraction = false) {
		$event_category = (string) $category;
		$event_action = (string) $action;
		$event_string = '5(' . $event_category . '*' . $event_action;
		if (!empty($label)) {
			$event_string .= '*' . ((string) $label) . ')';
		} else {
			$event_string .= ')';
		}
		if (!empty($value)) {
			$event_string .= '(' . ((int) intval($value)) . ')';
		}
		if ($opt_noninteraction) {
			$this->data['utmni'] = '1';
		}
		$this->data['utmt'] = 'event';
		return $this->data['utme'] = $event_string;
	}


	/**
	 * Create and send a transaction object
	 *
	 * Parameter order from https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingEcommerce
	 */
	public function send_transaction($transaction_id, $affiliation, $total, $tax, $shipping, $city, $region, $country) {
		$this->data['utmvw'] = '5.6.4dc';
		$this->data['utms'] = ++self::$requests_for_this_session;
		$this->data['utmt'] = 'tran';
		$this->data['utmtid'] = $transaction_id;
		$this->data['utmtst'] = $affiliation;
		$this->data['utmtto'] = $total;
		$this->data['utmttx'] = $tax;
		$this->data['utmtsp'] = $shipping;
		$this->data['utmtci'] = $city;
		$this->data['utmtrg'] = $region;
		$this->data['utmtco'] = $country;
		$this->data['utmcs'] = 'UTF-8';

		$this->send();
		$this->reset();

		return $this;
	}

	/**
	 * Add item to the created $transaction_id
	 *
	 * Parameter order from https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingEcommerce
	 */
	public function send_item($transaction_id, $sku, $product_name, $variation, $unit_price, $quantity) {
		$this->data['utmvw'] = '5.6.4dc';
		$this->data['utms'] = ++self::$requests_for_this_session;
		$this->data['utmt'] = 'item';
		$this->data['utmtid'] = $transaction_id;
		$this->data['utmipc'] = $sku;
		$this->data['utmipn'] = $product_name;
		$this->data['utmiva'] = $variation;
		$this->data['utmipr'] = $unit_price;
		$this->data['utmiqt'] = $quantity;
		$this->data['utmcs'] = 'UTF-8';

		$this->send();
		$this->reset();

		return $this;
	}
}
