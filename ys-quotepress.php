<?php
/**
* Plugin Name:  YS-QuotePress
* Description:  Custom Post Type "הצעת מחיר" (quote) + בסיס לתוסף הצעות מחיר.
* Version:      0.1.0
* Author:       Yaniv Sasson
* Text Domain:  ys-quotepress
* License: GPL-2.0-or-later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined('ABSPATH') ) exit;

// Composer autoload (אם קיים)
$__autoload = __DIR__ . '/vendor/autoload.php';

if ( file_exists($__autoload) ) {
	require_once $__autoload;
}


final class YS_QuotePress {
	
	const CPT = 'quote';

	public static function init() : void {
		
		add_action('init', [__CLASS__, 'register_cpt']);
		register_activation_hook(__FILE__, [__CLASS__, 'activate']);
		register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);

		// תבנית single ייעודית
		add_filter('single_template', [__CLASS__, 'load_single_template']);

		// CSS רק בעמודי הצעת מחיר
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets_js']);

		add_action('wp_enqueue_scripts', function () {
			if (is_singular(self::CPT)) {
				wp_localize_script('ysqp-validate-v3', 'YSQP', [
					'ajax_url'     => admin_url('admin-ajax.php'),
					//'thankyou_url' => home_url('/quote-thank-you/'),
					'nonce'        => wp_create_nonce('ysqp_form'),
				]);
			}
		});

		// הורדת PDF דרך פרמטר ?ysqp=pdf
		add_action('template_redirect', [__CLASS__, 'maybe_download_pdf']);
		
		// shortcode [ysqp_quote_form] 
		add_shortcode('ysqp_quote_form', [__CLASS__, 'shortcode_quote_form']);
		
		add_action('wp_ajax_submit_form_entry',        [__CLASS__, 'ajax_submit_form_entry']);
		add_action('wp_ajax_nopriv_submit_form_entry', [__CLASS__, 'ajax_submit_form_entry']);
		
		add_action('admin_menu', [__CLASS__, 'register_admin_page']);
	}
	
	public static function plugin_path() : string {
		return plugin_dir_path(__FILE__);
	}
	
	public static function plugin_url() : string {
		return plugin_dir_url(__FILE__);
	}

	public static function register_cpt() : void {
		$labels = [
			'name'               => 'הצעות מחיר',
			'singular_name'      => 'הצעת מחיר',
			'menu_name'          => 'הצעות מחיר',
			'name_admin_bar'     => 'הצעת מחיר',
			'add_new'            => 'הוספת חדשה',
			'add_new_item'       => 'הוספת הצעת מחיר',
			'new_item'           => 'הצעת מחיר חדשה',
			'edit_item'          => 'עריכת הצעת מחיר',
			'view_item'          => 'צפייה בהצעת מחיר',
			'all_items'          => 'כל ההצעות',
			'search_items'       => 'חיפוש הצעות',
			'parent_item_colon'  => 'הצעת אב:',
			'not_found'          => 'לא נמצאו הצעות',
			'not_found_in_trash' => 'לא נמצאו בהאשפה',
		];

		register_post_type(self::CPT, [
			'labels'              => $labels,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-media-text',
			'supports'            => ['title', 'editor', 'custom-fields', 'revisions', 'excerpt'],
			'has_archive'         => false,
			'hierarchical'        => false,
			'show_in_rest'        => true, // תומך גוטנברג + REST
			'rewrite'             => [
				'slug'       => 'quote',
				'with_front' => false,
			],
			'capability_type'     => 'post', // ברירת־מחדל, בלי capabilities ייעודיים כרגע
			'map_meta_cap'        => true,
		]);
	}

	public static function load_single_template( $single ) {
		if ( is_singular(self::CPT) ) {
			$template = self::plugin_path() . 'templates/single-quote.php';
			if ( file_exists($template) ) {
				return $template;
			}
		}
		return $single;
	}

	public static function enqueue_assets() : void {
		if ( is_singular(self::CPT) ) {
			wp_enqueue_style(	'ys-quotepress-quote',self::plugin_url() . 'assets/css/quote.css',[],'0.1.1');
			wp_enqueue_style(	'ys-quotepress-form',self::plugin_url() . 'assets/css/form.css',[],'0.1.3');
		}
	}

	public static function enqueue_assets_js() : void {
		if ( is_singular(self::CPT) ) {
			wp_enqueue_script( 'signature-pad', 'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js', [], null, true );
			wp_enqueue_script('ysqp-validate-v3',self::plugin_url() . 'assets/js/validation-v3.js',['signature-pad'],'0.1.2',true);
		}
	}
	
	public static function maybe_download_pdf() : void {
		if ( ! is_singular(self::CPT) ) return;
		if ( empty($_GET['ysqp']) || $_GET['ysqp'] !== 'pdf' ) return;

		// אבטחה: רק משתמשים מחוברים יכולים להוריד PDF
		if ( ! current_user_can('edit_posts') ) {
			wp_die(__('אין לך הרשאה להוריד את הקובץ.', 'ys-quotepress'), 403);
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) return;

		if ( ! class_exists(\Mpdf\Mpdf::class) ) {
			wp_die(__('ספריית mPDF לא מותקנת (composer).', 'ys-quotepress'));
		}

		// בונים את ה-HTML מתוך התבנית הקיימת
		$html = self::render_pdf_html($post_id);

		// הגדרות מומלצות ל-RTL/עברית עם Heebo
		$font_dir = self::plugin_path() . 'assets/fonts/Heebo/';
		$mpdf = new \Mpdf\Mpdf([
			'mode'             => 'utf-8',
			'format'           => 'A4',
			'orientation'      => 'P',
			'rtl'              => true,
			'autoScriptToLang' => true,
			'autoLangToFont'   => true,
			'default_font'     => 'heebo',
			'fontDir'          => [$font_dir],
			'fontdata'         => [
				'heebo' => [
					'R'  => 'Heebo-Regular.ttf',
					'B'  => 'Heebo-Bold.ttf',
					'L'  => 'Heebo-Light.ttf',
				],
			],
		]);

		// CSS חיצוני אם קיים
		$css_path = plugin_dir_path(__FILE__) . 'assets/css/pdf.css';
		if ( file_exists($css_path) ) {
			$css = file_get_contents($css_path);
			$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
		}

		// HTML גוף המסמך
		$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

		$filename = sprintf('quote-%d.pdf', $post_id);
		$mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
		exit;
	}

	private static function render_pdf_html(int $post_id, array $form_data = []) : string {
		// נתונים להצעה
		$post = get_post($post_id);
		if (!$post) return '';
		setup_postdata($post);

		// נתוני טופס לתבנית (ללא extract)
		$first_name = $form_data['first_name'] ?? '';
		$last_name = $form_data['last_name'] ?? '';
		$phone = $form_data['phone'] ?? '';
		$email = $form_data['email'] ?? '';
		$company_name = $form_data['company_name'] ?? '';
		$company_id = $form_data['company_id'] ?? '';
		$signature_dataurl = $form_data['signature_dataurl'] ?? '';

		ob_start();
		// קובץ תבנית PDF מתוך התוסף
		$pdf_template = self::plugin_path() . 'templates/pdf-quote.php';
		$theme_css_url = get_stylesheet_directory_uri() . '/css/style.css';
		$pdf_css_url  = self::plugin_url()  . 'assets/css/pdf.css';
		$form_css_url  = self::plugin_url()  . 'assets/css/form.css';

		// משתנים זמינים בתבנית:
		$quote_number   = get_post_meta($post_id, 'quote_number', true);
		$total_price    = get_post_meta($post_id, 'quote_total_price', true);
		$valid_until    = get_post_meta($post_id, 'quote_valid_until', true); // עתידי

		include $pdf_template;

		wp_reset_postdata();
		return (string) ob_get_clean();
	}

	public static function shortcode_quote_form($atts = []) : string {
	  ob_start();
	  include self::plugin_path() . 'templates/quote-form.php'; // אם תשמור את ה-HTML בקובץ
	  return ob_get_clean();
	}

	public static function ajax_submit_form_entry(): void {
		// אבטחה: nonce
		if (empty($_POST['_ysqp']) || ! wp_verify_nonce($_POST['_ysqp'], 'ysqp_form')) {
			wp_send_json_error(['message' => 'bad_nonce'], 403);
		}

		$post_id = isset($_POST['quote_id']) ? (int) $_POST['quote_id'] : 0;
		if ( ! $post_id || get_post_type($post_id) !== self::CPT ) {
			wp_send_json_error(['message' => 'invalid_quote'], 400);
		}

		// Rate limiting: max 3 submissions per IP per 5 minutes
		$ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
		$rate_key = 'ysqp_rate_' . md5($ip);
		$submissions = get_transient($rate_key);
		if ($submissions && $submissions >= 3) {
			wp_send_json_error(['message' => 'rate_limit_exceeded'], 429);
		}
		set_transient($rate_key, ($submissions ? $submissions + 1 : 1), 5 * MINUTE_IN_SECONDS);

		$signature_dataurl = '';
		if (!empty($_POST['signature_dataurl']) && is_string($_POST['signature_dataurl'])) {
			// ולידציה מתקדמת
			if (strpos($_POST['signature_dataurl'], 'data:image/png;base64,') === 0) {
				// הגבלת גודל: ~75KB base64 (100KB להיות בטוחים)
				if (strlen($_POST['signature_dataurl']) > 100000) {
					wp_send_json_error(['message' => 'signature_too_large'], 400);
				}
				$signature_dataurl = $_POST['signature_dataurl'];
			}
		}
		
		$form_data = [
		  'first_name'    => sanitize_text_field($_POST['first_name'] ?? ''),
		  'last_name'     => sanitize_text_field($_POST['last_name']  ?? ''),
		  'phone'         => sanitize_text_field($_POST['phone']      ?? ''),
		  'email'         => sanitize_email($_POST['email'] ?? ''),
		  'company_name'  => sanitize_text_field($_POST['company_name'] ?? ''),
		  'company_id'    => sanitize_text_field($_POST['company_id'] ?? ''),
		  'signature_dataurl' => $signature_dataurl,
		];
		// בנה HTML ל-PDF
		$html = self::render_pdf_html($post_id, $form_data);

		// mPDF (כבר התקנת)
		if ( ! class_exists(\Mpdf\Mpdf::class) ) {
			wp_send_json_error(['message' => 'mpdf_missing'], 500);
		}

		$font_dir = self::plugin_path() . 'assets/fonts/Heebo/';
		$mpdf = new \Mpdf\Mpdf([
			'mode'             => 'utf-8',
			'format'           => 'A4',
			'orientation'      => 'P',
			'rtl'              => true,
			'autoScriptToLang' => true,
			'autoLangToFont'   => true,
			'default_font'     => 'heebo',
			'fontDir'          => [$font_dir],
			'fontdata'         => [
				'heebo' => [
					'R'  => 'Heebo-Regular.ttf',
					'B'  => 'Heebo-Bold.ttf',
					'L'  => 'Heebo-Light.ttf',
				],
			],
		]);

		// CSS ל-PDF אם קיים
		$css_path = self::plugin_path() . 'assets/css/pdf.css';
		if ( file_exists($css_path) ) {
			$mpdf->WriteHTML(file_get_contents($css_path), \Mpdf\HTMLParserMode::HEADER_CSS);
		}
		$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

		// שמירה ל-uploads/ys-quotepress
		$up  = wp_upload_dir();
		$dir = trailingslashit($up['basedir']) . 'ys-quotepress';
		if ( ! is_dir($dir) ) wp_mkdir_p($dir);

		$filename = 'quote-id-' . $post_id . '-' . date('Ymd-His') . '.pdf';
		$path     = $dir . '/' . $filename;
		$mpdf->Output($path, \Mpdf\Output\Destination::FILE);

		$url = trailingslashit($up['baseurl']) . 'ys-quotepress/' . $filename;
		update_post_meta($post_id, '_ysqp_pdf_last_path', $path);
		update_post_meta($post_id, '_ysqp_pdf_last_url',  $url);

		// כתובת תודה (אפשר לשנות)
		$thanks = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url('/quote-thank-you/');
		//$thanks = add_query_arg(['quote' => $post_id, 'pdf' => 'created'], $thanks);

		// 2. רישום למדיה
		list($attach_id, $media_url) = self::attach_pdf_to_media($path, $post_id);
		if ($attach_id) {
			update_post_meta($post_id, '_ysqp_pdf_attachment_id', $attach_id);
		}
		if ($media_url) {
			update_post_meta($post_id, '_ysqp_pdf_last_url', $media_url);
		}

		// 3. הכנסה לטבלת "אושרו" (אפשר למלא שם/אימייל מהטופס אם שלחת)
		global $wpdb;
		$table = $wpdb->prefix . 'ysqp_signed_quotes';
		$now   = current_time('mysql');
		$hash  = wp_generate_password(20, false); // מזהה אישור (לשימוש עתידי)
		$ua    = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

		$customer_first_name  = sanitize_text_field($_POST['first_name'] ?? '');
		$customer_last_name   = sanitize_text_field($_POST['last_name'] ?? '');
		$customer_phone       = sanitize_text_field($_POST['phone'] ?? '');
		$customer_email       = sanitize_email($_POST['email'] ?? '');
		$customer_company_name = sanitize_text_field($_POST['company_name'] ?? '');
		$customer_company_id  = sanitize_text_field($_POST['company_id'] ?? '');
		$meta = [
			'raw' => array_map('sanitize_text_field', $_POST),
		];

		$wpdb->insert($table, [
			'quote_post_id'         => $post_id,
			'customer_first_name'   => $customer_first_name,
			'customer_last_name'    => $customer_last_name,
			'customer_phone'        => $customer_phone,
			'customer_email'        => $customer_email,
			'customer_company_name' => $customer_company_name,
			'customer_company_id'   => $customer_company_id,
			'approved_at'           => $now,
			'pdf_attachment_id'     => $attach_id ?: 0,
			'pdf_url'               => $media_url ?: $url,
			'confirm_hash'          => $hash,
			'ip'                    => $ip,
			'user_agent'            => $ua,
			'meta'                  => wp_json_encode($meta, JSON_UNESCAPED_UNICODE),
			'created_at'            => $now,
		], [
			'%d','%s','%s','%s','%s','%s','%s','%s','%d','%s','%s','%s','%s','%s','%s'
		]);


		wp_send_json_success([
			'message'     => 'success1',
			// 'redirect_to' => $thanks,
			'pdf_attachment_id' => $attach_id,
			'pdf_url'     => $url,
		]);
	}

	private static function attach_pdf_to_media(string $abs_path, int $post_id = 0) : array {
		if ( ! file_exists($abs_path) ) return [0, ''];
		$filetype = wp_check_filetype( basename($abs_path), null );
		$attachment = [
			'post_mime_type' => $filetype['type'] ?: 'application/pdf',
			'post_title'     => preg_replace('/\.[^.]+$/', '', basename($abs_path)),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_parent'    => $post_id,
		];
		$attach_id = wp_insert_attachment( $attachment, $abs_path, $post_id );
		if ( is_wp_error($attach_id) || ! $attach_id ) return [0, ''];

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$attach_data = wp_generate_attachment_metadata( $attach_id, $abs_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		$url = wp_get_attachment_url($attach_id) ?: '';
		return [$attach_id, $url];
	}

	private static function create_tables() : void {
		global $wpdb;
		$table = $wpdb->prefix . 'ysqp_signed_quotes';
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$sql = "CREATE TABLE $table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			quote_post_id BIGINT UNSIGNED NOT NULL,
			customer_first_name VARCHAR(190) DEFAULT '' NOT NULL,
			customer_last_name VARCHAR(190) DEFAULT '' NOT NULL,
			customer_phone VARCHAR(50) DEFAULT '' NOT NULL,
			customer_email VARCHAR(190) DEFAULT '' NOT NULL,
			customer_company_name VARCHAR(190) DEFAULT '' NOT NULL,
			customer_company_id VARCHAR(50) DEFAULT '' NOT NULL,
			approved_at DATETIME NOT NULL,
			pdf_attachment_id BIGINT UNSIGNED DEFAULT 0,
			pdf_url TEXT,
			confirm_hash VARCHAR(64) DEFAULT '' NOT NULL,
			ip VARCHAR(45) DEFAULT '' NOT NULL,
			user_agent TEXT,
			meta LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY quote_post_id (quote_post_id),
			KEY approved_at (approved_at),
			KEY pdf_attachment_id (pdf_attachment_id)
		) $charset;";
		dbDelta($sql);
	}

	public static function register_admin_page() : void {
		add_menu_page(
			'הצעות חתומות',                 // כותרת בתפריט
			'הצעות חתומות',                 // שם בתפריט
			'manage_options',                // הרשאת גישה
			'ysqp_signed_quotes',            // slug
			[__CLASS__, 'admin_page_html'],  // callback
			'dashicons-media-spreadsheet',   // אייקון
			26                               // מיקום בתפריט
		);
	}

	public static function admin_page_html() : void {
		if ( ! current_user_can('manage_options') ) return;

		global $wpdb;
		$table = $wpdb->prefix . 'ysqp_signed_quotes';

		// טיפול במחיקה (בטוח עם nonce)
		if ( isset($_GET['action'], $_GET['id'], $_GET['_wpnonce'])
			 && $_GET['action'] === 'delete'
			 && wp_verify_nonce($_GET['_wpnonce'], 'ysqp_delete_quote_' . $_GET['id']) ) {
			$wpdb->delete($table, ['id' => intval($_GET['id'])]);
			echo '<div class="notice notice-success"><p>ההצעה נמחקה בהצלחה.</p></div>';
		}

		// Pagination setup
		$per_page = 20;
		$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
		$offset = ($current_page - 1) * $per_page;

		// Get total count for pagination
		$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table");
		$total_pages = ceil($total_items / $per_page);

		// Get paginated results
		$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM $table ORDER BY id DESC LIMIT %d OFFSET %d",
			$per_page,
			$offset
		));
		?>
		<div class="wrap">
			<h1>הצעות חתומות</h1>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>שם פרטי</th>
						<th>שם משפחה</th>
						<th>טלפון</th>
						<th>אימייל</th>
						<th>שם חברה</th>
						<th>ח.פ</th>
						<th>תאריך</th>
						<th>PDF</th>
						<th>פעולות</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( $rows ) : ?>
						<?php foreach ( $rows as $r ) : ?>
							<tr>
								<td><?php echo esc_html($r->id); ?></td>
								<td><?php echo esc_html($r->customer_first_name); ?></td>
								<td><?php echo esc_html($r->customer_last_name); ?></td>
								<td><?php echo esc_html($r->customer_phone ?? ''); ?></td>
								<td><?php echo esc_html($r->customer_email); ?></td>
								<td><?php echo esc_html($r->customer_company_name ?? ''); ?></td>
								<td><?php echo esc_html($r->customer_company_id ?? ''); ?></td>
								<td><?php echo esc_html($r->approved_at); ?></td>
								<td>
									<?php if ( $r->pdf_url ) : ?>
										<a href="<?php echo esc_url($r->pdf_url); ?>" target="_blank">הורד PDF</a>
									<?php endif; ?>
								</td>
								<td>
									<?php
									$del_url = add_query_arg([
										'page'   => 'ysqp_signed_quotes',
										'action' => 'delete',
										'id'     => $r->id,
										'_wpnonce' => wp_create_nonce('ysqp_delete_quote_' . $r->id),
									], admin_url('admin.php'));
									?>
									<a href="<?php echo esc_url($del_url); ?>"
									   onclick="return confirm('למחוק את הרשומה הזו?');">מחק</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="10">אין רשומות להצגה.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ($total_pages > 1) : ?>
				<div class="tablenav bottom">
					<div class="tablenav-pages">
						<span class="displaying-num">
							<?php printf(
								_n('%s פריט', '%s פריטים', $total_items, 'ys-quotepress'),
								number_format_i18n($total_items)
							); ?>
						</span>
						<?php
						$page_links = paginate_links([
							'base'      => add_query_arg('paged', '%#%'),
							'format'    => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total'     => $total_pages,
							'current'   => $current_page,
							'type'      => 'plain',
						]);
						echo $page_links;
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}


	public static function activate() : void {
		self::register_cpt();
		self::create_tables();
		flush_rewrite_rules();
	}

	public static function deactivate() : void {
		flush_rewrite_rules();
	}
}

YS_QuotePress::init();
