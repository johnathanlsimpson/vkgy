<div class="col c1 documentation__wrapper">
	<div>
		<h2>
			Editing basic information
		</h2>
		<input class="any--hidden obscure__input" id="obscure-basic" type="checkbox" checked />
		<div class="obscure__container obscure--alt obscure--height any--margin">
			<h3>
				Name
			</h3>
			<div class="text text--outlined">
				<ul class="ul--bulleted">
					<li>
						Keep names true to the logotype when possible (include äccents), within reason.
						Use official names and official romaji when specified (official name is 大日本鬼端児組 悪童会に占拠され ゴシップ but official romaji is Gossip).
					</li>
					<li>
						If a band has changed their name, use the latest name.
					</li>
					<li>
						“Friendly name” is a URL-friendly version of the band's name; usually romanized version with symbols replaced by hyphens.
						These are automatically generated when adding a new artist, but can be modified when it makes sense (e.g. “e-m-grief” was changed to “em-grief” for readability).
					</li>
					<li>
						Friendly names must be unique; if two artists share the same name (<a class="artist" href="/artists/arcadia/">ArcAdiA</a> and <a class="artist" href="/artists/arcadia-hiroshima/">ARcaDia</a>), one must have a different friendly name.
						You can add the year that the band formed, or the band's hometown, or the vocalist's name, etc. (e.g. check the URL of <a class="artist" href="/artists/arcadia-hiroshima/">ARcaDia</a>).
					</li>
				</ul>
			</div>
			
			<h3>
				Description
			</h3>
			<div class="text text--outlined">
				<ul class="ul--bulleted">
					<li>
						Description should briefly identify band in a meaningful way; it's not really meant for a full biography. For example: “Last band of vocalist XYZ. Lasted only months before disbanding due to XYZ.”
					</li>
				</ul>
			</div>
			
			<h3>
				Label history
			</h3>
			<div class="text text--outlined">
				<ul class="ul--bulleted">
					<li>
						Enter the ID of the band's record label, enclosed in parentheses. The ID can be found on the label's edit page; <a class="symbol__company" href="/labels/matina/edit/">Matina</a>'s is 51, so you would type <span class="any__note">(51)</span>.
					</li>
					<li>
						Each line represents a new period in time. So if a band transferred from Matina to <a class="symbol__company" href="/labels/key-party/edit/">KEY PARTY</a>, the label history would be <span class="any__note" style="vertical-align: middle;">(51)<br />(292)</span>.
					</li>
					<li>
						A blank line indicates that the band is completely independent. If the band is managing themselves, but have given their self-management a title (e.g. <a class="artist" href="/artists/d/">D</a> was self-managed by <a class="symbol__company" href="/labels/god-child-records/">GOD CHILD RECORDS</a>), add it as a record label.
					</li>
					<li>
						If a band is on a sublabel, indicate it like: <span class="any__note">(51) &gt; (112)</span>.
					</li>
					<li>
						Comments can be added; e.g. if the band is produced by a label but not signed to it: <span class="any__note">(51) (produced)</span>.
					</li>
				</ul>
			</div>
			
			<label class="input__button obscure__button" for="obscure-basic">Show section</label>
		</div>
		
		<h2>
			Editing biography
		</h2>
		<input class="any--hidden obscure__input" id="obscure-doc-bio" type="checkbox" checked />
		<div class="text text--outlined obscure__container obscure--alt obscure--height">
			<ul class="ul--bulleted">
				<li>
					The biography section is a timeline of important events, such as formation, member changes, important lives, and so on. It is completely text-based, and has a number of features. <span class="symbol__error">Releases are added automatically.</span>
				</li>
				<li>
					Each biography entry starts with a date, formatted like yyyy-mm-dd, followed by a space, and then the event: <span class="any__note">1999-06-18 Dali forms.</span>.
				</li>
				<li>
					Entries are separated with one blank line. (The system assumes that a line break <em>not</em> followed by a date is part of the same entry.)<br />
					<span class="any__note" style="vertical-align: baseline;">1999-06-18 Bassist JUN joins.<br /><br />1999-07-01 Bassist JUN secedes.</span> is two entries; <span class="any__note" style="vertical-align: baseline;">1999-06-18 Bassist JUN joins.<br /><br />Later that day, he is run over by a car.</span> is one entry.
				</li>
				<li>
					<a href="/"><?php echo $vkgy ? "vk.gy" : "weloveucp.com"; ?></a>'s flavor of markdown can be used:<br />
					<span class="any__note">1997-01-01 /dali/ holds twoman vs /la sadies/. *The* **best** [record label](/labels/matina/).</span>
					is interprated as
					<span class="any__note">1997-01-01 <a class="artist" href="/artists/dali/">Dali</a> holds twoman vs <a class="artist" href="/artists/la-sadies/">La:Sadie's</a>. <em>The</em> <strong>best</strong> <a href="/labels/matina/">record label</a>.</span>
				</li>
				<li>
					Per <a href="/"><?php echo $vkgy ? "vk.gy" : "weloveucp.com"; ?></a>'s flavor of markdown, you can linke to an artist while showing a different name by following the artist markdown with <span class="any__note">[display name here]</span>:
					<span class="any__note">1997-01-01 /Dali/[SUGAR FORKFUL (シュガーフォークフル)] changes their name to /Dali/.</span>
					is interprated as
					<span class="any__note">1997-01-01 <a class="artist" href="/artists/dali/">SUGAR FORKFUL</a> changes their name to <a class="artist" href="/artists/dali/">Dali</a>.</span>
				</li>
				<li>
					Each entry should be tagged with a type. <span class="symbol__error">The system attempts to do this automatically; you can see in the preview box how the system is tagging the entry.</span>
				</li>
				<li>
					To manually tag an entry: at the end of the line, type a space, then hyphen, then follow with a word (or words separated by commas) from the following list:<br />
					<span class="any__tag">activity</span> <span class="any__tag">cancellation</span> <span class="any__tag">disbandment</span> <span class="any__tag">formation</span> <span class="any__tag">label</span> <span class="any__tag">lineup</span> <span class="any__tag">live</span> <span class="any__tag">media</span> <span class="any__tag">member</span> <span class="any__tag">name</span> <span class="any__tag">other</span> <span class="any__tag">release</span> <span class="any__tag">schedule</span> <span class="any__tag">setlist</span> <span class="any__tag">trouble</span><br />
					For example: <span class="any__note">1999-06-18 Bassist JUN joins. -member</span> or <span class="any__note">1999-06-18 Bassist JUN joins after oneman. -live,member</span>
				</li>
				<li>
					The <span class="any__tag">lineup</span>, <span class="any__tag">schedule</span>, and <span class="any__tag">setlist</span> tags display in a smaller text than other entries.
				</li>
				<li>
					The <span class="any__tag">disbandment</span> tag displays in a bright red, and should be used like so:<br />
					<span class="any__note">1999-06-18 Dali disbands -disbandment</span> or <span class="any__note">1999-06-18 Dali indefinitely pauses activity -disbandment</span>.<br />
					Try to use the verbiage that the band uses, e.g. “CODOMO A shuts down”.
				</li>
				<li>
					If an entry is within the same year or month as the previous entry, you only need to type a partial date:<br />
					<span class="any__note" style="vertical-align: baseline;">1999-07-01 Dali forms.<br /><br />02 First live is held.<br /><br />08-07 Bassist secedes.</span>
					is interprated as
					<span class="any__note" style="vertical-align: baseline;">1999-07-01 Dali forms.<br /><br />1997-07-02 First live is held.<br /><br />1997-08-07 Bassist secedes.</span>
				</li>
				<li>
					If you type a livehouse's romanized name and move on to the next entry, the system will automatically fill in the rest of the livehouse name (japanese name, city, proper formatting) and tag it with <span class="any__tag">label</span>. <span class="symbol__error">So far, this only works for about 20 livehouses; this list is expanding.</span><br />
					<span class="any__note" style="vertical-align: baseline;">1999-07-01 area<br /><br />16 rockmaykan</span>
					is interprated as
					<span class="any__note" style="vertical-align: baseline;">1999-07-01 Takadanobaba (高田馬場) AREA -schedule<br /><br />1997-07-16 Meguro (目黒) ROCKMAYKAN (鹿鳴館) -schedule</span>
				</li>
			</ul>
			<label class="input__button obscure__button" for="obscure-doc-bio">Show section</label>
		</div>
		
		<h2>
			Live schedule
		</h2>
		<input class="any--hidden obscure__input" id="obscure-schedule" type="checkbox" checked />
		<div class="text text--outlined obscure__container obscure--alt obscure--height">
			<ul class="ul--bulleted">
				<li>
					The live schedule is updated via the &ldquo;edit biography&rdquo; section.
				</li>
				<li>
					To add to the live schedule, enter the date, a space, the romanized livehouse name, another space, and the <span class="any__note">-schedule</span> tag. Like so:<br /><br />
					<span class="any__note">2018-06-27 rockmaykan -schedule</span>
				</li>
				<li>
					If you've written the livehouse name correctly, the database will automatically put in the area and capitalize the livehouse name appropriately, and this will appear in the biography preview.<br /><br />
					<span class="any__note">2018-06-27 rockmaykan -schedule</span> &rarr; <span class="any__note">2018-06-27 Meguro ROCKMAYKAN -schedule</span>
				</li>
				<li>
					If the database <em>doesn't</em> recognize the livehouse name, it will show an error in the preview section, and will automatically change the tag to <span class="any__note">-live</span>. In this case, please check that the official name was used, and that it was spelled correctly.<br /><br />
					Common misspellings/nicknames can be added to the database, and work just like regular names (example below). If a nickname needs to be added (or a livehouse is missing entirely), please notify <a class="user" href="/users/inartistic/">inartistic</a>.<br /><br />
					<span class="any__note">2018-06-27 rokumeikan -schedule</span> &rarr; <span class="any__note">2018-06-27 Meguro ROCKMAYKAN -schedule</span>
				</li>
				<li>
					Sometimes, livehouses in different areas have the same name. In this case, you can write the romanized area in front of the livehouse name, and it should change appropriately. Examples:<br /><br />
					<span class="any__note">2018-01-01 chop -schedule</span> &rarr; <span class="any__note">2018-01-01 Ikebukuro CHOP -schedule</span><br /><br />
					<span class="any__note">2018-02-02 fukui chop -schedule</span> &rarr; <span class="any__note">2018-02-02 Fukui CHOP -schedule</span><br />
				</li>
				<li>
					If another biography event happens on the same day as the live, the live will appear in both the &ldquo;history&rdquo; and &ldquo;live history&rdquo; sections; otherwise, it will <em>only</em> appear in the &ldquo;live history.&rdquo;
				</li>
				<li>
					Shortcut: the <span class="any__note">-schedule</span> tag can be shortened to <span class="any__note">-s</span>.<br /><br />
					Shorter shortcut: the <span class="any__note">-schedule</span> tag can be omitted entirely, and the database will attempt to guess the appropriate tag. But if the database guesses incorrectly, you should add the tag manually.
				</li>
				<li>
					Note: a full list of livehouses in the database exists, but is in an alpha state and not yet publically viewable.
				</li>
			</ul>
			<label class="input__button obscure__button" for="obscure-schedule">Show section</label>
		</div>
	</div>
</div>