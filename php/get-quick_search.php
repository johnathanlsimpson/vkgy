<?php

include_once('../php/include.php');
include_once('../php/function-quick_search.php');

$search_term = $_POST['q'];
$sanitized_search_term = sanitize( $search_term );
$search_url = '/search/?q='.$sanitized_search_term;

$search_results = quick_search( $sanitized_search_term );

if( strlen($search_term) ) {
	
	// If got some artists
	if( is_array($search_results) && !empty($search_results) ) {
		
		ob_start();
		
		?>
			<ul class="quick-search__list">
				<?php
					foreach($search_results as $artist) {
						
						$thumbnail_class = $artist['thumbnail_url'] ? null : 'quick-search__thumbnail--empty';
						$thumbnail_style = $artist['thumbnail_url'] ? 'background-image:url('.$artist['thumbnail_url'].');' : null;
						
						?>
							<li class="quick-search__artist card__container">
								
								<a class="card__link" href="<?= '/artists/'.$artist['friendly'].'/'; ?>"></a>
								
								<span class="quick-search__thumbnail <?= $thumbnail_class; ?> any--weaken" style="<?= $thumbnail_style; ?>"></span>
								
								<span class="quick-search__name card--subject">
									<?= ( $artist['romaji'] ? lang($artist['romaji'], $artist['name'], 'div') : $artist['name'] ); ?>
								</span>
								
							</li>
						<?php
						
					}
				?>
			</ul>
		<?php
		
		$output['result'] .= ob_get_clean();
		
	}
	
	// If results empty
	else {
		
		$output['result'] .= '
			<div class="any--weaken-color">
				No vkei artists found.
				<a class="symbol__arrow-right-circled" href="'.$search_url.'">search everything</a>
			</div>
		';
		
	}

	// Add controls
	$output['result'] .= '
		<div class="quick-search__controls any--flex any--weaken">
			<a class="symbol__search" href="'.$search_url.'">everything</a>
			<a href="/search/artists/?name='.$sanitized_search_term.'#result">artists</a>
			<a href="/search/releases/?release_name='.$sanitized_search_term.'#result">releases</a>
			<a href="/search/musicians/?name='.$sanitized_search_term.'#result">musicians</a>
			<a class="quick-search__close" href="#">close</a>
		</div>
	';

}

$output['status'] = $output['status'] ?: 'success';
echo json_encode($output);