<?php
/**
 * @file ScieloCitation.inc.php
 *
 * Copyright (c) 2010 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_scielo_classes
 * @brief Scielo Citation class.
 *
 */

class ScieloCitation{
	var $citation;

	function ScieloCitation(&$citation){
		$this->citation = $citation;
	}
	
	function getCitation($type="html"){
		$citation = null;
		$contrib = $this->getContrib($type);
		$serial  = $this->getSerial($type);
		$monograph = $this->getMonograph($type);

		$citation .= $contrib;
		$citation .= $serial;
		if(trim($serial) == "") $citation .= $monograph;

		return $citation;
	}
	
	function getAuthors($source = false, $type="html"){
		if(!$source) $authors = $this->citation->getAuthors(false);
		else $authors = $source;

		if($type == "xml") { $tagS = "<"; $tagE = ">"; }
		else { $tagS = "["; $tagE = "]"; }
	
		
		$etal = false;		
		$vauthors = null;		
		// Authors
		if(!is_null($authors) && $authors != ""){
			$authorsArray = explode(",", trim($authors));
			foreach($authorsArray as $author){
				$author = rtrim(ltrim(str_replace('.', NULL, $author)));
				
				// Check for et al
				if(str_replace(" ", "", strtolower($author)) == "etal"){
					$etal = true;
				}
				// Add authors
				else{
					$authorParts = explode(" ", $author);
					$vauthors .= "{$tagS}author role=\"nd\"{$tagE}{$tagS}surname{$tagE}".trim($authorParts[0]);
					for($i=1; $i < (count($authorParts)-1); $i++){						
						if((count($authorParts)-1) != $i) $vauthors .= " ";
						$vauthors .= $authorParts[$i];
					}
					$vauthors .= "{$tagS}/surname{$tagE}{$tagS}fname{$tagE}".$authorParts[count($authorParts)-1]."{$tagS}/fname{$tagE}{$tagS}/author{$tagE}";
				}
			}
		}
		
		// Editors
		if($this->citation->getEditors()){			
			$editorsArray = explode(",", trim($this->citation->getEditors()));
			foreach($editorsArray as $editor){
				$editor = rtrim(ltrim(str_replace('.', NULL, $editor)));
				
				// Check for et al
				if(str_replace(" ", "", strtolower($editor)) == "etal"){
					$etal = true;
				}
				// Add editors
				// else{
				// 	$editorParts = explode(" ", $editor);
				// 	$vauthors .= "{$tagS}author role=\"ed\"{$tagE}{$tagS}surname{$tagE}".$editorParts[0]."{$tagS}/surname{$tagE}{$tagS}fname{$tagE}";
				// 	for($i=1; $i<=count($editorParts);$i++){
				// 		$vauthors .= $editorParts[$i];
				// 		if(count($editorParts) != $i) $vauthors .= " ";
				// 	}
				// 	$vauthors .= "{$tagS}/fname{$tagE}{$tagS}/author{$tagE}";
				// }
				
				else{
					$editorParts = explode(" ", $editor);
					$vauthors .= "{$tagS}author role=\"ed\"{$tagE}{$tagS}surname{$tagE}".trim($editorParts[0]);
					for($i=1; $i<(count($editorParts)-1);$i++){						
						if((count($editorParts)-1) != $i) $vauthors .= " ";
						$vauthors .= $editorParts[$i];
					}
					$vauthors .= "{$tagS}/surname{$tagE}{$tagS}fname{$tagE}".$editorParts[count($editorParts)-1]."{$tagS}/fname{$tagE}{$tagS}/author{$tagE}";
				}
				
			}
			
		}
		
		if($etal) $vauthors .= "{$tagS}et-al{$tagE}et al{$tagS}/et-al{$tagE}";
		
		return $vauthors;
	}
	
	function getShortLang(){
		switch($this->citation->locale){
			case "English": return "en"; break;
			case "Español (España)": return "es"; break;
		}
	}
	
