<?php if($_SESSION["is_signed_in"]) { ?>

<?php
	include_once('../php/function-render_component.php');
	
	script([
		'/scripts/external/script-autosize.js',
		'/scripts/external/script-inputmask.js',
		'/scripts/external/script-tribute.js',
		'/scripts/script-initDelete.js',
		'/scripts/script-initTribute.js',
		'/blog/script-page-update.js',
	]);
	
	style([
		'/style/external/style-tribute.css',
		'/blog/style-page-update.css'
	]);
	
	// Get artist list
	include_once('../php/function-render_json_list.php');
	render_json_list('artist');
	
	// Set page title
	$page_header = '<div class="entry__title">'.$entry['title'].'</div>'.'<div class="entry__default-title">'.lang('Add article', '記事を追加する', 'div').'</div>';
	
	// Set preview token
	$token = friendly($entry['token'] ?: bin2hex(random_bytes(3)));
	
	// Set flags
	$is_queued = $entry['is_queued'] ? 1 : 0;
	$is_published = is_array($entry) && is_numeric($entry['id']) && !$entry['is_queued'] ? 1 : 0;
	$is_saved = 0;
	$is_scheduled = $entry['date_scheduled'] ? 1 : 0;
	$is_edit = is_array($entry) && is_numeric($entry['id']) ? 1 : 0;
	$is_translation = $entry['is_translation'] ? 1 : 0;
	
	// Set other junks
	$entry_url = '/blog/'.$entry['friendly'].'/';
	$was_published = $is_edit ? 1 : 0;
	
	// Navigation
	if($is_edit) {
		subnav([
			'Edit '.($is_published ? 'article' : 'draft') => '/blog/'.$entry['friendly'].'/edit/',
			($is_published ? 'View article' : 'Preview draft') => '/blog/'.$entry['friendly'].'/',
		], 'section');
		$active_page = '/blog/'.$entry['friendly'].'/edit/';
	}
	else {
		subnav([
			'Add article' => '/blog/add/',
		], 'section');
		$active_page = '/blog/add/';
	}
	
	// Make sure whoever's editing entry is included as a contributor
	if($entry['contributor_ids']) {
		$access_user = $access_user ?: new access_user($pdo);
		$contributor_ids = json_decode($entry['contributor_ids'], true);
		$contributor_ids[ $_SESSION['user_id'] ] = $_SESSION['user_id'];
	}
	else {
		$contributor_ids = [ $_SESSION['user_id'] => $_SESSION['user_id'] ];
	}
	
	// Loop through contributors and get user info
	if(is_array($contributor_ids) && !empty($contributor_ids)) {
		foreach($contributor_ids as $contributor_id) {
			$contributors[ $contributor_id ] = $access_user->access_user([ 'id' => $contributor_id, 'get' => 'name', 'limit' => 1 ]);
		}
	}
	
	// Get SNS overrides
	$entry['sns_overrides'] = $entry['sns_overrides'] ? json_decode($entry['sns_overrides'], true) : [];
?>

<!-- Rest of the fucking owl -->
<form action="/blog/function-update.php" class="row" enctype="multipart/form-data" method="post" name="form__update">
	
	<?php
		if($is_translation) {
			?>
				<div class="col c1">
					<div class="text text--outlined text--notice symbol__error">
						Currently editing <?= ['ja' => 'Japanese', 'fr' => 'French'][$entry['language']]; ?> translation. Most article options must be changed in the <a href="#translations">original English article</a>.
					</div>
				</div>
			<?php
		}
	?>
	
	<div class="col c1">
		
		<!-- Hidden inputs -->
		<input data-get="id" data-get-into="value" name="id" value="<?= $entry['id']; ?>" hidden />
		<input name="blog_id" value="<?= $entry['blog_id']; ?>" hidden />
		<input name="date_occurred" value="<?= $entry['date_occurred'] ?: date('Y-m-d'); ?>" hidden />
		
		<!-- Notices -->
		<div class="col c1">
			<div class="entry__error text text--outlined text--error symbol__error"><?= $error; ?></div>
		</div>
		
		<!-- Top third (title/summary) -->
		<input class="obscure__input" id="obscure-content" type="checkbox" hidden <?= strlen($entry['content'] > 500) ? 'checked' : null; ?> />
		<div class="col c2 obscure__container obscure--height obscure--faint" style="min-height:16rem;">
			
			<!-- Left (title/friendly/entry) -->
			<div>
				<ul class="text" style="padding-bottom: 0.5rem;">
					
					<!-- Toggle friendly -->
					<input class="input__choice friendly__toggle" id="friendly-toggle" type="checkbox" hidden />
					
					<!-- Title and URL -->
					<li class="input__row">
						<div class="input__group any--flex-grow">
							
							<label class="input__label"><?= lang('Title', 'タイトル', 'hidden'); ?></label>
							<input class="any--flex-grow" name="name" placeholder="title" value="<?= $entry["title"]; ?>" />
							
							<div class="friendly__preview any--weaken-color" style="width:100%;">
								vk.gy/blog/<span class="friendly__slug" style="background:none;"><?= $entry['friendly']; ?></span>&nbsp;
								<a class="symbol__edit friendly__edit-link <?= $is_translation ? 'any--hidden' : null; ?> " href="javascript:;"><label class="friendly__edit-button" for="friendly-toggle">edit</label></a>
							</div>
							
						</div>
					</li>
					
					<!-- Friendly -->
					<li class="input__row friendly__edit">
						<div class="input__group any--flex-grow">
							
							<label class="input__label"><?= lang('Friendly', 'リンク', 'hidden'); ?></label>
							<input class="any--flex-grow" name="friendly" placeholder="friendly" value="<?= $entry["friendly"]; ?>" />
							
						</div>
					</li>
					
					<!-- Main text -->
					<li class="input__row">
						<div class="input__group any--flex-grow">
							
							<label class="input__label"><?= lang('Entry content', '本文', 'hidden'); ?></label>
							<textarea class="input__textarea any--flex-grow any--tributable autosize" data-is-previewed="true" name="content" placeholder="blog entry here..."><?php echo $entry["content"]; ?></textarea>
							
						</div>
					</li>
					
				</ul>
			</div>
			
			<!-- Right (preview entry) -->
			<div>
				<div class="text text--outlined">
					
					<!-- Preview image -->
					<div class="update__image any--weaken" style="<?= $entry['image'] ? 'background-image:url(/images/'.$entry['image']['id'].'.large.jpg);' : null; ?>"></div>
					
					<!-- Preview date/author -->
					<div class="preview__stats">
						<span>
							<h5 class="preview__date" style="display:inline-block;">
								<span class="preview__datetime"><?= $entry['date_occurred'] ?: date('Y-m-d'); ?></span>
								<a class="symbol__edit a--inherit <?= $is_translation ? 'any--hidden' : null; ?> " href="#scheduling">edit</a>
							</h5>
						</span>
						<span>
							<h5 class="preview__user" style="display:inline-block;margin-right:1ch;">
								<a class="user a--inherit" data-icon="<?= $entry['user']['icon'] ?: $_SESSION['icon']; ?>" href="<?= $entry['user']['url'] ?: '/users/'.$_SESSION['username'].'/'; ?>" target="_blank"><?= $entry['user']['username'] ?: $_SESSION['username']; ?></a>
								<a class="symbol__edit a--inherit <?= $is_translation ? 'any--hidden' : null; ?> " href="#contributors">edit</a>
							</h5>
						</span>
					</div>
					
					<!-- Preview status/title -->
					<span class="preview__status" style="float:right;"></span>
					<h2 class="preview__title"><?= $entry['title'] ?: 'Untitled'; ?></h2>
					
					<!-- Preview main content -->
					<div class="preview__summary"></div>
					<div class="preview__note symbol__help any--weaken">
						The first paragraph should summarize the post―keep it concise. The artist mentioned here is treated as the featured artist.
					</div>
					<div class="update__preview"></div>
					
				</div>
			</div>
			
			<label class="input__button obscure__button" for="obscure-comments">Show more</label>
			
		</div>
		
		<!-- Images and tags -->
		<div class="col c2">
			<div>
				<?php
					include('../images/function-render_image_section.php');
					render_image_section($entry['images'], [
						'item_type' => 'blog',
						'item_id' => $is_translation ? $entry['blog_id'] : $entry['id'],
						'item_name' => 'blog entry',
						'description' => '',
						'default_id' => $entry['image_id'],
						'hide_blog' => '1',
						'hide_labels' => '1',
						'is_queued' => 1,
					]);
				?>
			</div>
			
			<div>
				<div class="text text--outlined <?= $is_translation ? 'any--hidden' : null; ?> " style="padding-top:0.5rem;">
					<?php
						if(is_array($tags)) {
							foreach($tags as $tag_key => $tag) {
								if(is_array($entry['tags']) && !empty($entry['tags'])) {
									foreach($entry['tags'] as $key => $current_tag) {
										if($current_tag['id'] === $tag['id']) {
											$tag['checked'] = true;
											unset($entry['tags'][$key]);
											break;
										}
									}
								}
								
								?>
									<label class="input__checkbox">
										<input class="input__choice" data-friendly="<?= $tag['friendly']; ?>" name="tags[]" value="<?= $tag['id']; ?>" type="checkbox" <?= $tag['checked'] ? 'checked' : null; ?> />
										<span class="symbol__tag"><?= $tag['tag']; ?></span>
									</label>
								<?php
							}
						}
					?>
				</div>
				
			</div>
		</div>
		
		<!-- Advanced options -->
		<div class="col c1">
			
			<h2>
				<?= lang('Other options', 'その他のオプション', 'div'); ?>
			</h2>
			
			<!-- Author and contributors -->
			<details class="<?= $is_translation ? 'any--hidden' : null; ?>" id="contributors">
				<summary class="h3 documentation__link">✍ Author and contributors</summary>
				<ul class="text text--outlined">
					<?php render_json_list('user'); ?>
					
					<!-- Main author -->
					<li class="input__row" x-data="{open:false}" style="clip-path:none;">
						<div class="input__group any--flex-grow">
							
							<label class="input__label">Main author</label>
							
							<a x-show="!open" class="user" data-icon="<?= $entry['user']['icon'] ?: $_SESSION['user_icon']; ?>" href="<?= $entry['user']['url'] ?: '/users/'.$_SESSION['username'].'/'; ?>"><?= $entry['user']['username'] ?: $_SESSION['username']; ?></a>
							<a x-on:click="open=true" x-show="!open" class="symbol__edit" style="margin-left:1ch;">edit</a>
							
							<div class="any--flex-grow" x-show="open">
								<select class="input any--flex-grow" name="user_id" placeholder="main author" data-source="users">
									<option></option>
									<option value="<?= is_numeric($entry['user']['id']) ? $entry['user']['id'] : $_SESSION['user_id']; ?>" selected><?= $entry['user']['username'] ?: $_SESSION['username']; ?></option>
								</select>
							</div>
							
							<div class="input__note symbol__help any--weaken">This user will be credited in social media posts. Only one main author may be chosen.</div>
							<div class="input__note symbol__help any--weaken">By default, this is the user who created the article.</div>
							
						</div>
					</li>
					
					<!-- Contributors -->
					<li class="input__row" x-data="{open:false}" style="clip-path:none;">
						<div class="input__group any--flex-grow">
							
							<label class="input__label">Contributors</label>
							
							<?php
								if(is_array($contributors) && !empty($contributors)) {
									foreach($contributors as $contributor) {
										echo '<a x-show="!open" class="user" data-icon="'.$contributor['icon'].'" data-is-vip="'.$contributor['is_vip'].'" href="'.$contributor['url'].'" style="margin-right:1ch;">'.$contributor['username'].'</a>';
									}
								}
								else {
									echo '<a x-show="!open" class="user" data-icon="'.$contributors[$_SESSION['user_id']]['icon'].'" data-is-vip="'.$contributors[$_SESSION['user_id']]['is_vip'].'" href="'.$contributors[$_SESSION['user_id']]['url'].'" style="margin-right:1ch;">'.$contributors[$_SESSION['user_id']]['username'].'</a>';
								}
							?>
							<a x-on:click="open=true" x-show="!open" class="symbol__edit">edit</a>
							
							<div x-show="open" class="any--flex-grow">
								<select class="input any--flex-grow" name="contributor_ids[]" placeholder="contributors" data-source="users" multiple>
									<option></option>
									<?php
										if(is_array($contributors) && !empty($contributors)) {
											foreach($contributors as $contributor) {
												echo '<option value="'.$contributor['id'].'" selected>'.$contributor['username'].'</option>';
											}
										}
									?>
								</select>
							</div>
							
							<div class="input__note symbol__help any--weaken">These users will be listed as contributors (except the user who is the main author). Multiple users may be chosen.</div>
							<div class="input__note symbol__help any--weaken">By default, this is any user who edited the article.</div>
							
						</div>
					</li>
					
				</ul>
			</details>
			
			<!-- Featured artist -->
			<details class="<?= $is_translation ? 'any--hidden' : null; ?>" id="artist">
				<summary class="h3 documentation__link">👨‍🎤 Featured artist</summary>
				<div class="text text--outlined" x-data="{open:false}">
					
					<?php render_json_list('artist'); ?>
					<div class="input__row" x-data="{open:false}">
						<div class="input__group any--flex-grow">
							
							<label class="input__label">Featured artist</label>
							
							<?php
								echo
									'<span x-show="!open" class="artist artist__link"><span class="artist__romaji"></span><span class="artist__name">'.
									(is_numeric($entry['artist_id']) ? $artist_list[$entry['artist_id']][2] : 'N/A').
									'</span></span>';
							?>
							<a x-on:click="open=true" x-show="!open" class="symbol__edit" style="line-height:2rem;margin-left:1ch;">edit</a>
							
							<div x-show="open" class="any--flex-grow">
								<select class="input" name="artist_id" placeholder="featured artist" data-source="artists">
									<option></option>
									<option value="<?= is_numeric($entry['artist_id']) ? $entry['artist_id'] : null; ?>" selected></option>
								</select>
							</div>
							
							<div class="input__note symbol__help any--weaken">The featured artist's Twitter accounts will be mentioned in the tweet. Information about the artist will also be added to the article.</div>
							<div class="input__note symbol__help any--weaken">By default, the featured artist is automatically set to the first artist mentioned in the summary.</div>
							
						</div>
					</div>
					
				</div>
			</details>
			
			<!-- Public preview link -->
			<details id="preview-link">
				<summary class="h3 documentation__link">🔗 Public preview link</summary>
				<div class="text text--outlined">
					<div class="input__row">
						<div class="input__group">
							
							<label class="input__label">Preview link</label>
							
							<a class="preview__link" href="<?= 'https://vk.gy/blog/'.$entry['friendly'].'/&preview='.$token; ?>"><?= 'https://vk.gy/blog/'.$entry['friendly'].'/&preview='.$token; ?></a>
							
							<input name="token" value="<?= $token; ?>" placeholder="token" hidden />
							
							<div class="symbol__help any--weaken input__note">Anyone with this link will be able to view the article, even if they're not signed in. It does not affect who can edit the article.</div>
							<div class="symbol__help any--weaken input__note <?= $is_translation ? 'any--hidden' : null; ?>">If you need to revoke access, <a class="preview__generate-link">generate a new preview link</a>.</div>
							
						</div>
					</div>
				</div>
			</details>
			
			<!-- Regenerate card image -->
			<details id="card-image">
				<summary class="h3 documentation__link">📸 Regenerate SNS image</summary>
				<div class="text text--outlined">
					<div class="input__row">
						<div class="input__group">
							
							<label class="input__label">Regenerate SNS image</label>
							
							<a class="card__regen symbol__random" href="#">click to regenerate</a>
							
							<div class="symbol__help any--weaken input__note">This will update the text and image that appears when the link to this entry is pasted into Twitter or Facebook.</div>
							
						</div>
					</div>
				</div>
			</details>
			
			<!-- Schedule article -->
			<details class="<?= $is_translation ? 'any--hidden' : null; ?>" <?= $entry['date_scheduled'] ? 'open' : null; ?> id="scheduling">
				<summary class="h3 documentation__link">📆 Scheduling</summary>
				<div class="text text--outlined">
					<div class="input__row">
						<span class="input__group">
							<label class="input__label">Publish date/time</label>
							<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" placeholder="yyyy-mm-dd" maxlength="10" name="date_scheduled" size="12" value="<?= substr($entry['date_scheduled'], 0, 10); ?>" />
							<input class="input--secondary" data-inputmask="'alias': 'hh:mm'" placeholder="hh:mm" maxlength="5" name="time_scheduled" size="7" value="<?= substr($entry['date_scheduled'], 11, 5); ?>" />
							&nbsp;<strong style="line-height:2rem;" class="any--weaken-color">JST</strong>
						</span>
						<div class="input__note symbol__help any--weaken">
							Scheduled articles will be saved as drafts, and automatically published on the date/time (JST) specified above.
						</div>
					</div>
				</div>
			</details>
			
			<!-- Sources -->
			<details class="<?= $is_translation ? 'any--hidden' : null; ?>" id="sources">
				<summary class="h3 documentation__link">🙏 Sources</summary>
				<div class="text text--outlined">
					
					<div class="input__row">
						<div class="input__group any--flex-grow">
							
							<label class="input__label">Sources</label>
							
							<textarea class="input__textarea any--flex-grow autosize" name="sources" placeholder="[Mr. Source](https://MrSource.com/)&#13;https://twitter.com/source2"><?= $entry['sources']; ?></textarea>
							
							<div class="input__note symbol__help any--weaken">Sources where the information came from (a tweet, the forum post, the band's website) or the name of the person who wrote the original article.</div>
							<div class="input__note symbol__help any--weaken">Each source should be on its own line. Only plain text, urls, or Markdown links are allowed.</div>
							<div class="input__note symbol__error any--weaken">Note that sources are no longer automatically mentioned in tweets. Use the <a href="#tweet">edit tweet</a> functionality for that.</div>
							
						</div>
					</div>
					
				</div>
			</details>
			
			<!-- Supplemental -->
			<details class="<?= $is_translation ? 'any--hidden' : null; ?>" id="supplemental">
				<summary class="h3 documentation__link">📚 Supplemental info</summary>
				<div class="text text--outlined">
					<div class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Supplemental</label>
							<textarea class="input__textarea any--flex-grow autosize" name="supplemental" placeholder="supplemental information"><?= $entry['supplemental']; ?></textarea>
							<div class="input__note symbol__help any--weaken">This is for any additional that supplements the main post, but is less important (live schedules, links, flyers, etc). Markdown is allowed.</div>
						</div>
					</div>
				</div>
			</details>
			
			<!-- Tweet -->
			<details class="<?= $is_translation ? 'any--hidden' : null; ?>" id="tweet">
				<summary class="h3 documentation__link">📱 Tweet</summary>
				<div class="col c2" x-data="{body:<?= $entry['sns_overrides']['body'] ? 'true' : 'false'; ?>,twitterMentions:<?= $entry['sns_overrides']['twitter_mentions'] ? 'true' : 'false'; ?>,twitterAuthors:<?= $entry['sns_overrides']['twitter_authors'] ? 'true' : 'false'; ?>,authors:<?= $entry['sns_overrides']['authors'] ? 'true' : 'false'; ?>}">
					
					<!-- Preview tweet -->
					<div>
						<div class="text text--outlined sns__container">
							
							<div class="sns__component tweet__heading">
								<div class="sns__text" data-get="heading"><?= $entry['sns_overrides']['heading']; ?></div>
								<label class="sns__label input__label" data-heading="heading"></label>
							</div>
							
							<div class="sns__component tweet__body">
								<div class="sns__text" data-get="body"><?= $entry['sns_overrides']['body']; ?></div>
								<label class="sns__label input__label" data-heading="body"><a x-on:click="body=true" x-show="!body" class="sns__edit a--inherit symbol__edit"></a></label>
							</div>
							
							<div class="sns__component tweet__translations">
									<div class="sns__text" data-get="translations"><?php
										if(is_array($entry['translations']) && !empty($entry['translations'])) {
											echo '[EN] https://vk.gy/blog/'.$entry['friendly'].'/';
											foreach($entry['translations'] as $translation) {
												echo "\n\n".'['.[ 'ja' => '日本語版', 'fr' => 'français ' ][ $translation['language'] ].'] https://vk.gy/blog/'.$translation['friendly'].'/';
											}
										}
									?></div>
									<label class="sns__label input__label">Translations</label>
							</div>
							
							<div class="sns__component tweet__mentions">
								<div class="sns__text" data-get="twitter_mentions"><?= $entry['sns_overrides']['twitter_mentions']; ?></div>
								<label class="sns__label input__label" data-heading="mentions"><a x-on:click="twitterMentions=true" x-show="!twitterMentions" class="sns__edit a--inherit symbol__edit"></a></label>
							</div>
							
							<div class="sns__component tweet__authors">
								<div class="sns__text" data-get="twitter_authors"><?= $entry['sns_overrides']['twitter_authors']; ?></div>
								<label class="sns__label input__label" data-heading="authors"><a x-on:click="twitterAuthors=true" x-show="!twitterAuthors" class="sns__edit a--inherit symbol__edit"></a></label>
							</div>
							
							<!--<div class="sns__component tweet__normal-authors">
								<div class="sns__text" data-get="authors"><?= $entry['sns_overrides']['authors']; ?></div>
								<label class="sns__label input__label" data-heading="authors"><a x-on:click="authors=true" x-show="!authors" class="sns__edit a--inherit symbol__edit"></a></label>
							</div>-->
							
							<div class="sns__component sns__length symbol__error any--weaken" data-length="0"></div>
							
							<div class="sns__component sns__image h5" <?= $entry['image'] ? 'style="background-image:url('.str_replace('.', '.small.', $entry['image']['url']).');"' : null; ?> ></div>
							
						</div>
					</div>
					
					<!-- Override tweet -->
					<div>
						
						<?php
							ob_start();
							?>
								<template id="template-sns">
									<label class="sns__img input__radio" style="margin-top:1rem;">
										<input class="input__choice" name="override_image_id" type="radio" value="{image_id}" {is_checked} />
										<span class="symbol__unchecked"></span>
										<img class="sns__thumb" src="{image_thumb}" />
									</label>
								</template>
							<?php
							$sns_template =  ob_get_clean();
							echo $sns_template;
							$sns_template = preg_replace('/'.'<\/?template.*?>'.'/', '', $sns_template);
						?>
						
						<!-- SNS overrides -->
						<ul class="text ul--compact">
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Heading</label>
									<div class="input__note any--weaken symbol__help">Heading is automatically set based on <a href="#tags">tags</a> (news vs interview).</div>
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Body <a x-on:click="body=true" x-show="!body" class="symbol__edit">edit</a></label>
									<textarea x-show="body" class="input__textarea any--flex-grow" name="override_body" placeholder="body text"><?= $entry['sns_overrides']['body']; ?></textarea>
									<div class="input__note any--weaken symbol__help">By default, uses the title of the entry.</div>
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Mentions <a x-on:click="twitterMentions=true" x-show="!twitterMentions" class="symbol__edit">edit</a></label>
									<input x-show="twitterMentions" class="any--flex-grow" name="override_twitter_mentions" placeholder="@band_1 @band_2" value="<?= $entry['sns_overrides']['twitter_mentions']; ?>" />
									<div class="input__note any--weaken symbol__help">Auto-populated with Twitter accounts connected to main artist.</div>
									<div class="input__note any--weaken symbol__help">Edit the artist's page to add Twitter accounts, and make sure they're marked SNS.</div>
									<div class="input__note any--weaken symbol__help">Include @ and username, and separate multiple accounts with a space.</div>
								</div>
							</li>
							
							<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Authors <a x-on:click="twitterAuthors=true" x-show="!twitterAuthors" class="symbol__edit">edit</a></label>
									<input x-show="twitterAuthors" class="any--flex-grow" name="override_twitter_authors" placeholder="@vkgy_user" value="<?= $entry['sns_overrides']['twitter_authors']; ?>" />
									<div class="input__note any--weaken symbol__help">By default, Twitter accounts of any vkgy member who contributed to entry.</div>
								</div>
							</li>
							
							<!--<li class="input__row">
								<div class="input__group any--flex-grow">
									<label class="input__label">Authors <a x-on:click="authors=true" x-show="!authors" class="symbol__edit">edit</a></label>
									<input x-show="authors" class="any--flex-grow" name="override_twitter_authors" placeholder="@vkgy_user" value="<?= $entry['sns_overrides']['authors']; ?>" />
									<div class="input__note any--weaken symbol__help">By default, Twitter accounts of any vkgy member who contributed to entry.</div>
								</div>
							</li>-->
							
							<!--<li class="input__row">
								<div class="input__group">
									<label class="input__label">Scheduling</label>
									<div class="input__note symbol__help any--weaken">
										By default, tweet will sent 15 minutes after entry is published (unless entry is scheduled, in which case tweet will be sent at the scheduled time).
									</div>
								</div>
							</li>-->
							
							<!-- Choose SNS image -->
							<li class="input__row">
								<div class="input__group sns__img-container">
									<label class="input__label">Image</label>
									<?php
										if(is_array($entry['images'])) {
											foreach($entry['images'] as $image) {
												echo render_component($sns_template, [
													'image_id' => $image['id'],
													'image_thumb' => str_replace('.', '.thumbnail.', $image['url']),
													'is_checked' => $entry['sns_overrides'] && $entry['sns_overrides']['image_id'] == $image['id'] ? 'checked' : null,
												]);
											}
										}
									?>
								</div>
								<div class="input__note any--weaken symbol__help">By default, set to the “default image” for the entry.</div>
							</li>

						</ul>
					</div>
				</div>
			</details>
			
			<!-- Translations -->
			<details id="translations">
				
				<!-- Translations -->
				<summary class="h3 documentation__link">🔠 Translations</summary>
				<ul class="text text--outlined translation__container">
					<?php
						// Possible language options
						$language_options = [ 'en' => 'English', 'ja' => '日本語', 'fr' => 'français' ];
						
						// Make sure translations array has at least English entry (i.e. making brand new article)
						if(!is_array($entry['translations']) || empty($entry['translations'])) {
							$entry['translations'] = [ [ 'language' => 'en' ] ];
							$entry['language'] = 'en';
						}
						
						// Display edit links for each translation
						if(is_array($entry['translations']) && !empty($entry['translations'])) {
							foreach($entry['translations'] as $translation) {
								?>
									<li>
										<?= $language_options[ $translation['language'] ]; ?>
										<?= $entry['language'] == $translation['language'] ? '<span class="any__note">currently editing</span>' : '<a class="symbol__edit" href="/blog/'.$translation['friendly'].'/edit/" target="_blank">edit</a>'; ?>
									</li>
								<?php
								
								// Remove language from options list so we don't generate duplicates later
								unset($language_options[ $translation['language'] ]);
								
							}
						}
						
						// Allow translation generation if some languages still untranslated
						if( is_array($language_options) && !empty($language_options) ) {
							?>
								<li class="translation__generation any--hidden">
									<div class="input__row">
										<div class="input__group">
											<select class="input translation__language" placeholder="select language ">
												<option></option>
												<?php
													foreach($language_options as $language_key => $language_name) {
														echo '<option value="'.$language_key.'">'.$language_name.'</option>';
													}
												?>
											</select>
										</div>
										<div class="input__group">
											<button class="translation__generate" type="button">
												Generate
											</button>
										</div>
									</div>
									<div class="translation__result text text--outlined text--compact text--notice" data-role="result"></div>
								</li>
								<li>
									<a class="translation__add symbol__plus"> add translation</a>
								</li>
							<?php
						}
					?>
				</ul>
			</details>
			
		</div>
		
		<!-- Submit area -->
		<div class="text text--docked save__container" data-is-edit="<?= $is_edit; ?>" data-is-queued="<?= $is_queued; ?>" data-is-published="<?= $is_published; ?>" data-is-first-autosave="0" data-is-saved="<?= $is_saved; ?>" data-is-scheduled="<?= $is_scheduled; ?>" data-is-translation="<?= $is_translation; ?>" >

			<div class="input__row" data-role="submit-container">
				<div class="save__group input__group any--flex-grow">
					
					<!-- Save area -->
					<button class="save__button" name="submit" type="submit">
						<m1>Save draft</m1>
						<m2>Save as draft</m2>
						<m3>Publish changes</m3>
						<m4>Publish</m4>
					</button>
					<span class="save__status" data-role="status"></span>
					<span class="save__state any--weaken">
						<m1>You have unsaved changes</m1>
						<m2>Saving</m2>
						<m3>Saved as draft</m3>
						<m4>Article published</m4>
						<m5>Couldn't save</m5>
					</span>

					<!-- Draft area -->
					<span  class="save__scheduled any--weaken">Will be published <span class="any__note"><?= $entry['date_scheduled']; ?></span></span>
					<input class="save__choice input__choice" id="is_queued" name="is_queued" type="checkbox" <?= $is_queued ? 'checked' : null; ?> />
					<label class="save__draft input__checkbox symbol__unchecked" for="is_queued">Save as draft?</label>
					
					<!-- Delete -->
					<?php $delete_button_class = $_SESSION['can_delete_data'] || $_SESSION['user_id'] === $entry['user_id'] ? null : 'any--hidden'; ?>
					<label class="input__radio symbol__trash symbol--standalone <?= $delete_button_class; ?>" data-get="id" data-get-into="data-id" data-id="<?= $entry["id"]; ?>" data-is-translation="<?= $entry['is_translation'] ? 1 : 0 ;?>" name="delete"></label>
					
					<a class="save__link symbol__arrow-right-circled" href="<?= $entry_url; ?>" target="_blank">
						<m1>Preview draft</m1>
						<m2>View entry</m2>
					</a>
					
					<!-- Notices -->
					<div class="save__notice any--weaken-size text text--compact symbol__help">
						<m1>Autosave turned off. This article will go live as soon as you press &ldquo;publish&rdquo; above.</m1>
						<m2>This article is still live until you press &ldquo;save draft.&rdquo;</m2>
						<m3>You are editing a published (live) entry. Changes must be saved manually.</m3>
						<m4>Automatically saved article as a draft.</m4>
					</div>
				</div>
				
			</div>

			<div class="any--flex any--hidden" data-role="edit-container">
				<a class="a--padded a--outlined any--flex-grow any--align-center" data-get="url" data-get-into="href" href="">View entry</a>
				<a class="a--padded" data-get="edit_url" data-get-into="href" data-role="edit">Edit</a>
			</div>

			<div class="text text--outlined text--notice update__result" data-role="result"></div>
			
		</div>

	</div>
</form>
	
<?php } ?>