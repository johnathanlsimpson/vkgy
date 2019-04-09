<div class="col c1 documentation__wrapper">
	<div>
		<h2>
			Adding musicians
		</h2>
		<ul class="ul--bulleted text text--outlined">
			<li>Use the musician's real name (or most common name) and most common position.</li>
			<li>Each musician must belong to at least one artist in the database; see 'band history' section.</li>
		</ul>
		
		<h2>
			Editing musicians
		</h2>
		<ul class="ul--bulleted text text--outlined">
			<li>To edit a musician, navigate to any band that he's a member of, and edit the band's page.</li>
			<li>Blood type, alternate stage names, and other details are also set via the band edit page.</li>
			<li>To un-link a musician and band, edit that band's page and remove the band from his band history. To delete him entirely, click <span class="any__note symbol__trash symbol--standalone"></span>.</li>
		</ul>
		
		<h2>
			Band history
		</h2>
		<ul class="ul--bulleted text text--outlined">
			<li>The 'band history' section is the musician's entire musical history, with each line representing an “era”, from oldest at the top, to newest at the bottom.</li>
			<li>Typically, a new line (era) is made when the musician leaves one band and joins another. A new line should also be used if a musician changes name or position while in the same band, or if the band changes names.</li>
			<li>
				To link a musician to a band in the database, use vkgy's special /slash/ notation. <span class="symbol__error">Every musician must link to at least one band.</span><br />
				If a band isn't in the database yet, write its name in plain text like so: Romanized (Japanese).<br />
				<span class="any__note" style="vertical-align:middle;">/sugar forkful/<br />Doku yori CHOCO (毒よりチョコ)</span> → <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/sugar-forkful/">SUGAR FORKFUL</a><br />Doku yori CHOCO (毒よりチョコ)</span>
			</li>
			<li>
				To show a different name for a band (e.g. its pre-name-change name), use vkgy's /slash/[bracket] notation.<br />
				<span class="any__note" style="vertical-align:middle;">/sugar forkful/[Doku yori CHOCO (毒よりチョコ)]<br />/sugar forkful/</span> → <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/sugar-forkful/">Doku yori CHOCO</a><br /><a class="artist" href="/artists/sugar-forkful/">SUGAR FORKFUL</a></span>
			</li>
			<li>
				If active in multiple bands at the same time, separate them with <span class="any__note">, </span>.<br />
				<span class="any__note">/Dali/, La'cryma christi</span>
			</li>
			<li>
				Use notes when appropriate. Try to keep each one specific.<br />
				<span class="any__note">/Dali/ (roadie)<br />/Dali/ (on vocals) (support) (as Mr. ReNN)<br />/DIR EN GREY/ (?), (president of Matina)<br />/Arc/ (as R.E.N.)<br />(retired)</span>
			</li>
			<li>
				Continuous sessions should be placed as if they're a normal band, but one-off sessions should be written after a <span class="any__note"> | </span>. All previous notation rules apply for one-off sessions.<br />
				<span class="any__note" style="vertical-align:middle;">/sugar forkful/ | Session A<br />/Dali/ | Session B, Session C<br />/Arc/, Continuous Session D | Session E (support)</span>  → <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/sugar-forkful/">SUGAR FORKFUL</a><br /><a class="artist" href="/artists/dali/">Dali</a><br /><a class="artist" href="/artists/arc">Arc</a>, Continuous Session D</span> &amp; <span class="any__note" style="vertical-align:middle;">Session A, Session B, Session C, Session E (support)</span>
			</li>
			<li>
				If the musician is not currently in an active band, and his status is unknown, leave a blank line at the bottom of his band history. If he's retired or deceased, put that as a note on the last line instead.<br />
				<span class="any__note" style="vertical-align:middle;">/dali/<br />&nbsp;</span> → <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/dali/">Dali</a><br />(unknown)</span>
			</li>
		</ul>
	</div>
</div>