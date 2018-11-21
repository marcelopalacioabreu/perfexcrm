<?php

defined('BASEPATH') or exit('No direct script access allowed');

function init_admin_assets()
{
    $CI = &get_instance();

    $locale      = $GLOBALS['locale'];
    $localeUpper = strtoupper($locale);

    // Javascript
    $CI->app_scripts->add('vendor-js', 'assets/plugins/app-build/vendor.js');

    $CI->app_scripts->add('jquery-migrate-js', 'assets/plugins/jquery/jquery-migrate.' . (ENVIRONMENT === 'production' ? 'min.' : '') . 'js');

    add_datatables_js_assets();
    add_moment_js_assets();
    add_bootstrap_select_js_assets();

    $CI->app_scripts->add('tinymce-js', 'assets/plugins/tinymce/tinymce.min.js');

    add_jquery_validation_js_assets();

    if (get_option('pusher_realtime_notifications') == 1) {
        $CI->app_scripts->add('pusher-js', 'https://js.pusher.com/4.1/pusher.min.js');
    }

    add_dropbox_js_assets();
    add_google_api_js_assets();

    // CSS
    add_favicon_link_asset();

    $CI->app_css->add('reset-css', 'assets/css/reset.min.css');
    $CI->app_css->add('roboto-css', 'assets/plugins/roboto/roboto.css');
    $CI->app_css->add('vendor-css', 'assets/plugins/app-build/vendor.css');

    if (is_rtl()) {
        $CI->app_css->add('bootstrap-rtl-css', 'assets/plugins/bootstrap-arabic/css/bootstrap-arabic.min.css');
    }
}

function init_customers_area_assets()
{
    $CI = &get_instance();

    $locale      = $GLOBALS['locale'];
    $localeUpper = strtoupper($locale);
    $groupName   = 'customers-area-default';

    $CI->app_scripts->add('bootstrap-js', 'assets/plugins/bootstrap/js/bootstrap.min.js', $groupName);

    add_datatables_js_assets($groupName);
    add_jquery_validation_js_assets($groupName);
    add_bootstrap_select_js_assets($groupName);

    $CI->app_scripts->add('datetimepicker-js', 'assets/plugins/datetimepicker/jquery.datetimepicker.full.min.js', $groupName);
    $CI->app_scripts->add('chart-js', 'assets/plugins/Chart.js/Chart.min.js', $groupName);
    $CI->app_scripts->add('colorpicker-js', 'assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js', $groupName);
    $CI->app_scripts->add('lightbox-js', 'assets/plugins/lightbox/js/lightbox.min.js', $groupName);

    if (is_client_logged_in()) {
        $CI->app_scripts->add('dropzone-js', 'assets/plugins/dropzone/min/dropzone.min.js', $groupName);
        $CI->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js', $groupName);
        add_moment_js_assets($groupName);
        add_projects_assets($groupName);
        add_dropbox_js_assets($groupName);
        add_calendar_assets($groupName, false);

        if (get_option('enable_google_picker') == '1') {
            add_google_api_js_assets($groupName);
            $CI->app_scripts->add('picker-js', 'assets/plugins/google-picker/picker.min.js', $groupName);
        }
    }

    // CSS
    add_favicon_link_asset($groupName);

    $CI->app_css->add('reset-css', 'assets/css/reset.min.css', $groupName);
    $CI->app_css->add('roboto-css', 'assets/plugins/roboto/roboto.css', $groupName);
    $CI->app_css->add('bootstrap-css', 'assets/plugins/bootstrap/css/bootstrap.min.css', $groupName);

    if (is_rtl()) {
        $CI->app_css->add('bootstrap-rtl-css', 'assets/plugins/bootstrap-arabic/css/bootstrap-arabic.min.css', $groupName);
    }

    $CI->app_css->add('datatables-css', 'assets/plugins/datatables/datatables.min.css', $groupName);
    $CI->app_css->add('fontawesome-css', 'assets/plugins/font-awesome/css/font-awesome.min.css', $groupName);
    $CI->app_css->add('datetimepicker-css', 'assets/plugins/datetimepicker/jquery.datetimepicker.min.css', $groupName);
    $CI->app_css->add('bootstrap-select-css', 'assets/plugins/bootstrap-select/css/bootstrap-select.min.css', $groupName);

    if (is_client_logged_in()) {
        $CI->app_css->add('dropzone-basic-css', 'assets/plugins/dropzone/min/basic.min.css', $groupName);
        $CI->app_css->add('dropzone-css', 'assets/plugins/dropzone/min/dropzone.min.css', $groupName);
    }

    $CI->app_css->add('lightbox-css', 'assets/plugins/lightbox/css/lightbox.min.css', $groupName);
    $CI->app_css->add('colorpicker-css', 'assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css', $groupName);
}


function add_calendar_assets($group = 'admin', $tryGcal = true)
{
    $locale = $GLOBALS['locale'];
    $CI     = &get_instance();

    $CI->app_scripts->add('full-calendar-js', 'assets/plugins/fullcalendar/fullcalendar.min.js', $group);

    if (get_option('google_api_key') != '' && $tryGcal) {
        $CI->app_scripts->add('full-calendar-gcal-js', 'assets/plugins/fullcalendar/gcal.min.js', $group);
    }

    if ($locale != 'en' && file_exists(FCPATH . 'assets/plugins/fullcalendar/locale/' . $locale . '.js')) {
        $CI->app_scripts->add('full-calendar-lang-js', 'assets/plugins/fullcalendar/locale/' . $locale . '.js', $group);
    }

    $CI->app_css->add('full-calendar-css', 'assets/plugins/fullcalendar/fullcalendar.min.css', $group);
}

