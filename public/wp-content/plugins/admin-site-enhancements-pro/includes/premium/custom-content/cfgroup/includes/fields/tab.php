<?php

class cfgroup_tab extends cfgroup_field
{

    function __construct() {
        $this->name = 'tab';
        $this->label = __( 'Tab', 'admin-site-enhancements' );
    }


    // Prevent tabs from inheriting the parent field HTML
    function html( $field ) {

    }


    // Prevent tabs from inheriting the parent options HTML
    function options_html( $key, $field ) {

    }


    // Tab handling javascript
    function input_head( $field = null ) {
    ?>
        <script>
        (function($) {
            $(document).on('click', '.cfgroup-tab', function() {
                var tab = $(this).attr('rel'),
                    $context = $(this).parents('.cfgroup_input');
                $context.find('.cfgroup-tab').removeClass('active');
                $context.find('.cfgroup-tab-content').removeClass('active');
                $(this).addClass('active');
                $context.find('.cfgroup-tab-content-' + tab).addClass('active');
            });

            $(function() {
                $('.cfgroup-tabs').each(function(){
                    $(this).find('.cfgroup-tab:first').click();
                });
            });
        })(jQuery);
        </script>
    <?php
    }
}
