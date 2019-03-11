<?php if($_SESSION["admin"]) { ?>

<?php
include_once('../php/include.php');
include_once('../php/external/class-kana.php');

class grassthread_scraper {
	private $document_version;
	private $document_encoding;
	public  $document;
	private $dom;
	private $url;
	private $artist;
	private $releases;
	private $release_formats;
	private $positions;
	private $kana;
	
	public  function __construct($pdo) {
		if(!isset($pdo) || !$pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
			include_once("../php/database-connect.php");
			$this->pdo = $pdo;
		}
		else {
			$this->pdo = $pdo;
		}
		
		$this->kana = new Kana();
		
		$this->release_formats = [
			'デモテープ'     => ['medium' => 'CT',   'format' => 'demo'],
			'cdシングル'     => ['medium' => 'CD',   'format' => 'single'],
			'maxiシングル'   => ['medium' => 'CD',   'format' => 'maxi-single'],
			'cdアルバム'     => ['medium' => 'CD',   'format' => 'full-album'],
			'参加オムニバス' => ['medium' => 'CD',   'format' => 'omnibus'],
			'オムニバス'     => ['medium' => 'CD',   'format' => 'omnibus'],
			'映像作品'       => ['medium' => 'DVD',  'format' => null],
			'書籍'           => ['medium' => 'Book', 'format' => null],
			'dvd'            => ['medium' => 'DVD',  'format' => null],
			'cd'             => ['medium' => 'CD',   'format' => null],
			'ビデオ'         => ['medium' => 'VHS',  'format' => null],
			'md'             => ['medium' => 'MD',   'format' => null],
		];
		
		$this->positions = [
			'',
			'Vocal',
			'Guitar',
			'Bass',
			'Drums',
			'Keyboard',
		];
	}
	
	public  function get_document($url) {
		$document = file_get_contents(str_replace('https://vk.gy/', '../', $url));
		
		if(strpos($document, 'google-cache') !== false || strpos($document, '<meta charset="UTF-8">') !== false) {
			$this->document_version = 2;
			$this->document_encoding = 'UTF-8';
		}
		elseif(strpos($document, '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML4.01 Transitional//EN">') !== false) {
			$this->document_version = 1;
			$this->document_encoding = 'SJIS';
		}
		else {
			$this->document_version = 0;
			$this->document_encoding = 'SJIS';
		}
		
		if($this->document_version < 2) {
			$filename = end(explode('/', $url));
			
			if(!file_exists('../uploads/grassthread-backups/'.$filename)) {
				rename('../uploads/grassthread/'.$filename, '../uploads/grassthread-backups/'.$filename);
				file_put_contents('../uploads/grassthread/'.$filename, mb_convert_encoding($document, 'UTF-8', $this->document_encoding));
			}
		}
		
		$document = mb_convert_encoding($document, 'HTML-ENTITIES', $this->document_encoding);
		
		$this->document = $document;
		$this->url = $url;
	}
	
	public  function get_dom() {
		// Supress warning about malformed documents
		libxml_use_internal_errors(true);
		
		$this->dom = new DOMDocument;
		$this->dom->loadHTML($this->document);
		$this->dom->preserveWhiteSpace = false;
		
		libxml_clear_errors();
	}
	
	public  function get_data() {
		$this->get_artist_name();
		$this->get_artist_notes();
		$this->get_labels();
		
		$tables = $this->get_tables();
		$this->parse_tables($tables);
		
		if(is_array($this->artist['bio'])) {
			$this->artist['bio'] = array_values($this->artist['bio']);
		}
		
		return ['artist' => $this->artist, 'releases' => $this->releases];
	}
	
	private function get_artist_name() {
		$title = $this->dom->getElementsByTagName('title');
		
		if($title) {
			$title = $title[0]->nodeValue;
			
			$title = str_replace(' | グラスレ', '', $title);
			
			if(preg_match('/'.'([^\(]+)(?:\((.+)\))?'.'/', $title, $title_match)) {
				$name = trim($title_match[1]);
				
				// Get artist name change
				$name_chunks = array_filter(explode("→", trim($name)));
				if(is_array($name_chunks) && count($name_chunks) > 1) {
					$last_name = end($name_chunks);
					
					for($i=0; $i<count($name_chunks); $i++) {
						if($i===0) {
							$name_change = '/'.$last_name.'/['.$name_chunks[$i].'] changes their name to ';
						}
						elseif($i===1) {
							$name_change .= '/'.$last_name.'/['.$name_chunks[$i].'].';
						}
						else {
							$name_change = substr($name_change, 0, -1).', then to /'.$last_name.'/['.$name_chunks[$i].'].';
						}
					}
					
					$this->artist['bio'][] = ['content' => $name_change, 'tag' => 'name'];
					
					$name = $last_name;
				}
				
				// If pronunciation, set in bio
				$pronunciation = $title_match[2];
				if($pronunciation) {
					$this->artist['bio'][] = ['content' => '/'.$name.'/ ('.$pronunciation.') forms.', 'tag' => 'formation'];
				}
				
				// Set name/romaji/friendly
				$this->artist['name'] = $name;
				
				if(preg_match('/'.'^[^A-z0-9]+$'.'/', $name)) {
					$romaji = $this->kana->from_kana($pronunciation ?: $name);
					
					if(preg_match('/'.'^[^A-z0-9]+$'.'/', $romaji)) {
						if(preg_match('/'.'\/([\w]+)\.html$'.'/', $this->url, $match)) {
							$romaji = $match[1];
						}
					}
					
					$this->artist['romaji'] = $romaji;
				}
				
				$this->artist['friendly'] = friendly($romaji ?: $name);
			}
		}
	}
	
	private function get_labels() {
		$sidebar = $this->dom->getElementById('sidebar1');
		if($sidebar) {
			foreach($sidebar->getElementsByTagName('a') as $a) {
				if($a->nodeValue !== 'ディスコグラフィ') {
					if(strpos($a->getAttribute('href'), 'http') === false) {
						$this->artist['labels'][] = $a->nodeValue;
					}
				}
			}
		}
	}
	
	private function get_artist_notes() {
		if($this->document_version === 2) {
			foreach($this->dom->getElementsByTagName('article') as $article) {
				foreach($article->getElementsByTagName('div') as $div) {
					if($div->getAttribute('class') === 'entry-container fix') {
						foreach($div->getElementsByTagName('p') as $p) {
							$this->parse_artist_note($p->nodeValue);
						}
					}
				}
			}
		}
		elseif($this->document_version === 1) {
			$main = $this->dom->getElementById('mainContent');
			foreach($main->getElementsByTagName('p') as $p) {
				$this->parse_artist_note($p->nodeValue);
			}
		}
		else {
			$xpath = new \DOMXPath($this->dom);
			foreach($xpath->query('//div[@id="mainContent"]/text()[not(ancestor::p)][normalize-space()]') as $text) {
				$this->parse_artist_note($text->nodeValue);
			}
		}
	}
	
	private function parse_artist_note($note) {
		$note = str_replace(["\r", "\n", "　"], " ", $note);
		$note = trim($note);
		
		if($note) {
			if(strpos($note, 'グラスレ') === false && strpos($note, 'ディスコグラフィ') === false && strpos($note, 'TOPに戻る') === false && strpos($note, '関係者') === false) {
				if(strpos($note, 'Vo.') !== false || strpos($note, 'Vocal') !== false || strpos($note, 'Gu.') !== false) {
					$note = str_replace('、', ' / ', $note);
					
					$note = str_replace(['Vo.', 'Gt.', 'Ba.', 'Dr.', 'Key.', 'サポート'], ['V. ', 'G. ', 'B. ', 'D. ', 'K. ', 'Support '], $note);
					
					$this->artist['bio']['lineup']['content'] .= ($this->artist['bio']['lineup'] ? "\n\n↓\n\n" : null).$note;
					$this->artist['bio']['lineup']['tag'] = 'lineup';
				}
				else {
					$this->artist['bio'][] = ['content' => 'Note: '.$note, 'tag' => 'other'];
				}
			}
		}
	}
	
	private function get_tables() {
		foreach($this->dom->getElementsByTagName('table') as $table_key => $table) {
			if($table->hasAttribute('summary')) {
				foreach($table->getElementsByTagName('tr') as $tr_key => $tr) {
					foreach($tr->getElementsByTagName('td') as $td_key => $td) {
						$rowspan = $td->getAttribute('rowspan') ?: 1;
						
						for($i=0; $i<$rowspan; $i++) {
							$tables[$table_key][$tr_key + $i][] = [
								'rowspan' => ($rowspan > 1) ? $rowspan : null,
								'content' => $td->textContent,
							];
						}
					}
				}
			}
		}
		
		if(is_array($tables)) {
			$tables = array_values($tables);
		}
		
		return $tables;
	}
	
	private function parse_tables($tables) {
		if(is_array($tables) && !empty($tables)) {
			foreach($tables as $table_key => $table) {
				$first_td_content = strtolower($table[0][0]['content']);
				
				if($table_key === 0 || in_array($first_td_content, ['vocal', 'guitar', 'bass', 'drums'])) {
					$this->parse_member_table($table, $table_key);
				}
				elseif(in_array($first_td_content, array_keys($this->release_formats))) {
					$this->parse_release_table($table, $first_td_content);
				}
			}
		}
	}
	
	private function parse_member_table($table, $table_key) {
		if(is_array($table) && !empty($table)) {
			foreach($table as $tr_key => $tr) {
				// Init member array
				$member = [];
				
				// Member name should always be in same spot
				$name = $tr[1]['content'];
				
				// Set up position
				$position = $tr[0]['content'];
				
				if(strpos($position, 'サポート') !== false) {
					$member['position_name'] = 'Support ';
					$position = str_replace('サポート', '', $position);
				}
				
				$position_key = array_search($position, $this->positions);
				$position_key = is_numeric($position_key) ? $position_key : 6;
				
				if($position_key === 6) {
					$member['position_name'] = str_replace('、', ' & ', $position);
				}
				
				$position = $position_key;
				
				// Get member name change
				$name_chunks = array_filter(explode("→", trim($name)));
				if(is_array($name_chunks) && count($name_chunks) > 1) {
					$last_name = end($name_chunks);
					
					for($i=0; $i<count($name_chunks); $i++) {
						if($i===0) {
							$name_change  = $position_key === 4 ? 'Drummer' : $this->positions[$position].'ist';
							$name_change .= ' '.$name_chunks[$i].' changes his name to ';
						}
						elseif($i===1) {
							$name_change .= $name_chunks[$i].'.';
						}
						else {
							$name_change = substr($name_change, 0, -1).', then to '.$name_chunks[$i].'.';
						}
					}
					
					$this->artist['bio'][] = ['content' => $name_change, 'tag' => 'name'];
					
					$name = $last_name;
				}
				
				// Set up temporary member array
				$member['position'] = $position;
				$member['name'] = $name;
				if(!preg_match('/'.'^\w+$'.'/', $name)) {
					$member['romaji'] = $this->kana->from_kana($name);
					$member['romaji'] = preg_match('/'.'^\w+$'.'/', $member['romaji']) ? $member['romaji'] : null;
				}
				
				// Member pronunciation/birth/history/sessions might or might not exist, so loop through each td in each tr and assign it accordingly
				foreach($tr as $td_key => $td) {
					if($td_key !== 0 && $td_key !== 1) {
						if(strpos($td['content'], '月') !== false || strpos($td['content'], '日') !== false || strpos($td['content'], '型') !== false) {
							if(preg_match('/'.'(?:(\d{1,2})月(\d{1,2})日)?(?:([A-Z]{1,2})型)?'.'/', $td['content'], $match)) {
								$member['birth_date'] = $match[1] && $match[2] ? ('0000-'.(strlen($match[1]) === 1 ? '0' : null).$match[1].'-'.(strlen($match[2]) === 1 ? '0' : null).$match[2]) : null;
								$member['blood_type'] = $match[3];
							}
						}
						elseif(strpos($td['content'], '→') !== false) {
							$member['history'] = $td['content'];
							$member['history'] = $this->get_secessions($member);
							$member['history'] = $this->parse_history($member['history']);
							
							if(strpos($member['history'], '/'.$this->artist['name'].'/') === false) {
								if(substr($member['history'], -1) === '/') {
									$member['history'] .= ', /'.$this->artist['name'].'/';
								}
								else {
									$member['history'] = substr($member['history'], 0, -1).', /'.$this->artist['name'].'/'.substr($member['history'], -1);
								}
							}
						}
						elseif($td_key === count($tr) - 1) {
							$member['sessions'] = $td['content'];
							$member['sessions'] = $this->parse_history($member['sessions']);
							
							if($member['sessions']) {
								$last_history = explode("\n", $member['history']);
								$count_history = count($last_history);
								if(end($last_history)) {
									$history_key = $count_history - 1;
								}
								else {
									$history_key = $count_history - 2;
								}
								$last_history[$history_key] .= ', '.$member['sessions'];
								$member['history'] = implode("\n", $last_history);
							}
						}
						else {
							$pronunciation = $td['content'];
							
							if(preg_match('/'.'^[^A-z0-9]+$'.'/', $name)) {
								$romaji = $this->kana->from_kana($pronunciation ?: $name);
							}
							
							$member['romaji'] = $romaji;
						}
						
						$member['friendly'] = friendly($member['romaji'] ?: $member['name']);
					}
				}
				
				// Figure out if members/ex-members/staff
				if($table_key === 0) {
					$member_type = 'current';
					$member['to_end'] = 1;
				}
				elseif($table_key === 1) {
					$member_type = 'ex';
					$member['to_end'] = null;
				}
				else {
					$member_type = 'staff';
					$member['to_end'] = null;
					$member['position'] = 6;
					$member['position_name'] = 'Roadie';
				}
				
				$this->artist['musicians'][] = $member;
				
				$this->get_secessions($member);
				
				unset($name, $romaji, $friendly);
			}
		}
	}
	
	private function parse_release_table($table, $release_format) {
		if(is_array($table) && !empty($table)) {
			// Get medium/format from first row of table, then unset that row
			$medium = $this->release_formats[$release_format]['medium'];
			$format = $this->release_formats[$release_format]['format'];
			unset($table[0]);
			
			// For remaining rows
			foreach($table as $tr_key => $tr) {
				// Get title/date/note from row (previous data parsing expanded rowspan td's appropriately)
				$title = str_replace('　', '', $tr[0]['content']) ?: '(unknown title)';
				$date = $this->parse_date($tr[1]['content']);
				$note = $tr[2]['content'];
				
				// Check for multiple dates
				if(strpos($tr[1]['content'], "\n") !== false || mb_strlen($tr[1]['content']) > 10) {
					$note = $note.($note ? "\n\n---\n\n" : null).'Release dates: '.str_replace("\r", ' ', $tr[1]['content']);
				}
				
				// Create unique key, in case of releases w/ same title but diff release dates
				$release_key = $date.'|'.$title;
				
				// Set release title/date/note/medium/format
				$releases[$release_key]['name'] = $title;
				$releases[$release_key]['date_occurred'] = trim(str_replace('　', '', $date)) ?: '0000-00-00';
				$releases[$release_key]['notes'] = $note;
				$releases[$release_key]['medium'][0] = $medium;
				$releases[$release_key]['format'][0] = $format;
				
				// Parse note
				if(preg_match_all('/'.'限定(\d+)'.'/', $note, $matches)) {
					$releases[$release_key]['press_limitation_num'] = $matches[1][0];
				}
				if(strpos($note, '配付') !== false) {
					$releases[$release_key]['price'] = '0 yen';
					$releases[$release_key]['venue_limitation'] = 'lives only';
				}
				if(($medium === 'dvd' || $medium === 'vhs') && strpos($note, 'ライブ') !== false) {
					$releases[$release_key]['format'][0] = $format ?: 'live recording';
				}
				if(strpos($note, 'ベスト') !== false) {
					$releases[$release_key]['format'][0] = $format ?: 'collection';
				}
				
				// Explode tracklist, in case tracklist uses breaks instead of separate rows
				$tmp_tracklist = explode("\n", $tr[3]['content']);
				
				// If tracklist *did* use breaks, append each one (and disregard composer info as it's not set)
				if(count($tmp_tracklist) > 1) {
					foreach($tmp_tracklist as $track) {
						$releases[$release_key]['tracklist'][] = ['title' => $track];
					}
				}
				else {
					$releases[$release_key]['tracklist'][] = [
						'title' => trim(str_replace('　', ' ', $tr[3]['content'])) ?: '(contents unknown)',
						'lyrics' => trim(str_replace('　', ' ', $tr[4]['content'])),
						'composition' => trim(str_replace('　', ' ', $tr[5]['content'])),
						'arrangement' => trim(str_replace('　', ' ', $tr[6]['content']))
					];
				}
				
				// Check if mini-album
				if($format === 'full-album' && count($releases[$release_key]['tracklist']) < 7) {
					$releases[$release_key]['format'][0] = 'mini-album';
				}
			}
			
			if(is_array($releases)) {
				$releases = array_values($releases);
				
				foreach($releases as $release_key => $release) {
					foreach($release['tracklist'] as $track_key => $track) {
						$releases[$release_key]['tracklist']['name'][$track_key] = $track['title'];
						$releases[$release_key]['tracklist']['artist_id'][$track_key] = '';
						
						if($track['lyrics'].$track['composition'].$track['arrangement']) {
							$releases[$release_key]['notes'] .=
								(strlen(trim($releases[$release_key]['notes'])) ? "\n\n---\n\n" : null).
								'*'.$track['title'].'*: '.
								($track['lyrics'] ? ' [lyrics] '.$track['lyrics'] : null).
								($track['composition'] ? ' [composition] '.$track['composition'] : null).
								($track['arrangement'] ? ' [arrangement] '.$track['arrangement'] : null);
						}
						
						unset($releases[$release_key]['tracklist'][$track_key]);
					}
				}
				
				if(is_array($this->releases) && !empty($this->releases)) {
					$this->releases = array_merge($this->releases, $releases);
				}
				else {
					$this->releases = $releases;
				}
			}
		}
	}
	
	private function parse_date($date) {
		$date_pattern = '(\d+)'.'(?:\/(\d{0,2}))?'.'(?:\/(\d{0,2}))?';
		
		if(preg_match('/'.$date_pattern.'/', $date, $match)) {
			$date =
				($match[1] > 18 ? '19' : '20').$match[1].
				'-'.
				($match[2] ? (strlen($match[2]) === 1 ? '0' : null).$match[2] : '00').
				'-'.
				($match[3] ? (strlen($match[3]) === 1 ? '0' : null).$match[3] : '00');
		}
		
		return $date;
	}
	
	private function parse_history($history) {
		// Standardize common notes and put space before
		$history = str_replace([
			'　',
			'(ローディ)',
			'ローディ',
			'(サポート)',
			'サポート',
			'(Vo)',
			'(Gu)',
			'(Ba)',
			'(Dr)'
		], [
			' ',
			' (roadie)',
			' (roadie)',
			' (support)',
			' (support)',
			' (on vocals)',
			' (on guitar)',
			' (on bass)',
			' (on drums)'
		], $history);
		
		// For remaining notes, assume 'as name,' unless matches secession date pattern
		preg_match_all('/'.'([^\s])\((.+?)\)'.'/', $history, $matches, PREG_SET_ORDER);
		if(is_array($matches) && !empty($matches)) {
			foreach($matches as $match) {
				$history = str_replace($match[0], $match[1].' (as '.$match[2].')', $history);
			}
		}
		
		$history = explode('→', $history);
		
		// Remove empty first line
		if(!trim($history[0])) {
			unset($history[0]);
			$history = is_array($history) ? array_values($history) : [];
		}
		
		foreach($history as $history_key => $history_line) {
			if($history_line) {
				$history[$history_key] = '/'.str_replace([' (', '、'], ['/ (', '/, /'], $history_line).(substr($history_line, -1) !== ')' ? '/' : null);
			}
		}
		
		$history = implode("\n", $history);
		
		return $history;
	}
	
	private function get_secessions($member) {
		$member_secession_pattern = '\('.'([\d\/]{2,10})'.'(?:[^脱退]+)?'.'脱退\)';
		
		if(preg_match('/'.$member_secession_pattern.'/', $member['history'], $match)) {
			$date = $this->parse_date($match[1]);
			
			$content  = $member['position'] === 4 ? 'Drummer' : $this->positions[$member['position']].'ist';
			$content .= ' '.$member['name'].' secedes.';
			
			$this->artist['bio'][] = ['date' => $date, 'content' => $content, 'tag' => 'member'];
			
			$member['history'] = str_replace($match[0], '', $member['history']);
		}
		
		return $member['history'];
	}
}

///////////////////////////////////////////////////////////////////////////////

$dir = "../uploads/grassthread/";
$pages = scandir($dir);
unset($pages[0], $pages[1]);

while(!isset($page)) {
	shuffle($pages);
	$page = str_replace(".html", '', $pages[0]);
	
	if(substr($page, 0, 8) === 'finished') {
		unset($page);
	}
}

$num_pages = count($pages);
for($i=0; $i < $num_pages; $i++) {
	if(substr($pages[$i], 0, 8) === 'finished') {
		$num_finished++;
	}
}

?>
	<div class="col c1">
		<div>
			<h1>
				Port from grassthread
			</h1>
			<div class="text text--outlined text--notice">
				<p class="symbol__error">
					Caution: this is janky! <strong>Please</strong> take care to clean the data.
				</p>
				
				<ol>
					<li>&ldquo;New artist&rdquo; will load a random grassthread page.</li>
					<li>Artist/musicians/releases will be auto-inserted into vkgy.</li>
					<li>After, the grassthread page and vkgy artist/discography pages are linked below. Raw data is printed below for troubleshooting.</li>
					<li><strong>Please</strong> add romaji and fix any errors. (FYI, artists are tagged &ldquo;needs review&rdquo; and releases are tagged &ldquo;needs review&rdquo; and &ldquo;needs romaji&rdquo;.)</li>
					<li>When completely finished correcting the artist/discography pages, click &ldquo;all errors corrected&rdquo; to remove this artist from the queue.</li>
				</ol>
				
				<p>
					<a class="a--padded a--outlined" href="&page=<?php echo $page; ?>">New artist</a>
					<a class="a--padded" href="&corrected=<?php echo $_GET["page"]; ?>">All errors corrected</a>
					<span class="any__note"><?php echo $num_finished; ?> ported</span>
				</p>
			</div>
		</div>
	</div>
<?php

///////////////////////////////////////////////////////////////////////////////

if($_GET['page']) {
	if(file_exists('../uploads/grassthread/'.$_GET['page'].'.html')) {
		$scraper = new grassthread_scraper($pdo);
		$scraper->get_document('https://vk.gy/uploads/grassthread/'.$_GET['page'].'.html');
		
		if($_GET["force"] === 'true' || (strpos($scraper->document, ',HR/HM,') === false && strpos($scraper->document, 'content="JPOP,') === false)) {
			$scraper->get_dom();
			$data = $scraper->get_data();
		}
		else {
			?>
				<div class="col c1">
					<div>
						<div class="text text--outlined text--error">
							<p class="symbol__error">
								This artist appears to a Western HR/HM or pop band, and will not be ported. Please mark <a class="a--padded" href="&corrected=<?php echo $_GET["page"]; ?>">all errors corrected</a> to remove it from the queue.
							</p>
							
							<p class="any--weaken">
								Want to force this page to be ported anyway? <a href="&page=<?php echo $_GET["page"]; ?>&force=true">Force port</a>
							</p>
						</div>
					</div>
				</div>
			<?php
		}
	}
}
elseif($_GET['corrected']) {
	if(file_exists('../uploads/grassthread/'.$_GET['corrected'].'.html')) {
		if(rename('../uploads/grassthread/'.$_GET['corrected'].'.html', '../uploads/grassthread/'.'finished-'.$_GET['corrected'].'.html')) {
			?>
				<div class="col c1">
					<div>
						<div class="text text--outlined text--notice">
							Good job! Artist archived.
						</div>
					</div>
				</div>
			<?php
		}
	}
}

///////////////////////////////////////////////////////////////////////////////

if($data) {
	// Insert artist if not in DB
	if(!$selected_artist_friendly) {
		$_POST = [
			'name' => [0 => $data['artist']['name']],
			'romaji' => [0 => $data['artist']['romaji']],
		];
		
		ob_start();
		include('../artists/function-add.php');
		$result = ob_get_contents();
		ob_end_clean();
		
		$extant_artist_id = is_numeric($artist_id) ? $artist_id : $row_check['id'];
		
		unset($_POST, $output, $artist_id);
	}
	
	// Get artist details from DB
	$sql_artist_name = 'SELECT id, name, romaji, friendly, active, type, concept_name, concept_romaji, description, label_history, official_links, is_exclusive FROM artists WHERE id=?';
	$stmt_artist_name = $pdo->prepare($sql_artist_name);
	$stmt_artist_name->execute([ $extant_artist_id ]);
	$rslt_artist_name = $stmt_artist_name->fetch();
	
	?>
		<div class="col c1">
			<div>
				<div class="text text--outlined">
					<ul class="ul--bulleted">
						<li><a href="https://vk.gy/artists/<?php echo $rslt_artist_name['friendly']; ?>/" target="_blank"><?php echo $rslt_artist_name['friendly']; ?> artist profile</a></li>
						<li><a href="https://vk.gy/releases/<?php echo $rslt_artist_name['friendly']; ?>/" target="_blank"><?php echo $rslt_artist_name['friendly']; ?> releases</a></li>
						<li><a href="https://vk.gy/uploads/grassthread/<?php echo $_GET["page"]; ?>.html" target="_blank"><?php echo $rslt_artist_name['friendly']; ?> grassthread page</a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="col c1">
			<div>
				<h2>
					Results from adding artist, editing artist, and adding releases
				</h2>
			</div>
		</div>
	<?php
	
	// Echo artist result
	echo '<div class="col c1"><div><pre>'.print_r(json_decode($result), true).'</pre></div></div>';
	unset($result);
	
	// Loop through musicians and add if not in DB
	if(is_array($data['artist']['musicians'])) {
		foreach($data['artist']['musicians'] as $musician_key => $musician) {
			$sql_check_musician = 'SELECT 1 FROM artists_musicians LEFT JOIN musicians ON musicians.id=artists_musicians.musician_id WHERE artists_musicians.artist_id=? AND musicians.name=? LIMIT 1';
			$stmt_check_musician = $pdo->prepare($sql_check_musician);
			$stmt_check_musician->execute([ $extant_artist_id, sanitize($musician['name']) ]);
			$rslt_check_musician = $stmt_check_musician->fetchColumn();
			
			if(!$rslt_check_musician) {
				$_POST['name'][0] = $musician['name'];
				$_POST['romaji'][0] = $musician['romaji'];
				$_POST['position'][0] = $musician['position'];
				
				$history = str_replace('/'.$data['artist']['name'].'/', '('.$extant_artist_id.')'.'/'.$data['artist']['name'].'/', $musician['history']);
				$_POST['history'][0] = $history;
				
				ob_start();
				include('../musicians/function-add.php');
				$result = ob_get_contents();
				ob_end_clean();
				echo '<pre>'.print_r(json_decode($result), true).'</pre>';
				unset($result);
				
				$data['artist']['musicians'][$musician_key]['name'] = $musician['name'];
				$data['artist']['musicians'][$musician_key]['romaji'] = $musician['romaji'];
				$data['artist']['musicians'][$musician_key]['position'] = $musician['position'];
				$data['artist']['musicians'][$musician_key]['usual_position'] = $musician['position'];
				$data['artist']['musicians'][$musician_key]['id'] = $musician_id;
				
				unset($_POST, $output, $musician_id, $history);
			}
		}
	}
	
	// Get formatted musician histories from DB, remove slashes from unlinked bands, then loop through data musicians and update history
	$sql_extant_musicians = 'SELECT musicians.history, musicians.id FROM artists_musicians LEFT JOIN musicians ON musicians.id=artists_musicians.musician_id WHERE artists_musicians.artist_id=?';
	$stmt_extant_musicians = $pdo->prepare($sql_extant_musicians);
	$stmt_extant_musicians->execute([ $extant_artist_id ]);
	$rslt_extant_musicians = $stmt_extant_musicians->fetchAll();
	if(is_array($rslt_extant_musicians) && !empty($rslt_extant_musicians)) {
		foreach($rslt_extant_musicians as $musician) {
			$history = $musician['history'];
			$history = preg_replace('/'.'^\/([^\/]+)\/'.'/m', '$1', $history);
			
			$extant_musician_histories[$musician['id']] = $history;
		}
	}
	if(is_array($data['artist']['musicians']) && !empty($data['artist']['musicians'])) {
		foreach($data['artist']['musicians'] as $musician_key => $musician) {
			if(is_numeric($musician['id'])) {
				$data['artist']['musicians'][$musician_key]['history'] = $extant_musician_histories[$musician['id']];
			}
			else {
				// Unset musicians that weren't *just* added, so we don't fuck them up during artist edit
				unset($data['artist']['musicians'][$musician_key]);
			}
		}
	}
	
	// Set up artist name/type/active
	$post = [
		'id' => $rslt_artist_name['id'],
		'name' => $rslt_artist_name['name'],
		'romaji' => $rslt_artist_name['romaji'],
		'friendly' => $rslt_artist_name['friendly'],
		'type' => $rslt_artist_name['type'] ?: 1,
		'active' => $rslt_artist_name['active'] ?: 2,
		"concept_name" => $rslt_artist_name["concept_name"],
		"concept_romaji" => $rslt_artist_name["concept_romaji"],
		"description" => $rslt_artist_name["description"],
		"label_history" => $rslt_artist_name["label_history"],
		"official_links" => $rslt_artist_name["official_links"],
		"is_exclusive" => $rslt_artist_name["is_exclusive"],
	];
	
	// Set up labels
	if(is_array($data['artist']['labels']) && !empty($data['artist']['labels'])) {
		foreach($data['artist']['labels'] as $label_key => $label) {
			$sql_label = 'SELECT id FROM labels WHERE name=? LIMIT 1';
			$stmt_label = $pdo->prepare($sql_label);
			$stmt_label->execute([ sanitize($label) ]);
			$rslt_label = $stmt_label->fetchColumn();
			
			$data['artist']['labels'][$label_key] = is_numeric($rslt_label) ? '('.$rslt_label.')' : null;
		}
		
		$data['artist']['label_history'] = implode("\n", array_filter($data['artist']['labels']));
	}
	$post['label_history'] = $rslt_artist_name['label_history'] ?: $data['artist']['label_history'];
	
	// Get old bio, turn to string
	$access_artist = new access_artist($pdo);
	$extant_artist_history = $access_artist->get_history($rslt_artist_name['id']);
	$new_history = '';
	
	if(is_array($extant_artist_history)) {
		for($i=0; $i<count($extant_artist_history); $i++) {
			if(!in_array('is_uneditable', $extant_artist_history[$i]['type'])) {
				$new_line  = $extant_artist_history[$i]['date_occurred'].' ';
				$new_line .= $extant_artist_history[$i]['content'].' -';
				
				$types = [];
				foreach($extant_artist_history[$i]['type'] as $type) {
					$types[] = $access_artist->artist_bio_types[$type];
				}
				$types = array_filter(array_unique($types));
				$new_line .= implode(',', $types)."\n\n";
				
				$new_history .= $new_line;
			}
		}
	}
	$extant_artist_history = $new_history;
	unset($new_history);
	
	// Add new bio to old bio
	if(is_array($data['artist']['bio']) && !empty($data['artist']['bio'])) {
		$new_history = '';
		$markdown_parser = new parse_markdown($pdo);
		
		foreach($data['artist']['bio'] as $bio_key => $bio) {
			$bio = ($bio['date'] ?: '0000-00-00').' '.$bio['content'].' -'.$bio['tag'];
			$bio = $markdown_parser->validate_markdown($bio);
			$data['artist']['bio'][$bio_key] = $bio;
		}
		
		$data['artist']['bio'] = $extant_artist_history.implode("\n\n", $data['artist']['bio']);
		$data['artist']['bio'] = implode("\n\n", array_unique(explode("\n\n", $data['artist']['bio'])));
	}
	
	// Edit artist
	$post['musicians'] = $data['artist']['musicians'];
	$post['bio'] = $data['artist']['bio'];
	$_POST = $post;
	ob_start();
	include('../artists/function-edit.php');
	$result = ob_get_contents();
	ob_end_clean();
	echo '<div class="col c1"><div><pre>'.print_r(json_decode($result), true).'</pre></div></div>';
	unset($result);
	
	// Add releases
	if(is_array($data['releases']) && !empty($data['releases'])) {
		foreach($data['releases'] as $add_release) {
			$release_artist_id = $add_release['format'][0] === 'omnibus' ? 0 : $extant_artist_id;
			
			$sql_check_release = 'SELECT 1 FROM releases WHERE artist_id=? AND name=? AND date_occurred=? LIMIT 1';
			$stmt_check_release = $pdo->prepare($sql_check_release);
			$stmt_check_release->execute([ $release_artist_id, sanitize($add_release['name']), $add_release['date_occurred'] ]);
			$rslt_check_release = $stmt_check_release->fetchColumn();
			
			if(!$rslt_check_release) {
				$post = $add_release;
				$post['artist_id'] = $release_artist_id;
				$_POST = $post;
				
				if(is_array($_POST) && is_array($_POST['tracklist']) && is_array($_POST['tracklist']['artist_id'])) {
					foreach($_POST['tracklist']['artist_id'] as $key => $value) {
						$_POST['tracklist']['artist_id'][$key] = $extant_artist_id;
					}
				}
				
				ob_start();
				include('../releases/function-add.php');
				$result = json_decode(ob_get_contents());
				ob_end_clean();
				echo '<div class="col c1"><div><pre>'.print_r($result, true).'</pre></div></div>';
				
				if(is_numeric($result->id)) {
					$release_id = $result->id;
					$sql_tags = 'INSERT INTO releases_tags (release_id, tag_id) VALUES (?, ?)';
					$stmt_tags = $pdo->prepare($sql_tags);
					$stmt_tags->execute([ $release_id, 1 ]);
					$stmt_tags->execute([ $release_id, 3 ]);
				}
				
				unset($result, $_POST, $release, $post, $sql_release, $sql_values, $sql_keys, $tracklist, $tmp_tracklist, $track_num);
			}
		}
	}
	
	echo '<div class="col c1"><div>';
	echo '<h2>Data scraped from grassthread</h2>';
	echo '<pre>'.print_r($data['artist'], true).'</pre>';
	echo '<pre>'.print_r($data['releases'], true).'</pre>';
	echo '</div></div>';
}
?>

<?php } ?>