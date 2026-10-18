<?php
/**
 * Pedeset — tema za 50godina.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PEDESET_VER', '1.0.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	register_nav_menus( array( 'primary' => 'Glavni izbornik' ) );
} );

/* ---------- Meta polja lokacije (vidljiva u REST-u) ---------- */
add_action( 'init', function () {
	$fields = array(
		'g50_old'      => 'integer', // ID slike 1968.
		'g50_new'      => 'integer', // ID slike 2018.
		'g50_lat'      => 'number',
		'g50_lng'      => 'number',
		'g50_type'     => 'string',  // grad | mjesto
		'g50_story'    => 'string',  // kratka priča o promjeni
		'g50_featured' => 'integer', // >0 = istaknuta priča (redoslijed)
	);
	foreach ( $fields as $key => $type ) {
		register_post_meta( 'page', $key, array(
			'type'          => $type,
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_pages' );
			},
		) );
	}
} );

/* ---------- Divi ostaci: makni et_pb_ shortcodove iz sadržaja ---------- */
add_filter( 'the_content', function ( $content ) {
	$content = preg_replace( '/\[\/?et_pb_[^\]]*\]/', '', $content );
	return $content;
}, 5 );

/* ---------- Lokacija iz stranice ---------- */
function pedeset_place( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type ) {
		return null;
	}
	$old = (int) get_post_meta( $post->ID, 'g50_old', true );
	$new = (int) get_post_meta( $post->ID, 'g50_new', true );
	if ( ! $old || ! $new ) {
		// Rezerva: stari twenty20 shortcode u sadržaju.
		if ( preg_match( '/\[twenty20[^\]]*img1="(\d+)"[^\]]*img2="(\d+)"/', $post->post_content, $m ) ) {
			$old = (int) $m[1];
			$new = (int) $m[2];
		}
	}
	if ( ! $old || ! $new ) {
		return null;
	}
	$type = get_post_meta( $post->ID, 'g50_type', true );
	return array(
		'id'       => $post->ID,
		'title'    => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
		'url'      => get_permalink( $post ),
		'old'      => $old,
		'new'      => $new,
		'lat'      => (float) get_post_meta( $post->ID, 'g50_lat', true ),
		'lng'      => (float) get_post_meta( $post->ID, 'g50_lng', true ),
		'type'     => $type ? $type : 'grad',
		'story'    => (string) get_post_meta( $post->ID, 'g50_story', true ),
		'featured' => (int) get_post_meta( $post->ID, 'g50_featured', true ),
	);
}

/** Sve lokacije (keširano). */
function pedeset_places() {
	$cached = get_transient( 'pedeset_places_v1' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$q      = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );
	$places = array();
	foreach ( $q as $p ) {
		$pl = pedeset_place( $p );
		if ( ! $pl ) {
			continue;
		}
		$t1 = wp_get_attachment_image_src( $pl['old'], 'medium_large' );
		$t2 = wp_get_attachment_image_src( $pl['new'], 'medium_large' );
		$places[] = array(
			'id'    => $pl['id'],
			't'     => $pl['title'],
			'u'     => $pl['url'],
			'lat'   => $pl['lat'],
			'lng'   => $pl['lng'],
			'k'     => $pl['type'],
			'f'     => $pl['featured'],
			's'     => $pl['story'] ? wp_trim_words( $pl['story'], 22, '…' ) : '',
			'a'     => $t1 ? $t1[0] : '',
			'b'     => $t2 ? $t2[0] : '',
		);
	}
	usort( $places, function ( $a, $b ) {
		return strcoll( $a['t'], $b['t'] );
	} );
	set_transient( 'pedeset_places_v1', $places, DAY_IN_SECONDS );
	return $places;
}

function pedeset_flush_places() {
	delete_transient( 'pedeset_places_v1' );
}
add_action( 'save_post_page', 'pedeset_flush_places' );
add_action( 'updated_post_meta', 'pedeset_flush_places' );
add_action( 'added_post_meta', 'pedeset_flush_places' );
add_action( 'switch_theme', 'pedeset_flush_places' );

