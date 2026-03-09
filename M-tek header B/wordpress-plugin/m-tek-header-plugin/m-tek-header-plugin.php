<?php
/**
 * Plugin Name: M-TEK Header B
 * Description: Viser M-TEK Header B som en WordPress-shortcode med enkle innstillinger for spacing og knapper.
 * Version: 1.1.0
 * Author: Codex
 */

if (! defined('ABSPATH')) {
	exit;
}

define('MTEK_HEADER_B_VERSION', '1.1.0');
define('MTEK_HEADER_B_OPTION_KEY', 'mtek_header_b_options');

function mtek_header_b_get_defaults() {
	return array(
		'hero_min_height'    => '92vh',
		'padding_top'        => '80px',
		'padding_bottom'     => '80px',
		'headline_html'      => 'Kaldt gulv?<br>Vi fikser <em>varmekabelen</em>.',
		'description_text'   => 'Vi finner hvor feilen ligger og reparerer den - uten unodig riving og uten lang ventetid.',
		'primary_text'       => 'Kontakt oss',
		'primary_url'        => 'https://m-tek.no/bestill-oppdrag/',
		'secondary_text'     => 'Ring oss',
		'secondary_url'      => 'tel:04915',
		'auto_header_offset' => '1',
	);
}

function mtek_header_b_sanitize_length($value, $default) {
	$value = is_string($value) ? trim($value) : '';

	if ($value === '') {
		return $default;
	}

	if (preg_match('/^\d+(?:\.\d+)?(?:px|vh|vw|%|rem|em)$/', $value)) {
		return $value;
	}

	return $default;
}

function mtek_header_b_sanitize_options($input) {
	$defaults = mtek_header_b_get_defaults();
	$input    = is_array($input) ? $input : array();

	return array(
		'hero_min_height'    => mtek_header_b_sanitize_length($input['hero_min_height'] ?? '', $defaults['hero_min_height']),
		'padding_top'        => mtek_header_b_sanitize_length($input['padding_top'] ?? '', $defaults['padding_top']),
		'padding_bottom'     => mtek_header_b_sanitize_length($input['padding_bottom'] ?? '', $defaults['padding_bottom']),
		'headline_html'      => wp_kses_post($input['headline_html'] ?? $defaults['headline_html']),
		'description_text'   => sanitize_textarea_field($input['description_text'] ?? $defaults['description_text']),
		'primary_text'       => sanitize_text_field($input['primary_text'] ?? $defaults['primary_text']),
		'primary_url'        => esc_url_raw($input['primary_url'] ?? $defaults['primary_url']),
		'secondary_text'     => sanitize_text_field($input['secondary_text'] ?? $defaults['secondary_text']),
		'secondary_url'      => sanitize_text_field($input['secondary_url'] ?? $defaults['secondary_url']),
		'auto_header_offset' => empty($input['auto_header_offset']) ? '0' : '1',
	);
}

function mtek_header_b_get_options() {
	$options = get_option(MTEK_HEADER_B_OPTION_KEY, array());

	return wp_parse_args(is_array($options) ? $options : array(), mtek_header_b_get_defaults());
}

function mtek_header_b_register_settings() {
	register_setting(
		'mtek_header_b_settings',
		MTEK_HEADER_B_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'mtek_header_b_sanitize_options',
			'default'           => mtek_header_b_get_defaults(),
		)
	);
}

add_action('admin_init', 'mtek_header_b_register_settings');

function mtek_header_b_add_settings_page() {
	add_menu_page(
		'M-TEK Header B',
		'M-TEK Header B',
		'manage_options',
		'mtek-header-b',
		'mtek_header_b_render_settings_page',
		'dashicons-format-image',
		58
	);

	add_options_page(
		'M-TEK Header B',
		'M-TEK Header B',
		'manage_options',
		'mtek-header-b',
		'mtek_header_b_render_settings_page'
	);
}

add_action('admin_menu', 'mtek_header_b_add_settings_page');

