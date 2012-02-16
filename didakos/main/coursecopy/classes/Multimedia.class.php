<?php // $Id: Link.class.php 6738 2005-10-28 07:59:36Z bmol $
/*
============================================================================== 
	Dokeos - elearning and course management software
	
	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) Bart Mollet (bart.mollet@hogent.be)
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
============================================================================== 
*/
require_once('Resource.class.php');
/**
 * A WWW-link from the Links-module in a Dokeos-course.
 * @author Bart Mollet <bart.mollet@hogent.be>
 */
class Multimedia extends Resource
{
	var $title;
	var $description;
	var $duration;
	var $source_id;
	var $target;
	
	function Multimedia($id,$title,$description,$duration,$source_id,$target)
	{
		parent::Resource($id,RESOURCE_MULTIMEDIA);
		$this->title = $title;
		$this->description = $description;
		$this->duration = $duration;
		$this->source_id = $source_id;
		$this->target = $target;
	}
	/**
	 * Show this resource
	 */
	function show()
	{
		parent::show();
		echo $this->title.' ('.$this->title.')';	
	}
}
?>