/* ---------- Skripte i stilovi ---------- */
add_action( 'wp_enqueue_scripts', function () {
	$dir = get_template_directory_uri() . '/assets';
	wp_enqueue_style( 'pedeset', $dir . '/app.css', array(), (string) filemtime( get_template_directory() . '/assets/app.css' ) );

	$needs_map = is_front_page();
	if ( $needs_map ) {
		wp_enqueue_style( 'leaflet', $dir . '/leaflet/leaflet.css', array(), '1.9.4' );
		wp_enqueue_script( 'leaflet', $dir . '/leaflet/leaflet.js', array(), '1.9.4', array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
	wp_enqueue_script( 'pedeset', $dir . '/app.js', $needs_map ? array( 'leaflet' ) : array(), (string) filemtime( get_template_directory() . '/assets/app.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script( 'pedeset', 'PEDESET', array(
		'places' => pedeset_places(),
		'home'   => home_url( '/' ),
	) );

	// Ne treba nam blok-biblioteka na front-endu.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'global-styles' );
	wp_dequeue_style( 'classic-theme-styles' );
}, 20 );

add_action( 'wp_head', function () {
	$dir = get_template_directory_uri() . '/assets/fonts';
	echo '<link rel="preload" href="' . esc_url( $dir . '/fraunces-latin-wght-normal.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<meta name="theme-color" content="#f3eee4">' . "\n";
}, 1 );

/* ---------- Čišćenje <head> ---------- */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

/* ---------- SEO: opis stranice lokacije (Yoast) ---------- */
function pedeset_desc( $desc ) {
	if ( ! is_page() ) {
		return $desc;
	}
	$pl = pedeset_place( get_queried_object_id() );
	if ( ! $pl ) {
		return $desc;
	}
	if ( $pl['story'] ) {
		$d = $pl['story'];
		if ( mb_strlen( $d ) <= 158 ) {
			return $d;
		}
		$cut = mb_substr( $d, 0, 158 );
		$cut = mb_substr( $cut, 0, (int) mb_strrpos( $cut, ' ' ) );
		return rtrim( $cut, ' ,.—–' ) . '…';
	}
	return sprintf( 'Usporedi %s iz zraka 1968. i 2018. godine. Povuci klizač i pogledaj kako se mjesto promijenilo u 50 godina.', $pl['title'] );
}
add_filter( 'wpseo_metadesc', 'pedeset_desc' );
add_filter( 'wpseo_opengraph_desc', 'pedeset_desc' );
add_filter( 'wpseo_twitter_description', 'pedeset_desc' );

/** Društvene mreže: slika usporedbe (istaknuta slika) za stranice lokacija. */
function pedeset_og_image( $img ) {
	if ( is_page() && pedeset_place( get_queried_object_id() ) && has_post_thumbnail( get_queried_object_id() ) ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id( get_queried_object_id() ), 'full' );
		if ( $src ) {
			return $src[0];
		}
	}
	return $img;
}
add_filter( 'wpseo_opengraph_image', 'pedeset_og_image' );
add_filter( 'wpseo_twitter_image', 'pedeset_og_image' );

/* ---------- Pomoćne ---------- */
function pedeset_img( $id, $size, $attrs = array() ) {
	return wp_get_attachment_image( $id, $size, false, array_merge( array( 'decoding' => 'async' ), $attrs ) );
}

/** Najbliže lokacije (km). */
function pedeset_nearby( $place, $n = 4 ) {
	if ( ! $place['lat'] ) {
		return array();
	}
	$out = array();
	foreach ( pedeset_places() as $p ) {
		if ( $p['id'] === $place['id'] || ! $p['lat'] ) {
			continue;
		}
		$dlat  = deg2rad( $p['lat'] - $place['lat'] );
		$dlng  = deg2rad( $p['lng'] - $place['lng'] );
		$a     = sin( $dlat / 2 ) ** 2 + cos( deg2rad( $place['lat'] ) ) * cos( deg2rad( $p['lat'] ) ) * sin( $dlng / 2 ) ** 2;
		$p['d'] = 6371 * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
		$out[]  = $p;
	}
	usort( $out, function ( $a, $b ) {
		return $a['d'] <=> $b['d'];
	} );
	return array_slice( $out, 0, $n );
}
require_once get_template_directory() . '/inc/social.php';
require_once get_template_directory() . '/inc/extras.php';
