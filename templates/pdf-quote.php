
<?php
/**
 * PDF Template for Quote
 * Vars: $post (WP_Post), $quote_number, $total_price, $valid_until, $pdf_css_url
 */
if ( ! defined('ABSPATH') ) exit;
?>
<!doctype html>
<html lang="he" dir="rtl">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="<?php echo esc_url($theme_css_url); ?>">
	<link rel="stylesheet" href="<?php echo esc_url($pdf_css_url); ?>">
	<link rel="stylesheet" href="<?php echo esc_url($form_css_url); ?>">
</head>
<body class="rtl">
	<main id="primary" class="ys-quote-wrap container container-small">
		<article id="post-<?php echo esc_attr($post->ID); ?>" class="ys-quote">

			<header class="ys-quote__header">
				<table width="100%" >
				<tbody>
				<tr>
					<td align="right">
						<div class="ys-quote__logo">
							<?php
								$img = '';
								$img = get_field('header_logo', 'options');
								if($img):
									echo '<img src="'.$img['url'].'" alt="'.$img['alt'].'" style="width:125px;" />';
								endif;
							?>
						</div>
						<div style="margin-top: 10px; font-size: 13px;">
							<div>יניב ששון הנדסת תוכנה</div>
							<div>0533029531</div>
							<div>yaniv@coweb.co.il</div>
						</div>
					</td>
					<td style="vertical-align: top;text-align: left;font-size: 13px;">
						<div style="display: inline-block;text-align: right;">
							<div class="ys-quote__meta" >
								<div class="ys-quote__date" ><?php echo esc_html( get_the_date('', $post) ); ?></div>
								<div class="ys-quote__id" >מספר הצעה: <?php echo esc_html($post->ID); ?></div>						
							</div>
							<?php
							$customer_details = get_post_meta($post->ID, 'quote_customer_details', true);
							if (!empty($customer_details)) :
							?>
							<div >
								<strong>עבור:</strong><br>
								<div style="margin-top: 5px;"><?php echo nl2br(esc_html($customer_details)); ?></div>
							</div>
							<?php endif; ?>
						</div>
					</td>
					</tr>	  
				</tbody>
				</table>
				<h1 class="ys-quote__title"><?php echo esc_html( get_the_title($post) ); ?></h1>
			</header>

			<section class="ys-quote__content">
				<?php
					// התוכן העשיר של ההצעה (WP)
					$content = apply_filters('the_content', $post->post_content);
					// Dompdf תומך ב-HTML בסיסי; הימנע מ-JS/iframes
					echo $content;
				?>
			</section>

			<section class="ys-quote-form-wrap">
			<table>
			<tbody>
				<tr>

					<td>
						<label>שם פרטי:</label>
						<p><?php echo esc_html( trim($first_name ?? '') ); ?></p>
						<hr/>
					</td>

					<td>
						<label>שם משפחה:</label>
						<p><?php echo esc_html( trim($last_name ?? '') ); ?></p>
						<hr/>
					</td>

					<td>
						<label>טלפון:</label>
						<p><?php echo esc_html( trim($phone ?? '') ); ?></p>
						<hr/>
					</td>

				</tr>

				<tr>

					<td>
						<label>אימייל:</label>
						<p><?php echo esc_html( trim($email ?? '') ); ?></p>
						<hr/>
					</td>

					<td>
						<label>שם החברה:</label>
						<p><?php echo esc_html( trim($company_name ?? '') ); ?></p>
						<hr/>
					</td>

					<td>
						<label>ח.פ / ע.מ:</label>
						<p><?php echo esc_html( trim($company_id ?? '') ); ?></p>
						<hr/>
					</td>

				</tr>

				<tr>

					<td>
						<label>חתימה:</label>
						<p>
						<?php if (!empty($signature_dataurl)) : ?>
							<img src="<?php echo esc_attr($signature_dataurl); ?>" alt="Signature" style="height:80px;">
						<?php endif; ?>
						</p>
						<hr/>
					</td>

					<td>	</td>

					<td></td>

				</tr>	

			</tbody>
			</table>
			</section>

			
		</article>
		
		<footer class="pdf-footer">
			<p>© <span class="ltr"><?php echo esc_html( date('Y') ); ?></span> <?php echo esc_html( get_bloginfo('name') ); ?> — כל הזכויות שמורות.</p>
		</footer>
		
	</main>

</body>
</html>
