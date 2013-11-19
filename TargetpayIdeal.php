<?php
	/**
	 * Targetpay iDEAL class
	 * @author Keim Websolutions (info@keimwebsolutions.nl)
	 * @copyright Free to use, edit and distrubute with your changes. Please keep my author name.
	 */
	class TargetpayIdeal {

		/**
		 * Options for the targetpay iDEAL API
		 * @var array
		 */
		private $options = array(
			'layoutcode'=>null,
			'test'=>false
		);

		/**
		 * Used for caching banks. Use getBanks() method. Don't change this attribute.
		 * @var array
		 */
		private $banks = null;

		/**
		 * The prepared transaction url
		 * @var string
		 */	
		private $transactionURL = null;

		/**
		 * The transaction id
		 * @var string
		 */
		private $transactionID = null;
                
                
                /**
                 * After (trying to) create transaction it contains the response.
                 * @var string 
                 */
                private $response = null;

		/**
		 * Constructor for Targatpay iDEAL API class
		 * @param array $options The options for this iDEAL API class.
		 */	
		public function __construct($options = array()) {
			$this->setOptions($options);
		}


		/**
		 * Set the options for the Targetpay API class
		 * @param array $options The options
		 */
		public function setOptions(array $options) {
			foreach($options as $name=>$value) {
				$this->setOption($name,$value);
			}
		}

		/**
		 * Set an Targetpay API Option
		 * @param string $name  The name of the Option
		 * @param string $value The value of the Option
		 */
		public function setOption($name,$value) {
			if(!array_key_exists($name, $this->options)) throw new TargetpayIdeal_Exception('Option "'.$name.'" not found');

			$this->options[$name] = $value;
		}


		/**
		 * Returns an option
		 * @param  string $name Name of the option
		 * @return string       The value of the option
		 */
		public function getOption($name) {
			if(!isset($this->options[$name])) throw new TargetpayIdeal_Exception('Option "'.$name.'" not found');

			return $this->options[$name] = $value;
		}


		/**
		 * Will return an array with all the support banks from targetpay or the cached bank object when this method is called earlier.
		 * @return array Array with all banks
		 */
		public function getBanks() {

			//When the banks are cached, return cached banks,
			if(is_array($this->banks) && count($this->banks) > 0) return $this->banks;

			//URL to bank list
			$url = 'https://www.targetpay.com/ideal/getissuers.php?format=xml';

			$contents = file_get_contents($url);

			$xml = new SimpleXMLElement($contents);

			if(count($xml->issuer) < 1) throw new TargetpayIdeal_Exception('Error while parsing returned XML with the support banks');

			$this->banks = array();

			foreach($xml->issuer as $issuer) {
				$this->banks[(string)$issuer['id']] = (string)$issuer;
			}

			return $this->banks;
		}

		/**
		 * Checks if the bank is supported/exists
		 * @param  int $bank_id The bank ID
		 * @return bool true when bank is supported, false when not supported.
		 */
		public function bankExists($bank_id) {
			$banks = $this->getBanks();

			return isset($banks[$bank_id]);
		}


		/**
		 * Prepares the payment
		 * @param  int $bank        The bank id
		 * @param  string $description Description of the payment
		 * @param  int $amount      The amount in cents
		 * @param  string $returnurl   The return url
		 * @param  string $reporturl   The report url
		 * @param  string $language    The language (default: nl)
		 * @return bool              True when payment is prepared false when payment preparation has failed.
		 */
		public function preparePayment($bank,$description,$amount,$returnurl,$reporturl=null,$language='nl') {
			
			$url = 'https://www.targetpay.com/ideal/start';
			
			$rtlo = $this->options['layoutcode'];

			if(!$this->bankExists($bank)) throw new TargetpayIdeal_Exception("Bank not supported");

			if($amount < 84 || $amount > 1000000) throw new TargetpayIdeal_Exception("The amount must be in cents. And be minimal 84 cents and maximal 1000000 cents.");			

			$url .= '?rtlo='.urlencode($rtlo);
			$url .= '&bank='.urlencode($bank);
			$url .= '&description='.urlencode($description);
			$url .= '&amount='.urlencode(round($amount));
			$url .= '&language='.urlencode($language);
			$url .= '&returnurl='.urlencode($returnurl);
			if($reporturl != null) $url .= '&reporturl='.urlencode($reporturl);
			$this->response = file_get_contents($url);
                        
			$aResponse = explode('|',$this->response);
			if (!isset($aResponse[1])) return false;
			$responseType = explode (' ',$aResponse[0]);

			if($responseType[0] != "000000") return false;

			$this->transactionURL = $aResponse[1];
			$this->transactionID = $responseType[1];

			return true;

		}
                
                public function getResponse()
                {
                    return $this->response;
                }

		/**
		 * Returns the transaction url, null when payment is not prepared.
		 * @return string The transaction URL
		 */
		public function getTransactionURL() {
			return $this->transactionURL;
		}

		/**
		 * Returns the transaction id, null when payment is not prepared
		 * @return string The transaction id
		 */
		public function getTransactionID() {
			return $this->transactionID;
		}

		/**
		 * Check an payment
		 * @param  string  $transactionID The transaction ID
		 * @param  boolean $once          Check only once
		 * @return boolean                 True when payment is successful, false when unsucesfull
		 */
		public function checkPayment($transactionID,$once=true) {
			$url = 'https://www.targetpay.com/ideal/check';
			
			$rtlo = $this->options['layoutcode'];

			$url .= '?rtlo='.urlencode($rtlo);
			$url .= '&trxid='.urlencode($transactionID);
			$url .= '&once='.($once == true ? '1' : '0');
			$url .= '&test='.($this->options['test'] == true ? '1' : '0');
			$response = file_get_contents($url);

			

			return $response == '000000 OK';
		}


	}


	class TargetpayIdeal_Exception extends Exception {}