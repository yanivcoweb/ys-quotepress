
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
		<article id="post-<?php the_ID(); ?>" <?php post_class('ys-quote'); ?>>

			<header class="ys-quote__header">
				<table width="100%" >
				<tbody>
				<tr>
					<td>
						<div class="ys-quote__logo">
							<?php
								$img = '';
								$img = get_field('header_logo', 'options');
								if($img):
									echo '<img src="'.$img['url'].'" alt="'.$img['alt'].'" />';
								endif;
							?>
						</div>
					</td>
					<td>
						<div class="ys-quote__meta" >
							<div class="ys-quote__date"><?php echo esc_html( get_the_date('', $post) ); ?></div>
							<div class="ys-quote__id">מספר הצעה: <?php the_ID(); ?></div>						
						</div>
					</td>
					</tr>	  
				</tbody>
				</table>
				<div>יניב ששון הנדסת תוכנה</div>
				<div>0533029531</div>
				<div>yaniv@coweb.co.il</div>
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
