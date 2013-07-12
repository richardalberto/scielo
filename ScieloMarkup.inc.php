<?php
/**
 * @file ScieloMarkup.inc.php
 *
 * Copyright (c) 2010 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_scielo
 * @brief SciELO Markup generator.
 *
 */
class ScieloMarkup {
    var $article;
    var $journal;
    var $issue;

    var $_data;   

    var $_order;

    function ScieloMarkup(&$article, &$journal, &$issue, $order){
        $this->article = $article;
        $this->journal = $journal;
        $this->issue = $issue;
        $this->articleOrder = $order;

        $journalSettingsDao = &DAORegistry::getDAO('JournalSettingsDAO');

        $this->fillData($order);        
        $_order = $order;
    }

    function add($index, $value){
        $this->_data[$index] = $value;
    }

    function getSetting($name){
        $plugin = &PluginRegistry::getPlugin('importexport', 'ScieloExportPlugin');

        return $plugin->getSetting($this->journal->getJournalId(), $name);
    }

    function getTemplatePath(){
        $plugin = &PluginRegistry::getPlugin('importexport', 'ScieloExportPlugin');

        return $plugin->getTemplatePath();
    }

    function addDataToTemplate(&$templateMgr){
        $sectionDao = &DAORegistry::getDAO('SectionDAO');

        // Common
        $templateMgr->assign_by_ref('article', $this->article);
        $templateMgr->assign_by_ref('journal', $this->journal);
        $templateMgr->assign_by_ref('issue', $this->issue);
        $templateMgr->assign_by_ref('section', $sectionDao->getSection($this->article->getSectionId()));

        // Authors
        //$authorsDao = &DAORegistry::getDAO('AuthorDAO');
        //$authors = &$authorsDao->getAuthorsByArticle($this->article->getArticleId());
        $authors = $this->article->getAuthors();
        
        $firstAuthor = &$authors[0];
        $templateMgr->assign_by_ref('authors', $authors);
        $templateMgr->assign_by_ref('firstAuthor', $firstAuthor);
        
        // Affs
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');
        $affsFields = array("aff_orgname", "aff_orgdiv1", "aff_orgdiv2", "aff_orgdiv3", "aff_city", "aff_state", "aff_country", "aff_zipcode", "aff_email");
        $affs = array();
        $affId = 0;
        foreach($authors as $id => $author){
            // don't repeat affs
            $found = false;
            $orgname = $articlesExtrasDao->getAuthorMetadataByAuthorId($author->getAuthorId(), 'aff_orgname');
            foreach($affs as $aff) {
                if(strtolower(trim($aff['aff_orgname'])) == strtolower(trim($orgname)) || strtolower(trim($orgname)) == "") {
                    $found = true;
                    break;
                }
            }
            
            if(!$found){
                $affId++; // add new Aff!
                foreach($affsFields as $field){
                    $value = $articlesExtrasDao->getAuthorMetadataByAuthorId($author->getAuthorId(), $field);

                    // //delete " simbol from aff fields for scielo markup
                    // $value = str_replace('"', '', $value);
                    
                    $id = "a" . sprintf('%02d', $affId);
                    if ($field == "aff_email"){
                        //$affs[$id][$field] = $articlesExtrasDao->getAuthorEmailByAuthorId($author->getAuthorId());
                        $affs[$id][$field] = $author->getEmail();
                    }elseif ($field == "aff_country") {
                        $affs[$id][$field] = $author->getCountryLocalized();
                    }
                    else{
                       $affs[$id][$field] = trim($value);
                    }
                }
            }
        }
        $templateMgr->assign_by_ref('affs', $affs);
        $templateMgr->assign_by_ref('articlesExtrasDao', $articlesExtrasDao);

        // Data
        foreach($this->_data as $index => $data){
            $templateMgr->assign($index, $data);
        }

    }

    function getBody(){
        $templateMgr = &TemplateManager::getManager();



        // Add data
        $this->addDataToTemplate($templateMgr);

        // Citations
        require_once("classes/Citation.inc.php");
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');
        $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($this->article->getArticleId()));
        
        $nCitations = array();
        if($citations){
            foreach($citations as $citation){
                $output = $this->assemblyCitation($citation);
                $nCitations[] = $output;
            }
        }
        
        $sectionDao = &DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($this->article->getSectionId());

