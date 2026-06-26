<?php
/**
 * Plugin Name: Omega Guard
 * Description: Protects your site from clones and impostors — injects your Omega Guard canary so a copied site is detected the moment it loads.
 * Version: 1.0.0
 * Author: Omega Point Solutions LLC
 * Author URI: https://omega-guard.omegapointsolutions.com
 * License: Proprietary
 * Requires at least: 5.0
 * Requires PHP: 7.2
 *
 * Omega Guard — brand-protection / clone-site detection.
 * Copyright (c) Omega Point Solutions LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

/**
 * Plugin constants.
 */
if ( ! defined( 'OMEGA_GUARD_VERSION' ) ) {
	define( 'OMEGA_GUARD_VERSION', '1.0.0' );
}
if ( ! defined( 'OMEGA_GUARD_BEACON_URL' ) ) {
	define( 'OMEGA_GUARD_BEACON_URL', 'https://guard.omegapointsolutions.com/beacon' );
}
if ( ! defined( 'OMEGA_GUARD_RECON_URL' ) ) {
	define( 'OMEGA_GUARD_RECON_URL', 'https://guard.omegapointsolutions.com/ingest/recon' );
}
if ( ! defined( 'OMEGA_GUARD_DASHBOARD_URL' ) ) {
	define( 'OMEGA_GUARD_DASHBOARD_URL', 'https://omega-guard.omegapointsolutions.com' );
}

/**
 * ---------------------------------------------------------------------------
 * Settings (Settings API)
 * ---------------------------------------------------------------------------
 */

/**
 * Register the settings page under Settings → Omega Guard.
 */
function omega_guard_add_settings_page() {
	add_options_page(
		'Omega Guard',
		'Omega Guard',
		'manage_options',
		'omega-guard',
		'omega_guard_render_settings_page'
	);
}
add_action( 'admin_menu', 'omega_guard_add_settings_page' );

/**
 * Register settings, section and fields with the Settings API.
 */
function omega_guard_register_settings() {
	register_setting(
		'omega_guard_settings',
		'omega_guard_token',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	register_setting(
		'omega_guard_settings',
		'omega_guard_portal_token',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);

	add_settings_section(
		'omega_guard_main',
		'Protection Kit',
		'omega_guard_section_intro',
		'omega-guard'
	);

	add_settings_field(
		'omega_guard_token',
		'Canary token',
		'omega_guard_field_token',
		'omega-guard',
		'omega_guard_main'
	);

	add_settings_field(
		'omega_guard_portal_token',
		'Portal token (optional)',
		'omega_guard_field_portal_token',
		'omega-guard',
		'omega_guard_main'
	);
}
add_action( 'admin_init', 'omega_guard_register_settings' );

/**
 * Section intro copy.
 */
function omega_guard_section_intro() {
	echo '<p>Copy these values from your Omega Guard dashboard &rarr; <strong>Protection Kit</strong>. ';
	echo 'Once your canary token is saved, Omega Guard begins watching for clones of this site.</p>';
}

/**
 * Render the Canary token field.
 */
function omega_guard_field_token() {
	$value = get_option( 'omega_guard_token', '' );
	printf(
		'<input type="text" id="omega_guard_token" name="omega_guard_token" value="%s" class="regular-text" autocomplete="off" spellcheck="false" />',
		esc_attr( $value )
	);
	echo '<p class="description">Your site-specific canary token. Find it in your Omega Guard dashboard &rarr; Protection Kit. This is required for protection to be active.</p>';
}

/**
 * Render the Portal token field.
 */
function omega_guard_field_portal_token() {
	$value = get_option( 'omega_guard_portal_token', '' );
	printf(
		'<input type="text" id="omega_guard_portal_token" name="omega_guard_portal_token" value="%s" class="regular-text" autocomplete="off" spellcheck="false" />',
		esc_attr( $value )
	);
	echo '<p class="description">Optional. Enables server-side recon reporting (detects clone tools scraping your site). Copy it from your Omega Guard dashboard &rarr; Protection Kit.</p>';
}

/**
 * Render the settings page.
 */
function omega_guard_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$token = get_option( 'omega_guard_token', '' );
	?>
	<div class="wrap">
		<h1>Omega Guard</h1>
		<p>Brand-protection &amp; clone-site detection by <strong>Omega Point Solutions LLC</strong>.</p>

		<?php if ( '' !== trim( (string) $token ) ) : ?>
			<div class="notice notice-success inline" style="margin:15px 0;padding:10px 12px;">
				<p style="margin:0;">
					<span class="dashicons dashicons-shield" style="color:#198754;"></span>
					<strong>This site is protected.</strong>
					Your Omega Guard canary is active. If a copy of this site loads anywhere else, it will be detected.
				</p>
			</div>
		<?php else : ?>
			<div class="notice notice-warning inline" style="margin:15px 0;padding:10px 12px;">
				<p style="margin:0;">
					<span class="dashicons dashicons-shield-alt" style="color:#b8860b;"></span>
					<strong>Protection is not active yet.</strong>
					Paste your canary token below to turn on Omega Guard.
				</p>
			</div>
		<?php endif; ?>

		<form action="options.php" method="post">
			<?php
			settings_fields( 'omega_guard_settings' );
			do_settings_sections( 'omega-guard' );
			submit_button( 'Save protection settings' );
			?>
		</form>

		<hr />
		<p>
			Need a token or want to see detections?
			<a href="<?php echo esc_url( OMEGA_GUARD_DASHBOARD_URL ); ?>" target="_blank" rel="noopener noreferrer">
				Open your Omega Guard dashboard &rarr;
			</a>
		</p>
	</div>
	<?php
}

/**
 * Add a quick "Settings" link on the Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array
 */
