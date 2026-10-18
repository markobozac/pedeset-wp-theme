<?php
get_header();
while ( have_posts() ) :
	the_post();
	$pl = pedeset_place( get_the_ID() );
	if ( $pl ) :
		$near = pedeset_nearby( $pl, 4 );
		?>
<article class="place">
	<header class="wrap place-head">
		<p class="eyebrow"><?php echo 'mjesto' === $pl['type'] ? 'Mjesto' : 'Grad'; ?> · 1968. → 2018.</p>
		<h1><?php echo esc_html( $pl['title'] ); ?></h1>
		<?php if ( $pl['story'] ) : ?>
			<p class="lede"><?php echo esc_html( $pl['story'] ); ?></p>
		<?php else : ?>
			<p class="lede">Povuci klizač i usporedi <?php echo esc_html( $pl['title'] ); ?> iz zraka 1968. i 2018. godine. Što se najviše promijenilo?</p>
		<?php endif; ?>
	</header>

	<div class="place-cmp">
		<?php get_template_part( 'parts/compare', null, array( 'place' => $pl, 'size' => 'full', 'eager' => true ) ); ?>
		<div class="cmp-tools wrap">
			<div class="zoom" role="group" aria-label="Uvećanje">
				<button type="button" class="tool" data-zoom="out" aria-label="Umanji">−</button>
				<button type="button" class="tool" data-zoom="reset" aria-label="Vrati">1×</button>
				<button type="button" class="tool" data-zoom="in" aria-label="Uvećaj">+</button>
			</div>
			<button type="button" class="tool" data-share>Podijeli</button>
		</div>
	</div>

	<?php if ( $pl['lat'] ) : ?>
	<section class="wrap today">
		<div class="today-card">
			<div>
				<h2>A kako izgleda danas?</h2>
				<p class="muted">Snimka iz 2018. stara je već nekoliko godina. Otvori satelitsku kartu na istom mjestu i provjeri što se promijenilo od tada.</p>
			</div>
			<a class="btn" href="<?php echo esc_url( sprintf( 'https://www.google.com/maps/@%1$.5f,%2$.5f,%3$dm/data=!3m1!1e3', $pl['lat'], $pl['lng'], 'mjesto' === $pl['type'] ? 3000 : 4000 ) ); ?>" target="_blank" rel="noopener">Satelitska karta ↗</a>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $near ) : ?>
	<section class="wrap near">
		<h2>U blizini</h2>
		<ul class="grid grid-4">
			<?php foreach ( $near as $p ) : ?>
			<li><a href="<?php echo esc_url( $p['u'] ); ?>">
				<span class="thumb"><img src="<?php echo esc_url( $p['a'] ); ?>" alt="" loading="lazy" decoding="async" width="384" height="220"></span>
				<span class="name"><?php echo esc_html( $p['t'] ); ?> <small><?php echo esc_html( number_format_i18n( $p['d'], 0 ) ); ?> km</small></span>
			</a></li>
			<?php endforeach; ?>
		</ul>
		<p><a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/#mjesta' ) ); ?>">Sva mjesta</a></p>
	</section>
	<?php endif; ?>
	<?php do_action( 'pedeset_after_place', $pl ); ?>
</article>
		<?php
	else :
		?>
<article class="wrap prose">
	<h1><?php the_title(); ?></h1>
	<?php the_content(); ?>
</article>
		<?php
	endif;
endwhile;
get_footer();