        $templateMgr->assign('refCount', count($citations));
        $templateMgr->assign_by_ref('citations', $nCitations);
        $templateMgr->assign_by_ref('section', $section);
        $html = $templateMgr->fetch($this->getTemplatePath() . 'templates/body.tpl');

        $html = $this->remplaceSpan($html);
        $html = $this->centerHtmlImages($html);

        
        return $this->filterHtml($html);
    }

    /**
     * 
     * Remplace span tags by equivalent font tags
     *
     */
    function remplaceSpan($html){

        
        $openSpanSmall = '<span style="font-size: small;">';
        $openFontSmall = '<font face="Verdana, Arial, Helvetica, sans-serif" size="3">';
        $html = str_replace($openSpanSmall, $openFontSmall, $html);
        $openSpanXSmall = '<span style="font-size: x-small;">';
        $openFontXSmall = '<font face="Verdana, Arial, Helvetica, sans-serif" size="2">';
        $html = str_replace($openSpanXSmall, $openFontXSmall, $html);
        $openSpanXXSmall = '<span style="font-size: xx-small;">';
        $openFontXXSmall = '<font face="Verdana, Arial, Helvetica, sans-serif" size="1">';
        $html = str_replace($openSpanXXSmall, $openFontXXSmall, $html);
        $openSpanMedium = '<span style="font-size: medium;">';
        $openFontMedium = '<font face="Verdana, Arial, Helvetica, sans-serif" size="4">';
        $html = str_replace($openSpanMedium, $openFontMedium, $html);
        $openSpanLarge = '<span style="font-size: large;">';
        $openFontLarge = '<font face="Verdana, Arial, Helvetica, sans-serif" size="5">';
        $html = str_replace($openSpanLarge, $openFontLarge, $html);
        $openSpanXLarge = '<span style="font-size: x-large;">';
        $openFontXLarge = '<font face="Verdana, Arial, Helvetica, sans-serif" size="6">';
        $html = str_replace($openSpanXLarge, $openFontXLarge, $html);
        $openSpanXXLarge = '<span style="font-size: xx-large;">';
        $openFontXXLarge = '<font face="Verdana, Arial, Helvetica, sans-serif" size="7">';        
        $html = str_replace($openSpanXXLarge, $openFontXXLarge, $html);


        $closeSpan = '</span>';
        $closeFont = '</font>';
        $html = str_replace($closeSpan, $closeFont, $html);

        return $html;
    }

    function getMarkup($type = "html"){

        $sectionDao = &DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($this->article->getSectionId());

        $templateMgr = &TemplateManager::getManager();

        // Add data
        $this->addDataToTemplate($templateMgr);

        // Citations
        require_once("classes/Citation.inc.php");
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');
        $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($this->article->getArticleId()));
        $nCitations = array();
        if($citations){
            require_once("classes/ScieloCitation.inc.php");
            $scieloCitations = array();

            foreach($citations as $citation){
                    $nCitations[] = new ScieloCitation($citation);
            }
        }

        $templateMgr->assign('refCount', count($citations));
        $templateMgr->assign_by_ref('citations', $nCitations);
        if ($type == "html" && $section->getAbstractsNotRequired()){
            $html = $templateMgr->fetch($this->getTemplatePath() . "templates/html/markupText.tpl");
        }else{
            $html = $templateMgr->fetch($this->getTemplatePath() . "templates/{$type}/markup.tpl");
        }

        return $this->filterHtml($html);
    }
    
    function getSummary() {
        $journal =& Request::getJournal();
        $issueDao =& DAORegistry::getDAO('IssueDAO');

        $templateMgr =& TemplateManager::getManager();
        $this->setupSummaryTemplate();

        $html = $templateMgr->fetch($this->getTemplatePath() . "templates/summary.tpl");
        
        return $this->filterHtml($html);
    }

    function filterHtml($html){

        $html = str_replace('“', '"', str_replace('”', '"', $html));

        $html = $this->replaceSimbols($html);
        $html = str_replace('–', '-', $html);
        //$html = str_replace('β', '&#946;', $html);
        //$html = str_replace('’', '\'', $html);

        //$html = str_replace('•', '', $html);
        $html = utf8_decode($html);


        // Change <pre> for <p>
        $html = str_replace("<pre>", "<p>", $html);
        $html = str_replace("</pre>", "</p>", $html);

        return $html;
    }


    // center all images from html using a <p> tag
    function centerHtmlImages($html){ 
        
        $html = preg_replace("/\<([Ii][Mm][Gg])(.*?)\>/",'<p align="center">'.'${0}'.'</p>',$html);
        
        return $html;
    }

    // replace extrain chars for properly ISO-8859-1 encode
    function replaceSimbols($html){
        $searchReplaceArray = array(
            '∀' => '&#8704;', 
            '∂' => '&#8706;',
            '∃' => '&#8707;',
            '∅' => '&#8709;',
            '∇' => '&#8711;',
            '∈' => '&#8712;',
            '∉' => '&#8713;',
            '∋' => '&#8715;',
            '∏' => '&#8719;',
            '∑' => '&#8721;',
            '−' => '&#8722;',
            '∗' => '&#8727;',
            '√' => '&#8730;',
            '∝' => '&#8733;',
            '∞' => '&#8734;',
            '∠' => '&#8736;',
            '∧' => '&#8743;',
            '∨' => '&#8744;',
            '∩' => '&#8745;',
            '∪' => '&#8746;',
            '∫' => '&#8747;',
            '∴' => '&#8756;',
            '∼' => '&#8764;',
            '≅' => '&#8773;',
            '≈' => '&#8776;',
            '≠' => '&#8800;',
            '≡' => '&#8801;',
            '≤' => '&#8804;',
            '≥' => '&#8805;',
            '⊂' => '&#8834;',
            '⊃' => '&#8835;',
            '⊄' => '&#8836;',
            '⊆' => '&#8838;',
            '⊇' => '&#8839;',
            '⊕' => '&#8853;',
            '⊗' => '&#8855;',
            '⊥' => '&#8869;',
            '⋅' => '&#8901;',
            'Α' => '&#913;',
            'Β' => '&#914;',
            'Γ' => '&#915;',
            'Δ' => '&#916;',
            'Ε' => '&#917;',
            'Ζ' => '&#918;',
            'Η' => '&#919;',
            'Θ' => '&#920;',
            'Ι' => '&#921;',
            'Κ' => '&#922;',
            'Λ' => '&#923;',
            'Μ' => '&#924;',
            'Ν' => '&#925;',
            'Ξ' => '&#926;',
            'Ο' => '&#927;',
            'Π' => '&#928;',
            'Ρ' => '&#929;',
            'Σ' => '&#931;',
            'Τ' => '&#932;',
            'Υ' => '&#933;',
            'Φ' => '&#934;',
            'Χ' => '&#935;',
            'Ψ' => '&#936;',
            'Ω' => '&#937;',
            'α' => '&#945;',
            'β' => '&#946;',
            'γ' => '&#947;',
            'δ' => '&#948;',
            'ε' => '&#949;',
            'ζ' => '&#950;',
            'η' => '&#951;',
            'θ' => '&#952;',
            'ι' => '&#953;',
            'κ' => '&#954;',
            'λ' => '&#955;',
            'μ' => '&#956;',
            'ν' => '&#957;',
            'ξ' => '&#958;',
            'ο' => '&#959;',
            'π' => '&#960;',
            'ρ' => '&#961;',
            'ς' => '&#962;',
            'σ' => '&#963;',
            'τ' => '&#964;',
            'υ' => '&#965;',
            'φ' => '&#966;',
            'χ' => '&#967;',
            'ψ' => '&#968;',
            'ω' => '&#969;',
            'ϑ' => '&#977;',
            'ϒ' => '&#978;',
            'ϒ' => '&#978;',
            'ϖ' => '&#982;',
            'Œ' => '&#338;',
            'œ' => '&#339;',
            'Š' => '&#352;',
            'š' => '&#353;',
            'Ÿ' => '&#376;',
            'ƒ' => '&#402;',
            'ˆ' => '&#710;',
            '˜' => '&#732;',
            ' ' => '&#8194;',
            ' ' => '&#8195;',
            ' ' => '&#8201;',
            '–' => '&#8211;',
            '—' => '&#8212;',
            '‘' => '&#8216;',
            '’' => '&#8217;',
            '\'' => '&#8217;',
            '‚' => '&#8218;',
            '“' => '&#8220;',
            '”' => '&#8221;',
            '”' => '&#8221;',
            '„' => '&#8222;',
            '†' => '&#8224;',
            '‡' => '&#8225;',
            '•' => '&#8226;',
            '…' => '&#8230;',
            '‰' => '&#8240;',
            '′' => '&#8242;',
            '″' => '&#8243;',
            '‹' => '&#8249;',
            '›' => '&#8250;',
            '‾' => '&#8254;',
            '€' => '&#8364;',
            '™' => '&#8482;',
            '←' => '&#8592;',
            '↑' => '&#8593;',
            '→' => '&#8594;',
            '↓' => '&#8595;',
            '↔' => '&#8596;',
            '↵' => '&#8629;',
            '⌈' => '&#8968;',
            '⌉' => '&#8969;',
            '⌊' => '&#8970;',
            '⌋' => '&#8971;',
            '◊' => '&#9674;',
            '♠' => '&#9824;',
            '♣' => '&#9827;',
            '♥' => '&#9829;',
            '♦' => '&#9830;',
            'đ' => '&#273;',
            'Đ' => '&#272;',
            'Ŋ' => '&#330;',
            'ŋ' => '&#331;',
            'Š' => '&#352;',
            'š' => '&#353;',
            'Ŧ' => '&#358;',
            'ŧ' => '&#359;',
            'Ž' => '&#381;',
            'ž' => '&#382;',
            'Ħ' => '&#294;',
            'ħ' => '&#295;',
            'Ż' => '&#379;',
            'ż' => '&#380;',
            'Ċ' => '&#266;',
            'ċ' => '&#267;',
            'Ġ' => '&#288;',
            'ġ' => '&#289;',
            'Ą' => '&#260;',
            'ą' => '&#261;',
            'Ć' => '&#262;',
            'ć' => '&#263;',
            'Ę' => '&#280;',
            'ę' => '&#281;',
            'Ł' => '&#321;',
            'ł' => '&#322;',
            'Ń' => '&#323;',
            'ń' => '&#324;',
            'Ó' => '&#211;',
            'ó' => '&#243;',
            'Ś' => '&#346;',
            'ś' => '&#347;',
            'Ź' => '&#377;',
            'ź' => '&#378;',
            'Ż' => '&#379;',
            'ż' => '&#380;',
            'ĸ' => '&#312;'
            );
        $html = str_replace(    
            array_keys($searchReplaceArray), 
            array_values($searchReplaceArray), 
            $html
            );

        return $html;

    }

    function getArticleType($images = array()){
        $hasTables = false;
        $hasFigures = false;
        $hasIlustrations = false;
        if($images){
            foreach($images as $image){
                    if($hasTables && $hasFigures && $hasIlustrations) break;

                    if($this->getImageType($image->getName()) == "t") $hasTables = true;
                    elseif($this->getImageType($image->getName()) == "f") $hasFigures = true;
                    elseif($this->getImageType($image->getName()) == "g") $hasIlustrations = true;
            }
        }

        $type  = $hasFigures ? "fig" : "";
        $type .= $hasIlustrations ? "ilus" : "";
        $type .= $hasTables ? "tab" : "";

        return $type != "" ? $type : "nd";
    }

    function getImageFileName($articleNumber, $number, $volume, &$image, $seq){
        $type=$this->getImageType($image->getName());

        $seq = sprintf("%02d", $seq);
        return "{$type}{$seq}{$articleNumber}{$number}{$volume}.jpg";
    }

    function getImageType($name){
        $type = "f";
        $explodeName = explode("-", $name);
        if (count($explodeName) > 1){
            $typeName = $explodeName[1];
            switch(strtolower($typeName[0])){
                case "t": { $type="t"; break; }
                case "g": { $type="g"; break; }
            }

        }else{
            switch(strtolower($name[0])){
                    case "t": { $type="t"; break; }
                    case "g": { $type="g"; break; }
            }
        }

        return $type;
    }

    /**
     * Makes a citation
     */
    function assemblyCitation(&$citation){
        $templateMgr = &TemplateManager::getManager();
        $templateMgr->assign_by_ref('citation', $citation);

        $output = $templateMgr->fetch($this->getTemplatePath() . 'templates/citation.tpl');

        return $output;
    }

    function setImagesAndLinksPaths($body, &$images){
        $initials = strtolower($this->journal->getJournalInitials());
        $vol = $this->issue->getVolume();
        $num = $this->issue->getNumber();
        $fseq = 1;
        $gseq = 1;
        $tseq = 1;

                
        if($images){
            foreach($images as $image){
                $name = preg_quote(rawurlencode((string)$image->getName()));
                $desc = preg_quote(rawurlencode((string)$image->getDescription()));
                
                
                $linkPattern = "|<a href=\"({$name}[^>])\">$desc<\/a>|Ui";
                
                $type=$this->getImageType($image->getName());
                //var_dump( $type."<br />");
                if ($type == "f"){
                    $body = $this->replaceImageAndLink($body,$name,$initials,$vol,$num,$image,$fseq);
                    $fseq++;
                }elseif ($type == "g") {
                    $body = $this->replaceImageAndLink($body,$name,$initials,$vol,$num,$image,$gseq);
                    $gseq++;
                }elseif ($type == "t") {
                    $body = $this->replaceImageAndLink($body,$name,$initials,$vol,$num,$image,$tseq);
                    $tseq++;
                }

                //var_dump($name."<br />");
                    
            }
            //die();
        }
        
        return $body;
    }

    /*
    * Return the image width
    */
    function getImageWidth($image){
        $articleFileManager = &new ArticleFileManager($this->article->getArticleId());
        $file =& $articleFileManager->getFile($image->getFileId());
        $srcFileName = $file->getFilePath();

        //hack for fix unknown bug after ojs version upgrade
        $srcFileName = str_replace("//", "/", $srcFileName);

        list($width, $height, $type, $attr) = getimagesize($srcFileName);
        return $width;
    }

    //// Find and replaces images && links url
    function replaceImageAndLink($body,$name,$initials,$vol,$num,$image,$seq){
        

        if ($this->getImageWidth($image) <= 580){
            // images stay in html
            
            $body = preg_replace('/([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $name . ')"/',
                        '\1="http:/img/revistas/'.$initials.'/v'.$vol.'n'.$num.'/'.$this->getImageFileName(sprintf("%02d", $this->articleOrder), $num, sprintf("%02d", $vol), $image, $seq) . '"', $body);
        }else{
            $anchorAndImagePattern = '/<[aA] [nN][aA][mM][eE]=["a-zA-Z0-9-]*><\/[aA]><img ([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $name . ')"(.*?)\/>/';
            $imagePattern = '/<img ([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $name . ')"(.*?)\/>/';
            preg_match($anchorAndImagePattern, $body, $anchorAndImageArray);
            $anchorAndImage = $anchorAndImageArray[0];
             
            if($anchorAndImage == NULL){
                $body = preg_replace('/([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $name . ')"/',
                        '\1="http:/img/revistas/'.$initials.'/v'.$vol.'n'.$num.'/'.$this->getImageFileName(sprintf("%02d", $this->articleOrder), $num, sprintf("%02d", $vol), $image, $seq) . '"', $body);
          
            } else{

                $linkPattern = '/<[aA] [nN][aA][mM][eE]="(.*?)">/';
                preg_match($linkPattern, $anchorAndImage, $linkNameArray);
                $linkName = $linkNameArray[1];
                
                $searchReplaceArray = array(
                            $anchorAndImage => '', 
                            '"#'.$linkName.'"' => '"http:/img/revistas/'.$initials.'/v'.$vol.'n'.$num.'/'.$this->getImageFileName(sprintf("%02d", $this->articleOrder), $num, sprintf("%02d", $vol), $image, $seq) . '"'
                        );

                $body = str_replace(    
                        array_keys($searchReplaceArray), 
                        array_values($searchReplaceArray), 
                        $body
                        );

            }
            
        }

        return $body;
    
    }

    function filter($text){
        //Remove spaces both at start/end
        $text = trim($text);

        // remove microsoft office extrain -- char
        $text = str_replace('–', '-', $text);

        //Remove points at the end
        $pos = strlen($text) - 1;
        if(substr($text, $pos, 1) == '.') $text = substr_replace($text, null, $pos, 1);

        return $text;
    }

    function fillData($order){
        // Locale
        $locale = Locale::getLocale();
        $localeShort = array_shift(explode("_", $locale));
        $this->add('locale', $locale);
        $this->add('localeShort', $localeShort);
        $this->add('otherLocales', explode(',', $this->getSetting("otherLanguages"))); // TODO: Change this, use journal secondary languages
        
        //Header
        $this->add('journalInitials', strtolower($this->journal->getJournalInitials()));
        $abbrev = $this->journal->getSetting('abbreviation');
        $this->add('stitle', $abbrev[$locale]);
        $this->add('onlineIssn', $this->journal->getSetting('onlineIssn'));
        $this->add('ccode', $this->getSetting("ccode"));
        $this->add('pii', "nd");// TODO: Add pii
        //$order++;
        $this->add('articleOrder', ($order < 10) ? "0".$order : $order);
        
        // Seccode
        $seccode  = strtolower($this->journal->getJournalInitials());
        if($this->article->getSectionId() < 10) $seccode .= "00";
        elseif($this->article->getSectionId() < 100) $seccode .= "0";
        $seccode .= $this->article->getSectionId();
        $this->add('seccode', $seccode);
        
        // Article
        $title = explode(":", $this->article->getArticleTitle());
        $this->add('articleTitle',$this->filter($title[0]));
        if(isset($title[1])) $this->add('articleSubTitle',$this->filter($title[1]));
        $titleEnUs = explode(":", $this->article->getTitle("en_US"));
        $this->add('articleTitleEnUS', $this->filter($titleEnUs[0]));
        if(isset($titleEnUs[1])) $this->add('articleSubTitleEnUs', $this->filter($titleEnUs[1]));
        $this->add('articleAbstract', $this->filter($this->article->getAbstract($locale)));
        
        // Keywords
        $keywords = explode(";", $this->article->getSubject($locale));
        foreach($keywords as $id => $keyword){
                $keywords[$id] = $this->filter($keyword);
        }
        $otherKeywords = explode(";", $this->article->getSubject("en_US"));
        foreach($otherKeywords as $id => $keyword){
                $otherKeywords[$id] = $this->filter($keyword);
        }

        $this->add('keywords', $keywords);
        $this->add('otherKeywords', $otherKeywords);
        
        // Pages
        $pages = $this->article->getPages();
        $pagesFormat = $this->getSetting("pagesFormat"); // TODO: There has to be a better way to do this
        if ($pagesFormat != ""){
            $splitter = trim(str_replace("#", NULL, $pagesFormat));
        }else{
            $splitter = "-";
        }
        
        
        $iPage = 1;
        $fPage = 1;
        if(!is_null($pages)){
            if($splitter != ""){
                $pagesAll = explode($splitter, $pages);
                $fPage = $pagesAll[0];
                $lPage = $pagesAll[1];
            }else{
                $fPage = $pages;
            }
        }
        $this->add('fPage', $fPage);
        if (isset($pagesAll[1])){
            $this->add('lPage', $lPage);
        }
        
        // Body
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');
        $body = $articlesExtrasDao->getArticleBody($this->article->getArticleId());
        
        // Images
        require_once("classes/Image.inc.php");
        $images = $articlesExtrasDao->getArticleImages($this->article->getArticleId());
        $images = unserialize($images);

        // Correct images&links paths
        $body = $this->setImagesAndLinksPaths($body, $images);
        $this->add('body', trim($body));
        $this->add('type', $this->getArticleType($images));

        // Contact
        $this->add('city', $this->getSetting("city"));
        $this->add('country', $this->getSetting("country"));
        $this->add('zipcode', $this->getSetting("zipcode"));
        $this->add('publisherInstitution', $this->getSetting("institution"));
        $this->add('institutionAdress', $this->getSetting("institutionAddress"));
    }

    function setupSummaryTemplate() {
        $journal =& Request::getJournal();
        $journalId = $journal->getId();
        $templateMgr =& TemplateManager::getManager();

        $issueHeadingTitle = $this->issue->getIssueIdentification(false, true);

        $locale = Locale::getLocale();
        $templateMgr->assign('locale', $locale);
        
        $templateMgr->assign('issueId', $this->issue->getBestIssueId());
        $templateMgr->assign('issue', $this->issue);
        
        $publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticles =& $publishedArticleDao->getPublishedArticlesInSections($this->issue->getId(), true);
        $templateMgr->assign('publishedArticles', $publishedArticles);

        $templateMgr->assign('showGalleyLinks', $journal->getSetting('showGalleyLinks'));
        $templateMgr->assign('issueHeadingTitle', $issueHeadingTitle);
    }

}
?>
