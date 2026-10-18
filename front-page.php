<?php
get_header();
$places   = pedeset_places();
$featured = array_values( array_filter( $places, function ( $p ) {
	return $p['f'] > 0;
} ) );
usort( $featured, function ( $a, $b ) {
	return $a['f'] <=> $b['f'];
} );
$hero = $featured ? pedeset_place( $featured[0]['id'] ) : ( $places ? pedeset_place( $places[0]['id'] ) : null );
$n_grad   = count( array_filter( $places, fn( $p ) => 'grad' === $p['k'] ) );
$n_mjesto = count( $places ) - $n_grad;
?>
<section class="hero">
	<div class="wrap hero-grid">
		<div class="hero-copy">
			<p class="eyebrow">Snimke iz zraka · 1968. i danas</p>
			<h1>Kako je tvoj grad izgledao <em>prije 50 godina?</em></h1>
			<p class="lede">Povuci klizač i usporedi Hrvatsku iz 1968. s današnjom — <?php echo (int) $n_grad; ?> gradova i <?php echo (int) $n_mjesto; ?> posebnih mjesta, snimke poravnate jedna preko druge.</p>
			<form class="finder" role="search" onsubmit="return false">
				<label class="sr" for="finder-q">Pronađi grad ili mjesto</label>
				<input id="finder-q" type="search" placeholder="Upiši grad, npr. Rijeka" autocomplete="off" list="finder-list">
				<datalist id="finder-list">
					<?php foreach ( $places as $p ) : ?>
						<option value="<?php echo esc_attr( $p['t'] ); ?>"></option>
					<?php endforeach; ?>
				</datalist>
				<button type="submit" class="btn" data-finder-go>Pogledaj</button>
			</form>
			<p class="hero-alt"><button type="button" class="link" data-random>Iznenadi me →</button></p>
		</div>
		<?php if ( $hero ) : ?>
		<figure class="hero-cmp">
			<?php get_template_part( 'parts/compare', null, array( 'place' => $hero, 'size' => 'large', 'eager' => true ) ); ?>
			<figcaption><a href="<?php echo esc_url( $hero['url'] ); ?>"><?php echo esc_html( $hero['title'] ); ?></a> — povuci klizač</figcaption>
		</figure>
		<?php endif; ?>
	</div>
</section>

<section id="karta" class="map-sec">
	<div class="wrap">
		<h2>Karta</h2>
		<p class="muted">Klikni točku za usporedbu. Crvene točke su gradovi, tamne posebna mjesta.</p>
	</div>
	<div id="home-map" class="home-map" role="region" aria-label="Karta lokacija"></div>
</section>

<?php if ( $featured ) : ?>
<section class="stories">
	<div class="wrap">
		<h2>Najveće promjene</h2>
		<div class="story-grid">
			<?php foreach ( $featured as $p ) : ?>
			<a class="story" href="<?php echo esc_url( $p['u'] ); ?>">
				<span class="story-img">
					<img src="<?php echo esc_url( $p['a'] ); ?>" alt="" loading="lazy" decoding="async" class="s-old">
					<img src="<?php echo esc_url( $p['b'] ); ?>" alt="" loading="lazy" decoding="async" class="s-new">
				</span>
				<span class="story-t"><?php echo esc_html( $p['t'] ); ?></span>
				<span class="story-s"><?php echo esc_html( $p['s'] ); ?></span>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section id="mjesta" class="all">
	<div class="wrap">
		<div class="all-head">
			<h2>Sva mjesta <span class="count" data-count><?php echo count( $places ); ?></span></h2>
			<div class="filters" role="group" aria-label="Filtar">
				<button type="button" class="chip is-on" data-filter="all">Sve</button>
				<button type="button" class="chip" data-filter="grad">Gradovi</button>
				<button type="button" class="chip" data-filter="mjesto">Mjesta</button>
				<label class="sr" for="grid-q">Traži</label>
				<input id="grid-q" type="search" placeholder="Traži…" data-grid-q>
			</div>
		</div>
		<ul class="grid" data-grid>
			<?php foreach ( $places as $p ) : ?>
			<li data-k="<?php echo esc_attr( $p['k'] ); ?>" data-t="<?php echo esc_attr( mb_strtolower( $p['t'] ) ); ?>">
				<a href="<?php echo esc_url( $p['u'] ); ?>">
					<span class="thumb"><img src="<?php echo esc_url( $p['a'] ); ?>" alt="" loading="lazy" decoding="async" width="384" height="220"></span>
					<span class="name"><?php echo esc_html( $p['t'] ); ?></span>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_footer();
