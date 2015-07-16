<?php
/**
 * This PHP class file will connect to an AFAS Software AppConnector.
 * The connection is based on a One Time Password and a user-bound token.
 * @author Gerard Nijboer
 *
 */
class AFASappconnector {
	// Configuration variable
	private $config;
	// This function is triggered whenever a new instance of the PHP class is instantiated
	public function AFASappconnector($config = array()) {
		// Save the configuration to the local variable
		$this->config = $config;
	}
	
	/**
	 * Generate a One Time Password for the given userId.
	 * The environmentKey is distilled from the global config file.
	 * An OTP will be sent to the email address of the Profit user, if known.
	 *
	 * @param string $userId        	
	 * @return multitype:NULL
	 */
	public function GenerateOTP($userId) {
		$data = array (
				'userId' => $userId,
				'apiKey' => $this->config ['apiKey'],
				'environmentKey' => $this->config ['environmentKey'],
				'description' => $_SERVER ['REMOTE_ADDR'] 
		);
		try {
			$client = new SoapClient ( $this->config ['webservices'] . 'tokenconnector.asmx?WSDL' );
			return $client->GenerateOTP ( $data );
		} catch ( SoapFault $e ) {
			throw $e;
		}
	}
	public function GetTokenFromOTP($userId, $otp) {
		$data = array (
				'userId' => $userId,
				'apiKey' => $this->config ['apiKey'],
				'environmentKey' => $this->config ['environmentKey'],
				'otp' => $otp 
		);
		try {
			$client = new SoapClient ( $this->config ['webservices'] . 'tokenconnector.asmx?WSDL' );
			return $client->GetTokenFromOTP ( $data );
		} catch ( SoapFault $e ) {
			throw $e;
		}
	}
	public function GetData($token, $connectorId, $filtersXml, $skip = 0, $take = 10) {
		// Prepare the data to send with the postback
		$data = array (
				'token' => '<token><version>1</version><data>' . $token . '</data></token>',
				'connectorId' => $connectorId,
				'filtersXml' => $filtersXml,
				'skip' => $skip,
				'take' => $take 
		);
		
		try {
			$client = new SoapClient ( $this->config ['webservices'] . 'appconnectorget.asmx?WSDL' );
			return simplexml_load_string ( $client->GetData ( $data )->GetDataResult );
		} catch ( SoapFault $e ) {
			throw $e;
		}
	}
	public function Execute($token, $connectorType, $dataXml, $connectorVersion = 1) {
		$data = array (
				'token' => '<token><version>1</version><data>' . $token . '</data></token>',
				'connectorType' => $connectorType,
				'connectorVersion' => $connectorVersion,
				'dataXml' => $dataXml 
		);
		try {
			$client = new SoapClient ( $this->config ['webservices'] . 'appconnectorupdate.asmx?WSDL' );
			return $client->Execute ( $data );
		} catch ( SoapFault $e ) {
			throw $e;
		}
	}
	public function DeleteToken($token) {
		$data = array (
				'token' => '<token><version>1</version><data>' . $token . '</data></token>' 
		);
		try {
			$client = new SoapClient ( $this->config ['webservices'] . 'tokenconnector.asmx?WSDL' );
			return $client->DeleteToken ( $data );
		} catch ( SoapFault $e ) {
			throw $e;
		}
	}
}
class AFASfilterxml {
	private $filters;
	public function AFASfilterxml() {
		$this->filters = array ();
	}
	public function addFilter(array $fields) {
		$result = '<Filter FilterId="' . rand ( 0, time () ) . '">';
		foreach ( $fields as $field ) {
			$result .= '<Field OperatorType="' . $field->OperatorType . '" FieldId="' . $line->FieldId . '">' . $line->Value . '</Field>';
		}
		$result .= '</Filter>';
		$this->filters [] = $result;
	}
	public function getFilterXml() {
		$result = '<Filters>';
		foreach ( $this->filters as $filter ) {
			$result .= $filter;
		}
		$result .= '</Filters';
		return $result;
	}
}