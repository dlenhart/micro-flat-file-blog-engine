<?php 

return [
    'configuration' => [
        
        'logging_enabled'   => true,
        'logging_path'      => BASE_PATH . '/logs/error.log',
        'debug'             => true

    ],
    'app' => [

        'app_name'                  => 'My First Blog',
        'env'                       => 'development',
        'posts_dir'                 => BASE_PATH . '/posts/',
        'post_preview_text_limit'   => 200,
        'posts_per_page'            => 5,
        'debug'                     => true,
        'allowed_post_file_types'   => 
        [
            
            [ 'txt', 'json', 'post' ] 
            
        ]
        
    ]
];