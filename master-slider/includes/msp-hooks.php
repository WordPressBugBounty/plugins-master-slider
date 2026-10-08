<?php


function msp_body_class( $classes ) {
	// add master slider spesific class to $classes array
	$classes[]      = '_masterslider';
	$classes['msl'] = '_ms_version_' . MSWP_AVERTA_VERSION;

	return $classes;
}

add_filter( 'body_class', 'msp_body_class' );

add_action( 'admin_notices', 'msp_review_on_wordpress' );
function msp_review_on_wordpress() {
	if ( msp_get_transient( 'msp_rate_notice_missed' ) == 'yes' ) {
		return;
	}
	?>
	<div class="notice msp-rate notice-info is-dismissible">
		<div class="msp-notice-image">
			<img width="105" src="<?php echo MSWP_AVERTA_URL . '/admin/assets/css/images/rating.svg';?>">
		</div>
		<h3><?php echo esc_html__( 'Hi! Thank you so much for using Master Slider.', 'master-slider' );?></h3>
		<p><?php echo esc_html__( 'Could you please do us a HUGE favor? If you could take 2 min of your time, we would be really thankful if you give Master Slider a 5-star rating on WordPress. By spreading the love, we can push Master Slider forward and create even greater free stuff in the future!', 'master-slider' ); ?></p>
		<a class="rate-btn" href="https://wordpress.org/support/plugin/master-slider/reviews/?filter=5#new-post" target="_blank"><span class="msp-overlay"></span><button ><?php echo esc_html__( 'Sure, I like Master slider', 'master-slider' );?></button></a>
		<a class="rate-btn skip-btn delay" href="#"><span class="msp-overlay"></span><button><?php echo esc_html__( 'Maybe Later', 'master-slider' );?></button></a>
		<a class="rate-btn skip-btn" href="#"><span class="msp-overlay"></span><button><?php echo esc_html__( 'I Already Did :)', 'master-slider' );?></button></a>
	</div>
	<?php
}

add_filter( 'wp_insert_post_data', 'msp_sanitize_ms_slider_before_save',  10, 3 );
function msp_sanitize_ms_slider_before_save( $data, $postarr, $unsanitized_postarr = null ) {

	// Skip if user is admin
    if (current_user_can('administrator')) {
        return $data;
    }

	$js_events = [
		'on_init',
		'on_change_start',
		'on_change_end',
		'on_waiting',
		'on_resize',
		'on_video_play',
		'on_video_close',
		'on_swipe_start',
		'on_swipe_move',
		'on_swipe_end',
	];

    // Check and clean post content
    if (!empty($data['post_content'])) {
		$content = $data['post_content'];
		$excerpt = $data['post_excerpt'];

		foreach ( $js_events as $event ) {
			$content = msp_strip_ms_slider_callback_attr( $content, $event );
			$excerpt = msp_strip_ms_slider_callback_attr( $excerpt, $event );
		}

        $data['post_content'] = $content;
		$data['post_excerpt'] = $excerpt;
    }



    return $data;
}

/**
 * Remove one ms_slider callback attribute from post content.
 *
 * Rendering ignores these attributes unless the shortcode comes from the
 * slider editor. This only keeps them out of stored post content.
 *
 * @param string $content Post content or excerpt.
 * @param string $event   Callback attribute name.
 * @return string
 */
function msp_strip_ms_slider_callback_attr( $content, $event ) {
	if ( ! is_string( $content ) || '' === $content || ! preg_match( '/\[ms_slider\b/i', $content ) ) {
		return $content;
	}

	$pattern = '/\s*' . preg_quote( $event, '/' ) . '\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s\]]+)/i';
	$updated = preg_replace( $pattern, '', $content );

	return is_string( $updated ) ? $updated : $content;
}
