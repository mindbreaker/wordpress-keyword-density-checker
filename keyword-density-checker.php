<?php
/*
Plugin Name: SEO-Tool - Keyword Density Checker
Plugin URI: http://www.keyword-statistics.net/wordpress-keyword-density-checker.html
Description: The SEO-Tool Keyword Density Checker is generating the statistics part (including the meta keywords suggestion) of the <a href="http://www.keyword-statistics.net/wordpress-plugin.html">Keyword Statistics Plugin</a> only. Densities of the used keywords and 2-/3-word phrases will be calculated while writing the content. No meta informations will be embedded into your blogs pages. This is for those of you who use other SEO plugins but also want an overview about the keywords and phrases in content &ndash; without the overhead of generating meta informations while serving the blogs pages.
Version: 1.1.2
Author: Alexander Müller
Author URI: http://www.keyword-statistics.net
*/
/*
Copyright (C) 2009-2012 Alexander Müller, (webmaster AT keyword-statistics DOT net)

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

class KeywordDensityChecker {
	var	$plugin_version,
		$plugin_dir;

	function KeywordDensityChecker () {
		$this->plugin_version = '1.1.2';
		$this->plugin_dir = basename (dirname (__FILE__));
		// language file
		load_plugin_textdomain ('keyword-density-checker');
		if (is_admin ()) {
			add_action ('admin_init', array ($this, 'init_settings'));
		}
		add_action ('admin_menu', array ($this, 'keyword_density_checker'));
		// integrate additional options menu
		add_action ('admin_menu', array ($this, 'additional_options_menu'));
		// for saving post-specific settings
		add_action ('save_post', array ($this, 'update_metadata'));
		add_action ('admin_head', array ($this, 'admin_header'));
		add_action ('admin_footer', array ($this, 'admin_footer'));
	}

	// get plugins configuration
	function get_plugin_configuration () {
		global $wp_version;
		if (substr ($wp_version, 0, 3) >= '2.7')
			// WP 2.7+
			$options = get_option ('keyword_density_checker_configuration');
		else {
			$options = array ();
			$options['version'] = get_option ('kdc_version');
			$options['default_language'] = get_option ('kdc_default_language');
			$options['filter_stopwords'] = get_option ('kdc_filter_stopwords');
			$options['max_list_items'] = get_option ('kdc_max_list_items');
			$options['automatic_update'] = get_option ('kdc_automatic_update');
			$options['update_interval'] = get_option ('kdc_update_interval');
			$options['2word_phrases'] = get_option ('kdc_2word_phrases');
			$options['3word_phrases'] = get_option ('kdc_3word_phrases');
			$options['meta_keywords_count'] = get_option ('kdc_meta_keywords_count');
			$options['max_keywords_count'] = get_option ('kdc_max_keywords_count');
			$options['max_keywords_length'] = get_option ('kdc_keywords_length');
			$options['authors_can_change_content_language'] = get_option ('kdc_authors_can_change_content_language');
			$options['authors_can_disable_stopword_filter'] = get_option ('kdc_authors_can_disable_stopword_filter');
		}
		return $options;
	}

	// update plugins configuration
	function set_plugin_configuration ($options) {
		global $wp_version;
		if (substr ($wp_version, 0, 3) >= '2.7')
			update_option ('keyword_density_checker_configuration', $options);
		else
			foreach ($options as $key => $value)
				update_option ('kdc_' . $key, $value);
	}

	// Plugin options
	function init_settings () {
		if (function_exists ('register_setting')) {
			// for WP 2.7+
			if (!get_option('keyword_density_checker_configuration')) {
				// set default or import configuration
				$default_configuration = array (
					'version' => get_option('kdc_version') ? get_option('kdc_version') : $this->plugin_version,
					'default_language' => get_option('kdc_default_language') ? get_option('kdc_default_language') : 'en',
					'filter_stopwords' => get_option('kdc_filter_stopwords') ? get_option('kdc_filter_stopwords') : 1,
					'max_list_items' => get_option('kdc_max_list_items') ? get_option('kdc_max_list_items') : 5,
					'automatic_update' => get_option('kdc_automatic_update') ? get_option('kdc_automatic_update') : 1,
					'update_interval' => get_option('kdc_update_interval') ? get_option('kdc_update_interval') : 5,
					'2word_phrases' => get_option('kdc_2word_phrases') ? get_option('kdc_2word_phrases') : 1,
					'3word_phrases' => get_option('kdc_3word_phrases') ? get_option('kdc_3word_phrases') : 1,
					'meta_keywords_count' => get_option('kdc_meta_keywords_count') ? get_option('kdc_meta_keywords_count') : 8,
					'max_keywords_count' => get_option('kdc_max_keywords_count') ? get_option('kdc_max_keywords_count') : 12,
					'max_keywords_length' => get_option('kdc_max_keywords_length') ? get_option('kdc_keywords_length') : 80,
					'authors_can_change_content_language' => get_option('kdc_authors_can_change_content_language') ? get_option('kdc_authors_can_change_content_language') : 0,
					'authors_can_disable_stopword_filter' => get_option('kdc_authors_can_disable_stopword_filter') ? get_option('kdc_authors_can_disable_stopword_filter') : 0
				);
				add_option ('keyword_density_checker_configuration', $default_configuration);
				// drop older configurations
				delete_option ('kdc_version');
				delete_option ('kdc_default_language');
				delete_option ('kdc_filter_stopwords');
				delete_option ('kdc_max_list_items');
				delete_option ('kdc_automatic_update');
				delete_option ('kdc_update_interval');
				delete_option ('kdc_2word_phrases');
				delete_option ('kdc_3word_phrases');
				delete_option ('kdc_meta_keywords_count');
				delete_option ('kdc_max_keywords_count');
				delete_option ('kdc_keywords_length');
				delete_option ('kdc_authors_can_change_content_language');
				delete_option ('kdc_authors_can_disable_stopword_filter');
			}
			register_setting ('plugin_options', 'keyword_density_checker_configuration');
		}
		else {
			// and for older versions
			if (!get_option('kdc_default_language')) {
				add_option ('kdc_version', $this->plugin_version);
				add_option ('kdc_default_language', 'en');
				add_option ('kdc_filter_stopwords', 1);
				add_option ('kdc_max_list_items', 5);
				add_option ('kdc_automatic_update', 1);
				add_option ('kdc_update_interval', 5);
				add_option ('kdc_2word_phrases', 1);
				add_option ('kdc_3word_phrases', 1);
				add_option ('kdc_meta_keywords_count', 8);
				add_option ('kdc_max_keywords_count', 12);
				add_option ('kdc_keywords_length', 80);
				add_option ('kdc_authors_can_change_content_language', 0);
				add_option ('kdc_authors_can_disable_stopword_filter', 0);
			}
		}
	}

	function is_author_not_admin () {
		global $current_user;
		$rval = $current_user->caps['author'] == 1 && $current_user->caps['administrator'] != 1;
		return $rval;
	}

	function is_contributor () {
		global $current_user;
		return $current_user->caps['contributor'] == 1 && $current_user->caps['administrator'] != 1;
	}

	// update the metadata
	function update_metadata () {
		global $post;

		// don't change meta informations if we are autosaving
		if (!$_POST['autosave']) {
			// in quick edit mode we have to leave already defined metadata (if there are some)
			if (!$_POST['kdclang'])
				// let's get out here without doing anything with meta informations
				return;
			$meta = array ('lang' => trim (htmlspecialchars (strip_tags ($_POST['kdclang']))));
			add_post_meta ($_POST['post_ID'], 'kdc_metadata', $meta, true) or update_post_meta ($_POST['post_ID'], 'kdc_metadata', $meta);
		}
	}

	// Plugin Output
	function post_keyword_density_checker () {
		global $post;
		$meta = get_post_meta ($post->ID, 'kdc_metadata', true);
		$options = $this->get_plugin_configuration (); ?>
		<table style="width:100%">
			<tr>
				<td style="width:25%">
					<label for="kdclang"><?php _e('Language', 'keyword-density-checker') ?>:</label>
					<?php if ((($this->is_author_not_admin() || $this->is_contributor()) && intval ($options['authors_can_change_content_language']) != 1)) { ?>
					<input type="hidden" name="kdclang" id="kdclang" value="<?php echo $meta['lang'] ? $meta['lang'] : $options['default_language'] ?>" />
					<select disabled="disabled" id="kdclang_view" name="kdclang_view" onchange="kdc_updateTextInfo()">
					<?php } else { ?>
					<select id="kdclang" name="kdclang" onchange="kdc_updateTextInfo()">
					<?php } ?>
						<option value="en" <?php echo $meta['lang'] == 'en' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'en' ? 'selected="selected"' : '' ?>>en</option>
						<option value="bg" <?php echo $meta['lang'] == 'bg' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'bg' ? 'selected="selected"' : '' ?>>bg</option>
						<option value="cs-cz" <?php echo $meta['lang'] == 'cs-cz' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'cs-cz' ? 'selected="selected"' : '' ?>>cs-cz</option>
						<option value="da" <?php echo $meta['lang'] == 'da' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'da' ? 'selected="selected"' : '' ?>>da</option>
						<option value="de" <?php echo $meta['lang'] == 'de' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'de' ? 'selected="selected"' : '' ?>>de</option>
						<option value="es" <?php echo $meta['lang'] == 'es' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'es' ? 'selected="selected"' : '' ?>>es</option>
						<option value="fr" <?php echo $meta['lang'] == 'fr' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'fr' ? 'selected="selected"' : '' ?>>fr</option>
						<option value="hu" <?php echo $meta['lang'] == 'hu' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'hu' ? 'selected="selected"' : '' ?>>hu</option>
						<option value="nl" <?php echo $meta['lang'] == 'nl' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'nl' ? 'selected="selected"' : '' ?>>nl</option>
						<option value="pl" <?php echo $meta['lang'] == 'pl' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'pl' ? 'selected="selected"' : '' ?>>pl</option>
						<option value="pt-br" <?php echo $meta['lang'] == 'pt-br' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'pt-br' ? 'selected="selected"' : '' ?>>pt-br</option>
						<option value="sk" <?php echo $meta['lang'] == 'sk' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'sk' ? 'selected="selected"' : '' ?>>sk</option>
						<option value="tr" <?php echo $meta['lang'] == 'tr' ? 'selected="selected"' : !$meta['lang'] && $options['default_language'] == 'tr' ? 'selected="selected"' : '' ?>>tr</option>
					</select>
				</td>
				<td style="width:25%">
					<label for="kdcfilter"><?php _e('Filter stopwords', 'keyword-density-checker') ?>:</label>
					<input <?php echo (($this->is_author_not_admin() || $this->is_contributor()) && intval ($options['authors_can_disable_stopword_filter']) != 1 ? 'disabled="disabled"' : '') ?>type="checkbox" name="kdcfilter" id="kdcfilter" onchange="kdc_updateTextInfo()" value="kdc_filter" <?php echo $options['filter_stopwords'] == 1 ? 'checked="checked"' : '' ?> />
				</td>
				<td style="width:25%">
					<label for="kdclines"><?php _e('Show first', 'keyword-density-checker') ?>:</label>
					<select id="kdclines" name="kdclines" onchange="kdc_updateTextInfo()">
						<?php for ($i = 1; $i < 11; $i++) echo '<option value="' . $i . '" ' . ($options['max_list_items'] == $i ? 'selected="selected"' : '') . '>' . $i . '</option>'; ?>
					</select>
				</td>
				<td><div class="submit"><input class="right" type="button" name="kdcupdate" onclick="kdc_updateTextInfo()" value=" <?php _e('Update') ?> "/></div></td>
			</tr>
		</table>
		<?php if ($options['automatic_update']) echo '<script type="text/javascript">var kdc_updateInterval = window.setInterval (\'kdc_updateTextInfo()\', ' . $options['update_interval'] . '000);</script>' ?>
		<hr />
		<div id="kdcstats"></div>
		<?php
	}

	function post_keyword_density_checker_div () {
		echo '<div class="dbx-b-ox-wrapper">' .
		     '<fieldset id="keyworddensitychecker" class="dbx-box">' .
		     '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' . 
		     __('Keyword Density Checker', 'keyword-density-checker') . "</h3></div>" .   
		     '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
		$this->post_keyword_density_checker ();
		echo "</div></div></fieldset></div>";
	}

	function keyword_density_checker () {
		if (function_exists ('add_meta_box')) {
			// only works with WP 2.5+
			add_meta_box ('keyworddensitycheckerbox', __('Keyword Density Checker', 'keyword-density-checker'), array ($this, 'post_keyword_density_checker'), 'post', 'normal', 'core');
			add_meta_box ('keyworddensitycheckerbox', __('Keyword Density Checker', 'keyword-density-checker'), array ($this, 'post_keyword_density_checker'), 'page', 'normal', 'core');
		} else {
			// older versions
			add_action ('dbx_post_advanced', array ($this, 'post_keyword_density_checker_div'));
			add_action ('dbx_page_advanced', array ($this, 'post_keyword_density_checker_div'));
		}
	}

	// add pages for plugins additional options
	function additional_options_menu () {
		// create a new submenu for the different options
		add_menu_page (__('Keyword Density Checker', 'keyword-density-checker'), __('Keyword Density Checker', 'keyword-density-checker'), 'administrator', 'kdc-additional-menu');
		// keyword density checker
		add_submenu_page ('kdc-additional-menu', __('Keyword Density Checker', 'keyword-density-checker'), __('Keyword Density Checker', 'keyword-density-checker'), 'administrator', 'kdc-additional-menu', array ($this, 'additional_options_keyword_density_checker'));
	}

	// settings for keyword density checker
	function additional_options_keyword_density_checker () {
		$options = $this->get_plugin_configuration ();
		if ($_POST['kdc_update_options'] == 1) {
			if (!wp_verify_nonce ($_POST['kdc_configuration_nonce'], 'kdc_configuration_nonce')) {
				$err = 1;
				$errmsg = __('Nonce verification failed:', 'keyword-density-checker') . ' ' . __('Error while attempting to save plugin configuration!', 'keyword-density-checker');
			}
			else {
				$options['authors_can_change_content_language'] = $options['authors_can_disable_stopword_filter'] = $options['filter_stopwords'] = $options['automatic_update'] = $options['2word_phrases'] = $options['3word_phrases'] = 0;
				foreach ($_POST as $key => $value) {
					switch ($key) {
						case 'kdc_filter_stopwords':
							$options['filter_stopwords'] = 1;
							break;
						case 'kdc_automatic_update':
							$options['automatic_update'] = 1;
							break;
						case 'kdc_2word_phrases':
							$options['2word_phrases'] = 1;
							break;
						case 'kdc_3word_phrases':
							$options['3word_phrases'] = 1;
							break;
						case 'kdc_authors_can_change_content_language':
							$options['authors_can_change_content_language'] = 1;
							break;
						case 'kdc_authors_can_disable_stopword_filter':
							$options['authors_can_disable_stopword_filter'] = 1;
							break;
						case 'kdc_max_list_items':
						case 'kdc_update_interval':
						case 'kdc_min_words':
							if (preg_match ('/[0-9]+/', $value))
								$options[substr ($key, 4)] = $value;
							else
								$err = 1;
							break;
						case 'kdc_default_language':
							if (preg_match ('/[a-z-]+/', $key))
								$options['default_language'] = $value;
							else
								$err = 1;
							break;
					}
				}
			}
			// show status message
			if (!isset ($err)) { 
				// update options in database if there was no input error
				$this->set_plugin_configuration ($options);
				?> <div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div> <?php
			}
			else { ?> <div class="error"><p><strong><?php echo __('Options not saved', 'keyword-density-checker') . (isset ($errmsg) ? ' - ' . $errmsg : '!'); ?></strong></p></div> <?php }
		} ?>
		<div class="wrap"><div id="icon-options-general" class="icon32"><br /></div>
			<h2><?php _e('Keyword Density Checker', 'keyword-density-checker') ?></h2>
			<form name="kdc_options" method="post" action="">
				<input type="hidden" name="kdc_update_options" value="1" />
				<input type="hidden" name="kdc_configuration_nonce" value="<?php echo wp_create_nonce ('kdc_configuration_nonce') ?>" />
				<table class="form-table">
					<tr><td colspan="2"><span class="description"><?php echo __('With these options you can configure the keyword density checker. You can set a stopwords filter and in which iterval the statistics will be generated. These settings are affecting the statistics shown at edit page/post only.', 'keyword-density-checker') ?></span></td></tr>
					<tr valign="top">
						<th scope="row"><?php _e('Default language', 'keyword-density-checker') ?></th>
						<td>
							<select name="kdc_default_language">
								<option value="en" <?php echo $options['default_language'] == 'en' ? ' selected="selected"' : '' ?>>en</option>
								<option value="bg" <?php echo $options['default_language'] == 'bg' ? ' selected="selected"' : '' ?>>bg</option>
								<option value="cs-cz" <?php echo $options['default_language'] == 'cs-cz' ? ' selected="selected"' : '' ?>>cs-cz</option>
								<option value="da" <?php echo $options['default_language'] == 'da' ? ' selected="selected"' : '' ?>>da</option>
								<option value="de" <?php echo $options['default_language'] == 'de' ? ' selected="selected"' : '' ?>>de</option>
								<option value="es" <?php echo $options['default_language'] == 'es' ? ' selected="selected"' : '' ?>>es</option>
								<option value="fr" <?php echo $options['default_language'] == 'fr' ? ' selected="selected"' : '' ?>>fr</option>
								<option value="hu" <?php echo $options['default_language'] == 'hu' ? ' selected="selected"' : '' ?>>hu</option>
								<option value="nl" <?php echo $options['default_language'] == 'nl' ? ' selected="selected"' : '' ?>>nl</option>
								<option value="pl" <?php echo $options['default_language'] == 'pl' ? ' selected="selected"' : '' ?>>pl</option>
								<option value="pt-br" <?php echo $options['default_language'] == 'pt-br' ? ' selected="selected"' : '' ?>>pt-br</option>
								<option value="sk" <?php echo $options['default_language'] == 'sk' ? ' selected="selected"' : '' ?>>sk</option>
								<option value="tr" <?php echo $options['default_language'] == 'tr' ? ' selected="selected"' : '' ?>>tr</option>
							</select>
							<span class="description"><?php _e('Default language for filtering stopwords', 'keyword-density-checker') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Filter stopwords', 'keyword-density-checker') ?></th>
						<td>
							<input type="checkbox" name="kdc_filter_stopwords" value="1" <?php echo $options['filter_stopwords'] == 1 ? 'checked="checked"' : '' ?> />
							<span class="description"><?php _e('Activate the stopword-filter by default when post/page-editor will be opened', 'keyword-density-checker') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Items in keyword lists', 'keyword-density-checker') ?></th>
						<td>
							<select name="kdc_max_list_items">
								<?php for ($i = 1; $i < 11; $i++) echo '<option value="' . $i . '" ' . ($options['max_list_items'] == $i ? 'selected="selected"' : '') . '>' . $i . '</option>'; ?>
							</select>
							<span class="description"><?php _e('Display this number of the most common keywords / key-phrases', 'keyword-density-checker') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Automatic Update', 'keyword-density-checker') ?></th>
						<td>
							<input type="checkbox" name="kdc_automatic_update" value="1" <?php echo $options['automatic_update'] == 1 ? 'checked="checked"' : '' ?> /><br/>
							<span class="description"><?php _e('Should the statistics refresh automatically? Turn off this option, if you have got slow JavaScript-Performance and use the button in the various edit-views instead!', 'keyword-density-checker') ?></span><br/>
							<?php _e('every', 'keyword-density-checker') ?> <select name="kdc_update_interval">
								<?php for ($i = 1; $i < 11; $i++) echo '<option value="' . $i . '" ' . ($options['update_interval'] == $i ? 'selected="selected"' : '') . '>' . $i . '</option>'; ?>
							</select> <?php _e('seconds', 'keyword-density-checker') ?>
							<span class="description"><?php _e('Number of seconds between the updates', 'keyword-density-checker') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Minimum number of words per post or page', 'keyword-density-checker') ?></th>
						<td>
							<select name="kdc_min_words">
								<?php for ($i = 50; $i < 500; $i+= 10) echo '<option value="' . $i . '" ' . ($options['min_words'] == $i ? 'selected="selected"' : '') . '>' . $i . '</option>'; ?>
							</select><br/>
							<span class="description"><?php _e('If there is no content it doesn\'t make sense to generate meta informations from it. Here you can set the minimum number of words in a post or page that should be reached before keywords and description meta will be generated.', 'keyword-density-checker') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Keyword Phrases', 'keyword-density-checker') ?></th>
						<td>
							<input type="checkbox" name="kdc_2word_phrases" value="1" <?php echo $options['2word_phrases'] == 1 ? 'checked="checked"' : '' ?> /> <?php _e('show 2-word phrases', 'keyword-density-checker') ?><br/>
							<input type="checkbox" name="kdc_3word_phrases" value="1" <?php echo $options['3word_phrases'] == 1 ? 'checked="checked"' : '' ?> /> <?php _e('show 3-word phrases', 'keyword-density-checker') ?><br/>
							<span class="description"><?php _e('Switching on or off statistics for 2- and 3-word phrases (if your browser\'s JavaScript-Engine ist too slow and it is disturbing while writing)', 'keyword-density-checker') ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Authors can', 'keyword-density-checker') ?></th>
						<td>
							<input type="checkbox" name="kdc_authors_can_change_content_language" value="1" <?php echo $options['authors_can_change_content_language'] == 1 ? 'checked="checked"' : '' ?> /> <?php _e('change the content language', 'keyword-density-checker') ?><br/>
							<input type="checkbox" name="kdc_authors_can_disable_stopword_filter" value="1" <?php echo $options['authors_can_disable_stopword_filter'] == 1 ? 'checked="checked"' : '' ?> /> <?php _e('switch status of the stopwords-filter', 'keyword-density-checker') ?><br/>
						</td>
					</tr>
				</table>
				<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update') ?>" /></p>
			</form>
		</div> <?php
	}

	// Header for stylesheets and scripts
	function admin_header () {
		global $wp;
		?>
		<script src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/stopwords.js" type="text/javascript"></script>
		<script src="../wp-content/plugins/<?php echo $this->plugin_dir ?>/textstat.js" type="text/javascript"></script>
		<style type="text/css">
		/* <![CDATA[ */
		#kdcstats { font-size:13px; color:#333 line-height:12px }
		#kdcstats table { margin-top:5px; margin-bottom:1em; width:100%; background-color:#f9f9f9; color:#333; line-height:12px; border:1px solid #f1f1f1 }
		#kdcstats th { background-color:#f1f1f1; padding:6px 1em; }
		#kdcstats td { padding:2px 0.5em; }
		#kdcstats u { font-weight:bold }
		/* ]]> */
		</style>
		<?php
	}

	// Footer
	function admin_footer () {
		global $post;
		$meta = get_post_meta ($post->ID, 'kdc_metadata', true);
		$options = $this->get_plugin_configuration ();?>
		<script type="text/javascript">
		/* <![CDATA[ */
		function kdc_updateTextInfo () {
			if (!document.getElementById ('kdclang').value)
				return;
			var lang = document.getElementById ('kdclang').value;
			var textfield = document.getElementById ('content');
			if (!textfield || typeof textfield != 'object' || textfield.type != 'textarea')
				return;
			if (!typeof lang == 'string')
				return;
			if (textfield.lang)
				if (typeof stopwords[textfield.lang] == 'object')
					language = textfield.lang;
			// output template
			var template = '<u><?php _e('Text Statistics', 'keyword-density-checker') ?></u>' +
				       '<table>' +
				       '<thead><tr><th>' + (document.getElementById ('kdcfilter').checked ? '<?php _e('Total Words with / without Stopwords', 'keyword-density-checker') ?>' : '<?php _e('Total Words', 'keyword-density-checker') ?>') + '</th><th>' + (document.getElementById ('kdcfilter').checked ? '<?php _e('Different Words with / without Stopwords', 'keyword-density-checker') ?>' : '<?php _e('Different Words', 'keyword-density-checker') ?>') + '</th><th><?php _e('Stopwords', 'keyword-density-checker') ?></th></tr></thead>' +
				       '<tbody><tr><td style="text-align:center">[WORDCOUNT]' + (document.getElementById ('kdcfilter').checked ? ' / [WORDCOUNT_FILTERED]' : '') +'</td><td style="text-align:center">[WORDCOUNT_DIFFERENT]' + (document.getElementById ('kdcfilter').checked ? ' / [WORDCOUNT_DIFFERENT_FILTERED]' : '') + '</td><td style="text-align:center">[WORDCOUNT_STOPWORDS]</td></tr></tbody>' +
				       '</table>' +
				       '[KEYS' + (document.getElementById ('kdcfilter').checked ? '_FILTERED' : '') + ':1:' + document.getElementById ('kdclines').value + ']' +
				       <?php if ($options['2word_phrases'] == 1) echo '\'[KEYS\' + (document.getElementById (\'kdcfilter\').checked ? \'_FILTERED\' : \'\') + \':2:\' + document.getElementById (\'kdclines\').value + \']\' +'; ?>
				       <?php if ($options['3word_phrases'] == 1) echo '\'[KEYS\' + (document.getElementById (\'kdcfilter\').checked ? \'_FILTERED\' : \'\') + \':3:\' + document.getElementById (\'kdclines\').value + \']\' +'; ?>
				       '<u><?php _e('Meta Keywords Suggestion', 'keyword-density-checker') ?></u>: [KEYWORDS:<?php echo $options['meta_keywords_count'] ?>]';
			// replace template variables
			// do we have an instance of tinyMCE?
			if (typeof tinyMCE != 'undefined' && tinyMCE.activeEditor)
				// need to save the content of editor before we can read it
				tinyMCE.triggerSave();
			// get content to analyze out of the textarea, filter caption-blocks and any other shortcodes before analysis
			var t = new TextStatistics (textfield.value.replace (/\[caption.*caption=\"([^"]*)\"[^\]]*](.*)\[\/caption\]/ig, " $1 $2 ").replace (/\[[^\]]+\/?\]/ig, ""), lang);
			if (template.match (/\[WORDCOUNT\]/ig))
				template = template.replace (/\[WORDCOUNT\]/ig, t.getWordCount ());
			if (template.match (/\[WORDCOUNT_FILTERED\]/ig))
				template = template.replace (/\[WORDCOUNT_FILTERED\]/ig, t.getWordCount (true));
			if (template.match (/\[WORDCOUNT_DIFFERENT\]/ig))
				template = template.replace (/\[WORDCOUNT_DIFFERENT\]/ig, t.getDifferentWordCount ());
			if (template.match (/\[WORDCOUNT_DIFFERENT_FILTERED\]/ig))
				template = template.replace (/\[WORDCOUNT_DIFFERENT_FILTERED\]/ig, t.getDifferentWordCount (true));
			if (template.match (/\[WORDCOUNT_STOPWORDS\]/ig))
				template = template.replace (/\[WORDCOUNT_STOPWORDS\]/ig, t.getStopWordCount ());
			if (template.match (/\[LANGUAGE\]/ig))
				template = template.replace (/\[LANGUAGE\]/ig, t.getLanguage ());
			if (template.match (/\[KEYWORDS:([0-9]+)\]/ig)) {
				var keycount = parseInt (RegExp.$1);
				template = template.replace (/\[KEYWORDS:[0-9]+\]/ig, t.getKeywordList (keycount));
			}
			var startString = '';
			// replace key informations in template
			var m = template.match (/\[KEYS(_FILTERED)?:[1-5]:[0-9]+\]/ig);
			if (m)
				for (var i = 0; i < m.length; i++) {
					var filtered = m[i].match (/_FILTERED/i) ? true : false;
					m[i].match (/[^:]:([1-5]):([0-9]+)/);
					var keylength = parseInt (RegExp.$1);
					var maxkeys = parseInt (RegExp.$2);
					var stats = t.getStats (keylength, filtered);
					if (stats.keys.length > 0) {
						var table = '<u>' + (keylength == 1 ? '<?php _e('Single Words', 'keyword-density-checker') ?>' : (keylength == 2 ? '<?php _e('2 Word Phrases', 'keyword-density-checker') ?>' : '<?php _e('3 Word Phrases', 'keyword-density-checker') ?>')) + '</u>' +
							    '<table><thead><tr><th style="width:6em;"><?php _e('Count', 'keyword-density-checker') ?></th><th style="width:10em;"><?php _e('Density', 'keyword-density-checker') ?></th><th><?php _e('Keyword / Phrase', 'keyword-density-checker') ?></th></tr></thead>' +
							    '<tfoot><tr><td colspan="3"><u><?php _e('Total / Different', 'keyword-density-checker') ?></u>: ' + stats.keycount + ' / ' + stats.different + '</td></tr></tfoot><tbody>';
						for (var j = 0; j < (stats.keys.length < maxkeys ? stats.keys.length : maxkeys); j++)
							table += '<tr><td style="text-align:right">' + stats.keys[j].getCount () + '</td><td style="text-align:right">' + stats.keys[j].getDensity () + '</td><td>' + stats.keys[j].getKey () + '</td></tr>';
						table += '</tbody></table>';
						template = template.replace (m[i], table);
						// get most important keyword and 2-word keyword phrase for generation of the page description
						if (keylength < 3)
							startString += stats.keys[0].getKey () + (keylength == 1 ? '|' : '');
					}
					else
						template = template.replace (m[i], '<u>' + (keylength == 1 ? '<?php _e('No single keywords found', 'keyword-density-checker') ?>' : (keylength == 2 ? '<?php _e('No 2-word phrases found', 'keyword-density-checker') ?>' : '<?php _e('No 3-word phrases found', 'keyword-density-checker') ?>')) + '</u><br/><br/>');
				}
			// output of collected information
			document.getElementById ('kdcstats').innerHTML = template;
		}
		/* ]]> */
		</script>
		<?php
	}
}
$keywordDensityChecker = new KeywordDensityChecker ();
?>