function mtek_header_b_plugin_action_links($links) {
	$settings_link = '<a href="' . esc_url(admin_url('admin.php?page=mtek-header-b')) . '">Settings</a>';
	array_unshift($links, $settings_link);

	return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mtek_header_b_plugin_action_links');

function mtek_header_b_render_settings_page() {
	if (! current_user_can('manage_options')) {
		return;
	}

	$options = mtek_header_b_get_options();
	?>
	<div class="wrap">
		<h1>M-TEK Header B</h1>
		<p>Shortcode: <code>[m_tek_header]</code></p>
		<form method="post" action="options.php">
			<?php settings_fields('mtek_header_b_settings'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="mtek-hero-min-height">Minstehoyde</label></th>
					<td><input id="mtek-hero-min-height" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[hero_min_height]" type="text" class="regular-text" value="<?php echo esc_attr($options['hero_min_height']); ?>">
					<p class="description">Eksempel: <code>92vh</code>, <code>760px</code>.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-padding-top">Spacing topp</label></th>
					<td><input id="mtek-padding-top" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[padding_top]" type="text" class="regular-text" value="<?php echo esc_attr($options['padding_top']); ?>">
					<p class="description">Eksempel: <code>24px</code> eller <code>4rem</code>.</p></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-padding-bottom">Spacing bunn</label></th>
					<td><input id="mtek-padding-bottom" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[padding_bottom]" type="text" class="regular-text" value="<?php echo esc_attr($options['padding_bottom']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-headline-html">Overskrift</label></th>
					<td>
						<textarea id="mtek-headline-html" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[headline_html]" rows="3" class="large-text"><?php echo esc_textarea($options['headline_html']); ?></textarea>
						<p class="description">Du kan bruke enkel HTML som <code>&lt;br&gt;</code> og <code>&lt;em&gt;</code>.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-description-text">Beskrivelse</label></th>
					<td>
						<textarea id="mtek-description-text" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[description_text]" rows="4" class="large-text"><?php echo esc_textarea($options['description_text']); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-primary-text">Tekst knapp 1</label></th>
					<td><input id="mtek-primary-text" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[primary_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['primary_text']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-primary-url">Lenke knapp 1</label></th>
					<td><input id="mtek-primary-url" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[primary_url]" type="url" class="regular-text" value="<?php echo esc_attr($options['primary_url']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-secondary-text">Tekst knapp 2</label></th>
					<td><input id="mtek-secondary-text" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[secondary_text]" type="text" class="regular-text" value="<?php echo esc_attr($options['secondary_text']); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="mtek-secondary-url">Lenke knapp 2</label></th>
					<td><input id="mtek-secondary-url" name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[secondary_url]" type="text" class="regular-text" value="<?php echo esc_attr($options['secondary_url']); ?>">
					<p class="description">Stotter for eksempel <code>tel:04915</code> og vanlige URL-er.</p></td>
				</tr>
				<tr>
					<th scope="row">Auto-offset under meny</th>
					<td><label><input name="<?php echo esc_attr(MTEK_HEADER_B_OPTION_KEY); ?>[auto_header_offset]" type="checkbox" value="1" <?php checked($options['auto_header_offset'], '1'); ?>> Forsok a plassere headeren automatisk rett under sticky meny.</label></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function mtek_header_b_render_shortcode() {
	static $instance = 0;

	$instance++;
	$options   = mtek_header_b_get_options();
	$iframe_id = 'mtek-header-b-frame-' . $instance;
	$iframe_src = add_query_arg(
		array(
			'headline_html_b64' => base64_encode(wp_json_encode($options['headline_html'])),
			'description'    => $options['description_text'],
			'primary_text'   => $options['primary_text'],
			'primary_url'    => $options['primary_url'],
			'secondary_text' => $options['secondary_text'],
			'secondary_url'  => $options['secondary_url'],
			'min_height'     => $options['hero_min_height'],
			'padding_top'    => $options['padding_top'],
			'padding_bottom' => $options['padding_bottom'],
		),
		plugins_url('assets/m-tek-header.html', __FILE__)
	);

	ob_start();
	?>
	<div class="mtek-header-b" data-auto-offset="<?php echo esc_attr($options['auto_header_offset']); ?>">
		<style>
			.mtek-header-b {
				position: relative;
				margin-top: 0;
				padding-top: var(--mtek-header-b-offset, 0px);
			}

			.mtek-header-b-frame {
				display: block;
				width: 100%;
				min-height: 680px;
				height: 760px;
				border: 0;
				overflow: hidden;
			}
		</style>
		<iframe
			id="<?php echo esc_attr($iframe_id); ?>"
			class="mtek-header-b-frame"
			src="<?php echo esc_url($iframe_src); ?>"
			title="<?php echo esc_attr__('M-TEK Header B', 'mtek-header-b'); ?>"
			loading="lazy"
			scrolling="no"
		></iframe>
	</div>
	<script>
		(function () {
			if (window.__mtekHeaderBListenerAttached) {
				return;
			}

			window.__mtekHeaderBListenerAttached = true;

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
				var wrappers = document.querySelectorAll('.mtek-header-b');

				for (var i = 0; i < wrappers.length; i += 1) {
					var offset = wrappers[i].getAttribute('data-auto-offset') === '1' ? getTopHeaderOffset() : 0;
					wrappers[i].style.setProperty('--mtek-header-b-offset', offset + 'px');
				}
			}

			applyHeaderOffset();
			window.addEventListener('load', applyHeaderOffset);
			window.addEventListener('resize', applyHeaderOffset);
			window.addEventListener('scroll', applyHeaderOffset, { passive: true });

			window.addEventListener('message', function (event) {
				var data = event.data;
				if (!data || data.type !== 'mtek-header-b') {
					return;
				}

				var frames = document.querySelectorAll('.mtek-header-b-frame');
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
					currentFrame.style.height = Math.max(parseInt(data.height, 10) || 0, 480) + 'px';
					return;
				}

				if (data.action === 'scrollTo' && data.target) {
					var target = document.getElementById(data.target);
					if (!target) {
						target = document.querySelector('[name="' + data.target.replace(/"/g, '\\"') + '"]');
					}

					if (target && typeof target.scrollIntoView === 'function') {
						target.scrollIntoView({ behavior: 'smooth', block: 'start' });
					}
				}
			});
		}());
	</script>
	<?php

	return ob_get_clean();
}

add_shortcode('m_tek_header', 'mtek_header_b_render_shortcode');
