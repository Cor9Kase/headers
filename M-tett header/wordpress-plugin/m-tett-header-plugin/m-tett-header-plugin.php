<?php
/**
 * Plugin Name: M-TETT Header
 * Description: Viser M-TETT-headeren som en WordPress-shortcode med enkle innstillinger for tekst, knapper og spacing.
 * Version: 1.0.0
 * Author: Codex
 */

if (! defined('ABSPATH')) {
	exit;
}

define('MTETT_HEADER_VERSION', '1.0.0');
define('MTETT_HEADER_OPTION_KEY', 'mtett_header_options');

function mtett_header_get_defaults() {
	return array(
		'hero_min_height'    => '92vh',
		'padding_top'        => '12px',
		'padding_bottom'     => '12px',
		'headline_html'      => 'M-TETT <span class="text-blue-600">redder badet</span>',
		'description_text'   => 'Vi reparerer membran og fliser lokalt - raskere, billigere og mer baerekraftig enn full renovering. Med over 20 ars erfaring finner vi losningen som sparer bade tid og penger.',
		'primary_text'       => 'Kontakt oss',
		'primary_url'        => 'https://m-tett.no/kontakt/',
		'secondary_text'     => 'Ring oss',
		'secondary_url'      => 'tel:04915',
		'hotspot_sluk_text'     => 'Lekkasje ved sluk',
		'hotspot_sluk_url'      => 'https://m-tett.no/kontakt/',
		'hotspot_flis_text'     => 'Sprekk i flis',
		'hotspot_flis_url'      => 'https://m-tett.no/kontakt/',
		'hotspot_sisterne_text' => 'Innebygget sisterne',
		'hotspot_sisterne_url'  => 'https://m-tett.no/kontakt/',
		'hotspot_armatur_text'  => 'Armatur',
		'hotspot_armatur_url'   => 'https://m-tett.no/kontakt/',
		'auto_header_offset' => '1',
	);
}

function mtett_header_sanitize_length($value, $default) {
	$value = is_string($value) ? trim($value) : '';

	if ($value === '') {
		return $default;
	}

	if (preg_match('/^\d+(?:\.\d+)?(?:px|vh|vw|%|rem|em)$/', $value)) {
		return $value;
	}

	return $default;
}

function mtett_header_sanitize_options($input) {
	$defaults = mtett_header_get_defaults();
	$input    = is_array($input) ? $input : array();

	return array(
		'hero_min_height'    => mtett_header_sanitize_length($input['hero_min_height'] ?? '', $defaults['hero_min_height']),
		'padding_top'        => mtett_header_sanitize_length($input['padding_top'] ?? '', $defaults['padding_top']),
		'padding_bottom'     => mtett_header_sanitize_length($input['padding_bottom'] ?? '', $defaults['padding_bottom']),
		'headline_html'      => wp_kses_post($input['headline_html'] ?? $defaults['headline_html']),
		'description_text'   => sanitize_textarea_field($input['description_text'] ?? $defaults['description_text']),
		'primary_text'       => sanitize_text_field($input['primary_text'] ?? $defaults['primary_text']),
		'primary_url'        => esc_url_raw($input['primary_url'] ?? $defaults['primary_url']),
		'secondary_text'     => sanitize_text_field($input['secondary_text'] ?? $defaults['secondary_text']),
		'secondary_url'      => sanitize_text_field($input['secondary_url'] ?? $defaults['secondary_url']),
		'hotspot_sluk_text'     => sanitize_text_field($input['hotspot_sluk_text'] ?? $defaults['hotspot_sluk_text']),
		'hotspot_sluk_url'      => sanitize_text_field($input['hotspot_sluk_url'] ?? $defaults['hotspot_sluk_url']),
		'hotspot_flis_text'     => sanitize_text_field($input['hotspot_flis_text'] ?? $defaults['hotspot_flis_text']),
		'hotspot_flis_url'      => sanitize_text_field($input['hotspot_flis_url'] ?? $defaults['hotspot_flis_url']),
		'hotspot_sisterne_text' => sanitize_text_field($input['hotspot_sisterne_text'] ?? $defaults['hotspot_sisterne_text']),
		'hotspot_sisterne_url'  => sanitize_text_field($input['hotspot_sisterne_url'] ?? $defaults['hotspot_sisterne_url']),
		'hotspot_armatur_text'  => sanitize_text_field($input['hotspot_armatur_text'] ?? $defaults['hotspot_armatur_text']),
		'hotspot_armatur_url'   => sanitize_text_field($input['hotspot_armatur_url'] ?? $defaults['hotspot_armatur_url']),
		'auto_header_offset' => empty($input['auto_header_offset']) ? '0' : '1',
	);
}

function mtett_header_get_options() {
	$options = get_option(MTETT_HEADER_OPTION_KEY, array());

	return wp_parse_args(is_array($options) ? $options : array(), mtett_header_get_defaults());
}

