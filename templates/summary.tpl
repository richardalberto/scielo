{**
 * summary.tpl
 *
 *
 * Issue summary
 *
 * $Id$
 *}
 
<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<TITLE>{$issueHeadingTitle}</TITLE>
</HEAD>
<BODY BGCOLOR="#ffffff">
 
 <h1>Tabla de Contenido</h1>
 
{foreach name=sections from=$publishedArticles item=section key=sectionId}
  {if $section.title}
    <div style="display:block">
      <br /><h2>{$section.title|escape}</h2>
    </div>
  {/if}

  {foreach from=$section.articles item=article}
    {assign var=articlePath value=$article->getBestArticleId($currentJournal)}
  
<div class="article">
      {if $article->getLocalizedAbstract() == ""}
        {assign var=hasAbstract value=0}
      {else}
        {assign var=hasAbstract value=1}
      {/if}
    
      {assign var=articleId value=$article->getArticleId()}
      {if (!$subscriptionRequired || $article->getAccessStatus() == $smarty.const.ARTICLE_ACCESS_OPEN || $subscribedUser || $subscribedDomain || ($subscriptionExpiryPartial && $articleExpiryPartial.$articleId))}
        {assign var=hasAccess value=1}
      {else}
        {assign var=hasAccess value=0}
      {/if}
      
  <div class="tocArticle">
        {if !$hasAccess || $hasAbstract}
          <a href="{url page="article" op="view" path=$articlePath}" title="{$article->getLocalizedTitle()|strip_unsafe_html}"><strong>{$article->getLocalizedTitle()|strip_unsafe_html}</strong></a>
        {else}
          <strong>{$article->getLocalizedTitle()|strip_unsafe_html}</strong>
        {/if}
        
        {if (!$section.hideAuthor && $article->getHideAuthor() == 0) || $article->getHideAuthor() == 2}
          <div class="titleAutors" style="padding-left: 5px; padding-right: 65px;">
          {foreach from=$article->getAuthors() item=author name=authorList}
            {$author->getFullName()|escape}{if !$smarty.foreach.authorList.last}, {else}.{/if}
          {/foreach}
          </div>
        {/if}
      </div>
  </div><!-- .tocArticle -->
  {/foreach}        
{/foreach}

</BODY>
</HTML>