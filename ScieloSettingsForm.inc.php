<?php

import('form.Form');

class ScieloSettingsForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function ScieloSettingsForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin = &$plugin;

		parent::Form($plugin->getTemplatePath() . 'templates/settingsForm.tpl');
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin = &$this->plugin;

		$this->setData('ccode', $plugin->getSetting($journalId, 'ccode'));
		$this->setData('journalAcronym', $plugin->getSetting($journalId, 'journalAcronym'));
		$this->setData('otherLanguages', $plugin->getSetting($journalId, 'otherLanguages'));
		$this->setData('pagesFormat', $plugin->getSetting($journalId, 'pagesFormat'));
		$this->setData('city', $plugin->getSetting($journalId, 'city'));
		$this->setData('country', $plugin->getSetting($journalId, 'country'));
		$this->setData('zipcode', $plugin->getSetting($journalId, 'zipcode'));
		$this->setData('institution', $plugin->getSetting($journalId, 'institution'));
		$this->setData('institutionAddress', $plugin->getSetting($journalId, 'institutionAddress'));
		$this->setData('skipedSections', $plugin->getSetting($journalId, 'skipedSections'));
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('displayPage','displayItems','recentItems', 'journalAcronym', 'otherLanguages', 'city', 'country', 'zipcode','institution', 'institutionAddress', 'ccode', 'pagesFormat', 'skipedSections'));
	}

	/**
	 * Save settings. 
	 */
	function execute() {
		$plugin = &$this->plugin;
		$journalId = $this->journalId;

		$plugin->updateSetting($journalId, 'ccode', $this->getData('ccode'));
		$plugin->updateSetting($journalId, 'journalAcronym', $this->getData('journalAcronym'));
		$plugin->updateSetting($journalId, 'otherLanguages', $this->getData('otherLanguages'));
		$plugin->updateSetting($journalId, 'pagesFormat', $this->getData('pagesFormat'));
		$plugin->updateSetting($journalId, 'city', $this->getData('city'));
		$plugin->updateSetting($journalId, 'country', $this->getData('country'));
		$plugin->updateSetting($journalId, 'zipcode', $this->getData('zipcode'));
		$plugin->updateSetting($journalId, 'institution', $this->getData('institution'));
		$plugin->updateSetting($journalId, 'institutionAddress', $this->getData('institutionAddress'));
		$plugin->updateSetting($journalId, 'skipedSections', $this->getData('skipedSections'));
	}

}

?>
