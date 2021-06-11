<?php

// ========================================================
// Setup
// ========================================================

include_once('../php/include.php');
include_once('../php/function-paginate.php');

script([
	'/scripts/external/script-selectize.js',
	'/scripts/external/script-alpine.js',
	'/scripts/script-initSelectize.js',
	
	'/artists/script-partial-list.js',
]);

style([
	'/style/external/style-selectize.css',
	'/style/style-selectize.css',
	'/style/style-pagination.css',
	
	'/artists/style-partial-list.css',
]);

$access_artist = new access_artist($pdo);

// ========================================================
// Pagination
// ========================================================

$page = is_numeric( $_GET['page'] ) ? $_GET['page'] : 1;
$limit = 30;
$limit_string = ( $limit * ( $page - 1 ) ).','.$limit;

// ========================================================
// Setup args
// ========================================================

// Get scene tags for use later
$sql_scenes = 'SELECT tags_artists.id, tags_artists.friendly, tags_artists.name, tags_artists.romaji FROM tags_artists WHERE tags_artists.type=? ORDER BY tags_artists.friendly ASC';
$stmt_scenes = $pdo->prepare($sql_scenes);
$stmt_scenes->execute([ 0 ]);
$scenes = $stmt_scenes->fetchAll();

// Save list of preferred scenes that will show by default
$preferred_scenes = [ 'soft-visual', 'kote-kei', 'osare-kei', 'tanbi-kei', 'wafuu' ];

// Format scenes for the default_args array
foreach( $scenes as $scene ) {
	$allowed_scenes[ $scene['friendly'] ] = $scene['friendly'];
}

// Allowed args
$default_args = [
	
	'active'    => [ 'active' => 1, 'disbanded' => 2, 'all' => null ],
	
	'order'     => [ 'name' => 'artists.friendly', 'formed' => [ 'date_formed', 'date_occurred' ], 'added' => 'artists.id' ],
	
	'direction' => [ 'up' => 'ASC', 'down' => 'DESC' ],
	
	'vkei_only' => [ true => true ],
	
	'scene'     => array_merge( [ 'any' => null ], $allowed_scenes ),
	
];

// Loop through default args, try to get from url, if not allowed, set to first allowed value
foreach( $default_args as $arg_key => $arg_values ) {
	
	$value = friendly( $_GET[$arg_key] );
	
	$allowed_values = array_keys( $arg_values );
	
	$args[ $arg_key ] = in_array( $value, $allowed_values ) ? $value : reset($allowed_values);
	
}

// Now format url-friendly args for actual query
foreach( $args as $arg_key => $value_key ) {
	
	$formatted_value = $default_args[ $arg_key ][ $value_key ];
	
	if( $formatted_value !== null ) {
		
		$formatted_args[ $arg_key ] = $formatted_value;
		
	}
	
}

// Format order some more
$formatted_args['order'] = is_array($formatted_args['order']) ? implode(' '.$formatted_args['direction'].', ', $formatted_args['order']).' '.$formatted_args['direction'] : $formatted_args['order'].' '.$formatted_args['direction'];

// Format tags
if( $formatted_args['scene'] ) {
	$formatted_args['tags'] = [ $formatted_args['scene'] ];
}

// For the artist count, remove the ordering since it can mess up query
$count_args = $formatted_args;
unset( $count_args['order'] );

// Set flag for whether or not any special filters are turned on
$args_are_active = $args['order'] != 'name' || $args['active'] != 'active' || $args['scene'] != 'any';

// ========================================================
// Get Data
// ========================================================

$artists = $access_artist->access_artist( array_merge( $formatted_args, [ 'get' => 'artist_list', 'limit' => $limit_string ] ) );

$num_artists = $access_artist->access_artist( array_merge( $count_args, [ 'get' => 'count' ] ) );

$_GET['officialURL'] = 'artists/';

$pagination = paginate([
	'limit' => $limit,
	'num_items' => $num_artists,
	'current_page' => $page,
]);

