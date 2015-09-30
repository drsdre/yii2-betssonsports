<?php
/**
 * Nordicbet.com API Yii2 Client Component
 *
 * @author Andre Schuurman <andre.schuurman@gmail.com>
 * @license MIT License
 */

namespace BetssonSports;

use SoapClient;
use SoapFault;
use yii\base\Component;
use yii\base\InvalidConfigException;

use XMLSoccer\Exception;

class Client extends Component {
	/**
	 * @var string url API endpoint
	 */
	public $service_url = "https://sbpartner.bpsgameserver.com/PartnerService/PartnerServicesV2.svc";

	/**
	 * @var array the array of SOAP client options.
	 */
	public $options = [];

	/**
	 * @var string login name of account
	 */
	public $loginName;

	/**
	 * @var string password of account
	 */
	public $password;

	/**
	 * @var SoapClient the SOAP client instance.
	 */
	private $_client;

	/**
	 * Initialize component
	 *
	 * @throws InvalidConfigException
	 */
	public function init() {
		parent::init();

		if ( empty( $this->service_url ) ) {
			throw new InvalidConfigException( "service_url cannot be empty. Please configure." );
		}
		if ( empty( $this->loginName ) ) {
			throw new InvalidConfigException( "loginName cannot be empty. Please configure." );
		}
		if ( empty( $this->password ) ) {
			throw new InvalidConfigException( "password cannot be empty. Please configure." );
		}
		try {
			$this->_client = new SoapClient($this->service_url, $this->options);
		} catch (SoapFault $e) {
			throw new Exception($e->getMessage(), (int) $e->getCode(), $e);
		}
	}

	/**
	 *	list available methods with params.
	 */
	public function __call( $name, $params ) {

		// prepend username/passwords to arguments
		$params = array_merge(
			[
				'loginName' => $this->loginName,
				'password' => $this->password,
			],
			$params
		);

		// Connect and do the SOAP call
		try {
			$result = $this->_client->$name($params);

		} catch (SoapFault $e) {
			throw new Exception($e->getMessage(), (int) $e->getCode(), $e);
		}
		return $result;
	}
}