<?php
	include_once("../avatar/avatar-options.php");
	
	ob_start();
?>

<svg version="1.1" id="" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="600px" height="600px" viewBox="0 0 600 600" enable-background="new 0 0 600 600" xml:space="preserve" style="height: 0; width: 0; display: block;">
	<style  type="text/css">
		.avatar--right {
			transform: scaleX(-1);
			transform-origin: 50% 50%;
		}
		.eyebrow__shape {
			transform: translateX(2%);
		}
		.head__base--warugaki {
			stroke: black;
			stroke-width: 3px;
		}
		.makeup__base--tear {
			fill-opacity: 0.25;
			stroke: aliceblue;
			stroke-opacity: 0.75;
		}
		.hair__left--buzz, .hair__right--buzz {
			opacity: 0.25;
		}
		.hats__base.hats__base--veil {
			fill-opacity: 0.25;
			stroke-width: 3px;
		}
		.bangs__base {
			fill-opacity: 0.9;
		}
		.avatar__filter--smear {
			filter: url(#avatar__filter--smear);
		}
		.avatar__filter--splatter {
			filter: url(#avatar__filter--splatter);
			fill-opacity: 0.75;
		}
		.eyeshadow__makeup--bruise {
			fill-opacity: 0.5;
			filter: url(#avatar__filter--blur);
			mask: url(#avatar__mask--bruise);
		}
		.eyeshadow__makeup--cut {
			mask: url(#avatar__mask--cut);
		}
	</style>
	
	<defs>
		<?php
			/* Clip paths */
			foreach($avatar_layers as $layer_name => $parts) {
				foreach($parts as $part_name => $part_attributes) {
					if(is_array($part_attributes["shapes"]) && !empty($part_attributes["shapes"])) {
						foreach($part_attributes["shapes"] as $shape_name => $shape) {
							if(is_array($shape) && $shape["is_clip_path"]) {
								$id = "{$layer_name}__{$part_name}--{$shape_name}.path";
								?>
									<clipPath id="<?php echo $id; ?>">
										<?php
											if($shape["custom"]) {
												echo '<'.$shape["custom"].' />';
											}
											else {
												?>
													<path d="<?php echo $shape["path"]; ?>" />
												<?php
											}
										?>
									</clipPath>
								<?php
							}
						}
					}
				}
			}
		?>
		
		<linearGradient id="avatar__gradient--bruise"  gradientTransform="rotate(120)"> 
			<stop offset="0%" stop-color="#000000" stop-opacity="100%" />
			<stop offset="70%" stop-color="#ffffff" stop-opacity="100%" />
		</linearGradient>
		
		<linearGradient id="avatar__gradient--rainbow">
			<stop offset="0%"  stop-color="#f20d0d" />
			<stop offset="20%" stop-color="#eef20d" />
			<stop offset="30%" stop-color="#eef20d" />
			<stop offset="60%" stop-color="#3cdd3c" />
			<stop offset="75%" stop-color="#308ce8" />
			<stop offset="90%" stop-color="#8c30e8" />
		</linearGradient>
		
		<linearGradient id="avatar__gradient--cut" gradientTransform="rotate(45)">
			<stop offset="0%" stop-color="#ffffff" stop-opacity="100%" />
			<stop offset="15%" stop-color="#ffffff" stop-opacity="100%" />
			<stop offset="65%" stop-color="#000000" stop-opacity="100%" />
		</linearGradient>
		
		<mask id="avatar__mask--cut">
			<rect x="300" y="300" width="150" height="200" fill="url(#avatar__gradient--cut)" />
		</mask>
		
		<mask id="avatar__mask--bruise">
			<rect x="150" y="320" width="200" height="200" fill="url(#avatar__gradient--bruise)" />
		</mask>
		
		<pattern id="avatar__image--rainbow" patternUnits="userSpaceOnUse" width="100" height="100">
			<image xlink:href="/avatar/rainbow.png" x="0" y="0" width="100" height="100" />
		</pattern>
	</defs>

	<filter id="avatar__filter--splatter">
		<feTurbulence type="turbulence" baseFrequency="0.05" numOctaves="5" result="turbulence"/>
		<feDisplacementMap in2="turbulence" in="SourceGraphic" scale="-500" xChannelSelector="R" yChannelSelector="G"/>
	</filter>
	<filter id="avatar__filter--smear">
		<feGaussianBlur in="SourceGraphic" stdDeviation="0,13" />
	</filter>
	<filter id="avatar__filter--blur">
		<feGaussianBlur in="SourceGraphic" stdDeviation="2" />
	</filter>
</svg>

<?php
	$avatar_definitions = ob_get_clean();
	
	echo !$suppress_echo ? $avatar_definitions : null;
?>