<?php

global $wpdb;

$sql = "
SELECT ID, post_title
FROM $wpdb->posts
WHERE post_type = 'asenha_cfgroup' AND post_status = 'publish'
ORDER BY post_title";
$results = $wpdb->get_results($sql);
?>

<style type="text/css">
.nav-tab { cursor: pointer; }
.nav-tab:first-child { margin-left: 15px; }
.tab-content { display: none; }
.tab-content.active { display: block; }
#button-export, #button-sync { margin-top: 4px; }
</style>

<script>
(function($) {
    $(function() {
        var cfgroup_nonce = '<?php echo wp_create_nonce( 'cfgroup_admin_nonce' ); ?>';

        $('.nav-tab').click(function() {
            $('.tab-content').removeClass('active');
            $('.nav-tab').removeClass('nav-tab-active');
            $('.tab-content.' + $(this).attr('rel')).addClass('active');
            $(this).addClass('nav-tab-active');
        });

        $('#button-export').click(function() {
            var groups = $('#export-field-groups').val();
            if (null != groups) {
                $.post(ajaxurl, {
                    action: 'cfgroup_ajax_handler',
                    action_type: 'export',
                    nonce: cfgroup_nonce,
                    field_groups: $('#export-field-groups').val()
                },
                function(response) {
                    $('#export-output').text(response);
                    $('#export-area').show();
                });
            }
        });

        $('#button-import').click(function() {
            $.post(ajaxurl, {
                action: 'cfgroup_ajax_handler',
                action_type: 'import',
                nonce: cfgroup_nonce,
                import_code: $('#import-code').val()
            },
            function(response) {
                $('#import-message').html(response);
            });
        });

        $('#button-reset').click(function() {
            if (confirm('This will delete all CFG data. Are you sure?')) {
                $.post(ajaxurl, {
                    action: 'cfgroup_ajax_handler',
                    action_type: 'reset',
                    nonce: cfgroup_nonce
                },
                function(response) {
                    window.location.replace(response);
                });
            }
        });
    });
})(jQuery);
</script>

<div class="wrap">
    <h1><?php _e( 'Tools', 'admin-site-enhancements' ); ?></h1>

    <h3 class="nav-tab-wrapper">
        <a class="nav-tab nav-tab-active" rel="export"><?php _e('Export', 'cfgroup'); ?></a>
        <a class="nav-tab" rel="import"><?php _e('Import', 'cfgroup'); ?></a>
        <a class="nav-tab" rel="reset"><?php _e('Reset', 'cfgroup'); ?></a>
    </h3>

    <div class="content-container">

        <!-- Export -->

        <div class="tab-content export active">
            <table>
                <tr>
                    <td style="width:300px; vertical-align:top">
                        <div>
                            <select id="export-field-groups" style="width:300px; height:120px" multiple="multiple">
                                <?php foreach ($results as $result) : ?>
                                <option value="<?php echo $result->ID; ?>"><?php echo $result->post_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <input type="button" id="button-export" class="button" value="<?php _e('Export', 'cfgroup'); ?>" />
                        </div>
                    </td>
                    <td style="width:300px; vertical-align:top">
                        <div id="export-area" style="display:none">
                            <div>
                                <textarea id="export-output" style="width:98%; height:120px"></textarea>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Import -->

        <div class="tab-content import">
            <textarea id="import-code" style="width:100%; height:120px" placeholder="Paste the import code here"></textarea>
            <div><input type="button" id="button-import" class="button" value="<?php _e('Import', 'cfgroup'); ?>" /></div>
            <div id="import-message"></div>
        </div>

        <!-- Reset -->

        <div class="tab-content reset">
            <h2><?php _e('Reset and deactivate.', 'cfgroup'); ?></h2>
            <p><?php _e('This will delete all CFG data and deactivate the plugin.', 'cfgroup'); ?></p>
            <input type="button" id="button-reset" class="button" value="<?php _e('Delete everything', 'cfgroup'); ?>" />
        </div>
    </div>
</div>