echo '<h2>'.lang('List of active visual kei bands', '活動中のビジュアル系バンド一覧', 'div').'</h2>';

echo '<div class="pagination any--small-margin">'.render_pagination($pagination).'</div>';

echo '<ul class="text artist__list">';
	
?>

<details class="history__filters" x-data="{ showScenes:0 }">

	<summary class="filters__control any--flex">
		
		<div class="any--weaken filters__note">
			<div class="h5">
				Filters
			</div>
			
			<?php if( $args_are_active ): ?>
				
				<span class="any__note">order by <?= $args['order']; ?></span>
				<span class="any__note"><?= $args['direction'] == 'up' ? '↑' : '↓'; ?></span>
				<span class="any__note"><?= $args['active']; ?></span>
				<?= $args['scene'] != 'any' ? '<span class="any__note">'.$args['scene'].'</span>' : null; ?>
				
			<?php else: ?>
				
				<span class="any__note">none</span>
				
			<?php endif; ?>
			
			<a class="symbol__edit filters__link filters__edit" href="#">edit</a>
			<a class="symbol__search filters__link" href="/search/artists/">advanced search</a>
			
			<?php if( $args_are_active || $args['page'] > 1 ): ?>
				<a class="symbol__delete filters__link" href="/artists/">reset</a>
			<?php endif; ?>
			
		</div>

		<span class="filters__open input__button symbol__filter">filter</span>
		<span class="filters__close input__button close">close</span>

	</summary>

	<form class="input__row" enctype="multipart/form-data" name="filter_artists">
		
		<!-- Order and helpers -->
		<div class="input__group">
			
			<label class="input__label">Sort</label>
			
			<input name="order" value="<?= $args['order']; ?>" hidden />
			<input name="direction" value="<?= $args['direction']; ?>" hidden />
			
			<?php foreach( [ 'name' => 'up', /*'formed' => 'down',*/ 'added' => 'down' ] as $order_key => $order_direction ): ?>
				<button class="input__button" data-active="<?= $args['order'] == $order_key ? 1 : 0; ?>" data-direction="<?= $args['order'] == $order_key ? $args['direction'] : null; ?>" data-default-direction="<?= $order_direction; ?>" name="sort[]" type="button" value="<?= $order_key; ?>">
					<?= ucfirst($order_key); ?> <span class="filter__arrow"><?= $args['order'] == $order_key ? ( $args['direction'] == 'up' ? '↑' : '↓' ) : null; ?></span>
				</button>
			<?php endforeach; ?>
			
		</div>
		
		<!-- Active -->
		<div class="input__group">
			
			<label class="input__label">Activity</label>
			
			<label class="input__radio">
				<input class="input__choice" name="active" type="radio" value="active" <?= $args['active'] == 'active' ? 'checked' : null; ?> />
				<span class="symbol__unchecked">active</span>
			</label>
			
			<label class="input__radio">
				<input class="input__choice" name="active" type="radio" value="disbanded" <?= $args['active'] == 'disbanded' ? 'checked' : null; ?> />
				<span class="symbol__unchecked">disbanded</span>
			</label>
			
			<label class="input__radio">
				<input class="input__choice" name="active" type="radio" value="all" <?= $args['active'] == 'all' ? 'checked' : null; ?> />
				<span class="symbol__unchecked">all</span>
			</label>
			
		</div>
		
		<!-- Scenes -->
		<div class="input__group">
			
			<label class="input__label">Scene</label>
			
			<label class="input__radio">
				<input class="input__choice" name="scene" type="radio" value="any" <?= $args['scene'] == 'any' ? 'checked' : null; ?> />
				<span class="symbol__unchecked">any</span>
			</label>
			
			<?php
				foreach($scenes as $scene) {
					if( in_array( $scene['friendly'], $preferred_scenes ) || ( $args['scene'] == $scene['friendly'] ) ) {
						?>
							<label class="input__radio">
								<input class="input__choice" name="scene" type="radio" value="<?= $scene['friendly']; ?>" <?= $args['scene'] == $scene['friendly'] ? 'checked' : null; ?> />
								<span class="symbol__unchecked"><?= str_replace(' kei', '', ( $scene['romaji'] ? lang($scene['romaji'], $scene['name'], 'hidden') : $scene['name'] ) ); ?></span>
							</label>
						<?php
					}
					else {
						$other_scenes[] = $scene;
					}
				}
					
				foreach($other_scenes as $scene) {
					?>
						<label class="input__radio" x-show="showScenes">
							<input class="input__choice" name="scene" type="radio" value="<?= $scene['friendly']; ?>" <?= $args['scene'] == $scene['friendly'] ? 'checked' : null; ?> />
							<span class="symbol__unchecked"><?= str_replace(' kei', '', ( $scene['romaji'] ? lang($scene['romaji'], $scene['name'], 'hidden') : $scene['name'] ) ); ?></span>
						</label>
					<?php
				}
			?>
			
			<a class="symbol__plus filters__link" x-on:click.prevent="showScenes=1" x-show="!showScenes">other</a>
			
		</div>
		
		<!-- Reset filters -->
		<div class="input__group">
			
			<label class="input__label">Reset</label>
			<button class="input__button symbol__delete" name="reset" type="button">Clear filters</button>
			
		</div>

	</form>