	function getContrib($type){
		$contrib = null;
		
		if($this->citation->isArticleInJournal() || $this->citation->getBookChapter() || $this->citation->getConferenceArticle() || $this->citation->getSitePage()){
			$templateMgr = &new TemplateManager();
			if($this->citation->isArticleInJournal()){
				$templateMgr->assign('authors', $this->getAuthors(false, $type));
				$templateMgr->assign('title', $this->getTitle());
				$templateMgr->assign('type', $this->citation->getTypeTitle());
				$templateMgr->assign('subtitle', trim($this->getSubtitle()));
			}
			elseif($this->citation->getBookChapter()){
				$templateMgr->assign('authors', $this->getAuthors($this->citation->_data['bookChapAuthors'], $type));
				$templateMgr->assign('title', $this->getTitle($this->citation->_data['bookChapTitle']));
				$templateMgr->assign('subtitle', trim($this->getSubtitle($this->citation->_data['bookChapTitle'])));
			}
			elseif($this->citation->getConferenceArticle()){
				$templateMgr->assign('authors', $this->citation->getConferenceArticleAuthors(), $type);
				$templateMgr->assign('title', $this->getTitle($this->citation->getConferenceArticleTitle()));
				$templateMgr->assign('subtitle', trim($this->getSubtitle($this->citation->getConferenceArticleTitle())));
			}
			elseif($this->citation->getSitePage()){
				$templateMgr->assign('authors', false, $type);
				$templateMgr->assign('title', $this->getTitle($this->citation->getSitePage()));
				$templateMgr->assign('subtitle', trim($this->getSubtitle($this->citation->getSitePage())));
			}

			$templateMgr->assign('lang', $this->getShortLang());
			
			$scieloExportPlugin = &PluginRegistry::getPlugin('importexport', 'ScieloExportPlugin');
			$contrib = $templateMgr->fetch($scieloExportPlugin->getTemplatePath()."templates/{$type}/contrib.tpl");
		}
		
		return $contrib;
	}
	
	function getSerial($type){
		$serial = null;

		$url = $this->citation->getUrl();
		////remplace ampersand for valid scielo citations url validation
		//$url = str_replace('&amp;', '&', $this->citation->getUrl());
		// $url = str_replace('&', '&amp;amp;', $this->citation->getUrl());



		if($this->isSerial()){
			$templateMgr = &new TemplateManager();			
			$templateMgr->assign('journal', $this->citation->getSource());
			$templateMgr->assign('inpress', $this->citation->getForthcomingDate() ? true : false);
			$templateMgr->assign('type', $this->citation->getTypeSource());
			$templateMgr->assign('dateISO', $this->getDateISO());
			$templateMgr->assign('date', $this->citation->getDate());
			$templateMgr->assign('cited', $this->citation->getCitationDate());
			$templateMgr->assign('citedDateISO', $this->getCitedDateISO());
			$templateMgr->assign('volume', $this->citation->getVolume());
			$templateMgr->assign('issue', $this->citation->getIssue());
			$templateMgr->assign('suppl', $this->getSuppl());
			$templateMgr->assign('part', $this->getPart());
			$templateMgr->assign('url', $url);
			$templateMgr->assign('pages', $this->citation->getPages());
			$templateMgr->assign('extent', $this->citation->getPageCount());
			
			$scieloExportPlugin = &PluginRegistry::getPlugin('importexport', 'ScieloExportPlugin');
			$serial = $templateMgr->fetch($scieloExportPlugin->getTemplatePath()."templates/{$type}/serial.tpl");
		}

		return $serial;
	}
	
	function getMonograph($type){
		$monog = null;

		////remplace andpersend for valid scielo citations url validation 
		//$url = str_replace('&amp;', '&', $this->citation->getUrl());
		//$url = str_replace('&', '&amp;amp;', $this->citation->getUrl());

		if($this->isMonog()){
			$templateMgr = &new TemplateManager();
	
			$templateMgr->assign('authors', $this->getAuthors(false, $type));
			$templateMgr->assign('orgAuthors', $this->getMonographOrgAuthors());	
			$templateMgr->assign('title', $this->getTitle());
			$templateMgr->assign('subtitle', trim($this->getSubtitle()));
			$templateMgr->assign('type', $this->citation->getTypeTitle());
			$templateMgr->assign('lang', $this->getShortLang());
			$templateMgr->assign('edition', $this->remove_non_numeric($this->citation->getEdition()));
			$templateMgr->assign('confgrp', $this->getConference());
			$templateMgr->assign('city', $this->citation->getPubPlace());
			$templateMgr->assign('state', trim($this->citation->getState()));
			$templateMgr->assign('pubname', $this->citation->getEditorial());
			$templateMgr->assign('inpress', $this->citation->getForthcomingDate() ? true : false);
			$templateMgr->assign('date', $this->citation->getDate());
			$templateMgr->assign('dateISO', $this->getDateISO());
			$templateMgr->assign('pages', $this->citation->getPages());
			$templateMgr->assign('thesis', $this->getThesis());
			$templateMgr->assign('cited', $this->citation->getCitationDate());
			$templateMgr->assign('citedDateISO', $this->getCitedDateISO());
			$templateMgr->assign('url', $url);
			
			$scieloExportPlugin = &PluginRegistry::getPlugin('importexport', 'ScieloExportPlugin');
			$monog = $templateMgr->fetch($scieloExportPlugin->getTemplatePath()."templates/{$type}/monograph.tpl");
		}
		
		return $monog;
	}
	
