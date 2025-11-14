<?php

defined( 'ABSPATH' ) || die();

Form_Builder_Preview::show_form( $form_id );

echo '<style>';
echo '#fb-container-00{';
self::get_style_vars( $formbuilder_styles, '' );
echo '}';
echo '</style>';
