<?php

echo app_compile_scripts('customers-area-default');

echo app_script(template_assets_path().'/js','global.js');

if(is_client_logged_in()) {
    echo app_script(template_assets_path().'/js','clients.js');
}
/**
 * Check for any alerts stored in session
 */
app_js_alerts();
/**
 * DO NOT REMOVE THIS LINE
 */
do_action('customers_after_js_scripts_load');
