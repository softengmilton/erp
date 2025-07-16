<?php
return [
    'allowedMediaType' => ['image', 'video', 'pdf'],

    'allowedFileExtensionToUpload' => [
        'image' => ['jpeg', 'jpg', 'png', 'svg'],
        'video' => ['mp4'],
        'pdf'   => ['pdf'],
    ],

    'allowedMediaRole' => [
        'other',
        'profile_image',
    ],
];
