<?php 
/**
 * What : Class for Accesing RajaOngkir API
 * When : on Calculate Cost
 * 
 * @see : https://docs.lsdplugins.com/en/docs/shipping-rajaongkir/
 * 
 * @package LSDCommerce
 * @subpackage Order/Insert
 * @since 1.0.0
 *
 * @param array $detail
 * @return int `cost` 
 */
class LSDCommerce_RajaOngkir {
	private $type;
	private $base_url;
	private $api;

	public function __construct( $type, $api ) {
		switch ($type) {
			case 'starter':
				$this->base_url = 'https://api.rajaongkir.com/starter/';
				break;
			case 'pro':
				$this->base_url = 'https://pro.rajaongkir.com/api/';
				break;
			default:
				$this->base_url = 'https://api.rajaongkir.com/starter/';
				break;
		}
		$this->api = $api;
	}

	public function get( $method, $query ){

		$payload = array(
			'method' => 'GET',
			'timeout' => 30,
			'headers'     => array(
				'key' => $this->api,
				'Content-Type'  => 'application/json',
			),
			'httpversion' => '1.0',
			'cookies' => array()
		);
		
		$response = wp_remote_get( $this->base_url . $method . $query, $payload );		
		return json_decode( wp_remote_retrieve_body( $response ), TRUE );
	}

	public function post( $method, $data){
		$payload = array(
			'method' => 'POST',
			'timeout' => 30,
			'headers'     => array(
				'key' => $this->api,
				'Content-Type'  => 'application/json',
			),
			'httpversion' => '1.0',
			'body' => json_encode( $data ),
			'cookies' => array()
		);
		
		$response = wp_remote_post( $this->base_url . $method , $payload );		
		return json_decode( wp_remote_retrieve_body( $response ), TRUE );
	}

	public function cost( $origin, $destination, $weight, $courier ){

		$data = array(
			'origin' 		=> $origin,
			'destination' 	=> $destination,
			'weight' 		=> $weight,
			'courier' 		=> $courier
		);

		return self::post( 'cost' , $data );
	}
}
?>