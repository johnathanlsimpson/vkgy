<?php
$s = 'SELECT id, tags_artists FROM blog';
$t = $pdo->prepare($s);
$t->execute();

$e = 'SELECT id FROM artists';
$f = $pdo->prepare($e);
$f->execute();
$g = $f->fetchAll();
foreach($g as $h) {
	$i[] = $h['id'];
}

foreach($t->fetchAll() as $r) {
	$tags = explode(')', str_replace('(', '', $r['tags_artists']));
	
	if(is_array($tags)) {
		foreach($tags as $tag_id) {
			if(is_numeric($tag_id) && in_array($tag_id, $i)) {
				$x .= '(?, ?, ?), ';
				$y[] = $r['id'];
				$y[] = $tag_id;
				$y[] = $_SESSION['userID'];
			}
		}
	}
}

$a = 'INSERT INTO blog_artists (blog_id, artist_id, user_id) VALUES '.substr($x, 0, -2);
$b = $pdo->prepare($a);
if($b->execute($y)) {
	echo 'a';
}
else {
	echo 'b';
	print_r($pdo->errorInfo());
}

echo $a;

//print_r($y);