function omega_guard_action_links( $links ) {
	$settings = sprintf(
		'<a href="%s">Settings</a>',
		esc_url( admin_url( 'options-general.php?page=omega-guard' ) )
	);
	array_unshift( $links, $settings );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'omega_guard_action_links' );

/**
 * ---------------------------------------------------------------------------
 * Canary injection (front end)
 * ---------------------------------------------------------------------------
 */

/**
 * Build the allowlist of legitimate hostnames for this site
 * (the home host plus its www / non-www counterpart).
 *
 * @return array
 */
function omega_guard_allowed_hosts() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$host = is_string( $host ) ? strtolower( $host ) : '';

	$hosts = array();
	if ( '' !== $host ) {
		$hosts[] = $host;
		if ( 0 === strpos( $host, 'www.' ) ) {
			$hosts[] = substr( $host, 4 );
		} else {
			$hosts[] = 'www.' . $host;
		}
	}

	return array_values( array_unique( array_filter( $hosts ) ) );
}

/**
 * Inject the Omega Guard canary into <head> when a token is configured.
 */
function omega_guard_inject_canary() {
	$token = trim( (string) get_option( 'omega_guard_token', '' ) );
	if ( '' === $token ) {
		return;
	}

	$favicon_url = add_query_arg(
		array(
			't' => $token,
			'v' => 'favicon',
		),
		OMEGA_GUARD_BEACON_URL
	);

	$pixel_url = add_query_arg(
		array(
			't' => $token,
			'v' => 'pixel',
		),
		OMEGA_GUARD_BEACON_URL
	);

	$js_beacon_base = add_query_arg(
		array(
			't' => $token,
			'v' => 'js',
		),
		OMEGA_GUARD_BEACON_URL
	);

	$allowed = omega_guard_allowed_hosts();

	echo "\n<!-- Omega Guard canary — clone protection by Omega Point Solutions LLC -->\n";

	// Favicon beacon.
	printf(
		'<link rel="icon" href="%s" />' . "\n",
		esc_url( $favicon_url )
	);

	// Hidden 1x1 tracking pixel.
	printf(
		'<img src="%s" width="1" height="1" alt="" aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;opacity:0;" />' . "\n",
		esc_url( $pixel_url )
	);

	// Off-domain JavaScript beacon (only fires if loaded from an unknown host).
	$js  = '(function(){';
	$js .= 'try{';
	$js .= 'var ok=' . wp_json_encode( $allowed ) . ';';
	$js .= "var b='" . esc_js( $js_beacon_base ) . "';";
	$js .= 'if(ok.indexOf(location.hostname)<0){';
	$js .= "var u=b+'&h='+encodeURIComponent(location.hostname);";
	$js .= 'if(navigator.sendBeacon){navigator.sendBeacon(u);}';
	$js .= 'else{var i=new Image();i.src=u;}';
	$js .= '}';
	$js .= '}catch(e){}';
	$js .= '})();';

	echo '<script>' . $js . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- value is built from esc_js() + wp_json_encode().
	echo "<!-- /Omega Guard canary -->\n";
}
add_action( 'wp_head', 'omega_guard_inject_canary', 1 );

/**
 * ---------------------------------------------------------------------------
 * Recon report (server side, non-blocking)
 * ---------------------------------------------------------------------------
 */

/**
 * Resolve the best-guess client IP from request headers.
 *
 * @return string
 */
function omega_guard_client_ip() {
	$candidates = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

	foreach ( $candidates as $key ) {
		if ( empty( $_SERVER[ $key ] ) ) {
			continue;
		}

		$raw = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

		// X-Forwarded-For may be a comma-separated list; take the first.
		if ( false !== strpos( $raw, ',' ) ) {
			$parts = explode( ',', $raw );
			$raw   = trim( $parts[0] );
		}

		$ip = filter_var( $raw, FILTER_VALIDATE_IP );
		if ( false !== $ip ) {
			return $ip;
		}
	}

	return '';
}

/**
 * Inspect the request user agent and, if it looks like a clone / scrape tool,
 * fire a non-blocking recon report to Omega Guard. Rate-limited per IP.
 */
function omega_guard_maybe_report_recon() {
	$portal = trim( (string) get_option( 'omega_guard_portal_token', '' ) );
	if ( '' === $portal ) {
		return;
	}

	// Never run on the admin side or for logged-in management requests.
	if ( is_admin() ) {
		return;
	}

	$ua = isset( $_SERVER['HTTP_USER_AGENT'] )
		? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) )
		: '';

	$pattern = '/HTTrack|Wget|curl|SingleFile|HeadlessChrome|PhantomJS|puppeteer|playwright|python-requests|Scrapy|Go-http-client/i';

	$is_suspicious = ( '' === $ua ) || (bool) preg_match( $pattern, $ua );
	if ( ! $is_suspicious ) {
		return;
	}

	$ip   = omega_guard_client_ip();
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$host = is_string( $host ) ? $host : '';

	// Rate-limit: at most one report per IP (or UA hash) per 10 minutes.
	$key   = 'omega_guard_recon_' . md5( ( '' !== $ip ? $ip : $ua ) );
	if ( false !== get_transient( $key ) ) {
		return;
	}
	set_transient( $key, 1, 10 * MINUTE_IN_SECONDS );

	$body = wp_json_encode(
		array(
			'ua'     => $ua,
			'ip'     => $ip,
			'domain' => $host,
		)
	);

	// Fire-and-forget — never block page rendering.
	wp_remote_post(
		OMEGA_GUARD_RECON_URL,
		array(
			'blocking' => false,
			'timeout'  => 1,
			'headers'  => array(
				'content-type'   => 'application/json',
				'x-portal-token' => $portal,
			),
			'body'     => $body,
		)
	);
}
add_action( 'init', 'omega_guard_maybe_report_recon' );
