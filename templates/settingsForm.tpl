{**
 * settingsForm.tpl
 *
 * Copyright (c) 2010 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Scielo Export Plugin Additional Settings
 *
 *}
{assign var="pageTitle" value="plugins.importexport.scielo.settings"}
{include file="common/header.tpl"}

{translate key="plugins.importexport.scielo.settings.description"}

<div class="separator">&nbsp;</div>

<form method="post" action="{plugin_url path="settings"}">
{include file="common/formErrors.tpl"}

<table width="100%" class="data">
  <tr valign="top">
  	<td colspan="2"><h3>{translate key="plugins.importexport.scielo.generalSettings"}</h3></td>
  </tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.ccode"}</td>
		<td width="75%" class="value">
    	<input type="text" name="ccode" id="ccode" {if $ccode}value="{$ccode}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.ccode.description"}
    </td>
	</tr>
	<tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.acronym"}</td>
		<td width="75%" class="value">
    	<input type="text" name="journalAcronym" id="journalAcronym" {if $journalAcronym}value="{$journalAcronym}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.acronym.description"}
    </td>
	</tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.otherLanguages"}</td>
		<td width="75%" class="value">
    	<input type="text" name="otherLanguages" id="otherLanguages" {if $otherLanguages}value="{$otherLanguages}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.otherLanguages.description"}
    </td>
	</tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.pagesFormat"}</td>
		<td width="75%" class="value">
    	<input type="text" name="pagesFormat" id="pagesFormat" {if $pagesFormat}value="{$pagesFormat}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.pagesFormat.description"}
    </td>
	</tr>
  <tr valign="top">
  	<td colspan="2"><h3>{translate key="plugins.importexport.scielo.contact"}</h3></td>
  </tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.institution"}</td>
		<td width="75%" class="value">
    	<input type="text" name="institution" id="institution" {if $institution}value="{$institution}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.institution.description"}
    </td>
	</tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.institutionAddress"}</td>
		<td width="75%" class="value">
    	<input type="text" name="institutionAddress" id="institutionAddress" {if $institutionAddress}value="{$institutionAddress}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.institutionAddress.description"}
    </td>
	</tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.city"}</td>
		<td width="75%" class="value">
    	<input type="text" name="city" id="city" {if $city}value="{$city}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.city.description"}
    </td>
	</tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.country"}</td>
		<td width="75%" class="value">
    	<input type="text" name="country" id="country" {if $country}value="{$country}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.country.description"}
    </td>
	</tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">{translate key="plugins.importexport.scielo.settings.zipcode"}</td>
		<td width="75%" class="value">
    	<input type="text" name="zipcode" id="zipcode" {if $zipcode}value="{$zipcode}"{/if}/>
      <br />
      {translate key="plugins.importexport.scielo.settings.zipcode.description"}
    </td>
	</tr>
	<tr valign="top">
  	<td colspan="2"><h3>Secciones</h3></td>
  </tr>
  <tr valign="top">
		<td width="25%" class="label" align="right">No exportar:</td>
		<td width="75%" class="value">
    	<input type="text" name="skipedSections" id="skipedSections" {if $skipedSections}value="{$skipedSections}"{/if}/>
      <br />
      Escriba las secciones que no deseas publicar al hacer una exportaci&oacute;n a SciELO. Separe los identificadores con comas (Ej: ART, IM, etc...)
    </td>
	</tr>
</table>

<br/>

<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/> <input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
