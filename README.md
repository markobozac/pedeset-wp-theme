# Pedeset — WordPress tema za 50godina

Laka tema (bez page buildera) za portal **[50godina](https://markobozac.com/50godina/)**: karta Hrvatske i usporedbe zračnih snimaka iz 1968. i danas, uz klizač, zumiranje i pretragu mjesta.

## Što sadrži
- `front-page.php` – hero, pretraga, Leaflet karta s pinovima, "Najveće promjene", filtrirana mreža mjesta
- `page.php` + `parts/compare.php` – stranica mjesta s klizačem (zoom, pinch), "U blizini", poveznica na satelitsku kartu
- `inc/social.php` – Open Graph / Twitter oznake, ikone, Google Analytics 4 uz Consent Mode v2 i obavijest o kolačićima
- `inc/extras.php` + `assets/extras.*` – izbornik za dijeljenje (WhatsApp, Facebook, X, e-mail, kopiraj link), položaj klizača u linku (`?r=NN`), moderirana forma za priče i fotografije (privatni tip sadržaja `g50_prijedlog`)
- Leaflet 1.9.4 i font Fraunces (OFL) su uključeni lokalno

## Podaci
Mjesta su WordPress stranice s meta poljima: `g50_old`, `g50_new` (ID priloga snimki 1968. / danas), `g50_lat`, `g50_lng`, `g50_type` (`grad` | `mjesto`), `g50_story`, `g50_featured`.

## Google Analytics
Isključen je dok se ne postavi ID: `wp option update pedeset_ga_id G-XXXXXXXXXX` (ili konstanta `PEDESET_GA_ID`). Prijavljeni urednici se ne prate.

## Napomene
- Slike za društvene mreže (`assets/og-home.jpg`, ikone `icon-192.png`, `icon-512.png`, `apple-touch-icon.png`, `favicon-48.png`) nisu u repozitoriju: generiraju se iz fotografija stranice. Dodaj vlastite u `assets/`.
- Zahtijeva WordPress 6.4+ i PHP 8.0+.

## Licenca
GPL-2.0-or-later. Leaflet: BSD-2-Clause. Fraunces: SIL OFL 1.1.
