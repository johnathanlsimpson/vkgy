<?php
	include_once("../php/include.php");
	
	$access_blog     = new access_blog($pdo);
	$markdown_parser = new parse_markdown($pdo);
	$entries         = $access_blog->access_blog(["page" => "latest", "get" => "basics"]);
	
	if(is_array($entries) && !empty($entries)) {
		foreach($entries as $key => $entry) {
			$sql_image = "SELECT IF(images.id IS NOT NULL, CONCAT('https://vk.gy/images/', images.id, IF(images.friendly IS NOT NULL, CONCAT('-', images.friendly), ''), '.', images.extension), '') AS image FROM blog LEFT JOIN images ON images.id=blog.image_id WHERE blog.id=? AND blog.image_id IS NOT NULL LIMIT 1";
			$stmt_image = $pdo->prepare($sql_image);
			$stmt_image->execute([$entry["id"]]);
			
			$entries[$key]["image"] = $stmt_image->fetchColumn();
			$entries[$key]["title"] = html_entity_decode($entry["title"], null, "UTF-8");
			$entries[$key]["date_occurred"] = date(DATE_RSS, strtotime($entry["date_occurred"]));
			$entries[$key]["url"] = "https://vk.gy/blog/".$entry["friendly"]."/";
		}
	}
	
	ob_start();
		?>
			<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:webfeeds="http://webfeeds.org/rss/1.0">
				<channel>
					<title>vkgy (ブイケージ)</title>
					<link>https://vk.gy</link>
					<atom:link href="https://vk.gy/rss/rss.xml" rel="self" type="application/rss+xml" />
					<description>The latest in visual kei news.</description>
					<language>en-us</language>
					<category>Music</category>
					<copyright>Copyright 2011~<?php echo date("Y"); ?> vk.gy</copyright>
					<lastBuildDate><?php echo date(DATE_RSS, time()); ?></lastBuildDate>
					<managingEditor>johnathan.l.simpson@gmail.com (John Simpson)</managingEditor>
					<webMaster>johnathan.l.simpson@gmail.com</webMaster>
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
										<title><?php echo $entry["title"]; ?></title>
										<link><?php echo $entry["url"]; ?></link>
										<guid><?php echo $entry["url"]; ?></guid>
										<dc:creator><?php echo $entry["username"]; ?></dc:creator>
										<pubDate><?php echo $entry["date_occurred"]; ?></pubDate>
										<content:encoded><![CDATA[<?php
											if($entry["image"]) {
												echo '<img alt="'.$entry["title"].'" class="webfeedsFeaturedVisual" src="'.$entry["image"].'" />'."\n";
											}
											$content = $entry["content"];
											$content = $markdown_parser->parse_markdown($content);
											$content = html_entity_decode($content, null, "UTF-8");
											$content = str_replace('data-src', 'src', $content);
											echo $content;
											
											echo
												'<hr />'.
												'<ul>'.
												'<li><a href="https://vk.gy/" target"_blank">vk.gy</a></li>'.
												'<li>Patreon: <a href="https://patreon.com/vkgy" target"_blank">vkgy</a></li>'.
												'<li>Twitter: <a href="https://twitter.com/vkgy_" target"_blank">@vkgy</a></li>'.
												'<li>Facebook: <a href="https://facebook.com/vkgy.official" target"_blank">vkgy.official</a></li>'.
												'<li>YouTube: <a href="https://youtube.com/c/vkgyofficial" target"_blank">vkgyofficial</a></li>'.
												'<li><a href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.cat_id=UA-03" target="_blank">Buy VK releases at CDJapan</a></li>'.
												'</ul>';
										?>]]></content:encoded>
										<description><![CDATA[<?php
											if($entry["image"]) {
												echo '<img alt="'.$entry["title"].'" class="webfeedsFeaturedVisual " src="'.$entry["image"].'" />';
											}
											$content = $entry["content"];
											$content = strtok($content, "\n");
											$content = $markdown_parser->parse_markdown($content);
											$content = html_entity_decode($content, null, "UTF-8");
											$content = str_replace('data-src', 'src', $content);
											echo $content;
											
											echo
												'<hr />'.
												'<ul>'.
												'<li><a href="https://vk.gy/" target"_blank">vk.gy</a></li>'.
												'<li>Patreon: <a href="https://patreon.com/vkgy" target"_blank">vkgy</a></li>'.
												'<li>Twitter: <a href="https://twitter.com/vkgy_" target"_blank">@vkgy</a></li>'.
												'<li>Facebook: <a href="https://facebook.com/vkgy.official" target"_blank">vkgy.official</a></li>'.
												'<li>YouTube: <a href="https://youtube.com/c/vkgyofficial" target"_blank">vkgyofficial</a></li>'.
												'<li><a href="http://www.cdjapan.co.jp/aff/click.cgi/PytJTGW7Lok/6128/A549875/searches?term.cat_id=UA-03" target="_blank">Buy VK releases at CDJapan</a></li>'.
												'</ul>';
										?>]]></description>
										<comments><?php echo $entry["url"]; ?></comments>
									</item>
								<?php
							}
						}
					?>
				</channel>
			</rss><?php
	$output = ob_get_clean();
	$output = str_replace(["\t"], "", $output);
	$output = '<?xml version="1.0" encoding="utf-8"?>'."\n".$output;
	
	header("content-type: text/xml; charset=utf-8");
	echo $output;
?>