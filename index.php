<?php
/**
 * @file plugins/importexport/scielo/index.php
 *
 * Copyright (c) 2010 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_scielo
 * @brief Wrapper for Scielo Export plugin.
 *
 */

require_once('ScieloExportPlugin.inc.php');
return new ScieloExportPlugin();

?>
