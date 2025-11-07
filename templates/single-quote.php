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
<main id="primary" class="ys-quote-wrap container container-small">
	<article id="post-<?php the_ID(); ?>" <?php post_class('ys-quote'); ?>>

		<header class="ys-quote__header">
			<h1 class="ys-quote__title"><?php the_title(); ?></h1>
			<div class="ys-quote__meta">
				<time class="ys-quote__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</div>
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
			<br /><br />
		</footer>

	</article>
</main>
<?php
get_footer();
