<?php
/**
 * Uninstall YS-QuotePress Plugin
 *
 * Cleans up database tables, post meta, uploaded PDFs, and transients
 * when the plugin is deleted (not just deactivated).
 */

// Exit if not called from WordPress uninstall
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
	exit;
}

global $wpdb;

// 1. Delete custom post type posts and meta
$quote_posts = get_posts([
	'post_type'   => 'quote',
	'numberposts' => -1,
	'post_status' => 'any',
]);

foreach ( $quote_posts as $quote ) {
	// Delete all post meta for this quote
	$wpdb->delete(
		$wpdb->postmeta,
		['post_id' => $quote->ID],
		['%d']
	);
	// Delete the post itself
	wp_delete_post($quote->ID, true);
}

// 2. Delete custom database table
$table = $wpdb->prefix . 'ysqp_signed_quotes';
$wpdb->query("DROP TABLE IF EXISTS $table");

// 3. Delete uploaded PDF files
$upload_dir = wp_upload_dir();
$pdf_dir = trailingslashit($upload_dir['basedir']) . 'ys-quotepress';

if ( is_dir($pdf_dir) ) {
	// Delete all files in the directory
	$files = glob($pdf_dir . '/*');
	if ( $files ) {
		foreach ( $files as $file ) {
			if ( is_file($file) ) {
				unlink($file);
			}
		}
	}
	// Remove the directory
	rmdir($pdf_dir);
}

// 4. Delete all rate-limiting transients
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$wpdb->esc_like('_transient_ysqp_rate_') . '%'
	)
);
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
		$wpdb->esc_like('_transient_timeout_ysqp_rate_') . '%'
	)
);

// 5. Flush rewrite rules
flush_rewrite_rules();
