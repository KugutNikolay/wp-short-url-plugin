<?php
/**
 * Plugin Name: Short URL Plugin
 * Description: A plugin for creating short URLs.
 * Version: 1.0
 * Author: Nikolay Kuhut
 * License: GPL2
 */


// Enqueue scripts and styles
function shortUrlPluginEnqueueScripts()
{
	wp_enqueue_style('short-url-plugin-style', plugin_dir_url(__FILE__) . 'css/short-url-plugin.css');
	wp_enqueue_script('short-url-plugin-script', plugin_dir_url(__FILE__) . 'js/short-url-plugin.js', ['jquery'], '', true);
}

add_action('wp_enqueue_scripts', 'shortUrlPluginEnqueueScripts');

// Create short_urls table on plugin activation
register_activation_hook(__FILE__, 'createShortUrlsTable');

function createShortUrlsTable()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'short_urls';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
        id int(11) NOT NULL AUTO_INCREMENT,
        url varchar(255) NOT NULL,
        short_code varchar(50) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY short_code_unique (short_code)
    ) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

// Add rewrite rules for the short-url endpoint
add_action('init', 'shortUrlPluginRewrite');

function shortUrlPluginRewrite()
{
	add_rewrite_rule('^([^/]+)/?$', 'index.php?short_url_plugin=$matches[1]', 'top');
	add_rewrite_tag('%short_url_plugin%', '([^&]+)');
	add_filter('query_vars', 'shortUrlPluginQueryVars');
	flush_rewrite_rules();
}

// Register query variable for the short_url_plugin
function shortUrlPluginQueryVars($vars)
{
	$vars[] = 'short_url_plugin';
	return $vars;
}

// Handle the short-url endpoint
add_action('template_redirect', 'shortUrlPluginPage');

function shortUrlPluginPage()
{
	global $wpdb;

	if (get_query_var('short_url_plugin')) {
		$short_code = sanitize_text_field(get_query_var('short_url_plugin'));

		$table_name = $wpdb->prefix . 'short_urls';
		$original_url = $wpdb->get_var($wpdb->prepare("SELECT url FROM $table_name WHERE short_code = %s", $short_code));

		if ($original_url) {
			wp_redirect($original_url, 301);
			exit();
		}
	}

	if (isset($_POST['url'])) {
		$url = esc_url_raw($_POST['url']);
		if (filter_var($url, FILTER_VALIDATE_URL)) {
			$table_name = $wpdb->prefix . 'short_urls';
			$short_code = $wpdb->get_var($wpdb->prepare("SELECT short_code FROM $table_name WHERE url = %s", $url));

			if (!$short_code) {
				do {
					$short_code = shortUrlPluginGenerateCode(microtime(true)*10000);
					$exist_short_code = $wpdb->get_var($wpdb->prepare("SELECT short_code FROM $table_name WHERE short_code = %s", $short_code));
					if(!$exist_short_code) {
						$wpdb->insert(
							$table_name,
							[
								'url'        => $url,
								'short_code' => $short_code
							]
						);
					}
				}
				while(!empty($exist_short_code));

			}
			echo home_url("/$short_code");
		}
	} else {
		include plugin_dir_path(__FILE__) . 'templates/page.php';
	}
	exit();
}

// Generate a random string for the short code
function shortUrlPluginGenerateCode($num)
{
	$base='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$b = 62;
	$r = $num  % $b ;
	$res = $base[$r];
	$q = floor($num/$b);
	while ($q) {
		$r = $q % $b;
		$q =floor($q/$b);
		$res = $base[$r].$res;
	}

	return $res;
}

