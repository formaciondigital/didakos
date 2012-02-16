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

class LinkedIn_Profile
{	
	protected $_id;
	protected $_firstname;
	protected $_lastname;
	protected $_headline;
	protected $_locationName;
	protected $_locationCountryCode;
	protected $_industry;
	protected $_distance;
	protected $_relationToViewerDistance;
	protected $_relationToViewerConnections;
	protected $_currentStatus;
	protected $_apiStandardProfileRequestHeaders;
	
	protected $_positions = array();
	protected $_eductions = array();
	 
	public function __construct()
	{
		
	}

	
	
	/**
	 * @param $eductions the $eductions to set
	 */
	public function setEductions($eductions) {
		$this->_eductions = $eductions;
	}

	/**
	 * @return the $eductions
	 */
	public function getEductions() {
		return $this->_eductions;
	}

	/**
	 * @param $positions the $positions to set
	 */
	public function setPositions($positions) {
		$this->_positions = $positions;
	}

	/**
	 * @return the $positions
	 */
	public function getPositions() {
		return $this->_positions;
	}

	/**
	 * @param $apiStandardProfileRequestHeaders the $apiStandardProfileRequestHeaders to set
	 */
	public function setApiStandardProfileRequestHeaders($apiStandardProfileRequestHeaders) {
		$this->_apiStandardProfileRequestHeaders = $apiStandardProfileRequestHeaders;
	}

	/**
	 * @return the $apiStandardProfileRequestHeaders
	 */
	public function getApiStandardProfileRequestHeaders() {
		return $this->_apiStandardProfileRequestHeaders;
	}

	/**
	 * @param $currentStatus the $currentStatus to set
	 */
	public function setCurrentStatus($currentStatus) {
		$this->_currentStatus = $currentStatus;
	}

	/**
	 * @return the $currentStatus
	 */
	public function getCurrentStatus() {
		return $this->_currentStatus;
	}

	/**
	 * @param $relationToViewerConnections the $relationToViewerConnections to set
	 */
	public function setRelationToViewerConnections($relationToViewerConnections) {
		$this->_relationToViewerConnections = $relationToViewerConnections;
	}

	/**
	 * @return the $relationToViewerConnections
	 */
	public function getRelationToViewerConnections() {
		return $this->_relationToViewerConnections;
	}

	/**
	 * @param $relationToViewerDistance the $relationToViewerDistance to set
	 */
	public function setRelationToViewerDistance($relationToViewerDistance) {
		$this->_relationToViewerDistance = $relationToViewerDistance;
	}

	/**
	 * @return the $relationToViewerDistance
	 */
	public function getRelationToViewerDistance() {
		return $this->_relationToViewerDistance;
	}

	/**
	 * @param $distance the $distance to set
	 */
	public function setDistance($distance) {
		$this->distance = $distance;
	}

	/**
	 * @return the $distance
	 */
	public function getDistance() {
		return $this->_distance;
	}

	/**
	 * @param $industry the $industry to set
	 */
	public function setIndustry($industry) {
		$this->_industry = $industry;
	}

	/**
	 * @return the $industry
	 */
	public function getIndustry() {
		return $this->_industry;
	}

	/**
	 * @param $locationCountryCode the $locationCountryCode to set
	 */
	public function setLocationCountryCode($locationCountryCode) {
		$this->_locationCountryCode = $locationCountryCode;
	}

	/**
	 * @return the $locationCountryCode
	 */
	public function getLocationCountryCode() {
		return $this->_locationCountryCode;
	}

	/**
	 * @param $locationName the $locationName to set
	 */
	public function setLocationName($locationName) {
		$this->_locationName = $locationName;
	}

	/**
	 * @return the $locationName
	 */
	public function getLocationName() {
		return $this->_locationName;
	}

	/**
	 * @param $headline the $headline to set
	 */
	public function setHeadline($headline) {
		$this->_headline = $headline;
	}

	/**
	 * @return the $headline
	 */
	public function getHeadline() {
		return $this->_headline;
	}

	/**
	 * @param $lastname the $lastname to set
	 */
	public function setLastname($lastname) {
		$this->_lastname = $lastname;
	}

	/**
	 * @return the $lastname
	 */
	public function getLastname() {
		return $this->_lastname;
	}

	/**
	 * @param $firstname the $firstname to set
	 */
	public function setFirstname($firstname) {
		$this->_firstname = $firstname;
	}

	/**
	 * @return the $firstname
	 */
	public function getFirstname() {
		return $this->_firstname;
	}

	/**
	 * @param $id the $id to set
	 */
	public function setId($id) {
		$this->_id = $id;
	}

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->_id;
	}
}