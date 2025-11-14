// Ref: https://plugins.trac.wordpress.org/browser/duplicate-page/tags/4.5/js/editor-script.js
var el = wp.element.createElement;
var __ = wp.i18n.__;
var registerPlugin = wp.plugins.registerPlugin;
var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
var buttonControl = wp.components.Button;

function asenhaPublicPreviewButton({}) {
    return el(
        PluginPostStatusInfo,
        {
            className: 'asenha-public-preview-status-info'
        },
        el(
            buttonControl,
            {
                variant: 'secondary',
                name: 'asenha_gutenberg_public_preview_link',
                isLink: true,
                title: pp_params.pp_post_title,
                href : pp_params.pp_public_preview_link
            }, pp_params.pp_post_text
        )
    );
}

registerPlugin( 'asenha-public-preview-status-info-plugin', {
    render: asenhaPublicPreviewButton
} );