<?php
/**
 * Pedeset: izbornik za dijeljenje (extras.js) + forma za prijedloge priča i fotografija.
 * Prijedlozi se spremaju kao "na čekanju" (g50_prijedlog) i NIKAD se ne objavljuju automatski.
 */

/* ---------- Skripta/stil samo na stranicama mjesta ---------- */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_page() || ! function_exists( 'pedeset_place' ) || ! pedeset_place( get_queried_object_id() ) ) {
			return;
		}
		$dir  = get_template_directory_uri() . '/assets';
		$path = get_template_directory() . '/assets';
		wp_enqueue_style( 'pedeset-extras', $dir . '/extras.css', array( 'pedeset' ), (string) filemtime( $path . '/extras.css' ) );
		wp_enqueue_script( 'pedeset-extras', $dir . '/extras.js', array( 'pedeset' ), (string) filemtime( $path . '/extras.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'pedeset-extras',
			'PEDESET_X',
			array(
				'ajax'  => admin_url( 'admin-ajax.php' ),
				'place' => get_queried_object_id(),
			)
		);
	},
	30
);

/* ---------- Prijedlozi (privatni tip sadržaja) ---------- */
add_action(
	'init',
	function () {
		register_post_type(
			'g50_prijedlog',
			array(
				'labels'          => array(
					'name'          => 'Prijedlozi posjetitelja',
					'singular_name' => 'Prijedlog',
					'menu_name'     => 'Prijedlozi',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-megaphone',
				'menu_position'   => 26,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'map_meta_cap'    => true,
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			)
		);
	}
);

add_action(
	'pedeset_after_place',
	function ( $pl ) {
		?>
<section class="wrap suggest" id="prijedlog">
	<details class="suggest-box">
		<summary>Imaš priču ili staru fotografiju ovog mjesta?</summary>
		<form class="suggest-form" data-suggest novalidate>
			<p class="muted">Podijeli što pamtiš o mjestu <strong><?php echo esc_html( $pl['title'] ); ?></strong>: kako je izgledalo, što se promijenilo, tko je tu živio. Prije objave sve pregledavam i javljam ti se.</p>
			<label>Tvoja priča ili ispravak
				<textarea name="message" rows="5" maxlength="2000" required></textarea>
			</label>
			<label>Poveznica na fotografiju (neobavezno)
				<input type="url" name="link" maxlength="300" placeholder="https://…">
			</label>
			<div class="suggest-row">
				<label>Ime (neobavezno)
					<input type="text" name="name" maxlength="80" autocomplete="name">
				</label>
				<label>E-mail (neobavezno, ako želiš odgovor)
					<input type="email" name="email" maxlength="120" autocomplete="email">
				</label>
			</div>
			<div class="suggest-hp" aria-hidden="true"><label>Web<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
			<input type="hidden" name="ts" value="">
			<p class="suggest-small">Šaljući obrazac pristaješ da pročitam tvoju poruku i, ako si ostavio/la e-mail, da ti odgovorim. Ništa se ne objavljuje bez tvog odobrenja.</p>
			<button type="submit" class="btn">Pošalji</button>
			<p class="suggest-msg" role="status" aria-live="polite"></p>
		</form>
	</details>
</section>
		<?php
	}
);

function pedeset_suggest_ip_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
	return 'g50s_' . md5( $ip . wp_salt() );
}

function pedeset_suggest_handle() {
	$fail = function ( $m, $code = 400 ) {
		wp_send_json_error( array( 'message' => $m ), $code );
	};
	// Skriveno polje (zamka za botove) i vremenska zamka
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'message' => 'Hvala!' ) ); // tiho ignoriraj
	}
	$ts = isset( $_POST['ts'] ) ? (int) $_POST['ts'] : 0;
	if ( $ts && ( time() * 1000 - $ts ) < 4000 ) {
		$fail( 'Pričekaj trenutak pa pošalji ponovno.' );
	}
	$key = pedeset_suggest_ip_key();
	$n   = (int) get_transient( $key );
	if ( $n >= 3 ) {
		$fail( 'Previše poruka u kratkom roku. Pokušaj kasnije.', 429 );
	}
	$pid     = isset( $_POST['place'] ) ? (int) $_POST['place'] : 0;
	$place   = $pid && function_exists( 'pedeset_place' ) ? pedeset_place( $pid ) : null;
	$message = isset( $_POST['message'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) ) : '';
	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$link    = isset( $_POST['link'] ) ? esc_url_raw( wp_unslash( $_POST['link'] ), array( 'http', 'https' ) ) : '';
	if ( ! $place ) {
		$fail( 'Nepoznato mjesto.' );
	}
	if ( mb_strlen( $message ) < 10 ) {
		$fail( 'Napiši barem nekoliko riječi.' );
	}
	$message = mb_substr( $message, 0, 2000 );
	$id      = wp_insert_post(
		array(
			'post_type'    => 'g50_prijedlog',
			'post_status'  => 'pending',
			'post_title'   => 'Prijedlog: ' . $place['title'],
			'post_content' => $message,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		$fail( 'Spremanje nije uspjelo, pokušaj kasnije.', 500 );
	}
	update_post_meta( $id, 'g50_place', $pid );
	update_post_meta( $id, 'g50_name', mb_substr( $name, 0, 80 ) );
	update_post_meta( $id, 'g50_email', $email );
	update_post_meta( $id, 'g50_link', $link );
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
	$body = "Mjesto: {$place['title']}\n" . get_permalink( $pid ) . "\n\nIme: $name\nE-mail: $email\nFotografija: $link\n\n$message\n\nPregled: " . admin_url( 'post.php?post=' . $id . '&action=edit' );
	$headers = array();
	if ( $email ) {
		$headers[] = 'Reply-To: ' . $email;
	}
	@wp_mail( get_option( 'admin_email' ), '[50 godina] Novi prijedlog: ' . $place['title'], $body, $headers );
	wp_send_json_success( array( 'message' => 'Hvala! Poruka je poslana, javljam se ako zatrebam dodatne detalje.' ) );
}
add_action( 'wp_ajax_g50_suggest', 'pedeset_suggest_handle' );
add_action( 'wp_ajax_nopriv_g50_suggest', 'pedeset_suggest_handle' );

/**
 * Preusmjeravanje starih adresa mjesta koja su preimenovana (stari slug -> novi slug).
 */
function pedeset_renamed_places_redirect() {
	if ( ! is_404() ) {
		return;
	}
	$map  = array( 'oroslavlje' => 'oroslavje' );
	$path = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' );
	$slug = basename( $path );
	if ( isset( $map[ $slug ] ) ) {
		wp_safe_redirect( home_url( '/' . $map[ $slug ] . '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'pedeset_renamed_places_redirect' );