function mtett_header_register_settings() {
	register_setting(
		'mtett_header_settings',
		MTETT_HEADER_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'mtett_header_sanitize_options',
			'default'           => mtett_header_get_defaults(),
		)
	);
}

add_action('admin_init', 'mtett_header_register_settings');

function mtett_header_add_settings_page() {
	add_menu_page(
		'M-TETT Header',
		'M-TETT Header',
		'manage_options',
		'mtett-header',
		'mtett_header_render_settings_page',
		'dashicons-format-image',
		59
	);

	add_options_page(
		'M-TETT Header',
		'M-TETT Header',
		'manage_options',
		'mtett-header',
		'mtett_header_render_settings_page'
	);
}

add_action('admin_menu', 'mtett_header_add_settings_page');

function mtett_header_plugin_action_links($links) {
	$settings_link = '<a href="' . esc_url(admin_url('admin.php?page=mtett-header')) . '">Settings</a>';
	array_unshift($links, $settings_link);

	return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mtett_header_plugin_action_links');

function mtett_header_render_settings_page() {
	if (! current_user_can('manage_options')) {
		return;
	}

	$options = mtett_header_get_options();
	?>
	<div class="wrap">
		<h1>M-TETT Header</h1>
		<p>Shortcode: <code>[m_tett_header]</code></p>
		<form method="post" action="options.php">
			<?php settings_fields('mtett_header_settings'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="mtett-hero-min-height">Minstehoyde</label></th>
					<td><input id="mtett-hero-min-height" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hero_min_height]" type="text" class="regular-text" value="<?php echo esc_attr($options['hero_min_height']); ?>">
					<p class="description">Eksempel: <code>92vh</code>, <code>760px</code>.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-padding-top">Spacing topp</label></th>
					<td><input id="mtett-padding-top" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[padding_top]" type="text" class="regular-text" value="<?php echo esc_attr($options['padding_top']); ?>">
					<p class="description">Eksempel: <code>24px</code> eller <code>4rem</code>.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-padding-bottom">Spacing bunn</label></th>
					<td><input id="mtett-padding-bottom" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[padding_bottom]" type="text" class="regular-text" value="<?php echo esc_attr($options['padding_bottom']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-headline-html">Overskrift</label></th>
					<td>
						<textarea id="mtett-headline-html" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[headline_html]" rows="3" class="large-text"><?php echo esc_textarea($options['headline_html']); ?></textarea>
						<p class="description">Du kan bruke enkel HTML som <code>&lt;br&gt;</code> og <code>&lt;span&gt;</code>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-description-text">Beskrivelse</label></th>
					<td>
						<textarea id="mtett-description-text" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[description_text]" rows="4" class="large-text"><?php echo esc_textarea($options['description_text']); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-primary-text">Tekst knapp 1</label></th>
					<td><input id="mtett-primary-text" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[primary_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['primary_text']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-primary-url">Lenke knapp 1</label></th>
					<td><input id="mtett-primary-url" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[primary_url]" type="url" class="regular-text" value="<?php echo esc_attr($options['primary_url']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-secondary-text">Tekst knapp 2</label></th>
					<td><input id="mtett-secondary-text" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[secondary_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['secondary_text']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-secondary-url">Lenke knapp 2</label></th>
					<td><input id="mtett-secondary-url" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[secondary_url]" type="text" class="regular-text" value="<?php echo esc_attr($options['secondary_url']); ?>">
					<p class="description">Stotter for eksempel <code>tel:04915</code> og vanlige URL-er.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-hotspot-sluk-text">Hotspot sluk</label></th>
					<td>
						<input id="mtett-hotspot-sluk-text" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_sluk_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_sluk_text']); ?>">
						<p><input name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_sluk_url]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_sluk_url']); ?>"></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-hotspot-flis-text">Hotspot flis</label></th>
					<td>
						<input id="mtett-hotspot-flis-text" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_flis_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_flis_text']); ?>">
						<p><input name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_flis_url]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_flis_url']); ?>"></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-hotspot-sisterne-text">Hotspot sisterne</label></th>
					<td>
						<input id="mtett-hotspot-sisterne-text" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_sisterne_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_sisterne_text']); ?>">
						<p><input name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_sisterne_url]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_sisterne_url']); ?>"></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mtett-hotspot-armatur-text">Hotspot armatur</label></th>
					<td>
						<input id="mtett-hotspot-armatur-text" name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_armatur_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_armatur_text']); ?>">
						<p><input name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[hotspot_armatur_url]" type="text" class="regular-text" value="<?php echo esc_attr($options['hotspot_armatur_url']); ?>"></p>
						<p class="description">Hotspots fungerer som klikkbare knapper.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Auto-offset under meny</th>
					<td><label><input name="<?php echo esc_attr(MTETT_HEADER_OPTION_KEY); ?>[auto_header_offset]" type="checkbox" value="1" <?php checked($options['auto_header_offset'], '1'); ?>> Forsok a plassere headeren automatisk rett under sticky meny.</label></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function mtett_header_render_shortcode() {
	static $instance = 0;

	$instance++;
	$options    = mtett_header_get_options();
	$iframe_id  = 'mtett-header-frame-' . $instance;
	$iframe_src = add_query_arg(
		array(
			'headline_html_b64' => base64_encode(wp_json_encode($options['headline_html'])),
			'description'    => $options['description_text'],
			'primary_text'   => $options['primary_text'],
			'primary_url'    => $options['primary_url'],
			'secondary_text' => $options['secondary_text'],
			'secondary_url'  => $options['secondary_url'],
			'hotspot_sluk_text'     => $options['hotspot_sluk_text'],
			'hotspot_sluk_url'      => $options['hotspot_sluk_url'],
			'hotspot_flis_text'     => $options['hotspot_flis_text'],
			'hotspot_flis_url'      => $options['hotspot_flis_url'],
			'hotspot_sisterne_text' => $options['hotspot_sisterne_text'],
			'hotspot_sisterne_url'  => $options['hotspot_sisterne_url'],
			'hotspot_armatur_text'  => $options['hotspot_armatur_text'],
			'hotspot_armatur_url'   => $options['hotspot_armatur_url'],
			'min_height'     => $options['hero_min_height'],
			'padding_top'    => $options['padding_top'],
			'padding_bottom' => $options['padding_bottom'],
		),
		plugins_url('assets/m-tett-header.html', __FILE__)
	);

	ob_start();
	?>
	<div class="mtett-header" data-auto-offset="<?php echo esc_attr($options['auto_header_offset']); ?>">
		<style>
			.mtett-header {
				position: relative;
				margin-top: 0;
				padding-top: var(--mtett-header-offset, 0px);
			}

			.mtett-header-frame {
				display: block;
				width: 100%;
				min-height: 520px;
				height: 720px;
				border: 0;
				overflow: hidden;
			}
		</style>
		<iframe
			id="<?php echo esc_attr($iframe_id); ?>"
			class="mtett-header-frame"
			src="<?php echo esc_url($iframe_src); ?>"
			title="<?php echo esc_attr__('M-TETT Header', 'mtett-header'); ?>"
			loading="lazy"
			scrolling="no"
		></iframe>
	</div>
	<script>
		(function () {
			if (window.__mtettHeaderListenerAttached) {
				return;
			}

			window.__mtettHeaderListenerAttached = true;

			function getTopHeaderOffset() {
				var selectors = [
					'header[role="banner"]',
					'header.site-header',
					'#masthead',
					'.site-header',
					'.header',
					'.elementor-location-header'
				];
				var maxBottom = 0;

				selectors.forEach(function (selector) {
					var nodes = document.querySelectorAll(selector);
					for (var i = 0; i < nodes.length; i += 1) {
						var style = window.getComputedStyle(nodes[i]);
						if (style.display === 'none' || style.visibility === 'hidden') {
							continue;
						}

						if (style.position !== 'fixed' && style.position !== 'sticky') {
							continue;
						}

						var rect = nodes[i].getBoundingClientRect();
						if (rect.top <= 0 && rect.bottom > maxBottom) {
							maxBottom = rect.bottom;
						}
					}
				});

				var adminBar = document.getElementById('wpadminbar');
				if (adminBar) {
					var adminRect = adminBar.getBoundingClientRect();
					if (adminRect.bottom > maxBottom) {
						maxBottom = adminRect.bottom;
					}
				}

				return Math.max(Math.ceil(maxBottom), 0);
			}

			function applyHeaderOffset() {
				var wrappers = document.querySelectorAll('.mtett-header');

				for (var i = 0; i < wrappers.length; i += 1) {
					var offset = wrappers[i].getAttribute('data-auto-offset') === '1' ? getTopHeaderOffset() : 0;
					wrappers[i].style.setProperty('--mtett-header-offset', offset + 'px');
				}
			}

			applyHeaderOffset();
			window.addEventListener('load', applyHeaderOffset);
			window.addEventListener('resize', applyHeaderOffset);
			window.addEventListener('scroll', applyHeaderOffset, { passive: true });

			window.addEventListener('message', function (event) {
				var data = event.data;
				if (!data || data.type !== 'mtett-header') {
					return;
				}

				var frames = document.querySelectorAll('.mtett-header-frame');
				var currentFrame = null;

				for (var i = 0; i < frames.length; i += 1) {
					if (frames[i].contentWindow === event.source) {
						currentFrame = frames[i];
						break;
					}
				}

				if (!currentFrame) {
					return;
				}

				if (data.action === 'height' && data.height) {
					currentFrame.style.height = Math.max(parseInt(data.height, 10) || 0, 420) + 'px';
				}
			});
		}());
	</script>
	<?php

	return ob_get_clean();
}

add_shortcode('m_tett_header', 'mtett_header_render_shortcode');
