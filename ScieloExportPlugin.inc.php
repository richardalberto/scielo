<?php
/**
 * @file ScieloExportPlugin.inc.php
 *
 * Copyright (c) 2010 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_importexport_scielo
 * @brief SciELO Export plugin.
 *
 */

import('classes.plugins.ImportExportPlugin');
import('file.ArticleFileManager');

class ScieloExportPlugin extends ImportExportPlugin {
    function register($category, $path) {
            $success = parent::register($category, $path);

            $this->addLocaleData();

            return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return String name of plugin
     */
    function getName() {
                    return 'ScieloExportPlugin';
    }

    function getDisplayName() {
            return Locale::translate('plugins.importexport.scielo.displayName');
    }

    function getDescription() {
            if(!$this->isArticlesExtrasInstalled()){
                    return Locale::translate('plugins.importexport.scielo.articlesExtrasRequired');
            }

            return Locale::translate('plugins.importexport.scielo.description');
    }

    function isArticlesExtrasInstalled() {
            $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', 'ArticlesExtrasPlugin');

            if ( $articlesExtrasPlugin )
                    return $articlesExtrasPlugin->getEnabled();

            return false;
    }

    function display(&$args, $request) {
            parent::display($args, $request);
            switch (array_shift($args)) {
                    case 'exportArticle':
                            $articleId = array_shift($args);
                            $articleDao = &DAORegistry::getDAO('PublishedArticleDAO');
                            $article = &$articleDao->getPublishedArticleByArticleId($articleId, null, false);

                            $articles[0] = $article;
                            
                            $this->makeZip($articles);
                            break;
                    case 'exportIssue':
                            $issueId = array_shift($args);

                            $articleDao = &DAORegistry::getDAO('PublishedArticleDAO');
                            $articles = &$articleDao->getPublishedArticles($issueId, null, false);

                            $this->makeZip($articles);
                            break;
                    case 'showIssue':
                            $issueId = array_shift($args);
                            $publishedArticleDao = &DAORegistry::getDAO('PublishedArticleDAO');
                            $articles = &$publishedArticleDao->getPublishedArticles($issueId, null, false);

                            $templateMgr = &TemplateManager::getManager();
                            $templateMgr->assign_by_ref('articles', $articles);
                            $templateMgr->display($this->getTemplatePath() . 'templates/articles.tpl');
                            break;
                    case 'settings':{
                            $journal = Request::getJournal();
                            $templateMgr = &TemplateManager::getManager();

                            $this->import('ScieloSettingsForm');
                            $form =& new ScieloSettingsForm($this, $journal->getJournalId());

                            if (Request::getUserVar('save')) {
                                    $form->readInputData();
                                    if ($form->validate()) {
                                            $form->execute();
                                            Request::redirect(null, null, 'plugins');
                                    } else {
                                            $form->display();
                                    }
                            } else {
                                    $form->initData();
                                    $form->display();
                            }
                            break;
                    }
                    default:
                            // Display a list of issues for export
                            $journal = &Request::getJournal();
                            $issueDao = &DAORegistry::getDAO('IssueDAO');
                            $issues = &$issueDao->getIssues($journal->getJournalId(), Handler::getRangeInfo('issues'));

                            $templateMgr = &TemplateManager::getManager();
                            $templateMgr->assign_by_ref('issues', $issues);
                            $templateMgr->display($this->getTemplatePath() . 'templates/issues.tpl');
            }
    }

    function makeZip($articles){
            $this->import('ScieloZip');
            $journal = &Request::getJournal();

            $scieloZip = new ScieloZip($articles, $journal);
            $scieloZip->getZip();
    }
}

?>
