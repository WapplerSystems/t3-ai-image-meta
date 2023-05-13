<?php

use TYPO3\CMS\Backend\Controller;


return [

    'image_description_suggest' => [
        'path' => '/record/suggest/description',
        'target' => \WapplerSystems\AiImageMeta\Controller\ImageMetaAjaxController::class . '::descriptionAction',
    ],

];
