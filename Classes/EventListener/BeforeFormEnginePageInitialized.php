<?php

namespace WapplerSystems\AiImageMeta\EventListener;

use TYPO3\CMS\Backend\Controller\Event\BeforeFormEnginePageInitializedEvent;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BeforeFormEnginePageInitialized
{
    public function __invoke(BeforeFormEnginePageInitializedEvent $event): void
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadJavaScriptModule('@ai-image-meta/backend/description.js');
    }
}
