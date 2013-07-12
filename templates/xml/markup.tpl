<?xml version="1.0" encoding="ISO-8859-1"?>
<article pii="{$pii}" doctopic="oa" language="{$localeShort}" ccode="{$ccode}" status="1" version="3.1" type="{$type}" order="{$articleOrder}" seccode="{$seccode}" sponsor="nd" stitle="{$stitle}" volid="{$issue->getVolume()}" issueno="{$issue->getNumber()}" dateiso="{$issue->getDatePublished()|date_format:'%Y%m00'}" fpage="{$fPage}" lpage="{$lPage}" issn="{$onlineIssn}">

<front>
<titlegrp>
<title language="{$localeShort}">{$articleTitle}</title>{if $articleSubTitle}<subtitle>{$articleSubTitle}</subtitle>{/if}
<title language="en">{$articleTitleEnUS}</title>{if $articleSubTitleEnUs}<subtitle>{$articleSubTitleEnUs}</subtitle>{/if}
</titlegrp>
<authgrp>
{foreach from=$authors item=author}<author role="nd" rid="a01"><fname>{$author->getFirstName()}</fname><surname>{$author->getLastName()}</surname></author>{/foreach}
</authgrp>

<aff id="a01" orgname="{$publisherInstitution}">
<city>{$city}</city> 
<country>{$country}</country>
<zipcode>{$zipcode}</zipcode>
<email>{$firstAuthor->getEmail()}</email></aff>

<bibcom>
{if !$section->getAbstractsNotRequired()}<abstract language="{$localeShort}">{$article->getAbstract($locale)}</abstract>{/if}
{if $keywords|@count != 0}
{assign var="keywordsCount" value=1}
<keygrp scheme="decs">{foreach from=$keywords item=keyword}<keyword type="{if $keywordsCount eq 1}m{else}s{/if}" language="{$localeShort}">{$keyword}</keyword>{assign var="keywordsCount" value=$keywordsCount+1}{/foreach}</keygrp>
{/if}

{if !$section->getAbstractsNotRequired()}<abstract language="en">{$article->getAbstract("en_US")}</abstract>{/if}
{if $otherKeywords|@count != 0}
{assign var="otherKeywordsCount" value=1}
<keygrp scheme="decs">{foreach from=$otherKeywords item=keyword}<keyword type="{if $otherKeywordsCount eq 1}m{else}s{/if}" language="en">{$keyword}</keyword>{assign var="otherKeywordsCount" value=$otherKeywordsCount+1}{/foreach}</keygrp>
{/if}

</bibcom>

</front>

<body>{$body}</body>

<back>
{if $refCount != 0}<vancouv standard="vancouv" count="{$refCount}">{assign var='counter' value=1}
{foreach from=$citations item=citation}<vcitat><no>{$counter}</no>{$citation->getCitation('xml')}</vcitat>{assign var='counter' value=$counter+1}{/foreach}</vancouv>{/if}

<bbibcom>
<hist>
<received dateiso="{$article->getDateSubmitted()|date_format:'%Y%m%d'}">{$article->getDateSubmitted()|date_format:'%d/%B/%Y'}</received><accepted dateiso="{$article->getDatePublished()|date_format:'%Y%m%d'}">{$article->getDatePublished()|date_format:'%d/%B/%Y'}</accepted>
</hist>
</bbibcom>

</back>
</article>
