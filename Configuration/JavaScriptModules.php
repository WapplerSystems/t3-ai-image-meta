<?php

return [
    'dependencies' => [
        'core',
    ],
    'tags' => [
        'backend.module',
        'backend.form',
    ],
    'imports' => [
        '@ai-image-meta/backend/' =>  'EXT:ai_image_meta/Resources/Public/JavaScript/Backend/'
    ],
];
