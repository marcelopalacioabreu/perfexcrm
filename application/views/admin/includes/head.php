<!DOCTYPE html>
<html lang="en">
<head>
    <?php $isRTL = (is_rtl() ? 'true' : 'false'); ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1" />

    <title><?php echo isset($title) ? $title : get_option('companyname'); ?></title>

    <?php echo app_compile_css(); ?>

    <?php echo app_stylesheet('assets/css','style.css'); ?>

    <?php if(file_exists(FCPATH.'assets/css/custom.css')){ ?>
        <link href="<?php echo base_url('assets/css/custom.css'); ?>" rel="stylesheet" id="custom-css">
    <?php } ?>

    <?php render_custom_styles(array('general','tabs','buttons','admin','modals','tags')); ?>
    <?php render_admin_js_variables(); ?>

    <script>
        appLang['datatables'] = <?php echo json_encode(get_datatables_language_array()); ?>;
        var totalUnreadNotifications = <?php echo $current_user->total_unread_notifications; ?>,
        userRecentSearches = <?php echo json_encode(get_staff_recent_search_history()); ?>,
        proposalsTemplates = <?php echo json_encode(get_proposal_templates()); ?>,
        contractsTemplates = <?php echo json_encode(get_contract_templates()); ?>,
        availableTags = <?php echo json_encode(get_tags_clean()); ?>,
        availableTagsIds = <?php echo json_encode(get_tags_ids()); ?>,
        billingAndShippingFields = ['billing_street','billing_city','billing_state','billing_zip','billing_country','shipping_street','shipping_city','shipping_state','shipping_zip','shipping_country'],
        locale = '<?php echo $locale; ?>',
        isRTL = '<?php echo $isRTL; ?>',
        tinymceLang = '<?php echo get_tinymce_language(get_locale_key($app_language)); ?>',
        monthsJSON = '<?php echo json_encode(array(_l('January'),_l('February'),_l('March'),_l('April'),_l('May'),_l('June'),_l('July'),_l('August'),_l('September'),_l('October'),_l('November'),_l('December'))); ?>',
        taskid,taskTrackingStatsData,taskAttachmentDropzone,taskCommentAttachmentDropzone,leadAttachmentsDropzone,newsFeedDropzone,expensePreviewDropzone,taskTrackingChart,cfh_popover_templates = {},_table_api;
    </script>
    <?php do_action('app_admin_head'); ?>
</head>
<body <?php echo admin_body_class(isset($bodyclass) ? $bodyclass : ''); ?><?php if($isRTL === 'true'){ echo 'dir="rtl"';}; ?>>
<?php do_action('after_body_start'); ?>
