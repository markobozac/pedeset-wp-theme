<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main">Preskoči na sadržaj</a>
<header class="site-head">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="50 godina — naslovnica">
		<span class="brand-num">50</span>
		<span class="brand-txt">godina<br><small>1968. → danas</small></span>
	</a>
	<nav class="head-nav" aria-label="Glavni izbornik">
		<a href="<?php echo esc_url( home_url( '/#karta' ) ); ?>">Karta</a>
		<a href="<?php echo esc_url( home_url( '/#mjesta' ) ); ?>">Sva mjesta</a>
		<a href="<?php echo esc_url( home_url( '/o-projektu/' ) ); ?>">O projektu</a>
	</nav>
</header>
<main id="main">
