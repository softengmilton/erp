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
        'store_product_image',
        'store_expense_file',
    ],
];
