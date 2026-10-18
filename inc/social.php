<?php
/**
 * Pedeset: društvene mreže (Open Graph / Twitter), ikone i Google Analytics 4.
 * GA4 ID se postavlja opcijom `pedeset_ga_id` (npr. G-XXXXXXXXXX); bez nje se ništa ne učitava.
 */

/* ---------- Pomoćne ---------- */
function pedeset_social_data() {
	static $d = null;
	if ( null !== $d ) {
		return $d;
	}
	$site  = '50 godina';
	$d     = array(
		'site'   => $site,
		'locale' => 'hr_HR',
		'type'   => 'website',
		'title'  => wp_get_document_title(),
		'desc'   => '',
		'url'    => home_url( '/' ),
		'image'  => get_template_directory_uri() . '/assets/og-home.jpg',
		'w'      => 1200,
		'h'      => 630,
		'mime'   => 'image/jpeg',
		'alt'    => 'Zračna snimka iz 1968. uz današnju snimku istog mjesta. Naslov: Kako je Hrvatska izgledala prije 50 godina i danas?',
	);
	if ( is_front_page() ) {
		$d['desc'] = 'Povuci klizač i usporedi 123 grada i 11 posebnih mjesta iz zraka 1968. i danas. Pronađi svoj grad i vidi što se promijenilo u 50 godina.';
		$d['url']  = home_url( '/' );
	} elseif ( is_singular() ) {
		$id         = get_queried_object_id();
		$d['url']   = get_permalink( $id );
		$d['type']  = 'article';
		$d['desc']  = function_exists( 'pedeset_desc' ) ? pedeset_desc( '' ) : '';
		if ( ! $d['desc'] ) {
			$d['desc'] = wp_strip_all_tags( get_the_excerpt( $id ) );
		}
		if ( has_post_thumbnail( $id ) ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'full' );
			if ( $src ) {
				$d['image'] = $src[0];
				$d['w']     = (int) $src[1];
				$d['h']     = (int) $src[2];
				$d['mime']  = ( false !== stripos( $src[0], '.png' ) ) ? 'image/png' : ( ( false !== stripos( $src[0], '.webp' ) ) ? 'image/webp' : 'image/jpeg' );
				$t          = get_the_title( $id );
				$d['alt']   = sprintf( '%s iz zraka 1968. i 2018. godine, usporedba snimki', $t );
			}
		}
	} else {
		$d['desc'] = 'Usporedi zračne snimke Hrvatske iz 1968. i danas.';
	}
	$d['desc'] = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $d['desc'] ) ) );
	return $d;
}

/* ---------- Yoast: isključi njegov OG/Twitter, ispisujemo vlastiti potpuni skup ---------- */
add_filter(
	'wpseo_frontend_presenters',
	function ( $presenters ) {
		return array_values(
			array_filter(
				(array) $presenters,
				function ( $p ) {
					$c = is_object( $p ) ? get_class( $p ) : '';
					return false === strpos( $c, '\\Open_Graph\\' ) && false === strpos( $c, '\\Twitter\\' );
				}
			)
		);
	}
);
add_filter( 'wpseo_metadesc', function ( $d ) {
	$s = pedeset_social_data();
	return $s['desc'] ? $s['desc'] : $d;
}, 20 );

add_action(
	'wp_head',
	function () {
		if ( is_404() || is_search() ) {
			return;
		}
		$s = pedeset_social_data();
		$m = function ( $attr, $name, $val ) {
			if ( '' === $val || null === $val ) {
				return;
			}
			echo '<meta ' . $attr . '="' . esc_attr( $name ) . '" content="' . esc_attr( $val ) . '">' . "\n";
		};
		echo "\n<!-- Pedeset: društvene mreže -->\n";
		$m( 'property', 'og:locale', $s['locale'] );
		$m( 'property', 'og:type', $s['type'] );
		$m( 'property', 'og:site_name', $s['site'] );
		$m( 'property', 'og:title', $s['title'] );
		$m( 'property', 'og:description', $s['desc'] );
		$m( 'property', 'og:url', $s['url'] );
		$m( 'property', 'og:image', $s['image'] );
		$m( 'property', 'og:image:secure_url', $s['image'] );
		$m( 'property', 'og:image:width', $s['w'] );
		$m( 'property', 'og:image:height', $s['h'] );
		$m( 'property', 'og:image:type', $s['mime'] );
		$m( 'property', 'og:image:alt', $s['alt'] );
		$m( 'name', 'twitter:card', 'summary_large_image' );
		$m( 'name', 'twitter:title', $s['title'] );
		$m( 'name', 'twitter:description', $s['desc'] );
		$m( 'name', 'twitter:image', $s['image'] );
		$m( 'name', 'twitter:image:alt', $s['alt'] );
		echo "<!-- /Pedeset -->\n";
	},
	6
);

