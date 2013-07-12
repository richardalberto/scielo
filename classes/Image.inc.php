<?php
/**
 * @file Image.inc.php
 *
 * Copyright (c) 2010 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_scielo_classes
 * @brief Image class.
 *
 */

class Image{
	var $name;
	var $description;
	var $fileId;
	
	function Image($name, $description, $fileId){
		$this->name = $name;
		$this->description = $description;
		$this->fileId = $fileId;
	}
	
	function getName(){
		return $this->name;
	}
	
	function getDescription(){
		return $this->description;
	}
	
	function getFileId(){
		return $this->fileId;
	}
}
?>