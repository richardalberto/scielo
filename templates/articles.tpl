{**
 * articles.tpl
 *
 *
 * List of  articles on selected issue to potentially export
 *
 * $Id: articles.tpl,v 1.7 2007/09/04 16:31:43 damnpoet Exp $
 *}
{assign var="pageTitle" value="plugins.importexport.scielo.selectIssue.long"}
{assign var="pageCrumbTitle" value="plugins.importexport.scielo.selectIssue.short"}
{include file="common/header.tpl"}

<br/>

<a name="issues"></a>

<table width="100%" class="listing">
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	<tr class="heading" valign="bottom">
		<td width="65%">{translate key="article.title"}</td>
		<td width="5%" align="right">{translate key="common.action"}</td>
	</tr>
	<tr>
		<td colspan="4" class="headseparator">&nbsp;</td>
	</tr>
	
{foreach from=$articles item=article}
	<tr valign="top">
		<td><a href="{url page="article" op="view" path=$article->getArticleId()}" class="action">{$article->getArticleTitle()|escape}</a></td>
		<td align="right"><a href="{plugin_url path="exportArticle"|to_array:$article->getArticleId()}" class="action">{translate key="common.export"}</a></td>
	</tr>
	<tr>
		<td colspan="4" class="separator">&nbsp;</td>
	</tr>
{/foreach}
{if !$articles}
	<tr>
		<td colspan="4" class="nodata">{translate key="article.noArticles"}</td>
	</tr>
	<tr>
		<td colspan="4" class="endseparator">&nbsp;</td>
	</tr>
{/if}
</table>
{include file="common/footer.tpl"}
