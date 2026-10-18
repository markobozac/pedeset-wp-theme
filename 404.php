<?php
get_header();
?>
<div class="wrap prose notfound">
	<p class="eyebrow">404</p>
	<h1>Ovo mjesto ne postoji ni 1968. ni danas</h1>
	<p>Stranica koju tražiš nije pronađena. Pokušaj s kartom ili popisom svih mjesta.</p>
	<p><a class="btn" href="<?php echo esc_url( home_url( '/#karta' ) ); ?>">Na kartu</a></p>
</div>
<?php
get_footer();
