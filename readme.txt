=== SEO-Tool - Keyword Density Checker ===
Contributors: alm
Donate link: http://www.keyword-statistics.net
Tags: post,admin,plugin,posts,google,page,seo,statistics,meta,keywords,density,yahoo,bing,stats,pages,edit,editor,author,content,text,analytics,keyword,statistic,meta keywords,search engines,search engine optimization,keyword density,language,multilingual,multilanguage
Requires at least: 1.5
Tested up to: 3.3.1
Stable tag: trunk

This SEO-Tool provides a keyword analyzer calculating the keyword density of the page/post content and generating statistics for keywords and phrases.

== Description ==

This SEO plugin is automatically generating an overview about the keyword densities of the used keywords and 2-/3-word keyphrases in the pages / posts content.
The statistics will be updated in a specific interval while the author is writing the text in edit-post and edit-page dialog.
These informations have informational character only - no meta information will be inserted into your blogs pages.
You can use the meta keyword suggestion at the bottom of the statistics to copy these informations into the input fields of your favourite SEO plugin.
Stopwords for different languages can be filtered out of the text before the keywords densities will be calculated.
The keyword density checker tool is a branch of the [keyword statistics plugin](http://www.keyword-statistics.net/wordpress-plugin.html).
If you don't need the meta stuff the keyword density checker is much faster &ndash; no code will be executed on ordinary pageviews.
The additional code is needed in page-/post-edit mode of admins and authors only.

**Features of the SEO-Tool:**

* Automatic calculation of the **keyword density** for single keywords and keyphrases (2- and 3-words) while writing the content
* Meta keywords suggestion depending on the pages/posts content
* Language specific stopword lists
* Optional filter for stopwords
* Update interval for the statistics generation can be changed and switched off
* Generation of statistics for 2- and 3-word phrases can be deactivated
* Definition of the default language and stopword filter status for authors
* Prevent authors and contributors from changing the content language and stopword filter status
* Uses nonces for plugins security

**Integrated stopwords for:**

* Brazilian Portoguese
* Bulgarian
* Czech
* Danish
* Dutch
* English
* French
* Hungarian
* German
* Polish
* Slovak
* Spanish
* Turkish

**Backend translations for:**

* English
* German

Read more about this SEO plugin on [keyword density checker](http://www.keyword-statistics.net/wordpress-keyword-density-checker.html) - [german](http://www.keyword-statistics.net/de/wordpress-keyworddichte-plugin.html)

== Screenshots ==

1. Settings for the plugin
2. Generated output for the keyword densities of the content in page-/post-edit

== Changelog ==

= 1.1.2 =
* Checked for compability with versions since 3.0.1 - Plugin still works fine

= 1.1.1 =
* Ignore shortcodes when analyzing the content. The text between the opening and closing part of a shortcode will still remain at the content. Only the caption shortcode will stay with its caption attribute.

= 1.1.0 =
* Integrated hungarian words for the stopword filter - Thanks to Németh Balázs

= 1.0.9 =
* Bulgarian wordlist filter - Thanks to Yassen Yotov

= 1.0.8 =
* Added slovak words for filtering stopwords - Thanks to Ondrej Tatliak

= 1.0.7 =
* New stopwords for czech language - Thanks to adyba

= 1.0.6 =
* Integration of danish wordlist

= 1.0.5 =
* Now the plugin comes with french stopwords

= 1.0.4 =
* New stopword list for polish language - Thanks to Michał Jankowski

= 1.0.3 =
* Added nonces for secure saving of plugins options

= 1.0.2 =
* Integrated spanish stopword list

= 1.0.1 =
* Added german translation of the plugins backend

= 1.0.0 =
* Initial release extracted from the current version of the keyword statistics plugin

== Installation ==

* Copy the folder `keyword-density-checker` to the plugins directory of your blog.
* Enable the plugin at your admin panel.
* Use the options panel to choose the settings you want.
