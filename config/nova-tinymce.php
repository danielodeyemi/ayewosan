<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | Here you can define the options that are passed to all NovaTinyMCE
    | fields by default.
    |
    */

    'default_options' => [
        'content_css' => '/vendor/tinymce/skins/ui/oxide/content.min.css',
        'skin_url' => '/vendor/tinymce/skins/ui/oxide',
        'content_css_dark' => '/vendor/tinymce/skins/ui/oxide-dark/content.min.css',
        'skin_url_dark' => '/vendor/tinymce/skins/ui/oxide-dark',
        'path_absolute' => '/',
        'plugins' => [
            'lists', 'template', 'preview','anchor','pagebreak','image','wordcount','fullscreen','directionality', 'table', 'fullscreen'
        ],
        'toolbar' => 'template | undo redo | styleselect | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | image | bullist numlist outdent indent | link | table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol | fullscreen',
        'relative_urls' => false,
        'use_lfm' => false,
        'use_dark' => true,
        'lfm_url' => 'filemanager',
    ],
];
