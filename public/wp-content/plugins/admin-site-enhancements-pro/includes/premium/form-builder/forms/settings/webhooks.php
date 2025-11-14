<?php
defined( 'ABSPATH' ) || die();
$enable_webhooks = isset( $settings['enable_webhooks'] ) ? $settings['enable_webhooks'] : 'on';
?>
<div class="fb-form-container fb-grid-container">
    <div class="fb-form-row fb-grid-container">
        <div class="fb-grid-6">
            <label><?php esc_html_e( 'Send Form Submissions to Webhooks', 'admin-site-enhancements' ); ?></label>
            <div class="fb-setting-fields fb-toggle-input-field">
                <input type="hidden" name="enable_webhooks" value="off">
                <input type="checkbox" id="enable_webhooks" name="enable_webhooks" value="on" <?php checked( $enable_webhooks, 'on', true ); ?>>
            </div>
        </div>
    </div>
    <div class="fb-form-row fb-multiple-rows fb-grid-container">
        <div class="fb-grid-3">
            <label><?php esc_html_e( 'Webhook URLs', 'admin-site-enhancements' ); ?></label>
            <div class="fb-multiple-webhook">
                <?php
                $webhook_urls = isset( $settings['webhook_urls'] ) ? $settings['webhook_urls'] : '';
                if ( ! empty( $webhook_urls ) ) {
                    $webhook_urls = explode( ',', $settings['webhook_urls'] );
                    foreach ( $webhook_urls as $webhook_url ) {
                        ?>
                        <div class="fb-webhook-row">
                            <input type="text" name="webhook_urls[]" value="<?php echo esc_attr( $webhook_url ); ?>" />
                            <span class="fb fb-trash-can-outline fb-delete-webhook-row"><?php echo wp_kses( Form_Builder_Icons::get( 'delete' ), Form_Builder_Common_Methods::get_kses_extended_ruleset() ); ?></span>
                        </div>
                    <?php 
                    } 
                }
                ?>
            </div>
            <button type="button" class="button button-primary fb-add-webhook"><?php esc_html_e( 'Add a Webhook URL', 'admin-site-enhancements' ); ?></button>
        </div>
    </div>
</div>