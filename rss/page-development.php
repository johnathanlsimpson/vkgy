<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:webfeeds="http://webfeeds.org/rss/1.0">
	<channel>
		<title>Development | vkgy (ブイケージ)</title>
		<link>https://vk.gy/development/</link>
		<atom:link href="https://vk.gy/rss/development.xml" rel="self" type="application/rss+xml" />
		<description>Development updates from vkgy.</description>
		<language>en-us</language>
		<category>Music</category>
		<copyright>Copyright 2011~<?= date("Y"); ?> vk.gy</copyright>
		<lastBuildDate><?= date(DATE_RSS, time()); ?></lastBuildDate>
		<managingEditor>johnathan.l.simpson@gmail.com (John Simpson)</managingEditor>
		<webMaster>johnathan.l.simpson@gmail.com (John Simpson)</webMaster>
		<webfeeds:cover image="https://vk.gy/style/header.png" />
		<webfeeds:icon>https://vk.gy/style/logomark-eye.svg</webfeeds:icon>
		<webfeeds:logo>https://vk.gy/style/logomark-eye.svg</webfeeds:logo>
		<webfeeds:accentColor>9b476a</webfeeds:accentColor>
		<webfeeds:analytics id="UA-119760328" engine="GoogleAnalytics" />
		<webfeeds:related layout="card" target="browser" />

		<?php
			if(is_array($entries) && !empty($entries)) {
				foreach($entries as $entry) {
					?>
						<item>
							<title><?= $entry['title']; ?></title>
							<link><?= $entry['url']; ?></link>
							<guid><?= $entry['url']; ?></guid>
							<dc:creator><?= $entry['username']; ?></dc:creator>
							<pubDate><?= date(DATE_RFC822, strtotime($entry['date_occurred'])); ?></pubDate>
							<description><![CDATA[<?php
								if($entry['image']) {
									echo '<img alt="'.$entry['title'].'" class="webfeedsFeaturedVisual " src="'.$entry['image'].'" />';
								}
								$content = $entry['content'];
								$content = strtok($content, "\n");
								$content = $markdown_parser->parse_markdown($content);
								$content = html_entity_decode($content, null, "UTF-8");
								$content = str_replace('data-src', 'src', $content);
								echo $content;
							?>]]></description>
							<content:encoded><![CDATA[<?php
								if($entry['image']) {
									echo '<img alt="'.$entry['title'].'" class="webfeedsFeaturedVisual" src="'.$entry['image'].'" />'."\n";
								}
								$content = $entry['content'];
								$content = $markdown_parser->parse_markdown($content);
								$content = html_entity_decode($content, null, "UTF-8");
								$content = str_replace('data-src', 'src', $content);
								echo $content;
							?>]]></content:encoded>
							<comments><?= $entry['url'].'#comments'; ?></comments>
						</item>
					<?php
				}
			}
		?>
	</channel>
</rss>