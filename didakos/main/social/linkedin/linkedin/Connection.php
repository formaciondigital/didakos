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

class LinkedIn_Connection
{
	protected $_firstname;
	protected $_id;
	protected $_lastname;
	protected $_headline;
	protected $_location;
	protected $_industry;
	protected $_api;
	protected $_siteUrl;
	
	
	public function __construct ()
	{
		
	}
	
	public function toArray()
	{
		
	}
	
	/**
	 * @param $siteUrl the $siteUrl to set
	 */
	public function setSiteUrl($siteUrl) {
		$this->_siteUrl = $siteUrl;
	}

	/**
	 * @return the $siteUrl
	 */
	public function getSiteUrl() {
		return $this->_siteUrl;
	}

	/**
	 * @param $api the $api to set
	 */
	public function setApi($api) {
		$this->_api = $api;
	}

	/**
	 * @return the $api
	 */
	public function getApi() {
		return $this->_api;
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
	 * @param $location the $location to set
	 */
	public function setLocation($location) {
		$this->_location = $location;
	}

	/**
	 * @return the $location
	 */
	public function getLocation() {
		return $this->_location;
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

}