/* ---------- Ikone ---------- */
remove_action( 'wp_head', 'wp_site_icon', 99 );
add_action(
	'wp_head',
	function () {
		$u = get_template_directory_uri() . '/assets/';
		echo '<link rel="icon" type="image/png" sizes="48x48" href="' . esc_url( $u . 'favicon-48.png' ) . '">' . "\n";
		echo '<link rel="icon" type="image/png" sizes="192x192" href="' . esc_url( $u . 'icon-192.png' ) . '">' . "\n";
		echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $u . 'apple-touch-icon.png' ) . '">' . "\n";
	},
	7
);

/* ---------- Google Analytics 4 (uz Consent Mode v2 i obavijest o kolačićima) ---------- */
function pedeset_ga_id() {
	$id = defined( 'PEDESET_GA_ID' ) ? PEDESET_GA_ID : get_option( 'pedeset_ga_id', '' );
	return preg_match( '/^G-[A-Z0-9]{6,}$/', (string) $id ) ? $id : '';
}
function pedeset_ga_on() {
	return pedeset_ga_id() && ! current_user_can( 'edit_pages' );
}

add_action(
	'wp_head',
	function () {
		if ( ! pedeset_ga_on() ) {
			return;
		}
		$id = pedeset_ga_id();
		?>
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}
gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});
try{if(localStorage.getItem('g50c')==='1'){gtag('consent','update',{analytics_storage:'granted'})}}catch(e){}
gtag('js',new Date());gtag('config','<?php echo esc_js( $id ); ?>',{anonymize_ip:true});
</script>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $id ); ?>"></script>
		<?php
	},
	2
);

add_action(
	'wp_footer',
	function () {
		if ( ! pedeset_ga_on() ) {
			return;
		}
		?>
<div id="g50-consent" hidden role="dialog" aria-label="Kolačići" style="position:fixed;left:12px;right:12px;bottom:12px;z-index:9999;max-width:560px;margin:0 auto;background:#14110d;color:#f3eee4;border-radius:14px;padding:14px 16px;font:15px/1.4 system-ui,sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.3)">
	<p style="margin:0 0 10px">Koristimo anonimnu analitiku (Google Analytics) kako bismo vidjeli koja se mjesta najviše gledaju. Slažeš li se s tim?</p>
	<button type="button" data-v="1" style="background:#ff7a59;color:#14110d;border:0;border-radius:999px;padding:8px 16px;font:inherit;font-weight:600;cursor:pointer">Slažem se</button>
	<button type="button" data-v="0" style="background:transparent;color:#f3eee4;border:1px solid #f3eee466;border-radius:999px;padding:8px 16px;font:inherit;cursor:pointer;margin-left:6px">Ne, hvala</button>
</div>
<script>
(function(){var b=document.getElementById('g50-consent');if(!b)return;var s;try{s=localStorage.getItem('g50c')}catch(e){}
if(s===null||s===undefined){b.hidden=false}
b.addEventListener('click',function(e){var v=e.target.getAttribute&&e.target.getAttribute('data-v');if(v===null||v===undefined)return;
try{localStorage.setItem('g50c',v)}catch(x){}
if(v==='1'&&window.gtag){gtag('consent','update',{analytics_storage:'granted'})}
b.hidden=true})})();
</script>
		<?php
	},
	50
);
