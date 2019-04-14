## Artist details

* ''description,, is used to *very* briefly identify/differentiate the band, e.g. “Last band of KISAKI, a trouble-prone goth-rock trio.”

* ''official links,, accepts plain URLs (official website, Twitter profiles, blogs, etc.), separated with line breaks.



## Artist tags

* Artists can be tagged and untagged by any user, but only editors can remove certain tags.

* ''exclusive,, is for artists whose profiles feature information that can't be found on other websites. Only use this tag if you did research and discovered new information.

* ''foreign,, is for any artists that are outside of Japan. See **[Adding artists](/documentation/add-artists/)** for criteria for foreign artists.

* ''non-visual,, should be used for any non-visual bands. See **[Adding artists](/documentation/add-artists/)** for criteria for non-vk artists.

* ''needs review,, can be used any time you're unsure about some of the information, or think there may be a mistake (please leave a comment explaning, if necessary).



## Label history

* Similar to a musician's band history, each line represents an era. If you want to link a label, type its ID (found on the label's page) enclosed in parentheses. For labels not in the database, just type the plain text name.

 ''(51),, ''→,, [Matina](/labels/matina/)

* If a band belongs to multiple labels at once, comma separate them. Notes can be used to differentiate them.

 For sublabels, link the parent label, then ''>,,, then link the sublabel.

 ''(51) > (52)\nABC Label, DEF Label (distribution),,

* A blank line indicates that the band was completely independent at that time. If the band manages themselves but formed a company to do so, treat that as a normal label.



## Artist biography

* The artist biography is completely text-based. There's a learning curve, but it's been optimized for non-Japanese speakers, has several features to increase efficiency, and automagically sets certain artist details.

* Each entry in the biography needs a date and some content (present-tense, active voice, full sentences, use **[Markdown](/documentation/markdown/)** to link artists or format text).

 ''1999-06-18 /Dali/ forms.,,

* Separate entries with a blank line. Multi-line entries are allowed, but each entry should basically be a single “thought”—if two distinct things happened on the same date, they should be two separate entries (albeit with the same date).

 ''1999-06-18 Bassist JUN joins.\n\nLater that day, he suddenly secedes.\n\n1999-06-18 The band's wigs are stolen after a live.,,