function add_projects_assets($group = 'admin')
{
    $CI = &get_instance();

    $CI->app_scripts->add('jquery-comments-js', 'assets/plugins/jquery-comments/js/jquery-comments.min.js', $group);
    $CI->app_scripts->add('jquery-gantt-js', 'assets/plugins/gantt/js/jquery.fn.gantt.min.js', $group);

    $CI->app_css->add('jquery-comments-css', 'assets/plugins/jquery-comments/css/jquery-comments.css', $group);
    $CI->app_css->add('jquery-gantt-css', 'assets/plugins/gantt/css/style.css', $group);
}

function add_favicon_link_asset($group = 'admin')
{
    $favIcon = get_option('favicon');
    if ($favIcon != '') {
        get_instance()->app_css->add('favicon', [
        'path'       => 'uploads/company/' . $favIcon,
        'version'    => false,
        'attributes' => [
            'rel'  => 'shortcut icon',
            'type' => false,
        ],
        ], $group);
    }
}

function add_jquery_validation_js_assets($group = 'admin')
{
    $CI          = &get_instance();
    $locale      = $GLOBALS['locale'];
    $localeUpper = strtoupper($locale);

    $jqValidationBase = 'assets/plugins/jquery-validation/';
    $CI->app_scripts->add('jquery-validation-js', $jqValidationBase . 'jquery.validate.min.js', $group);

    if ($locale != 'en') {
        if (file_exists(FCPATH . $jqValidationBase . 'localization/messages_' . $locale . '.min.js')) {
            $CI->app_scripts->add('jquery-validation-lang-js', $jqValidationBase . 'localization/messages_' . $locale . '.min.js', $group);
        } elseif (file_exists(FCPATH . $jqValidationBase . 'localization/messages_' . $locale . '_' . $localeUpper . '.min.js')) {
            $CI->app_scripts->add('jquery-validation-lang-js', $jqValidationBase . 'localization/messages_' . $locale . '_' . $localeUpper . '.min.js', $group);
        }
    }
}

function add_bootstrap_select_js_assets($group = 'admin')
{
    $CI           = &get_instance();
    $locale       = $GLOBALS['locale'];
    $localeUpper  = strtoupper($locale);
    $bsSelectBase = 'assets/plugins/bootstrap-select/js/';
    $CI->app_scripts->add('bootstrap-select-js', 'assets/plugins/app-build/bootstrap-select.min.js', $group);

    if ($locale != 'en') {
        if (file_exists(FCPATH . $bsSelectBase . 'i18n/defaults-' . $locale . '.min.js')) {
            $CI->app_scripts->add('bootstrap-select-lang-js', $bsSelectBase . 'i18n/defaults-' . $locale . '.min.js', $group);
        } elseif (file_exists(FCPATH . $bsSelectBase . 'i18n/defaults-' . $locale . '_' . $localeUpper . '.min.js')) {
            $CI->app_scripts->add('bootstrap-select-lang-js', $bsSelectBase . 'i18n/defaults-' . $locale . '_' . $localeUpper . '.min.js', $group);
        }
    }
}

function add_dropbox_js_assets($group = 'admin')
{
    if (get_option('dropbox_app_key') != '') {
        get_instance()->app_scripts->add('dropboxjs', [
            'path'       => 'https://www.dropbox.com/static/api/2/dropins.js',
            'attributes' => [
                'data-app-key' => get_option('dropbox_app_key'),
            ],
        ], $group);
    }
}

function add_google_api_js_assets($group = 'admin')
{
    if (get_option('enable_google_picker') == '1') {
        get_instance()->app_scripts->add('google-js', [
            'path'       => 'https://apis.google.com/js/api.js?onload=onGoogleApiLoad',
            'attributes' => [
                'defer',
            ],
        ], $group);
    }
}

function add_moment_js_assets($group = 'admin')
{
    get_instance()->app_scripts->add('moment-js', 'assets/plugins/app-build/moment.min.js', $group);
}

function add_datatables_js_assets($group = 'admin')
{
    get_instance()->app_scripts->add('datatables-js', 'assets/plugins/datatables/datatables.min.js', $group);
}

function app_compile_css($group = 'admin')
{
    return get_instance()->app_css->compile($group);
}

function app_compile_scripts($group = 'admin')
{
    return get_instance()->app_scripts->compile($group);
}

/**
 * Load app stylesheet based on option
 * Can load minified stylesheet and non minified
 *
 * This function also check if there is my_ prefix stylesheet to load them.
 * If in options is set to load minified files and the filename that is passed do not contain minified version the
 * original file will be used.
 *
 * @param  string $path
 * @param  string $filename
 * @return string
 */
function app_stylesheet($path, $filename)
{
    return get_instance()->app_css->coreStylesheet($path, $filename);
}
/**
 * Load app script based on option
 * Can load minified stylesheet and non minified
 *
 * This function also check if there is my_ prefix stylesheet to load them.
 * If in options is set to load minified files and the filename that is passed do not contain minified version the
 * original file will be used.
 *
 * @param  string $path
 * @param  string $filename
 * @return string
 */
function app_script($path, $filename)
{
    return get_instance()->app_scripts->coreScript($path, $filename);
}