	function isSerial(){
		if($this->citation->hasWebsiteDate()) return false;
		elseif($this->citation->isArticleInJournal()) return true;
		elseif($this->citation->getPubPlace() || !$this->citation->getEditorial()) return false;
		elseif($this->citation->isElectronicMaterial() && ($this->citation->getVolume() || !$this->citation->getIssue())) return true;
			
		return false;
	}
	
	function isMonog(){
		if($this->citation->isMonograph()) return true;
		elseif($this->citation->isElectronicMaterial() && !$this->citation->getVolume() && !$this->citation->getIssue()) return true;
		elseif($this->citation->hasWebsiteDate()) return true;
		
		return false;
	}
	
	function getDateISO(){
		$date = null;
		
		// Site Date
		if($this->citation->hasWebsiteDate()){
			$date = $this->citation->getDate()."0000";
		}
		elseif($this->citation->getForthcomingDate()){
			$date = $this->citation->getForthcomingDate()."0000";
		}
		// Pub Date
		else{
			$date = $this->citation->_data['year'];
			if(isset($this->citation->_data['month']) && ($this->citation->_data['month'] > 0)){
				if($this->citation->_data['month'] > 9)
					$date .= $this->citation->_data['month']; 
				else
					$date .= "0" . $this->citation->_data['month'];
			}
			else $date .= "00";
			if(isset($this->citation->_data['day']) && ($this->citation->_data['day'] > 0)){
				if($this->citation->_data['day'] > 9)
					$date .= $this->citation->_data['day']; 
				else
					$date .= "0" . $this->citation->_data['day'];
			}
			else $date .= "00";
		}
		
		return $date;
	}
	
	function getCitedDateISO(){
		if(!$this->citation->hasCitationDate()) return null;
		$date = $this->citation->_data['citYear'];
		if(isset($this->citation->_data['citMonth']) && ($this->citation->_data['citMonth'] > 0)){
			if($this->citation->_data['citMonth'] > 9)
				$date .= $this->citation->_data['citMonth']; 
			else
				$date .= "0" . $this->citation->_data['citMonth'];
		}
		else $date .= "00";
		if(isset($this->citation->_data['citDay']) && ($this->citation->_data['citDay'] > 0)){
			if($this->citation->_data['citDay'] > 9)
				$date .= $this->citation->_data['citDay']; 
			else
				$date .= "0" . $this->citation->_data['citDay'];
		}
		else $date .= "00";
		
		return $date;
	}
	
	
	function getTitle($title = null){
		if(!is_null($title)) $fullTitle = $title;
		else $fullTitle = $this->citation->getTitle();
		
		$explode = explode(':', $fullTitle);
		if(!isset($explode[1])) $explode = explode('.', $fullTitle); // try '.' if ':' was not found!
		return $explode[0];
	}
	
	function getSubtitle($title = null){
		if(!is_null($title)) $fullTitle = $title;
		else $fullTitle = $this->citation->getTitle();
		
		$explode = explode(':', $fullTitle);
		if(!isset($explode[1])) $explode = explode('.', $fullTitle); // try '.' if ':' was not found!
		return isset($explode[1]) ? $explode[1] : null;
	}
	
