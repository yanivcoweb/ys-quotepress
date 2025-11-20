<?php
/**
 * Template: Single Quote (from plugin)
 * Displays a single 'quote' post with basic layout.
 * @package YS-QuotePress
 */
if ( ! defined('ABSPATH') ) exit;

get_header();
the_post();
?>
<?php get_template_part('template-part', 'topnav'); ?>
<?php get_template_part('template-part', 'head-qoutepress'); ?>
<section id="primary" >
	<div class="section-single-quote">
	<div class="ys-quote-wrap">
		<article id="post-<?php the_ID(); ?>" <?php post_class('ys-quote clearfix'); ?>>

			<header >
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
							<div class="ys-quote__date"><?php echo esc_html( get_the_date() ); ?></div>
							<div class="ys-quote__id">מספר הצעה: <?php the_ID(); ?></div>						
						</div>
					</td>
					</tr>	  
				</tbody>
				</table>
				<div>יניב ששון הנדסת תוכנה</div>
				<div>0533029531</div>
				<div>yaniv@coweb.co.il</div>

				<h1 class="ys-quote__title"><?php the_title(); ?></h1>
			</header>

			<section class="ys-quote__content">
				<?php the_content(); ?>
			</section>


			<?php
			// מקום למחיר/שורה תחתונה – אפשר למשוך ממטא:
			$total_price = get_post_meta(get_the_ID(), 'quote_total_price', true);
			if ( $total_price ) : ?>
				<section class="ys-quote__total">
					<strong><?php esc_html_e('סכום כולל:', 'ys-quotepress'); ?></strong>
					<span class="ys-quote__price"><?php echo esc_html( $total_price ); ?></span>
				</section>
			<?php endif; ?>

			<?php echo do_shortcode('[ysqp_quote_form]'); ?>
			
			<footer class="ys-quote__footer">
				<div class="ys-quote__actions">
					<?php /*<a class="ys-btn ys-btn--ghost" href="#approve"><?php esc_html_e('אישור הצעה', 'ys-quotepress'); ?></a>*/ ?>
					<?php if ( current_user_can('edit_posts') ) : ?>
					<?php
					$download_url = add_query_arg( ['ysqp' => 'pdf'], get_permalink() );
					?>
					<a class="ys-btn ys-btn--ghost" href="<?php echo esc_url($download_url); ?>">
						<?php esc_html_e('הורדת PDF', 'ys-quotepress'); ?>
					</a>
					<?php endif; ?>
				</div>
			</footer>

		</article>
	</div>
</div>
</section>
<?php get_template_part('template-part', 'footer'); ?>
<?php
get_footer();
