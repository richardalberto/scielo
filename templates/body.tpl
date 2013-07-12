<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<TITLE>{$article->getArticleTitle()}</TITLE>
</HEAD>
<BODY BGCOLOR="#ffffff">
<div align="right">
  <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><B>{$section->getLocalizedIdentifyType()}</B></font></p>
</div>
<p>&nbsp;</p> 
<p><b><font face="Verdana, Arial, Helvetica, sans-serif" size="4">{$article->getArticleTitle()}</font></b></p>
<p>&nbsp;</p>
<p><b><font face="Verdana, Arial, Helvetica, sans-serif" size="3">{$article->getTitle("en_US")}</font></b></p>
<p>&nbsp;</p>
<p>&nbsp;</p>

<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><B>



{assign var='count' value=1}
{foreach from=$authors item=author}

{foreach from=$affs key=keyvar item=aff}
{if $articlesExtrasDao->getAuthorMetadataByAuthorId($author->getAuthorId(), "aff_orgname") == $aff.aff_orgname}
{assign var="affId" value=$keyvar}
{/if}
{/foreach}

{assign var="value" value=$affId|replace:"a":""}
{if $value == "01" || $value == "02" || $value == "03" || $value == "04" || $value == "05" || $value == "06" || $value == "07" || $value == "08" || $value == "09"}
{assign var="value" value=$value|replace:"0":""}
{/if}

{if $count != 1}, {/if}{$author->getFullName()}<sup>{$value}</sup>
{assign var='count' value=$count+1}
{/foreach}

</B></font></P>

{assign var="hadaffs" value=1}
{if count($affs) <= 1 }
{foreach from=$affs key=keyvar item=aff}
{if $aff.aff_orgname == ""}
{assign var="hadaffs" value=0}
{/if}
{/foreach}
{/if}


<p>&nbsp;</p>
{if $hadaffs == 0}
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$authors[0]->getAuthorBiography()}</font></P>
{else}

<p><font face="Verdana, Arial, Helvetica, sans-serif" size="2">

{if $hadaffs == 0}
<sup>1</sup> {$publisherInstitution}, {$city}, {$country}, {$zipcode}
{else}
{foreach from=$affs key=keyvar item=aff}

{assign var="value" value=$keyvar|replace:"a":""}
{if $value == "01" || $value == "02" || $value == "03" || $value == "04" || $value == "05" || $value == "06" || $value == "07" || $value == "08" || $value == "09"}
{assign var="value" value=$value|replace:"0":""}
{/if}

<sup>{$value}</sup> {$aff.aff_orgname}{if $aff.aff_orgdiv1 != ""}, {$aff.aff_orgdiv1}{/if}{if $aff.aff_orgdiv2 != ""}, {$aff.aff_orgdiv2}{/if}{if $aff.aff_orgdiv3 != ""}, {$aff.aff_orgdiv3}{/if}{if $aff.aff_city != ""}, {$aff.aff_city}{/if}{if $aff.aff_state != ""}, {$aff.aff_state}{/if}{if $aff.aff_country != ""}, {$aff.aff_country}{/if}{if $aff.aff_zipcode != ""}, CP: {$aff.aff_zipcode}{/if}
<br />
{/foreach}
{/if}
</font></p>
{/if}
<P>&nbsp;</P>
<P>&nbsp;</P>
{if !$section->getAbstractsNotRequired()}
<hr />
<P></P> 
<P></P>
<P></P>
<font face="Verdana, Arial, Helvetica, sans-serif" size="2"><B>RESUMEN </B></font> 
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$article->getAbstract($locale)}</font></P>
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><B>Palabras clave:</B> 
{assign var="keywordsCount" value="1"}
{foreach from=$keywords item=keyword}{$keyword}{if $keywords|@count eq $keywordsCount}.{else}; {/if}{assign var="keywordsCount" value=$keywordsCount+1}{/foreach}
</font></P>
<hr> 
<P>
<font face="Verdana, Arial, Helvetica, sans-serif" size="2"><B>ABSTRACT </B></font> 
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$article->getAbstract("en_US")}</font></P>
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><B>Key words</B>: 
{assign var="keywordsCount" value="1"}
{foreach from=$otherKeywords item=keyword}{$keyword}{if $keywords|@count eq $keywordsCount}.{else}; {/if}{assign var="keywordsCount" value=$keywordsCount+1}{/foreach}
</font></P>
<hr> 
<P>&nbsp;</P>
<P>&nbsp;</P>
{/if}
<P> 

<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2">{$body}</font></P>

<P>&nbsp;</P>
<P>&nbsp;</P> 
<P></P>
<P></P>
<P> </P>
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="3"><B>REFERENCIAS 
  BIBLIOGR&Aacute;FICAS</B></font><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
  </font> </P>
{assign var='refCount' value=1}
 {foreach from=$citations item=citation}
 <P><font face="Verdana, Arial, Helvetica, sans-serif" size="2"> {$refCount}. {$citation}</font></P>
 {assign var='refCount' value=$refCount+1}
 {/foreach}

<P>&nbsp;</P>
<P>&nbsp;</P>
<P></P>
<P></P>
<P></P> 
<P></P> 
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Recibido: {$article->getDateSubmitted()|date_format:'%d de %B de %Y'}.<BR>Aprobado: {$article->getDatePublished()|date_format:'%d de %B de %Y'}. </font></P>
<P>&nbsp;</P>
<P>&nbsp; </P>
<P></P>
<P></P>
<P> </P>
<P> </P>
<P><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><I>{$firstAuthor->getFullName()}</I>. {$firstAuthor->getAuthorBiography()} Correo electr&oacute;nico: <U><FONT COLOR="#0000ff"><a href="mailto:{$firstAuthor->getEmail()}">{$firstAuthor->getEmail()}</a></FONT></U> 
  </font> </P>

</BODY>
</HTML>