	function getMonographOrgAuthors(){
		$authors = NULL;
		if(isset($this->citation->_data['author_organization'])){
			$authorsArray = explode(",", trim($this->citation->_data['author_organization']));
			foreach($authorsArray as $author){
				$author = explode(";", trim(str_replace('.', NULL, $author)));
				$orgname = isset($author[1]) ? $author[1] : $author[0];
				$orgdiv = isset($author[1])  ? $author[0] : null;
				
				$authors .= "[corpauth][orgname]".ltrim(rtrim($orgname))."[/orgname]";
				if(!is_null($orgdiv)) $authors .= "[orgdiv]".ltrim(rtrim($orgdiv))."[/orgdiv]";
				$authors .= "[/corpauth]";
			}
		}
		
		return $authors;
	}
	
	function getPages(){
		if($this->citation->getPages()) return $this->citation->getPages();
		elseif($this->citation->getPageCount()) return $this->citation->getPageCount();
		
		return false;
	}
	
	function getSuppl(){
		if($this->citation->getVolumeSuppl()) return $this->citation->getVolumeSuppl();
		elseif($this->citation->getIssueSuppl()) return $this->citation->getIssueSuppl();
		
		return null;
	}
	
	function getPart(){
		if($this->citation->getVolumePart()) return $this->citation->getVolumePart();
		elseif($this->citation->getIssuePart()) return $this->citation->getIssuePart();
		
		return null;
	}
	
	function getConference(){
		$conference  = $this->citation->getConferenceSponsor() ? "[sponsor]" . $this->citation->getConferenceSponsor() ."[/sponsor]" : "";
		$conference .= $this->citation->getConferenceTitle() ? "[confname]" . $this->citation->getConferenceTitle() ."[/confname]" : "";
		$conference .= $this->citation->hasConferenceDate() ? "[date dateiso=" .$this->getConferenceDateISO(). "]" . $this->citation->getConferenceDate() ."[/date]" : "";
		$conference .= $this->citation->getConferenceCity() ? "[city]" . $this->citation->getConferenceCity() ."[/city]" : "";
		$conference .= $this->citation->getConferenceState() ? "[country]" . $this->citation->getConferenceState() ."[/country]" : "";		
		
		return $conference != "" ? $conference : false;
	}
	
	function getConferenceDateISO(){
		if(!$this->citation->hasConferenceDate()) return null;
		$date = $this->citation->_data['confYear'];
		if(isset($this->citation->_data['confMonth']) && ($this->citation->_data['confMonth'] > 0)){
			if($this->citation->_data['confMonth'] > 9)
				$date .= $this->citation->_data['confMonth']; 
			else
				$date .= "0" . $this->citation->_data['confMonth'];
		}
		else $date .= "00";
		if(isset($this->citation->_data['confDayFrom']) && ($this->citation->_data['confDayFrom'] > 0)){
			if($this->citation->_data['confDayFrom'] > 9)
				$date .= $this->citation->_data['confDayFrom']; 
			else
				$date .= "0" . $this->citation->_data['confDayFrom'];
		}
		else $date .= "00";
		
		return $date;
	}
	
	function isThesis(){
		if(stristr($this->citation->getTypeTitle(), "dissertation") || stristr($this->citation->getTypeTitle(), "tesis")) return true;
		
		return false;
	}
	
	function getThesis(){
		if($this->isThesis()){
			$thesis  = "[degree]" . trim(str_ireplace("dissertation", "", str_ireplace("tesis", "",$this->citation->getTypeTitle()))) . "[/degree]";
			$thesis .= $this->citation->getPubPlace() ? "[city]" . $this->citation->getPubPlace() ."[/city]" : "";
			$thesis .= $this->citation->getState() ? "[state]" . $this->citation->getState() ."[/state]" : "";
			$thesis .= $this->citation->hasDate() ? "[date dateiso=\"" .$this->getDateISO(). "\"]" . $this->citation->getDate() ."[/date]" : "";
			$org = $this->citation->getEditorial() ? explode(".", $this->citation->getEditorial()) : "0";
			$thesis .= isset($org[0]) ? "[orgname]" . trim($org[0]) ."[/orgname]" : "";
			$thesis .= isset($org[1]) ? "[orgdiv]" . trim($org[1]) ."[/orgdiv]" : "";		
		
			return $thesis;
		}
		
		return false;
	}

	function remove_non_numeric($string) {
		return preg_replace("/[^0-9]/", "", $string);
	}
}
?>
