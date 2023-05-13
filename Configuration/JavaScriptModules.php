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
        '@ai-image-meta/backend/' => [
            'path' => 'EXT:ai-image-meta/Resources/Public/JavaScript/',
        ],
    ],
];