* Each entry gets a ''-tag,, at the end. These are generated automatically—check the preview section while typing—and you'll see them in the text next time you go to edit the artist.

 You can also manually set the tags for each entry (see **[Biography tags](/documentation/edit-artist/#biography-tags)**) by typing, at the end of the line, a hyphen followed by comma-separated tag(s).

 {1999-06-18 /Dali/ forms. -formation\n\n1999-06-17 Guitarist JUN joins, but sprains his ankle. -member,trouble}

* Entries do *not* have to be in a certain order; the system will auto-organize them based on ''-tag,, and date.

* Dates must be in the ''yyyy-mm-dd,, format, but you can use zeroes when you're unsure of the exact date.

 ''1999-11-31 Something happened.\n\n1999-12-00 Guitarist JUN secedes around this time.,,

* For quicker entry, you can type partial dates. If you only type the month and day, the system will assume that the event occurred in the same year as the event immediately before it. If you type only the day, it will assume the same year and month.

 ''1999-05-31 Guitarist JUN secedes.\n\n06-15 Bassist Junno secedes.\n\n16 Vocalist Jennu secedes.\n\n07-30 Roadie Juza secedes.,, ''→,, ''1999-05-31 Guitarist JUN secedes.\n\n1999-06-15 Bassist Junno secedes.\n\n1999-06-16 Vocalist Jennu secedes.\n\n1999-07-30 Roadie Juza secedes.,,

* If the band formed very recently, or an upcoming member change has been added to the bio, a news post may be automatically generated. In that case, you'll be notified about the blog post and will be able to edit it, if you want.



## Biography tags

### Normal tags

* ''-label,, The band joins, leaves, or starts a label.

* ''-media,, Magazine or radio appearances, fanclub publications, etc.

* ''-member,, A member joins, leaves, begins support, ends support, etc.

* ''-name,, A member *or* the artist itself changes its name.

 For band name changes, pronunciation will be pulled from entries with this tag. Like with ''-formation,,, type the pronunciation in katakana/hiragana in parentheses after the band link.

 ''2018-01-06 Guitarist JUN changes his name to JUNNA. -name\n\n2018-02-13 /Dali/ changes its name to /LAREINE/ (ラレーヌ). -name,,

* ''-trouble,, Bad events such as death, injury, arrest, etc.

* ''-live,, Generic information about live events. Unlike ''-schedule,, (see **[Editing live schedule](/documentation/edit-artist/#editing-live-schedule)**), these entries are *not* added to the band's live history section.

 ''2018-06-06 Twoman vs /Gackt/ is held. -live,,

* ''-other,, Anything that doesn't fit any other tags.

---

### Special tags

* ''-activity,, Change in activity, but not formation/disbandment. i.e. the band moves from Tokyo to Nagoya, or officially starts activity after having played secret lives.

* ''-formation,, When the band forms or re-forms. Will be highlighted on the artist page.

 The artist's pronunciation will be automatically pulled from entries with this tag. Pronunciation should be katakana/hiragana in parentheses after the band's link.

 Activity area is also pulled from these entries. Simply type “in XXX” at the end of the sentence, with XXX being the area (romaji or Japanese ok).

 Activity period is also based on these entries, but is calculated automatically.

 ''1999-06-18 /Dali/ (ダリ) forms in Nagoya. -formation,,

* ''-disbandment,, The artist disbands or otherwise stops activity. Will be highlighted on the artist page.

 This one should be concise; further details about the disbandment should be added in a separate entry.

 If the band used a different term than “disbandment” (e.g. “shutdown” or “sealed”), use that instead.

 Activity period is based on these entries, and is calculated automatically.

 ''2000-01-06 /Dali/ disbands -disbandment\n\n2000-01-06 Live is held, and due to all members seceding at once, the band breaks up.,,

* ''-lineup,, Use when listing the new lineup after formation or member change. (*Only* the lineup should go here; other information about the member change can go in a separate entry.)

 It should be formatted with the initial of the position, followed by the musician's name. Musicians should be comma-or-slash-separated.

 If the lineup changed multiple times within one timeframe, you can use line breaks and arrows. If you don't know a position, just use a ?. If you're unsure about the lineup, just slap (?) on there.

 ''1999-06-00 Guitarist JUN secedes. Later that month (date unknown), bassist Jan secedes. -member\n\n1999-06-00 V. Abel / G. Kain / B. Jan / ?. Ken\n\n↓\n\nV. Abel / G. Kain / ?. Ken -lineup\n\n1999-07-12 Ken may have seceded at this point (unconfirmed).\n\n1999-07-12 V. Abel / G. Kain (?) -lineup,,

* ''-setlist,, A slash-separated list of songs that were played at a live. Will format the text differently.

 ''1999-06-15 Metamorphose / Walk in the Rain / site of scaffold -lineup,,

* ''-release,, Almost never used manually; releases are automatically pulled from the database and given this tag. Anything tagged with this will appear in a different format.

 Note that every release must be added to the database, even if information about it is unknown or it was cancelled. Theoretically, there should never be a released mention in the artist's biography that isn't in the database.

* ''-schedule,, or ''-s,, A special tag that allows you to edit the band's live schedule directly from the biography. See **[Editing live schedule](/documentation/edit-artist/#editing-live-schedule)**.



## Editing live schedule

* The live schedule is edited via the biography section, but appears in its own section and will eventually be searchable. Entries in the live schedule automatically contribute to the “popularity” section.

 To add a live appearance, type the date, followed by the livehouse name. The city can be ommitted (or not), and you can type the name in either romaji or Japanese. In the preview you can see that the system will auto fill-in the city and official livehouse name.

 ''2018-01-30 rockmaykan\n\n2018-02-31 目黒鹿鳴館,,

* Live schedule entries get the ''-schedule,, tag, but you can usually ommit this and the system will fill it in instead. (You can also use ''-s,, as a shorter version of the tag, if you're tagging manually.)

* The system is set up to accept nicknames and misspellings for the most common livehouses. If the system doesn't recognize the livehouse you typed, it will warn you in the preview, and will change the tag to ''-live,, instead of ''-schedule,,.

 ''2018-01-30 rokumeikan\n\n2018-01-31 rockmaykan,,

* Here's a [list of livehouses in the database](/lives/livehouses/). Leave a comment somewhere or [hit up Discord](https://discord.gg/jw8jzXn) if you need a livehouse or livehouse nickname added.

 Note that non-livehouses (e.g. “in front of the H&amp;M in Shibuya”) will not be added. For those you'll have to manually type the full name and slap a ''-live,, tag on it.

* Live schedule entries must have an exact date. To increase speed, you can ommit the year and/or month, and the system will assume it was the same year and/or month as the previous entry.

 ''2018-01-30 rockmaykan\n\n02-01 club holiday\n\n05 muse\n\n06 vintage,, ''→,, ''2018-01-30 rockmaykan\n\n2018-02-01 club holiday\n\n2018-02-05 muse\n\n2018-02-06 vintage,,

* You can also list other artists that appeared at the live. To do this, follow the livehouse name with a hyphen and then type the artist names in a comma separated list, followed by the ''-schedule,, or ''-s,, tag. (You'll have to manually tag the entry in this case.)

 If the system recognizes any of the band names you typed, it will automatically add this live to that band's live history. Any bands *not* in the database will still be linked to that live within the database, and this information will eventually be searchable.

 ''2018-01-30 rockmaykan - Dali, Le vi-sage, Megaromania -s,,