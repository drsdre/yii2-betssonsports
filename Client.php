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

use BetssonSports\Cache;
use BetssonSports\Exception;
use BetssonSports\models\BetssonCategory;

class Client extends Component {
	/**
	 * @var string url API endpoint
	 */
	public $service_url = "https://sbpartner.bpsgameserver.com/PartnerService/PartnerServicesV2.svc?wsdl";

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
	 * @var string language code
	 */
	private $LanguageCode = 'EN';

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

	public function setLanguageCode($LanguageCode) {
		$this->LanguageCode = $LanguageCode;
	}

	/**
	 *	list available methods with params.
	 */
	public function __call( $name, $params ) {

		// Set username/passwords for call
		$call_params = [
			'loginName' => $this->loginName,
			'password' => $this->password,
			'languageCode' => $this->LanguageCode,
		];

		// Add additional parameters
		if (isset($params[0])) {
			$call_params = array_merge($call_params, $params[0]);
		}

		// Connect and do the SOAP call
		try {
			$result = $this->_client->$name($call_params);

		} catch (SoapFault $e) {
			throw new Exception($e->getMessage(), (int) $e->getCode(), $e);
		}
		return $result;
	}

	/**
	 * Update the cache
	 *
	 * @param bool|false|string $force Force full data retrieval, or update or no data update
	 * @param bool|false $expire Expire data in the database
	 *
	 * @return array process statistics
	 */
	public function updateCache($force = false, $expire = false) {

		$cache = new Cache($this);

		// Init data if force or no existing categories
		if ($force === false || BetssonCategory::find()->count() == 0) {
			$cache->initData();
		} elseif ($force === true) {
			$cache->updateData();
		}

		// Expire data
		if ($expire) {
			$cache->expireData();
		}
		return $cache->getStatistics();
	}
}