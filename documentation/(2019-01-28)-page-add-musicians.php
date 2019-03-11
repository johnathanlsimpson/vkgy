<div class="col c1">
	<div>
		<h2>
			Adding musicians
		</h2>
		<div class="documentation__basic edit__hidden">
			<div class="text text--outlined">
				<ul class="ul--bulleted">
					<li>
						If known, use the musician's real name. Different stage names for each band can be specified later.
					</li>
					<li>
						Select <em>usual</em> position. This can be changed per band later.
					</li>
					<li>
						“Band history” goes from oldest band to newest, top to bottom. Each line signifies a different “era” in the musician's band history.
					</li>
					<li>
						If a musician changes position or stagename while active in a band, this should be treated as separate “eras” (e.g. separate lines). Same goes for if a band changes name while the musician is a member.
					</li>
					<li>
						Band names surrounded by forward slashes will be linked to bands in the database. <span class="symbol__error">Each musician must link to at least one band in the database.</span><br />
						Bands that aren't likely to be in the database can be written normally (romanized name followed by Japanese name in parentheses).<br />
						<span class="any__note" style="vertical-align:middle;">/sugar forkful/<br />Doku yori CHOCO (毒よりチョコ)</span> → <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/sugar-forkful/">SUGAR FORKFUL</a><br />Doku yori CHOCO (毒よりチョコ)</span>
					</li>
					<li>
						To link to a band but display a different name, follow the slash with the display name enclosed in brackets. <span class="symbol__error">You should do this any time the band changes name while the musician is a member.</span><br />
						<span class="any__note" style="vertical-align:middle;">/sugar forkful/[Doku yori CHOCO (毒よりチョコ)]<br />/sugar forkful/</span> → <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/sugar-forkful/">Doku yori CHOCO</a><br /><a class="artist" href="/artists/sugar-forkful/">SUGAR FORKFUL</a></span>
					</li>
					<li>
						If active in multiple bands at one time, they should be on the same line, separated with a comma and space.<br />
						<span class="any__note">/Dali/, La'cryma christi</span>
					</li>
					<li>
						Notes can be written beside a band. Denote when the musician is <span class="any__note">(support)</span>, or if they are in a different position than usual (e.g. <span class="any__note">(on guitar)</span>), or if they are under a different stage name than usual (<span class="any__note">(as REN (れん))</span>), etc.<br />
						Multiple notes can be used at once. Notes can also be used on their own, to note that musician is president of a label, or has retired, etc.<br />
						<span class="any__note">/Dali/ (on guitar) (support)<br />/Dali/ (on vocals)<br />(president of Matina)<br />/Arc/, (president of Matina)<br />(retired)</span>
					</li>
					<li>
						<em>Brief</em> session appearances can be added after a space-enclosed pipe (<span class="any__note"> | </span>) on any line. These brief session appearances will be moved from the normal member history into a special section just for sessions. Note that <em>ongoing</em> sessions should be treated as normal bands.
						<br />
						<span class="any__note" style="vertical-align:middle;">/sugar forkful/ | Session A<br />/Dali/ | Session B, Session C </span> → <span style="vertical-align:middle;">bands:</span> <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/sugar-forkful/">SUGAR FORKFUL</a><br /><a class="artist" href="/artists/dali/">Dali</a></span> <span style="vertical-align:middle;">sessions:</span> <span class="any__note" style="vertical-align:middle;">Session A, Session B, Session C</span>
					</li>
					<li>
						A single blank line at the end of the band history indicates that the musician's current status is unknown. <span class="symbol__error">Only mark a musician <span class="any__note">(retired)</span> if this has been confirmed somewhere.</span><br />
						<span class="any__note" style="vertical-align:middle;">/dali/<br />&nbsp;</span> → <span class="any__note" style="vertical-align:middle;"><a class="artist" href="/artists/dali/">Dali</a><br />(unknown)</span>
					</li>
					<li>
						The musician's band history (and other details) can be edited later by editing one of the bands to which the musician belongs.
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>