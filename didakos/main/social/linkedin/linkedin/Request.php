<?php
/* Copyright (C) 2009 Winfred Peereboom  <wpeereboom@developmentit.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * or see http://www.gnu.org/
 */

class LinkedIn_Request
{

	protected $_oauth;
	protected $_state;
	protected $_secret;
	protected $_token;
	protected $_oauth_token_secret;
	protected $_oauth_token;
	protected $_oauth_verifier;
	protected $_profile;
	protected $_profielFields;
	protected $_connections;	
	protected $_connectionObjs = array();	
	protected $_numConnections;
	protected $_profileFields;
	protected $__profileObj;
	
	public function __construct() 
	{
		
		$oauth = new OAuth(CUSTOMER_KEY, CUSTOMER_SECRET,
						   OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
						   
  		$oauth->setAuthType(OAUTH_AUTH_TYPE_AUTHORIZATION);
  		$oauth->setNonce(rand());
  		$oauth->enableDebug();
  		$this->setOauth($oauth);
        
  	
        if(isset($_GET['oauth_token']) && $_GET['oauth_token'] != "") {
        	$this->setOauth_token($_GET['oauth_token']);
        }
  		        
  		if(!$this->getOauth_token() && !$this->getState()) {
  			$this->login();
  		} else if($this->getState() == 1) {

  			$oauth->setToken($this->getOauth_token(), $this->getOauth_token_secret());
		   	
            $access_token_info = $oauth->getAccessToken(BASE_API_URL . ACC_PATH);
    		
    		$this->setState(2);
		    $this->setToken($access_token_info['oauth_token']);
		    $this->setSecret($access_token_info['oauth_token_secret']);
		    $this->setOauth_verifier($_GET['oauth_verifier']);
  		}
	} 
	
	public function login() 
	{	
		$request_token_info = $this->_oauth->getRequestToken(BASE_API_URL . REQUEST_PATH);
		$this->setOauth_token_secret($request_token_info['oauth_token_secret']);
		$this->setOauth_token($request_token_info['oauth_token']);
		$this->setState(1);
		
		header('Location: '. BASE_API_URL . AUTH_PATH .
										'?oauth_token='.$this->getOauth_token());
    	exit;    	
	}
	
	public function pullProfile(array $fields = null) 
	{
		$this->_oauth->setToken($this->getToken(),$this->getSecret());
		if(count($fields) == 0) {
			$fields = array(
						  'id',
						  'first-name',
						  'last-name',
						  'summary',
						  'positions',
						  'educations'
		                  );
		}
				
		$this->setProfileFields($fields);
		$uri = BASE_API_URL . '/v1/people/~:(' . implode(",",$fields) . ')';
		                  
		$this->_oauth->fetch($uri);		  
		$profile = $this->_oauth->getLastResponse();
		$this->setProfile($profile);		    
		return $this->parseProfile();
	}
	
	public function parseProfile ()
	{
		$profileXML = simplexml_load_string($this->getProfile());
		$fields = $this->getProfileFields();
		
		$profile = new LinkedIn_Profile();
	
		if(isset($profileXML->{'first-name'}))
			$profile->setFirstname($profileXML->{'first-name'});
		
		if(isset($profileXML->{'last-name'}))
			$profile->setLastname($profileXML->{'last-name'});

		if(isset($profileXML->headline))
			$profile->setHeadline($profileXML->headline);	
		
		if(isset($profileXML->location->name))
			$profile->setLocationName($profileXML->location->name);

		if(isset($profileXML->location->country->code))
			$profile->setLocationCountryCode($profileXML->location->country->code);

		if(isset($profileXML->industry))
			$profile->setIndustry($profileXML->industry);

		if(isset($profileXML->distance))
			$profile->setDistance($profileXML->distance);

		if(isset($profileXML->{'current-status'}))
			$profile->setCurrentStatus($profileXML->{'current-status'});

		if(isset($profileXML->{'relation-to-viewer'}->distance))
			$profile->setRelationToViewerDistance(
							$profileXML->{'relation-to-viewer'}->distance);	

		if(isset($profileXML->{'relation-to-viewer'}->connections))
			$profile->setRelationToViewerConnections(
							$profileXML->{'relation-to-viewer'}->connections);	

		if(isset($profileXML->{'api-standard-profile-request'}->headers))
			$profile->setApiStandardProfileRequestHeaders(
							$profileXML->{'api-standard-profile-request'}->headers);

		$this->_profileObj = $profile;
		return $profile;							
	}
	
	public function pullConnections ()
	{
		 $this->_oauth->setToken($this->getToken(),$this->getSecret());		  			 
		 $this->_oauth->fetch(BASE_API_URL . '/v1/people/~/connections');
		 $response =  $this->_oauth->getLastResponse();
		 $connections =  $this->_oauth->getLastResponse();
		 $this->setConnections($connections);
		 $this->parseConnections();
		 return $this->getConnectionObjs();
	}
	
	public function parseConnections ()
	{
		$xml = simplexml_load_string($this->getConnections());
		foreach($xml->children() AS $person) {
			$connection = new LinkedIn_Connection();

			$connection->setId((string) $person->id);
			$connection->setFirstname((string) $person->{'first-name'});
			$connection->setLastname((string) $person->{'last-name'});
			$connection->setHeadline((string) $person->headline);
			$connection->setIndustry((string) $person->industry);
			
    		$locations = array();
			foreach($person->location AS $location) {
				$locationItem = array ();
				$locationItem['name'] = $location->name;
				$locationItem['countryCode'] = $location->country->code;
				array_push($locations, $locationItem); 
			}
			$connection->setLocation($locations);

			$this->setConnectionObjs($connection);
		}
	}
	
	public function setProfileFields($fields) 
	{
		$this->_profileFields = $fields;
	}
	
	public function getProfileFields()
	{
		return $this->_profileFields;	
	}
	
	public function setConnectionObjs($connection)
	{
		$this->_connectionObjs[] = $connection; 	
	}
	
	public function getConnectionObjs()
	{
		return $this->_connectionObjs;
	}
	
	/**
	 * @param $_numConnections the $_numConnections to set
	 */
	public function setNumConnections($numConnections) {
		$this->_numConnections = $numConnections;
	}

	/**
	 * @param $_connections the $_connections to set
	 */
	public function setConnections($connections) {
		$this->_connections = $connections;
	}

	/**
	 * @param $_profielFields the $_profielFields to set
	 */
	public function setProfielFields($profielFields) {
		$this->_profielFields = $profielFields;
	}

	/**
	 * @param $_profile the $_profile to set
	 */
	public function setProfile($profile) {
		$this->_profile = $profile;
	}

	/**
	 * @param $_oauth_verifier the $_oauth_verifier to set
	 */
	public function setOauth_verifier($oauth_verifier) {
		$this->_oauth_verifier = $oauth_verifier;
		$_SESSION['_oauth_verifier'] = $oauth_verifier;
	}

	/**
	 * @param $_oauth_token the $_oauth_token to set
	 */
	public function setOauth_token($oauth_token) {
		$this->_oauth_token = $oauth_token;
		$_SESSION['_oauth_token'] = $oauth_token;
	}

	/**
	 * @param $_oauth_token_secret the $_oauth_token_secret to set
	 */
	public function setOauth_token_secret($oauth_token_secret) {
		$this->_oauth_token_secret = $oauth_token_secret;
		$_SESSION['_oauth_token_secret'] = $oauth_token_secret;
	}

	/**
	 * @param $_token the $_token to set
	 */
	public function setToken($token) {
		$this->_token = $token;
		$_SESSION['_token'] = $token;
	}

	/**
	 * @param $_secret the $_secret to set
	 */
	public function setSecret($secret) {
		$this->_secret = $secret;
		$_SESSION['_secret'] = $secret;
	}

	/**
	 * @param $_state the $_state to set
	 */
	public function setState($state) {
		$this->_state = $state;
		$_SESSION['_state'] = $state;
	}

	/**
	 * @param $_oauth the $_oauth to set
	 */
	public function setOauth($oauth) {
		$this->_oauth = $oauth;
	}

	/**
	 * @return the $_numConnections
	 */
	public function getNumConnections() {
		return $this->_numConnections;
	}

	/**
	 * @return the $_connections
	 */
	public function getConnections() {
		return $this->_connections;
	}

	/**
	 * @return the $_profielFields
	 */
	public function getProfielFields() {
		return $this->_profielFields;
	}

	/**
	 * @return the $_profile
	 */
	public function getProfile() {
		return $this->_profile;
	}

	/**
	 * @return the $_oauth_verifier
	 */
	public function getOauth_verifier() {
		if(isset($_SESSION['_oauth_verifier']))
		  $this->setOauth_verifier($_SESSION['_oauth_verifier']);
		  
		return $this->_oauth_verifier;
	}

	/**
	 * @return the $_oauth_token
	 */
	public function getOauth_token() {
		if(isset($_SESSION['_oauth_token']))
          $this->setOauth_token($_SESSION['_oauth_token']);

        if(null != $this->_oauth_token && "" != $this->_oauth_token) {  
		  return $this->_oauth_token;
        } else {
        	return false;
        }
	}

	/**
	 * @return the $_oauth_token_secret
	 */
	public function getOauth_token_secret() {
		if(isset($_SESSION['_oauth_token_secret']))
          $this->setOauth_token_secret($_SESSION['_oauth_token_secret']);
          
		return $this->_oauth_token_secret;
	}

	/**
	 * @return the $_token
	 */
	public function getToken() {
		if(isset($_SESSION['_token']))
          $this->setToken($_SESSION['_token']);
          
		return $this->_token;
	}

	/**
	 * @return the $_secret
	 */
	public function getSecret() {
		if(isset($_SESSION['_secret']))
          $this->setSecret($_SESSION['_secret']);
          
		return $this->_secret;
	}

	/**
	 * @return the $_state
	 */
	public function getState() {
		if(isset($_SESSION['_state']))
          $this->setState($_SESSION['_state']);

	    if(null != $this->_state && "" != $this->_state) {  
          return $this->_state;
        } else {
            return false;
        }  
	}

	/**
	 * @return the $_oauth
	 */
	public function getOauth() {
		return $this->_oauth;
	}
}