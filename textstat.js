/*
Copyright (C) 2008-2012 Alexander Müller, (webmaster AT keyword-statistics DOT net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
function Keyword (key, count, density) {
	var _key = typeof key == 'string' ? key : '';
	var _count = typeof count == 'number' ? parseInt (count) : 0;
	var _density = typeof density == 'number' ? parseFloat (density) : 0;

	this.getKey = function () { return _key };
	this.setKey = function (key) { _key = typeof key == 'string' ? key : '' };
	this.getCount = function () { return _count };
	this.setCount = function (count) { _count = typeof count == 'number' ? parseInt (count) : 0 };
	this.getDensity = function () { return _density };
	this.setDensity = function (density) { _density = typeof density == 'number' ? Math.floor (parseFloat (density) * 100) / 100 : 0 };
	this.toString = function () { return _count + ' x ' + _key + ' - ' + _density + '%'; };
}
function keysort (a, b) { if (b.getCount () != a.getCount ()) return b.getCount () - a.getCount (); return b.getKey () < a.getKey () };

function TextStatistics (content, language) {
	var _content = typeof content == 'string' ? content : '';
	var _language = typeof language == 'string' && typeof stopwords[language] == 'object' ? language : 'en';
	var _words = content.toLowerCase ().replace (/<[^>]+>/g, '').replace (/\n/g, ' ').replace (/-/g, ' ').replace (/&ndash;/g, '-').replace (/&[br]dquo;/g, '"').replace (/&uuml;/g, 'ü').replace (/&ouml;/g, 'ö').replace (/&auml;/g, 'ä').replace (/&szlig;/g, 'ß').replace (/&[^;]+;/g, '|').replace (/[\|\(\)\{\}\[\]\/\*\.,;:!\?\"]/g, ' ').replace (/\s([^\s]\s)+/g, ' ').replace (/\s+/g, ' ').replace (/\s[^\s]$/g, '').replace (/^[^\s]\s/g, '').replace (/\s$/g, '').replace (/^\s/g, '');
	var _wordcount = _words.replace (/[^\s]+/g, 'W').replace (/\s/g, '').length;

	this.getContent = function () {
		return content.replace (/<[^>]+>/g, '').replace (/\n/g, ' ').replace (/&ndash;/gi, '-').replace (/&[br]dquo;/gi, '"').replace (/&uuml;/gi, 'ü').replace (/&ouml;/gi, 'ö').replace (/&auml;/gi, 'ä').replace (/&szlig;/gi, 'ß').replace (/&[^;]+;/g, '').replace (/\s+/g, ' ').replace (/\s+$/g, '').replace (/^\s+/g, '');
	};

	var _deleteStopWords = function (language) {
		var words = _words;
		if (typeof language == 'string' && typeof stopwords[language] == 'object') {
			for (var i = 0; i < stopwords[language].length; i++)
				words = words.replace (new RegExp (' (' + stopwords[language][i] + ' )+', 'g'), ' ').replace (new RegExp ('^' + stopwords[language][i] + '[ ]+', 'g'), '').replace (new RegExp ('[ ]+' + stopwords[language][i] + '$', 'g'), '');
		}
		return words;
	};

	var _getWords = function (dropStopWords, language) {
		var words = _words;
		if (typeof dropStopWords == 'boolean' && dropStopWords)
			if (typeof language == 'string' && typeof stopwords[language] == 'object')
				words = _deleteStopWords (language);
			else
				words = _deleteStopWords (_language);
		return words;
	};

	this.getLanguage = function () { return _language };

	this.getStats = function (phraseLength, dropStopWords, language) {
		if (typeof phraseLength != 'number' || phraseLength < 1)
			phraseLength = 1;
		var words = _getWords (dropStopWords, language);
		var wordcount = words.replace (/[^\s]+/g, 'W').replace (/\s/g, '').length;
		var w = new Array ();
		var keys = 0;
		if (phraseLength == 1) {
			var wordarray = words.split (' ').sort ();
			var p = '';
			var c = 0;
			while (wordarray.length) {
				var e = wordarray.shift ();
				if (p == '') { p = e; c = 1; }
				else if (p == e) c++;
				else { if (c > 1) { w.push (new Keyword (p, c)); keys+= c; } p = e; c = 1; }
			}
			if (p != '' && c > 1) {
				w.push (new Keyword (p, c));
				keys += c;
			}
		}
		else {
			words = words + ' ';
			var p = '';
			for (var i = 0; i < wordcount - phraseLength + 1; i++) {
				var reg = new RegExp ("^" + (i > 0 ? "([^ ]+ ){" + i + "}" : '') + "(([^ ]+ ){" + phraseLength + "})");
				var phrase = (i == 0 ? words.match (reg)[1] : words.match (reg)[2]);
				var c = (' ' + words).replace (new RegExp (" " + phrase, "g"), '°').replace (/[^°]/g, '').length;
				phrase = phrase.replace (/ $/, '');
				if (c > 1) 
					if (!p.match (new RegExp (phrase + '°'))) {
						p = p + phrase + '°';
						keys += c;
						w.push (new Keyword (phrase, c));
					}
			}
		}
		w.sort (keysort);
		for (var i = 0; i < w.length; i++)
			w[i].setDensity (Math.floor (w[i].getCount () * phraseLength * 10000 / wordcount) / 100);
		return { 'keycount': keys, 'different': w.length, 'keys': w };
	};

	this.getDifferentWordCount = function (dropStopWords, language) {
		var words = _getWords (dropStopWords, language);
		var wordarray = words.split (' ').sort ();
		var p = '';
		var c = 0;
		while (wordarray.length) {
			var e = wordarray.shift ();
			if (p != e) { c++; p = e; }
		}
		return c;
	};

	this.getStopWordCount = function (language) {
		if (typeof language == 'string' && typeof stopwords[language] == 'object')
			var wordcount = _deleteStopWords (language).replace (/[^\s]+/g, 'W').replace (/\s/g, '').length;
		else if (typeof language == 'undefined' && typeof stopwords[_language] == 'object')
			var wordcount = _deleteStopWords (_language).replace (/[^\s]+/g, 'W').replace (/\s/g, '').length;
		return wordcount ? _wordcount - wordcount : 0;
	};

	this.getWordCount = function (dropStopWords, language) {
		var wordcount = _wordcount;
		if (typeof dropStopWords == 'boolean' && dropStopWords) {
			if (typeof language == 'string' && typeof stopwords[language] == 'object')
				wordcount = _deleteStopWords (language).replace (/[^\s]+/g, 'W').replace (/\s/g, '').length;
			else if (typeof language == 'undefined' && typeof stopwords[_language] == 'object')
				wordcount = _deleteStopWords (_language).replace (/[^\s]+/g, 'W').replace (/\s/g, '').length;
		}
		return wordcount;
	};

	this.getKeywordList = function (count) {
		if (typeof count != 'number')
			count = 5;
		else
			if (count < 1)
				return '';
		var stats = this.getStats (1, true);
		var keys = '';
		for (var i = 0; i < (stats.keys.length < count ? stats.keys.length : count); i++)
			keys += (i > 0 ? ',' : '') + stats.keys[i].getKey ();
		return keys;
	};
};

