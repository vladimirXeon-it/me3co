<?php

return [
    'default_language'  => 'English',
    'assets_folder'     => env('APP_URL').'/vendor/grocery-crud/', // asegúrate APP_URL en .env
    'default_per_page'  => 20,
    'paging_options'    => ['10','25','50','100'],
    'environment'       => 'development',
    'theme'             => 'bootstrap-v5', // usa 'bootstrap-v4' si tu build no trae v5
    'xss_clean'         => false,
    'column_character_limiter' => 120,
    'upload_allowed_file_types' => ['gif','jpeg','jpg','png','svg','pdf','doc','docx','xls','xlsx','ppt','pptx','txt'],
    'remove_file_on_delete' => false,
    'show_image_preview'    => false,
    'open_in_modal'         => true,
    'url_history'           => true,
    'action_button_type'    => 'icon-text',
    'max_action_buttons'    => ['mobile'=>1,'desktop'=>2],
    'actions_column_side'   => 'left',
    'optimize_sql_queries'  => false,
    'publish_events'        => false,
    'remember_state_upon_refresh'   => true,
    'remember_filters_upon_refresh' => true,
    'display_js_files_in_output'    => false,
];
