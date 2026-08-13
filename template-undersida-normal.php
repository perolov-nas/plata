<?php
/**
 * Template Name: Undersida normal
 *
 * Innehåll till vänster och en innehållsförteckning till höger, byggd av
 * h2- och h3-rubrikerna i innehållet.
 *
 * @package Plata
 */

get_header();

while ( have_posts() ) :
	the_post();

	$rendered = apply_filters( 'the_content', get_the_content() );
	$rendered = str_replace( ']]>', ']]&gt;', $rendered );

	$show_toc = plata_page_shows_toc( get_the_ID() );
	$toc      = $show_toc
		? plata_build_toc( $rendered )
		: array(
			'content'  => $rendered,
			'headings' => array(),
		);
	$has_toc  = $show_toc && ! empty( $toc['headings'] );
	?>
	<main id="main" class="site-main site-main--wide">
		<article <?php post_class( 'page-toc' ); ?>>
			<!-- <header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header> -->

			<div class="page-toc__grid<?php echo $has_toc ? '' : ' page-toc__grid--single'; ?>">
				<?php if ( $has_toc ) : ?>
					<aside class="toc">
						<div class="toc__header">
							<h4 class="toc__title" id="plata-toc-title">
								<?php esc_html_e( 'Innehåll', 'plata' ); ?>
							</h4>
							<button
								class="toc__toggle"
								type="button"
								aria-expanded="false"
								aria-controls="plata-toc-panel"
							>
								<span class="screen-reader-text">
									<?php esc_html_e( 'Visa innehållsförteckning', 'plata' ); ?>
								</span>
								<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
									<path d="m6 9 6 6 6-6" />
								</svg>
							</button>
						</div>

						<nav class="toc__panel" id="plata-toc-panel" aria-labelledby="plata-toc-title">
							<?php plata_render_toc( $toc['headings'] ); ?>
						</nav>
					</aside>
				<?php endif; ?>

				<div class="entry-content">
					<?php
					// Innehållet har redan passerat the_content-filtren ovan.
					echo $toc['content']; // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped
					?>
				</div>
			</div>
		</article>
	</main>
	<?php
endwhile;

get_footer();
