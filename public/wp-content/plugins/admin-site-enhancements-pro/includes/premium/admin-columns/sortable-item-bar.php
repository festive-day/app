<div class="item-bar">
	<div class="item-left-side">
		<span class="dashicons dashicons-menu"></span> <span class="column-title"><?php echo esc_html( wp_strip_all_tags( $custom_title ) ); ?></span><?php if ( ! empty( $handler ) ): ?><span class="custom-field-handler" title="<?php echo esc_attr( $handler_title );?>"><?php echo esc_html( $handler ) . '<span>: ' . esc_html( $type ) . '</span>'; ?></span><?php endif; ?><?php if ( $is_taxonomy ) : ?><span class="taxonomy-tag">Taxonomy</span><?php endif; ?><span class="column-key<?php echo esc_attr( $column_key_class ); ?>"><?php if ( $is_custom_field == 'yes' ): ?><span class="custom-field-indicator"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="#bbb" d="M22 4H2v16h10v-2H4V6h16v4h2V4zm-3 13h3v-2h-3v-3h-2v3h-3v2h3v3h2v-3z"/></svg></span><?php endif; ?><?php if ( $is_extra_column == 'yes' ): ?><span class="extra-column-indicator"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20"><path fill="#bbb" d="M13.11 4.36L9.87 7.6L8 5.73l3.24-3.24c.35-.34 1.05-.2 1.56.32c.52.51.66 1.21.31 1.55zm-8 1.77l.91-1.12l9.01 9.01l-1.19.84c-.71.71-2.63 1.16-3.82 1.16H6.14L4.9 17.26c-.59.59-1.54.59-2.12 0a1.49 1.49 0 0 1 0-2.12l1.24-1.24v-3.88c0-1.13.4-3.19 1.09-3.89zm7.26 3.97l3.24-3.24c.34-.35 1.04-.21 1.55.31c.52.51.66 1.21.31 1.55l-3.24 3.25z"/></svg></span><?php endif; ?><?php echo esc_html( $column_key ); ?></span>
	</div>
	<div class="item-right-side">
		<div class="column-action-links">
			<a href="#" class="button button-small button-secondary settings-button"><?php echo esc_html__( 'Edit', 'admin-site-enhancements' ); ?></a><a href="#" class="button button-small button-secondary delete-button"><?php echo esc_html__( 'Discard', 'admin-site-enhancements' ); ?></a>
		</div>
		<div class="data-type">
		</div>
		<div class="column-width">
			<span class="width-number"><?php echo esc_html( $width_number ); ?></span><span class="width-type"><?php echo esc_html( $width_type_label ); ?></span>
		</div>
		<div class="sortable-icon<?php if ( $is_the_default_sort ): ?> default-sort<?php endif; ?>"><?php if ( 'yes' == $is_sortable ): // https://icon-sets.iconify.design/fluent/text-sort-ascending-16-regular/ ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16"><path fill="#3c434a" d="M5.462 1.308a.5.5 0 0 0-.923 0l-2.5 6a.5.5 0 0 0 .923.384L3.667 6h2.666l.705 1.692a.5.5 0 1 0 .924-.384zM4.083 5L5 2.8L5.917 5zM2.5 9.5A.5.5 0 0 1 3 9h3.5a.5.5 0 0 1 .41.787L3.96 14H6.5a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.41-.787L5.54 10H3a.5.5 0 0 1-.5-.5m10-8.5a.5.5 0 0 1 .5.5v11.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L12 13.293V1.5a.5.5 0 0 1 .5-.5"/></svg><?php else: ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 16 16"><path fill="#ccc" d="M5.462 1.308a.5.5 0 0 0-.923 0l-2.5 6a.5.5 0 0 0 .923.384L3.667 6h2.666l.705 1.692a.5.5 0 1 0 .924-.384zM4.083 5L5 2.8L5.917 5zM2.5 9.5A.5.5 0 0 1 3 9h3.5a.5.5 0 0 1 .41.787L3.96 14H6.5a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.41-.787L5.54 10H3a.5.5 0 0 1-.5-.5m10-8.5a.5.5 0 0 1 .5.5v11.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L12 13.293V1.5a.5.5 0 0 1 .5-.5"/></svg><?php endif; ?></div>
	</div>
