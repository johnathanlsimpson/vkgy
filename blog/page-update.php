<?php if($_SESSION["is_signed_in"]) { ?>

<?php
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
	if($entry) {
		$page_header = '<div class="entry__title">'.lang('Edit: '.$entry['title'], '[編集] '.$entry['title'], 'div').'</div>';
	}
	else {
		$page_header = '<div class="entry__title">'.lang('Add article', '記事を追加する', 'div').'</div>';
	}
	
	// Set preview token
	$token = $entry['token'] ?: bin2hex(random_bytes(3));
	
	// Set states
	$is_queued = $entry['is_queued'] ? 1 : 0;
	$is_published = is_array($entry) && is_numeric($entry['id']) && !$entry['is_queued'] ? 1 : 0;
	$is_saved = 0;
	$is_scheduled = $entry['date_scheduled'] ? 1 : 0;
	$is_edit = is_array($entry) && is_numeric($entry['id']) ? 1 : 0;
	$entry_url = '/blog/'.$entry['friendly'].'/';
	$was_published = $is_edit ? 1 : 0;
?>

<!-- Rest of the fucking owl -->
<form action="/blog/function-update.php" class="row" enctype="multipart/form-data" method="post" name="form__update">
	<div class="col c1">
		
		<!-- Hidden inputs -->
		<input data-get="id" data-get-into="value" name="id" value="<?= $entry['id']; ?>" hidden />
		<input                                     name="token" value="<?= $token; ?>" hidden />
		<input                                     name="date_occurred" value="<?= $entry['date_occurred'] ?: date('Y-m-d'); ?>" hidden />

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
								<a class="symbol__edit friendly__edit-link" href="javascript:;"><label class="friendly__edit-button" for="friendly-toggle">edit</label></a>
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
						<h5 class="preview__date"><?= $entry['date_occurred'] ?: date('Y-m-d'); ?></h5>
						<h5 class="preview__user"><a class="user a--inherit" data-icon="<?= $entry['user']['icon'] ?: $_SESSION['icon']; ?>" href="<?= $entry['user']['url'] ?: '/users/'.$_SESSION['username'].'/'; ?>" target="_blank"><?= $entry['user']['username'] ?: $_SESSION['username']; ?></a></h5>
					</div>
					
					<!-- Preview status/title -->
					<span class="preview__status" style="float:right;"></span>
					<h2 class="preview__title"><?= $entry['title'] ?: 'Untitled'; ?></h2>

					<!-- Preview main content -->
					<div class="update__preview"></div>

				</div>
			</div>

			<label class="input__button obscure__button" for="obscure-comments">Show more</label>

		</div>
		
		<!-- Random shit -->
		<div class="col c1">
			
			<!-- Change author -->
			<div class="text">
				<?php render_json_list('user'); ?>
				<select class="input" name="user_id" placeholder="main author" data-source="users">
					<option></option>
					<option value="<?= is_numeric($entry['user']['id']) ? $entry['user']['id'] : $_SESSION['user_id']; ?>" selected><?= $entry['user']['username'] ?: $_SESSION['username']; ?></option>
				</select>
			</div>
			
			<!-- Choose SNS image -->
			<?php
				include('../php/function-render_component.php');
				ob_start();
				?>
					<template id="template-sns">
						<label class="sns__label input__radio">
							<input class="input__choice" name="sns_image_id" type="radio" value="{image_id}" {is_checked} />
							<span class="symbol__unchecked"></span>
							<img class="sns__thumb" src="{image_thumb}" />
						</label>
					</template>
				<?php
				$sns_template =  ob_get_clean();
				echo $sns_template;
				$sns_template = preg_replace('/'.'<\/?template.*?>'.'/', '', $sns_template);
			?>
			<div class="text">
				<div class="sns__container">
					<?php
						if(is_array($entry['images'])) {
							foreach($entry['images'] as $image) {
								echo render_component($sns_template, [
									'image_id' => $image['id'],
									'image_thumb' => str_replace('.', '.thumbnail.', $image['url']),
									'is_checked' => $entry['sns_image_id'] === $image['id'] ? 'checked' : null,
								]);
							}
						}
					?>
				</div>
			</div>
			
			<!-- Change tweet -->
			<?php
				if(is_numeric($entry['id'])) {
					$sql_sns = 'SELECT date_occurred, is_completed, content FROM queued_social WHERE item_type=? AND item_id=? LIMIT 1';
					$stmt_sns = $pdo->prepare($sql_sns);
					$stmt_sns->execute([ 'blog_post', $entry['id'] ]);
					$rslt_sns = $stmt_sns->fetch();
				}
			?>
			
			<h3>
				<?= lang('Twitter', 'ツイーター', 'div'); ?>
			</h3>
			
			<div class="col c4-ABBB">
				<div>
					
				<div class="text text--compact text--outlined text--notice sns__tweet" style="margin:0;">
					<?= trim(str_replace("\n", '<br />', $rslt_sns['content'])); ?>
				</div>
				<div class="any--weaken symbol__help">
					The above tweet will be auto-posted 15 minutes after the article is published. <a class="symbol__edit" href="">Edit tweet</a>
				</div>
				<div class="any--weaken symbol__help">
					The above tweet was published on <?= $rslt_sns['date_occurred']; ?>. <button>
					Delete?
					</button>
				</div>
				</div>
			<div>
				
			<div class="text">
				
				
				<div style="">
					
					<li class="input__row">
						<div class="input__group">
							<label class="input__label">Heading</label>
							<div style="" class="any--weaken symbol__help">Heading (news vs interview) is automatically set based on entry's tags.</div>
						</div>
					</li>
					
					<li class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Content</label>
							<textarea class="input__textarea any--flex-grow"></textarea>
							<div style="width:100%;" class="any--weaken symbol__help">By default, content is set to match title of entry.</div>
						</div>
					</li>
					
					<li class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Twitter mentions</label>
							<input class=" any--flex-grow" />
							<div style="width:100%;" class="any--weaken symbol__help">Auto-populated with Twitter accounts of main artist.</div>
						</div>
						<div class="input__group any--flex-grow">
							<label class="input__label">Author</label>
							<input class=" any--flex-grow" />
							<div style="width:100%;" class="any--weaken symbol__help">Auto-set to Twitter account of main author.</div>
						</div>
					</li>
					
				</div>
				
				
				
				
			</div>
				</div>
			</div>
			
			<!-- Change main artist -->
			<div class="text">
				<div class="input__row">
					<div class="input__group">
						<label class="input__label">Main artist</label>
						<a class="artist artist__link" data-id="" href=""><span class="artist__romaji"></span><span class="artist__name"></span></a>
						<span class="any--weaken-color artist--none">no artist set</span>
														<a class="" href="javascript:;"><label class="artist__edit symbol__edit" for="artist-toggle">edit</label></a>

					</div>
					<div class="input__group">
						<label class="input__label">Main artist</label>
						<?php render_json_list('artist'); ?>
						<select class="input" name="artist_id" placeholder="main artist" data-source="artists">
							<option></option>
							<option value="<?= is_numeric($entry['artist_id']) ? $entry['artist_id'] : null; ?>" selected></option>
						</select>
					</div>
				</div>
				<div class="any--weaken symbol__help">
					The &ldquo;main artist&rdquo; will be automatically tagged when this article is posted to Twitter. By default, it's set to the first artist mentioned in the first paragraph of the article.
				</div>
			</div>
			<style>
				.artist__link,
				.artist--none,
				.artist__edit {
					line-height: 2rem;
				}
				.artist__edit {
					cursor: pointer;
					margin-left: 1ch;
				}
				.artist--none,
				.artist__link[data-id=""] {
					display: none;
				}
				.artist__link[data-id=""] + .artist--none {
					display: inline-block;
				}
			</style>
			
			


		<!-- Preview entry content -->
		<div class="obscure__container obscure--height obscure--faint content__row any--hidden">
			<div class="col c3-AAB content__wrapper" style="">
				<div class="">

					<!-- Sources and supplements -->
					<div class="input__row any--margin">
						<div class="input__group any--flex-grow any--hidden">
							<label class="input__label"><?= lang('Sources', 'ソース', 'hidden'); ?></label>
							<!--textarea class="input__textarea any--flex-grow autosize" data-is-previewed="true" name="content2" placeholder="https://source.com/&#10;@TwitterUser"></textarea>-->
						</div>
						<div class="input__group" style="align-self:flex-start;">
							<label class="input__label">Other options</label>
							<label class="input__button">Add sources</label>&nbsp;
							<label class="input__button">Add supplements</label>&nbsp;
							<label class="input__button">Change authors</label>&nbsp;
							<label class="input__button">Change artist</label>
						</div>
						<div class="input__group any--hidden" style="width:100%;">
							<span class="symbol__help any--weaken">Sources will appear at the end of the article. URLs, Twitter handles, and plain text work best.</span>
						</div>
					</div>

					<!-- Supplements -->
					<li class="input__row any--hidden">
						<div class="input__group any--flex-grow">
							<label class="input__label">Supplemental</label>
							<textarea class="input__textarea any--flex-grow autosize" name="content3" placeholder="https://source.com/&#10;@TwitterUser"><?php echo $entry["content"]; ?></textarea>
						</div>
						<div class="input__group" style="width:100%;">
							<span class="symbol__help any--weaken">This is for supplemental material such as links, live schedules, or long text that doesn't belong in the article proper. This information is truncated by default.</span>
						</div>
					</li>

					<style>
						.friendly__toggle:checked ~ .friendly__show {
							display: none;
						}
						.friendly__toggle:not(:checked) ~ .friendly__edit {
							display: none;
						}
						.friendly__slug:empty::before {
							content: "…";
						}
						.friendly__slug:empty + .friendly__edit-link {
							display: none;
						}
						.friendly__edit-button {
							cursor: pointer;
						}
						.friendly__preview {
							margin-top: 0.5rem;
						}
						[name="name"]:placeholder-shown + .friendly__preview {
							display: none;
						}

						.preview__stats {
							display: flex;
							justify-content: space-between;
							overflow: hidden;
							margin-bottom: 0.5rem;
							max-width: 100%;
						}
						.preview__username {
							max-width: 100%;
							overflow: hidden;
							text-overflow: ellipsis;
							white-space: nowrap;
						}
						.preview__title {
							padding-bottom: 0;
						}
						.preview__summary:not(:empty) {
							margin-top: 1rem;
						}
					</style>

					<style>
						/* To fix issue with negative margin making border weird--will have to see how affects performance */
						li.input__row {
							clip-path: polygon(0.5rem 0, 100% 0, 100% 100%, 0 100%);
						}
					</style>

					<script>
					</script>

				</div>
				<div class="">
					<h5>
						<?= lang('Preview', 'プレビュー', 'hidden'); ?>
						<span class="preview__content-status"></span>
					</h5>
				</div>
			</div>
			<label class="input__button obscure__button" for="obscure-comments">Show more</label>


					<style>
						/*.content__row {
							margin: 0 0 3rem 0;
						}
						.content__wrapper {
							background:hsl(var(--background--alt));
							padding:1rem !important;
						}*/
						.obscure__input:checked + .content__row {
							max-height: 16rem !important;
						}
					</style>
		</div>

		<!-- Other stuff -->
		<div class="col c1">




				<input data-get="friendly" data-get-into="value" name="friendly" type="hidden" value="<?php echo $entry["friendly"]; ?>" />

				<h2 class="update__header any--hidden">
					<?php echo $entry ? "Edit" : "Add"; ?> entry
				</h2>
				<div class="text any--hidden">
					<div class="input__row">
						<div class="input__group any--flex-grow">
							<!--<label class="input__label">Title</label>
							<input class="any--flex-grow" name="name" placeholder="title" value="<?php echo $entry["title"]; ?>" />-->
						</div>
					</div>

					<div class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Entry content</label>
							<textarea class="input__textarea any--flex-grow any--tributable autosize" name="content4" placeholder="blog entry here..."><?php echo $entry["content"]; ?></textarea>
						</div>
					</div>
				</div>

				<div class="text text--outlined text--info console any--hidden"></div>

				<h3 class="any--hidden">
					Advanced
				</h3>
				<input class="any--hidden obscure__input" id="obscure-advanced" type="checkbox" checked />
				<ul class="text obscure__container obscure--height any--hidden">
					<!-- Friendly -->
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">URL-friendly title</label>
								<input class="any--flex-grow" name="friendly" placeholder="friendly" value="<?php echo $entry["friendly"]; ?>" />
							</div>
						</div>
					</li>

					<!-- Credit -->
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Sources</label>
								<textarea class="input__textarea any--flex-grow autosize" name="sources" placeholder="http://example.com/&#10;@band_official"><?php echo $entry['sources']; ?></textarea>
							</div>
						</div>

						<div class="symbol__help any--weaken-color" style="margin-top: 1rem;">
							This will appear at the bottom of the post. If a Twitter handle is included, when the post is automatically tweeted, that account will be included as an author (in addition to the Twitter handle of whoever wrote this post).
						</div>
					</li>

					<!-- Supplemental -->
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Supplemental info</label>
								<textarea class="input__textarea any--flex-grow autosize" name="supplemental" placeholder="* Blog: http://example.com/&#10;* OHP: http://example.com/"><?php echo $entry['supplemental']; ?></textarea>
							</div>
						</div>

						<div class="symbol__help any--weaken-color" style="margin-top: 1rem;">
							This will appear at the bottom of the post, and is hidden by default. It's for long sections of secondary information, such as live schedules, links, advertising, etc.
						</div>
					</li>

					<!-- Japanese text -->
					<li>
						<div class="input__row">
							<div class="input__group any--flex-grow">
								<label class="input__label">Japanese translation</label>
								<textarea class="input__textarea any--flex-grow autosize" name="content_ja" placeholder="記事"><?php echo $entry['content_ja']; ?></textarea>
							</div>
						</div>
					</li>

					<label class="input__button obscure__button" for="obscure-advanced">Show options</label>
				</ul>

		</div>
		<div class="col c2">

			<div>

				<h3>
					<?= lang('Images', '写真', 'div'); ?>
				</h3>
				<?php
					include('../images/function-render_image_section.php');
					render_image_section($entry['images'], [
						'item_type' => 'blog',
						'item_id' => $entry['id'],
						'item_name' => 'blog entry',
						'description' => '',
						'default_id' => $entry['image_id'],
						'hide_blog' => '1',
						'hide_labels' => '1',
						'hide_musicians' => '1',
						'hide_releases' => '1',
						'is_queued' => 1,
					]);
				?>
			</div>

			<div>
				<h3>
					<?= lang('Tags', 'タッグ', 'div'); ?>
				</h3>
				<div class="text text--outlined" style="padding-top:0.5rem;">
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

				<h3>
					<?= lang('Advanced options', 'オプション', 'div'); ?>
				</h3>
				<div class="text text--outlined">

					<li class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">SNS image</label>
							<div style="white-space:nowrap;width:100%;margin-top:0.5rem;">

								<label class="input__radio" style="height:auto;">
									<input class="input__choice" type="radio" />
									<span class="symbol__unchecked"><img src="https://vk.gy/images/39984.thumbnail.png" style="margin:-0.5rem;height:50px;width:50px;object-fit:cover;" /></span>
								</label>
								<label class="input__radio" style="height:auto;">
									<input class="input__choice" type="radio" />
									<span class="symbol__unchecked"><img src="https://vk.gy/images/39984.thumbnail.png" style="margin:-0.5rem;height:50px;width:50px;object-fit:cover;" /></span>
								</label>
								<label class="input__radio" style="height:auto;">
									<input class="input__choice" type="radio" />
									<span class="symbol__unchecked"><img src="https://vk.gy/images/39984.thumbnail.png" style="margin:-0.5rem;height:50px;width:50px;object-fit:cover;" /></span>
								</label>
								<label class="input__radio" style="height:auto;">
									<input class="input__choice" type="radio" />
									<span class="symbol__unchecked"><img src="https://vk.gy/images/39984.thumbnail.png" style="margin:-0.5rem;height:50px;width:50px;object-fit:cover;" /></span>
								</label>
								<label class="input__radio" style="height:auto;">
									<input class="input__choice" type="radio" />
									<span class="symbol__unchecked"><img src="https://vk.gy/images/39984.thumbnail.png" style="margin:-0.5rem;height:50px;width:50px;object-fit:cover;" /></span>
								</label>
								<label class="input__radio" style="height:auto;">
									<input class="input__choice" type="radio" />
									<span class="symbol__unchecked"><img src="https://vk.gy/images/39984.thumbnail.png" style="margin:-0.5rem;height:50px;width:50px;object-fit:cover;" /></span>
								</label>
								<label class="input__radio" style="height:auto;">
									<input class="input__choice" type="radio" />
									<span class="symbol__unchecked"><img src="https://vk.gy/images/39984.thumbnail.png" style="margin:-0.5rem;height:50px;width:50px;object-fit:cover;" /></span>
								</label>

							</div>
						</div>
						<div class="input__group" style="width:100%;">
							<span class="symbol__help any--weaken">Will be used when article is shared to Facebook/Twitter. If none chosen, article's default image will be used instead.</span>
						</div>
					</li>

					<li class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Tweet text</label>
							<textarea class="input__textarea autosize any--flex-grow">💬 Interview・インタービュー

	Sacrifice

	[Eng] https://vk.gy/blog/blah/

	[日本語版] https://vk.gy/blog/blah-jp/

	🔗 @band_official, @band_member
	✍ @writer</textarea>
						</div>
						<div class="input__group" style="width:100%;">
							<span class="symbol__help any--weaken">Auto-generated based on title, summary, and tags. Mentioned accounts are auto-populated based on main artist/main author of article.</span>
						</div>
					</li>

					<li class="input__row">
						<div class="input__group any--flex-grow">
							<label class="input__label">Facebook text</label>
							<textarea class="input__textarea autosize any--flex-grow">💬 Interview・インタービュー

	Sacrifice

	[Eng] https://vk.gy/blog/blah/

	[日本語版] https://vk.gy/blog/blah-jp/</textarea>
						</div>
						<div class="input__group" style="width:100%;">
							<span class="symbol__help any--weaken">Auto-generated based on title, summary, and tags. Will use article's default image, unless SNS image is specified.</span>
						</div>
					</li>


				</div>
			</div>
		</div>
		<div class="col c1">

				<h3>
					Scheduling <?php echo $entry['date_scheduled']; ?>
				</h3>
				<ul class="text text--outlined any--weaken-color">
					<li class="input__row">
						<span class="input__group">
							<label class="input__label">Post date/time</label>
							<input class="input" data-inputmask="'alias': 'yyyy-mm-dd'" placeholder="yyyy-mm-dd" maxlength="10" name="date_scheduled" size="12" value="<?php echo substr($entry['date_scheduled'], 0, 10); ?>" />
							<input class="input--secondary" data-inputmask="'alias': 'hh:mm'" placeholder="hh:mm" maxlength="5" name="time_scheduled" size="7" value="<?php echo substr($entry['date_scheduled'], 11, 5); ?>" />
							&nbsp;JST
						</span>
					</li>

					<li class="symbol__help">
						Scheuled entries will be saved as drafts, until the date/time that they go live.
					</li>

					<li class="symbol__help">
						Date/time is in <strong>24 hour JST</strong>. Use <a href="https://savvytime.com/converter/jst" target="_blank">this converter</a> if you need help.
					</li>

					<li class="symbol__error">
						The entry will automatically go live at the specified date/time. Please be sure the entry is correct before then.
					</li>
				</ul>
		<?php
			/*if(is_array($queued_entries) && !empty($queued_entries)) {
				?>
					<div class="col c1">
						<div>
							<h2>
								<?php echo lang('Queued/scheduled entries', '出す予定', ['container' => 'div']); ?>
							</h2>
							<ul class="text ul--compact">
								<?php
									foreach($queued_entries as $entry) {
										?>
											<li>
												<a href="<?php echo '/blog/'.$entry['friendly'].'/edit/'; ?>"><?php echo $entry['title']; ?></a>
											</li>
										<?php
									}
								?>
							</ul>
						</div>
					</div>
				<?php
			}*/
		?>
		</div>
	
		<div class="text text--docked save__container" data-is-edit="<?= $is_edit; ?>" data-is-queued="<?= $is_queued; ?>" data-is-published="<?= $is_published; ?>" data-is-first-autosave="0" data-is-saved="<?= $is_saved; ?>" data-is-scheduled="<?= $is_scheduled; ?>">




					<div class="input__row" data-role="submit-container">
						<div class="save__group input__group any--flex-grow">

							<!-- Display controls -->
							<?php /* 
							<input name="is_published" value="<?= $entry['is_published'] ? 1 : 0; ?>" hidden />
							<input name="is_saved" value="<?= $entry['is_saved'] ? 1 : 0; ?>" hidden />
							<input name="is_scheduled" value="<?= $entry['date_scheduled'] ? 1 : 0; ?>" hidden /> */ ?>

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
							
							<style>
								/* Alignment */
								.save__group {
									align-items: center;
								}
								.save__button, .save__draft {
									margin-top: 0;
								}
								.save__status[class*="symbol"] {
									margin-right: -4px;
								}
								.save__state:not(:empty) {
									margin-left: 0.5rem;
								}
								.save__draft, .save__scheduled {
									margin-left: auto;
								}
								
								/* By default, hide message states */
								.save__draft, .save__scheduled, .save__link, .save__notice,
								.save__button > *, .save__link > *, .save__state > *, .save__notice > * {
									display: none;
								}
								
								/* When entry is live, make it extra clear */
								[data-is-published="1"] {
									border-color: hsl(var(--accent));
									border-bottom-color: transparent;
								}
								[data-is-published="1"] .save__notice {
									border-color: currentColor;
									color: hsl(var(--accent));
								}
								
								/* Style success messages */
								.symbol__success + .save__state {
									color: hsl(var(--attention--secondary));
								}
								
								/* Save button text */
								[data-is-queued="1"][data-is-published="0"]                .save__button m1,
								[data-is-queued="1"][data-is-published="1"]                .save__button m2,
								[data-is-queued="0"][data-is-published="1"]                .save__button m3,
								[data-is-queued="0"][data-is-published="0"]                .save__button m4,
								
								/* Draft checkbox and scheduled notice */
								[data-is-scheduled="1"]                                    .save__scheduled,
								[data-is-scheduled="0"]                                    .save__draft,
								
								/* View link text */
								[data-is-published="1"]                                    .save__link,
								[data-is-saved="1"]                                        .save__link,
								[data-is-edit="1"]                                         .save__link,
								[data-is-queued="1"]                                       .save__link   m1,
								[data-is-queued="0"]                                       .save__link   m2,
								
								/* State of save */
								[data-is-saved="0"] .save__status:not([class*="symbol"]) + .save__state  m1,
								.symbol__loading +                                         .save__state  m2,
								[data-is-queued="1"] .symbol__success +                    .save__state  m3,
								[data-is-published="1"] .symbol__success +                 .save__state  m4,
								.symbol__error +                                           .save__state  m5,
								
								/* Alerts */
								[data-is-queued="0"][data-is-published="0"]                .save__notice,
								[data-is-queued="0"][data-is-published="0"]                .save__notice m1,
								[data-is-queued="1"][data-is-published="1"]                .save__notice,
								[data-is-queued="1"][data-is-published="1"]                .save__notice m2,
								[data-is-queued="0"][data-is-published="1"]                .save__notice,
								[data-is-queued="0"][data-is-published="1"]                .save__notice m3,
								[data-is-first-autosave="1"]                                   .save__notice,
								[data-is-first-autosave="1"]                                   .save__notice m4 {
									display: initial;
								}



								<?php //[name="is_queued"]:checked           ~ .save__button :nth-of-type(1),                                     /* Saving as draft */
								//[name="is_queued"]:not(:checked)     ~ [name="is_published"][value="0"] ~ .save__button :nth-of-type(3),  /* Saving as published by haven't saved yet */
								//[name="is_queued"]:not(:checked)     ~ [name="is_published"][value="1"] ~ .save__button :nth-of-type(2),  /* Saving as published after having published already */
								//[name="is_queued"]:checked           ~ .save__link :nth-of-type(1),                                       /* View draft */
								//[name="is_queued"]:not(:checked)     ~ .save__link :nth-of-type(2),                                       /* View published entry */
								//[name="is_published"][value="0"]     ~ [name="is_saved"][value="0"]     ~ .save__link,                    /* View link but not yet saved and not yet published */
								//[name="is_published"][value="1"]     ~ .save__status.symbol__success    ~ .save__state :nth-of-type(5),   /* State when saved and published */
								//[name="is_published"][value="0"]     ~ .save__status.symbol__success    ~ .save__state :nth-of-type(2),   /* State when done draft */
								//.save__status:not([class*="symbol"]) ~ .save__state :nth-of-type(4),                                      /* State when not yet saved, or when in publish mode */
								//.save__status.symbol__loading        ~ .save__state :nth-of-type(1),                                      /* State when saving */
								//.save__status.symbol__error          ~ .save__state :nth-of-type(3),                                      /* State when something went wrong */
								//[name="is_scheduled"][value="1"]     ~ .save__notice :nth-of-type(1),                                     /* Notice when scheduled */
								//[name="is_queued"]:not(:checked)     ~ [name="is_published"][value="0"] ~ [name="is_scheduled"][value="0"] ~ .save__notice :nth-of-type(2), /* Notice when publishing mode but not yet published */
								//[name="is_queued"]:not(:checked)     ~ [name="is_published"][value="1"] ~ .save__notice :nth-of-type(3) { /* Notice when editing published entry */
							//		display: initial;
								//}
							//	/* Notice when editing draft that's not scheduled */
								//[name="is_queued"]:checked ~ [name="is_scheduled"][value="0"] ~ .save__notice,
								//[name="is_queued"]:checked ~ [name="is_scheduled"][value="0"] ~ .save__link,
								//[name="is_queued"]:not(:checked) ~ [name="is_published"][value="0"] ~ [name="is_saved"][value="0"] ~ .save__link { display: none; } */ ?>

								/* Checkbox styling */
								.save__choice:checked ~ .save__draft {
									color: inherit;
								}
								.save__choice:checked ~ .save__draft::before {
									-moz-clip-path: url(#symbol__checkbox--checked);
									-webkit-clip-path: url(#symbol__checkbox--checked);
									clip-path: url(#symbol__checkbox--checked);
									color: inherit;
									opacity: 1;
								}

								/* Special notices */
								.save__notice {
									border-color: hsl(var(--text--secondary));
									margin: 1rem 0 0 0;
									width: 100%;
								}
								.save__notice:empty {
									display: none;
								}
								.save__notice.symbol__help {
								}
							</style>

							<?php /*<input class="input__choice" name="blah" id="blah1" type="radio" checked />
							<input class="input__choice" name="blah" id="blah2" type="radio" />


							<!--<button name="submit" type="submit"><?= 'Save draft'; ?></button>

							<span class="save__status" data-role="status"></span>
							<span class="any--weaken" style="margin-left:1ch;line-height:2rem;">Not saved</span>

							&nbsp;

							<a class="symbol__next any--weaken-size" style="line-height:2rem;">Preview draft</a>-->


							<button name="submit" type="submit"><?= 'Save &amp; Publish'; ?></button>

							<span class="save__status" data-role="status"></span>
							<span class="any--weaken" style="margin-left:1ch;line-height:2rem;">You have unsaved changes</span>

							&nbsp;

							<!--<span class="any--weaken"><a class="symbol__arrow-right-circled" style="line-height:2rem;">View entry</a></span>-->


							&nbsp;

							<input class="input__choice" type="checkbox" />
							<label class="input__checkbox symbol__unchecked" style="margin-left:auto;">Save as draft?</label>
							<a class="a--padded symbol__arrow-right-circled" href="" style="line-height:2rem;padding-top:0;padding-bottom:0;">View post</a> 

							<style>
								.input__choice:checked + .input__checkbox.symbol__unchecked::before {
									-moz-clip-path: url(#symbol__checkbox--checked);
									-webkit-clip-path: url(#symbol__checkbox--checked);
									clip-path: url(#symbol__checkbox--checked);
								}
							</style>

							<!--<label class="input__radio symbol__unchecked" for="blah1" style="margin-left:auto;">Draft</label>
							<label class="input__radio symbol__unchecked" for="blah2">Publish</label>-->
							<div class="text--error symbol__error any--weaken-size text text--outlined text--compact" style="margin: 1rem 0 0 0;color:hsl(var(--accent));width: 100%;">You are editing a published (live) entry. Changes must be saved manually.</div>

							<style>
								#blah1:checked 
							</style>


							<!--<input class="input__choice" id="is_queued" name="is_queued" type="checkbox" value="1" <?php echo $entry['is_queued'] ? 'checked' : null; ?> />
							<label class="input__radio symbol__unchecked" for="is_queued">Save as draft?</label>-->*/ ?>
						</div>

						<div class="input__group any--hidden">
							<?php $delete_button_class = $_SESSION['can_delete_data'] || $_SESSION['user_id'] === $entry['user_id'] ? null : 'any--hidden'; ?>
							<label class="input__radio symbol__trash symbol--standalone <?= $delete_button_class; ?>" data-get="id" data-get-into="data-id" data-id="<?= $entry["id"]; ?>" name="delete"></label>
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



	
<style>
	.drafts__container {
		display: grid;
		grid-gap: 1rem;
		grid-template-columns: repeat(3, minmax(0, 33%));
	}
	.drafts__container li {
		border: none;
		padding: 0;
	}
	.drafts__container li:nth-of-type(n + 4) {
		display: none;
	}
</style>
				
<style>
	.artist__nav {
		flex-wrap: wrap;
		justify-content: space-between;
		margin-top: -1rem;
		padding: 1rem 0;
		position: -webkit-sticky;
		position: sticky;
		top: 3rem;
		z-index: 3;
	}
	.artist__nav .li {
		display: block;
	}
	@media(max-width:799.9px) {
		.artist__nav {
			background-image: linear-gradient(hsl(var(--background--secondary)), hsl(var(--background--secondary)));
			background-position: 0 -1rem;
			background-repeat: no-repeat;
			padding: 0.5rem 0 1rem 0;
			text-align: center;
			grid-column: 1 / -1;
		}
		.artist__nav::after {
			bottom: 0;
			box-shadow: inset 0 1.5rem 1rem -1rem hsl(var(--background--secondary));
			content: "";
			display: block;
			height: 1rem;
			left: 0;
			position: absolute;
			right: 0;
		}
		.artist__nav .li {
			border: none;
			margin: 0;
			padding: 0;
		}
		.artist__nav [href*='former'], .artist__nav [href*='staff'], .artist__nav [href*='schedule'] {
			display: none;
		}
	}
	@media(min-width:800px) {
		.artist__nav.artist__nav {
			flex-direction: column;
			margin-right: var(--gutter);
			width: auto;
		}
	}
</style>
	
	<script>
		
		
	
	</script>
<?php } ?>