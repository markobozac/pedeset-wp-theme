<?php
/**
 * Usporedni klizač 1968. ↔ 2018.
 * $args: place, size, eager
 */
$pl    = $args['place'];
$size  = $args['size'] ?? 'full';
$eager = ! empty( $args['eager'] );
$attrs = array(
	'loading'       => $eager ? 'eager' : 'lazy',
	'draggable'     => 'false',
	'fetchpriority' => $eager ? 'high' : 'auto',
);
?>
<div class="cmp" data-cmp style="--pos:50%">
	<div class="cmp-stage" data-stage>
		<div class="cmp-layer cmp-new">
			<?php echo pedeset_img( $pl['new'], $size, array_merge( $attrs, array( 'alt' => $pl['title'] . ' iz zraka 2018.' ) ) ); ?>
		</div>
		<div class="cmp-layer cmp-old">
			<?php echo pedeset_img( $pl['old'], $size, array_merge( $attrs, array( 'alt' => $pl['title'] . ' iz zraka 1968.' ) ) ); ?>
		</div>
	</div>
	<span class="cmp-tag cmp-tag-l">1968.</span>
	<span class="cmp-tag cmp-tag-r">2018.</span>
	<div class="cmp-handle" aria-hidden="true"><span></span></div>
	<input class="cmp-range" type="range" min="0" max="100" value="50" step="0.1" aria-label="Usporedba: lijevo 1968., desno 2018.">
</div>