</div>
<div class="item-settings" style="display:none;" data-column-key="<?php echo esc_attr( $column_key ); ?>">
	<div class="item-setting">
		<label for="title_custom_<?php echo esc_attr( $column_key ); ?>">Label</label>
		<input id="title_custom_<?php echo esc_attr( $column_key ); ?>" type="text" value="<?php echo esc_attr( wp_strip_all_tags( $custom_title ) ); ?>" name="title_custom_<?php echo esc_attr( $column_key ); ?>" class="column-label title-custom" />
		<?php if ( $may_use_original_title ): ?>
		<div class="title-original">
			<input type="checkbox" id="title_original_<?php echo esc_attr( $column_key ); ?>" name="title_original_<?php echo esc_attr( $column_key ); ?>" <?php checked( $use_original_title, 'yes' ); ?> class="settings-checkbox title-original-checkbox" />
			<label for="title_original_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Use original label', 'admin-site-enhancements' ); ?></label>
		</div>
		<?php endif; ?>
	</div>
	<div class="item-setting">
		<label for="column_width_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Width', 'admin-site-enhancements' ); ?></label>
		<input id="column_width_<?php echo esc_attr( $column_key ); ?>" type="number" value="<?php echo esc_attr( $width_number ); ?>" name="column_width_<?php echo esc_attr( $column_key ); ?>" class="column-width-input">
		<div class="radio-group width-type">
			<input type="radio" name="width_type_<?php echo esc_attr( $column_key ); ?>" id="px_<?php echo esc_attr( $column_key ); ?>" value="px" class="width-type-radios" <?php checked( $width_type, 'px' ); ?> />
			<label for="px_<?php echo esc_attr( $column_key ); ?>">px</label>
			<input type="radio" name="width_type_<?php echo esc_attr( $column_key ); ?>" id="percent_<?php echo esc_attr( $column_key ); ?>" value="%" class="width-type-radios" <?php checked( $width_type, '%' ); ?> />
			<label for="percent_<?php echo esc_attr( $column_key ); ?>">%</label>
			<input type="radio" name="width_type_<?php echo esc_attr( $column_key ); ?>" id="auto_<?php echo esc_attr( $column_key ); ?>" value="auto" class="width-type-radios" <?php checked( $width_type, 'auto' ); ?> />
			<label for="auto_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Auto', 'admin-site-enhancements' ); ?></label>
		</div>
	</div>
	<?php if ( 'yes' == $is_formatable ): ?>
	<div class="item-setting">
		<label for="format_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Format', 'admin-site-enhancements' ); ?></label>
		<div class="item-sub-settings">
			<div class="select format-type">
				<select name="format_type_<?php echo esc_attr( $column_key ); ?>" class="input-select">
					<option value="default" <?php selected( $format_type, 'default', true ); ?>><?php echo esc_html__( 'Default', 'admin-site-enhancements' ); ?></option>
					<option value="number" <?php selected( $format_type, 'number', true ); ?>><?php echo esc_html__( 'Number', 'admin-site-enhancements' ); ?></option>
					<option value="date_time" <?php selected( $format_type, 'date_time', true ); ?>><?php echo esc_html__( 'Date Time', 'admin-site-enhancements' ); ?></option>
				</select>
			</div>
			<div class="select number-format-type">
				<select name="number_format_type_<?php echo esc_attr( $column_key ); ?>" class="input-select">
					<option value="comma" <?php selected( $number_format_type, 'comma', true ); ?>>9,999.99</option><!-- Comma (en_US) -->
					<option value="dot" <?php selected( $number_format_type, 'dot', true ); ?>>9.999,99</option><!-- Dot (de_DE) -->
					<option value="space" <?php selected( $number_format_type, 'space', true ); ?>>9 999,99</option><!-- Space (fr_FR) -->
				</select>
			</div>
			<div class="inline-setting number-decimal-point">
				<input id="number_decimal_point_<?php echo esc_attr( $column_key ); ?>" type="number" value="<?php echo esc_attr( $number_decimal_point ); ?>" name="number_decimal_point_<?php echo esc_attr( $column_key ); ?>" placeholder="0" min="0" max="9" class="number-decimal-point-input">
				<div class="input-suffix"><?php echo esc_html__( 'decimal points', 'admin-site-enhancements' ); ?></div>
			</div>
			<div class="select date-time-format-type">
				<select name="date_time_format_type_<?php echo esc_attr( $column_key ); ?>" class="input-select">
					<option value="M j, Y" <?php selected( $date_time_format_type, 'M j, Y', true ); ?>><?php echo esc_html( wp_date( 'M j, Y', time() ) ); ?></option>
					<option value="F j, Y" <?php selected( $date_time_format_type, 'F j, Y', true ); ?>><?php echo esc_html( wp_date( 'F j, Y', time() ) ); ?></option>
					<option value="F jS, Y" <?php selected( $date_time_format_type, 'F jS, Y', true ); ?>><?php echo esc_html( wp_date( 'F jS, Y', time() ) ); ?></option>
					<option value="l, F jS, Y" <?php selected( $date_time_format_type, 'l, F jS, Y', true ); ?>><?php echo esc_html( wp_date( 'l, F jS, Y', time() ) ); ?></option>
					<option value="m-d-Y" <?php selected( $date_time_format_type, 'm-d-Y', true ); ?>><?php echo esc_html( wp_date( 'm-d-Y', time() ) ); ?></option>
					<option value="m/d/Y" <?php selected( $date_time_format_type, 'm/d/Y', true ); ?>><?php echo esc_html( wp_date( 'm/d/Y', time() ) ); ?></option>
					<option value="d-m-Y" <?php selected( $date_time_format_type, 'd-m-Y', true ); ?>><?php echo esc_html( wp_date( 'd-m-Y', time() ) ); ?></option>
					<option value="d/m/Y" <?php selected( $date_time_format_type, 'd/m/Y', true ); ?>><?php echo esc_html( wp_date( 'd/m/Y', time() ) ); ?></option>
					<option value="Y-m-d" <?php selected( $date_time_format_type, 'Y-m-d', true ); ?>><?php echo esc_html( wp_date( 'Y-m-d', time() ) ); ?></option>
					<option value="Y/m/d" <?php selected( $date_time_format_type, 'Y/m/d', true ); ?>><?php echo esc_html( wp_date( 'Y/m/d', time() ) ); ?></option>
					<option value="H:i" <?php selected( $date_time_format_type, 'H:i', true ); ?>><?php echo esc_html( wp_date( 'H:i', time() ) ); ?></option>
					<option value="h:i a" <?php selected( $date_time_format_type, 'h:i a', true ); ?>><?php echo esc_html( wp_date( 'h:i a', time() ) ); ?></option>
					<option value="H:i:s" <?php selected( $date_time_format_type, 'H:i:s', true ); ?>><?php echo esc_html( wp_date( 'H:i:s', time() ) ); ?></option>
					<option value="h:i:s a" <?php selected( $date_time_format_type, 'h:i:s a', true ); ?>><?php echo esc_html( wp_date( 'h:i:s a', time() ) ); ?></option>
					<option value="F j, Y - H:i:s" <?php selected( $date_time_format_type, 'F j, Y - H:i:s', true ); ?>><?php echo esc_html( wp_date( 'F j, Y - H:i:s', time() ) ); ?></option>
					<option value="F j, Y - h:i:s a" <?php selected( $date_time_format_type, 'F j, Y - h:i:s a', true ); ?>><?php echo esc_html( wp_date( 'F j, Y - h:i:s a', time() ) ); ?></option>
					<option value="n/j/y" <?php selected( $date_time_format_type, 'n/j/y', true ); ?>><?php echo esc_html( wp_date( 'n/j/y', time() ) ); ?></option>
					<option value="n/j/y \a\t g:i a" <?php selected( $date_time_format_type, 'n/j/y \a\t g:i a', true ); ?>><?php echo esc_html( wp_date( 'n/j/y \a\t g:i a', time() ) ); ?></option>
					<option value="custom" <?php selected( $date_time_format_type, 'custom', true ); ?>><?php echo esc_html__( 'Custom', 'admin-site-enhancements' ); ?></option>
				</select>
			</div>
			<div class="inline-setting date-time-format-type-custom">
				<input id="date_time_format_custom_<?php echo esc_attr( $column_key ); ?>" type="text" name="date_time_format_custom_<?php echo esc_attr( $column_key ); ?>" value="<?php echo esc_attr( $date_time_format_custom ); ?>" placeholder="<?php echo esc_attr__( 'e.g. F j, Y - H:i:s', 'admin-site-enhancements' ); ?>" class="date-time-format-custom" />
				<div class="setting-info format-info">
					<a href="https://www.wpase.com/documentation/custom-date-time-format-in-admin-columns-manager/" target="_blank">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 26 26"><g fill="currentColor"><path fill-rule="evenodd" d="M13 10a2 2 0 0 1 2 2v7a2 2 0 1 1-4 0v-7a2 2 0 0 1 2-2" clip-rule="evenodd"/><path d="M15 7a2 2 0 1 1-4 0a2 2 0 0 1 4 0"/><path fill-rule="evenodd" d="M13 24c6.075 0 11-4.925 11-11S19.075 2 13 2S2 6.925 2 13s4.925 11 11 11m0 2c7.18 0 13-5.82 13-13S20.18 0 13 0S0 5.82 0 13s5.82 13 13 13" clip-rule="evenodd"/></g></svg>
					</a>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<?php if ( 'yes' == $is_sortable ): ?>
	<div class="item-setting default-sort-setting">
		<label for="sort_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Sort', 'admin-site-enhancements' ); ?></label>
		<div class="item-sub-settings">
	        <input type="checkbox"  id="default_sort_<?php echo esc_attr( $column_key ); ?>" name="default_sort_<?php echo esc_attr( $column_key ); ?>" <?php checked( $is_the_default_sort, true ); ?> class="default-sort-checkbox default-sort-checkbox-<?php echo esc_attr( $column_key ); ?>" >
	        <label for="default_sort_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Use this for the default sort', 'admin-site-enhancements' ); ?></label>
	    </div>
	</div>
	<div class="item-setting sort-order-setting sort-order-setting-<?php echo esc_attr( $column_key ); ?>" style="display:<?php echo esc_attr( $sort_order_display ); ?>;">
		<label for="sort_order_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Sort Order', 'admin-site-enhancements' ); ?></label>
		<div class="item-sub-settings">
			<div class="radio-group sort-order">
				<input type="radio" name="sort_order_<?php echo esc_attr( $column_key ); ?>" id="asc_<?php echo esc_attr( $column_key ); ?>" value="asc" class="sort-order-radios" <?php checked( $sort_order, 'asc' ); ?> />
				<label for="asc_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Ascending', 'admin-site-enhancements' ); ?></label>
				<input type="radio" name="sort_order_<?php echo esc_attr( $column_key ); ?>" id="desc_<?php echo esc_attr( $column_key ); ?>" value="desc" class="sort-order-radios" <?php checked( $sort_order, 'desc' ); ?> />
				<label for="desc_<?php echo esc_attr( $column_key ); ?>"><?php echo esc_html__( 'Descending', 'admin-site-enhancements' ); ?></label>
			</div>
	    </div>
	</div>
	<?php endif; ?>
	<div class="settings-footer">
		<a class="button button-small button-secondary close-settings-button"><?php echo esc_html__( 'Close', 'admin-site-enhancements' ); ?></a>
	</div>
</div>