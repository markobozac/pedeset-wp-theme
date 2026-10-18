<?php
get_header();
?>
<div class="wrap prose">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article>
				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<?php the_excerpt(); ?>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<h1>Ništa ovdje</h1>
		<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Natrag na kartu</a></p>
	<?php endif; ?>
</div>
<?php
get_footer();
