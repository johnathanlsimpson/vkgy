## Markdown basics

* “Markdown” is type of code that allows you to format your text when adding blog posts or comments.

 {*This band* is **awesome**. I had to [Google](https://google.com/) them.}

* Lists are easy, and you can easily separate sections with lines, if you want:

 ''* List\n* List\n\n---\n\n1. Another\n1. List,, ''→,, [[<ul class="ul--compact"><li>List</li><li>List</li></ul><hr /><ol class="ul--compact"><li>Another</li><li>List</li></ol>]]



## Linking artists, labels, and users

* [vkgy](https://vk.gy/) has its own special version of Markdown that has additional features.

 To generate an artist link, type its name in ''/slashes/,,, and the system will do the rest. In most areas, as soon as you type the first ''/,,, the system will offer autocomplete suggestions.

 Similarly, typing ''=,, will produce a list of labels, and typing ''@,, will produce a list of vkgy users.

 {@inartistic said that /orgel/ belonged to =matina=.}

* If you would like to link an artist but display a different name, you can follow the /slashes/ with the [display name] like so:

 {/inspire/ used to be called /inspire/[Ruin's;lave].}

 Please note that the [display name] must immediately follow the artist's name (if there is a space between them, it will not work).



## Embeds

* You can embed images, and give them a title if you'd like.

 ''![You can also leave the brackets blank, if you want.](https://vk.gy/images/4614-omikuji-group-shot.jpg),,

 ![You can also leave the brackets blank, if you want.](https://vk.gy/images/4614-omikuji-group-shot.jpg)

* Easily embed release information from vkgy, or tweets, or YouTube videos, just by pasting the link:

 ''https://vk.gy/releases/munou-na-lucid/18113/shinshoku-refrain/\n\nhttps://twitter.com/vkgy_/status/1114272537062588416\n\nhttps://www.youtube.com/watch?v=iRUXRpYY0JU,,



## Advanced: how linking works

* **Most users don't have to worry about this**, but if you're curious, this is how linking works.

 When you type ''/,, and select an artist, a pretty token with that artist's name will be shown in the textarea. However, the text that's *actually* added will look like this: ''(123)/band name/[optional display name],,.

 The ''(123),, portion is the most important part: the artist's ID. This is what's used by the database to generate the link. The ''/band name/,, portion is purely decorative, and is there for the editor's sake. And ''[optional display name],, is of course used to set an alternate display name.

 So with that being the case, you may occasionally encounter plain text that looks like ''(123),,―that just means that someone manually entered the band ID instead of using the handy dropdown.

 A similar pattern is followed for labels: ''{123}=label name=[optional display name],,.