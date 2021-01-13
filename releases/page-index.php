<?php

include_once("../releases/head.php");

$access_release = new access_release($pdo);
$access_user = new access_user($pdo);

script([
	"/scripts/external/script-selectize.js",
	"/scripts/external/script-inputmask.js",
	"/scripts/script-initSelectize.js",
	"/releases/script-page-index.js"
]);

style([
	"/style/external/style-selectize.css",
	"/releases/style-page-index.css"
]);

subnav([
	lang('Release calendar', '新譜一覧', ['secondary_class' => 'any--hidden']) => '/releases/',
	lang('Search', 'サーチ', ['secondary_class' => 'any--hidden']) => '/search/releases/',
]);

$page_title = 'New visual kei releases | ビジュアル系 新譜情報';
$page_description = 'New visual kei releases info and ranking for '.date('F Y').'. ビジュアル系 新譜情報一覧 ランキング '.date('Y年m月');

$page_header = lang('New visual kei releases', 'ビジュアル系 新譜情報', ['container' => 'div']);

$markdown_parser = new parse_markdown($pdo);

?>

<?php if($error): ?>
	<div class="col c1">
		<div>
			<div class="text text--outlined text--error symbol__error"><?= $error; ?></div>
		</div>
	</div>
<?php endif; ?>

<div class="col c4-AAAB">

<div class="col c1">
	<?php
		
		$access_release = new access_release($pdo);
		$upcoming_releases = $access_release->access_release([ 'get' => 'list', 'start_date' => date('Y-m-01'), 'order' => 'releases.date_occurred ASC' ]);
		$upcoming_releases = array_values($upcoming_releases);
		
		foreach($upcoming_releases as $release) {
			$month = substr($release['date_occurred'], 0, 7).'-01';
			$monthly_releases[$month][] = 'https://vk.gy/releases/dummy/'.$release['id'].'/dummy/';
		}
		
		foreach($monthly_releases as $date => $releases) {
			?>
				<h2>
					<?= lang(date('F', strtotime($date)).' '.substr($date, 0, 4).' - new vkei releases', substr($date, 0, 4).'年'.substr($date, 5, 2).'月・ビジュアル系 新作', 'div'); ?>
				</h2>
				
				<div class="releases__month any--margin">
					<?= $markdown_parser->parse_markdown( implode("\n", $releases) ); ?>
				</div>
			<?php
		}
		
	?>
</div>
	
	<!-- CDJ -->
	
	<div>
		<h2>
			<?= lang('Top vkei preorders', 'ビジュアル系 予約ランキング', 'div'); ?>
		</h2>
	<div class="x" style="">
		<script src="https://www.cdjapan.co.jp/aff/data/tp_visual_cd_sen_ure.js"></script>
		
		
		
		<script src="https://www.cdjapan.co.jp/aff/data/tp_visual_cd_sen_ure.js"></script>
		
		<script type="text/javascript">
			//let CdjapanAffiliate = {};
			let blah = document.querySelector('.x');
			
			
			conf = {
				'sid':'6128',
				'aid':'A549875',
			};
				let tmpl = '';
				let allPreorders = CdjapanAffiliate.data.click_page + conf.sid + '/' + conf.aid + '/' + CdjapanAffiliate.data.more_page;
				let updated = CdjapanAffiliate.data.last_upd;
				let preorderLink, image, date, artist, title;
				
				for(let i=0; i < CdjapanAffiliate.data.list.length; i++ ){
					
					preorderLink = CdjapanAffiliate.data.click_page + conf.sid + '/' + conf.aid + '/detailview.html%3FKEY%3D' + CdjapanAffiliate.data.list[i].key;
					image = CdjapanAffiliate.data.list[i].img;
					date = CdjapanAffiliate.data.list[i].rel;
					artist = CdjapanAffiliate.data.list[i].artist;
					title = CdjapanAffiliate.data.list[i].title;
					
					tmpl +=
						'<li class="z ranking__item">'+
						'<a class="y any--flex" href="'+preorderLink+'" target="_blank">'+
						(i < 3 ? '<span class="ranking__number symbol__user"></span>' : '')+
						'<div style="flex:1;">'+
						'<strong>' +artist+'</strong>'+
						'<br />'+
						title+
						'</div>'+
						'<img src="'+image+'" style="margin-left:0.5rem;width:100px;object-fit:cover;" />'+
						'</a>'+
						'</li>';
					
				}
				
				blah.innerHTML = '<ol style="counter-reset:defaultcounter;">' + tmpl + '</ol>';
		</script>
		
		
		
		
	</div>
</div>
	
</div>

<style>
	.z::before {
		display: none;
	}
	.y {
		margin: -0.5rem;
		padding: 0.5rem;
	}
	.y:hover {
		box-shadow: inset 0 0 0 3px currentColor;
	}
</style>

<style>
	.releases__month {
		display:grid;
		grid-gap:2rem;
	}
	@media(min-width:1300px) {
		.releases__month {
			grid-template-columns: 1fr 1fr 1fr;
		}
	}
	@media(min-width:900px) and (max-width:1299.99px) {
		.releases__month {
			grid-template-columns: 1fr 1fr;
		}
	}
	@media(min-width:600px) and (max-width:799.99px) {
		.releases__month {
			grid-template-columns: 1fr 1fr;
		}
	}
	.module--release {
		display: inline-block;
		margin: 0;
		padding: 0;
	}
	.release-card__container {
		flex-wrap: nowrap;
		height: 100%;
		max-height: 400px;
		padding-top: calc(1rem + 150px);
	}
	.release-card__artist-image {
		background: hsl(var(--background--bold));
		background-position: center 30%;
		background-size: cover;
		height: 150px;
		margin: 0;
		width: auto;
		position: absolute;
		left: 0;
		right: 0;
		top: 0;
	}
	.release-card__cover {
		height: 100%;
	}
	.release-card__right {
		overflow: hidden;
	}
	.release-card__right::after {
		background: linear-gradient( hsla(var(--background),0), hsla(var(--background),1) );
		bottom: 0;
		content: "";
		display: block;
		flex: none;
		height: 2rem;
		position: sticky;
		width: 100%;
	}
	
	
	
	
.ranking__item:nth-of-type(1) .ranking__number {
	background-color: hsl(51,100%,50%);
	color: hsl(40,100%,38%);
}
.ranking__item:nth-of-type(2) .ranking__number {
	background-color: hsl(0,0%,75%);
	color: hsl(0,0%,55%);
}
.ranking__item:nth-of-type(3) .ranking__number {
	background-color: hsl(30,61%,70%);
	color: hsl(30,61%,50%);
}
.ranking__number {
	border-radius: 50%;
	display: inline-block;
	flex: none;
	float: left;
	height: 2rem;
	margin-left: -1rem;
	margin-top: -4px;
	margin-right: 5px;
	vertical-align: middle;
	width: 2rem;
}
.ranking__number::before {
	color: inherit;
	font-size: 24px;
	opacity: 1;
	left: 50%;
	position: absolute;
	top: 50%;
	transform: translate(calc(-50% - 1px), calc(-50% - 1px)) rotate(-20deg);
}
.ranking__number::after {
	color: hsl(var(--background--secondary));
	content: counter(defaultcounter);
	font-size: 13px;
	font-style: italic;
	font-weight: bold;
	left: 50%;
	line-height: 0;
	position: absolute;
	top: 50%;
	transform: translate(calc(-50% - 2px), calc(-50% + 2px)) rotate(-20deg);
}
	
</style>