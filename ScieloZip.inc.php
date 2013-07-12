<?php
/**
 * @file ScieloZip.inc.php
 *
 * Copyright (c) 2010 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_scielo
 * @brief SciELO Zip generator.
 *
 */

require_once("ziplib/ziplib.php");
require_once("ScieloMarkup.inc.php");

class ScieloZip extends Ziplib {
    var $articles;
    var $journal;

    function ScieloZip(&$articles, &$journal){
        $this->articles = $articles;
        $this->journal = $journal;
    }

    function getZip(){
        foreach ($this->articles as $order => $article){
            
			$plugin = &PluginRegistry::getPlugin('importexport', 'ScieloExportPlugin');
            $articleId = $article->getArticleId();
			
			$sectionDao =& DAORegistry::getDAO('SectionDAO');
			$section =& $sectionDao->getSection($article->getSectionId());
			
			// skip noted sections! TODO: Improve settings!, Abbrev depends on the locale, change to section ID
			$skipedSections = explode(",", strtolower($plugin->getSetting($this->journal->getJournalId(), 'skipedSections')));
			if(in_array(strtolower($section->getLocalizedAbbrev()), $skipedSections)) continue;
			
			// !_-----
            $issueDao = &DAORegistry::getDAO('IssueDAO');
            $issue = &$issueDao->getIssueByArticleId($articleId, null) ;
            
            $volume = sprintf("%02d", $issue->getVolume());
            $number = $issue->getNumber();
			$articleNumber = sprintf("%02d",$order+1);
			$journalInitials = strtolower($this->journal->getJournalInitials());
            
            $scieloMarkup = new ScieloMarkup($article, $this->journal, $issue, $order+1);
            
            // File Structure
            //// Physical path for current files
            $srcDir = Config::getVar("files", "files_dir")."/journals/".$this->journal->getJournalId()."/articles/".$articleId;

            //// Relative path for zip files
            $destDir = strtolower($this->journal->getJournalInitials())."/v$volume"."n$number/";
            
			if($order == 0) { // only the first time
				//// Summary (html)
				$summary = $scieloMarkup->getSummary();
				$summary_filename = $destDir."Sumario.html";
				$this->zl_add_file($summary, $summary_filename,"g9");
			}
			
            //// Body (html)
            $html = $scieloMarkup->getBody();
			$filename = "{$journalInitials}{$articleNumber}{$number}{$volume}";
            $html_filename = $destDir."body/".$filename.".html";
            $this->zl_add_file($html, $html_filename,"g9");
            //var_dump($scieloMarkup->getBody());
            //die();
            
            //// Markup
            $html = $scieloMarkup->getMarkup();
            $filename = "{$journalInitials}{$articleNumber}{$number}{$volume}";
            $html_filename = $destDir."markup/".$filename.".html";
            $this->zl_add_file($html, $html_filename,"g9");
			
			$xml = $scieloMarkup->getMarkup("xml");
            $filename = "{$journalInitials}{$articleNumber}{$number}{$volume}";
            $xml_filename = $destDir."xml/".$filename.".xml";
            $this->zl_add_file($xml, $xml_filename,"g9");

            //// Img
            require_once("classes/Image.inc.php");
            $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');
            $articleFileManager = &new ArticleFileManager($article->getArticleId());

            $imgFiles = unserialize($articlesExtrasDao->getArticleImages($article->getArticleId()));

            if($imgFiles){
                $fseq = 1;
                $gseq = 1;
                $tseq = 1;
                foreach ($imgFiles as $imgFile){
                    $file =& $articleFileManager->getFile($imgFile->getFileId());
                    $srcFileName = $file->getFilePath();
                    $type = $this->getImageType($imgFile->getName());
                    if ($type == "f"){
                        $this->addImage($srcFileName,$destDir,$articleNumber,$number,$volume,$imgFile,$fseq);
                        $fseq++;
                    }elseif ($type == "g") {
                        $this->addImage($srcFileName,$destDir,$articleNumber,$number,$volume,$imgFile,$gseq);
                        $gseq++;
                    }elseif ($type == "t") {
                        $this->addImage($srcFileName,$destDir,$articleNumber,$number,$volume,$imgFile,$tseq);
                        $tseq++;
                    }
                }
            }

            //// PDF
            $galleys = &$article->getGalleys();
            foreach ($galleys as $galley){
                    $label = strtolower($galley->_data["label"]);
                    if($label != "pdf") continue;

                    $srcFileName = $srcDir."/public/".$galley->_data["fileName"];
                    if(file_exists($srcFileName) && ($content_pdf = file_get_contents($srcFileName))){
                            $pdfFileName = $destDir."pdf/".$filename.".pdf";

                            $this->zl_add_file($content_pdf, $pdfFileName,"g9");
                    }
            }
        }

        // Stream the zip file for download
		if(count($this->articles) == 1)
			$zipFilename = strtolower($this->journal->getJournalInitials()).".v{$volume}n{$number}a{$this->articles[0]->getArticleId()}.zip";
		else
			$zipFilename = strtolower($this->journal->getJournalInitials()).".v$volume"."n$number.zip";
			
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=\"".$zipFilename."\"");
        echo $this->zl_pack(null);
    }

    function addImage($srcFileName,$destDir,$articleNumber,$number,$volume,$imgFile,$seq){
        if($content_img = file_get_contents($srcFileName)){
            $imgFileName = $destDir."img/".$this->getImageFileName($articleNumber, $number, $volume, $imgFile, $seq);
            //$imgFileName = $this->getFullImageFilename($destDir,$articleNumber,$number,$volume,$imgFile,$seq);
            $this->zl_add_file($content_img, $imgFileName,"g9");
        }
    }

    function getImageFileName($articleNumber, $number, $volume, $image, $seq){
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
}
?>