</details>

<?php

foreach( $artists as $artist ) {
	
	$artist_image_url = '/artists/'.$artist['friendly'].'/main.medium.jpg';
	$artist_image_exists = image_exists($artist_image_url, $pdo, true) ?: null;

	?>
			<li class="list__item any--flex card__container">
				
				<a class="list__link card__link" href="<?= '/artists/'.$artist['friendly'].'/'; ?>"></a>
				
				<div class="list__image <?= $artist_image_exists ? null : 'list--no-image'; ?> lazy" data-src="<?= $artist_image_exists ? $artist_image_url : null; ?>"></div>
				
				<span class="list__status"><span class="<?= 'status--'.( $artist['active'] == 1 ? 'active' : ( $artist['active'] == 2 ? 'disbanded' : 'other' ) ); ?>"></span></span>
				
				<div class="list__content card--subject">
					
					<div class="h3">
						<?= $artist['romaji'] ? lang($artist['romaji'], $artist['name'], 'div') : $artist['name']; ?>
					</div>
					
					<?php
						// Trim description
						$description_limit = 15;
						$description = $artist['description'];
						$description = $markdown_parser->parse_markdown($description);
						$description = strip_tags($description);
						$description = explode(' ', $description);
						$description = count($description) > 15 ? implode(' ', array_slice($description, 0, 15)).'...' : implode(' ', $description);
					?>
					
					<div class="list__description any--weaken"><?= $description; ?></div>
					
					<div class="list__year any--weaken"><?= $artist['date_occurred'] ?: '?'; ?></div>
					
					<div class="list__tags any--weaken"><?php
						
						if( is_array($artist['tags']) && !empty($artist['tags']) ) {
							
							echo '<span class="symbol__tag"></span>';
							
							$tags = [];
							foreach($artist['tags'] as $tag) {
								if($tag['type'] == 0) {
									$tags[] = '<a class="a--inherit card--clickable" href="/artists/&scene='.$tag['friendly'].'&active=all" style="border:0.25rem solid;border-color:transparent;">'.( $tag['romaji'] ? lang($tag['romaji'], $tag['name'], 'hidden') : $tag['name'] ).'</a>';
								}
								else {
									$tags[] = ( $tag['romaji'] ? lang($tag['romaji'], $tag['name'], 'hidden') : $tag['name'] );
								}
							}
							
							echo implode(', ', $tags);
							
						}
						
					?></div>
					
				</div>
				
			</li>
	<?php
}

echo '</ul>';

echo '<div class="pagination any--small-margin">'.render_pagination($pagination).'</div>';