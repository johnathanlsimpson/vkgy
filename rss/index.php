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
					<title>vkgy - visual kei library (ブイケージ・V系ライブラリ)</title>
					<link>https://vk.gy</link>
					<atom:link href="https://vk.gy/rss/rss.xml" rel="self" type="application/rss+xml" />
					<description>Visual kei library. (ブイケージはビジュアル系のライブラリです。)</description>
					<language>en-us</language>
					<category>Music</category>
					<copyright>Copyright 2011~<?php echo date("Y"); ?> vk.gy</copyright>
					<lastBuildDate><?php echo date(DATE_RSS, time()); ?></lastBuildDate>
					<managingEditor>johnathan.l.simpson@gmail.com (John Simpson)</managingEditor>
					<webMaster>johnathan.l.simpson@gmail.com</webMaster>
					<webfeeds:cover image="https://weloveucp.com/style/header.png" />
					<webfeeds:icon>https://weloveucp.com/style/logo.svg</webfeeds:icon>
					<webfeeds:logo>https://weloveucp.com/style/logo.svg</webfeeds:logo>
					<webfeeds:accentColor>9b476a</webfeeds:accentColor>
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
												echo '<img alt="'.$entry["title"].'" class="webfeedsFeaturedVisual classname" src="'.$entry["image"].'" />'."\n";
											}
											$content = $entry["content"];
											$content = $markdown_parser->parse_markdown($content);
											$content = html_entity_decode($content, null, "UTF-8");
											echo $content;
										?>]]></content:encoded>
										<description><![CDATA[<?php
											if($entry["image"]) {
												echo '<img alt="'.$entry["title"].'" class="webfeedsFeaturedVisual classname" src="'.$entry["image"].'" />';
											}
											$content = $entry["content"];
											$content = strtok($content, "\n");
											$content = $markdown_parser->parse_markdown($content);
											$content = html_entity_decode($content, null, "UTF-8");
											echo $content;
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