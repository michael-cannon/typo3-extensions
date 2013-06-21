<?php

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');

class tx_commentsgravatar {
	var $conf = null;
	var $cbj = null;
	var $extKey = 'comments_gravatar';

	/**
	 * Do some basic initialisation
	 */
	function init() {
		// $this->pi_setPiVarDefaults();
		$this->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['comments_gravatar.'];
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
	}
	
	/**
	 * Hook called for each comment item
	 * You can set additional markers here
	 *
	 * @param	array		an array of markers coming from tt_news
	 * @param	array		the configuration coming from tt_news
	 * @return	array		modified marker array
	 */
	function comments_getComments($params, $pObj) {
		$this->init();

		$markers = $params['markers'];
		$markers['###GRAVATAR###'] = '';

		if ( ! $this->conf['enable'] ) return $markers;

		$row = $params['row'];

		$email = $row['email'];

		$name = array();
		$name[] = $row['firstname'];
		$name[] = $row['lastname'];
		$name = trim(implode(' ', $name));

		// generate gravatar
		$gravatarImage = $email;

		// borrowed from t3blog/pi1/widgets/blogList/class.blogList.php
		// Default needed if user don't have a gravatar and don't have a local pic, but email is stated
		$default 	= (! $this->conf['defaultIcon'])
			? 'http://'. $_SERVER['HTTP_HOST']. '/'.  t3lib_extMgm::siteRelPath($this->extKey). 'res/nopic_50_f.jpg'
			: $this->conf['defaultIcon'];

		$size 		= $this->conf['iconSize'] ? $this->conf['iconSize'] : 48;
		$style 		= $this->conf['style'] ? $this->conf['style'] : '';

		$grav_url 	= 'http://www.gravatar.com/avatar/'. md5($email).	'?d='. urlencode($default).'&amp;s='.intval($size).'&amp;r='.$this->conf['rating'];
		$gravatar 	= '<img src="'. $grav_url . '" alt="Gravatar: '. $name . '"
		title="Gravatar: '. $name . '. Visit gravatar.com for your own icon" height="' . $size . '" height="' . $size
		. '" class="comments_gravatar" style="'.$style.'" />';

		$markers['###GRAVATAR###'] = $gravatar;

		// allow safer tags in comments
		$comment	= $markers['###COMMENT_CONTENT###'];
		$search		= array(
			'&lt;pre&gt;'
			, '&lt;/pre&gt;'
			, '[pre]'
			, '[/pre]'
			, '&lt;code&gt;'
			, '&lt;/code&gt;'
			, '[code]'
			, '[/code]'
			, '&lt;blockquote&gt;'
			, '&lt;/blockquote&gt;'
			, '[blockquote]'
			, '[/blockquote]'
			, '&lt;blockquote&gt;'
			, '&lt;/blockquote&gt;'
			, '[blockquote]'
			, '[/blockquote]'
			, '&lt;p&gt;'
			, '&lt;/p&gt;'
			, '[p]'
			, '[/p]'
			, '&lt;ul&gt;'
			, '&lt;/ul&gt;'
			, '[ul]'
			, '[/ul]'
			, '&lt;ol&gt;'
			, '&lt;/ol&gt;'
			, '[ol]'
			, '[/ol]'
			, '&lt;li&gt;'
			, '&lt;/li&gt;'
			, '[li]'
			, '[/li]'
			, '&lt;b&gt;'
			, '&lt;/b&gt;'
			, '[b]'
			, '[/b]'
			, '&lt;i&gt;'
			, '&lt;/i&gt;'
			, '[i]'
			, '[/i]'
			, '&lt;cite&gt;'
			, '&lt;/cite&gt;'
			, '[cite]'
			, '[/cite]'
			, '&lt;h2&gt;'
			, '&lt;/h2&gt;'
			, '[h2]'
			, '[/h2]'
			, '&lt;h3&gt;'
			, '&lt;/h3&gt;'
			, '[h3]'
			, '[/h3]'
		);
		$replace	= array(
			'<pre>'
			, '</pre>'
			, '<pre>'
			, '</pre>'
			, '<code>'
			, '</code>'
			, '<code>'
			, '</code>'
			, '<blockquote>'
			, '</blockquote>'
			, '<blockquote>'
			, '</blockquote>'
			, '<p>'
			, '</p>'
			, '<p>'
			, '</p>'
			, '<ul>'
			, '</ul>'
			, '<ul>'
			, '</ul>'
			, '<ol>'
			, '</ol>'
			, '<ol>'
			, '</ol>'
			, '<li>'
			, '</li>'
			, '<li>'
			, '</li>'
			, '<b>'
			, '</b>'
			, '<b>'
			, '</b>'
			, '<i>'
			, '</i>'
			, '<i>'
			, '</i>'
			, '<cite>'
			, '</cite>'
			, '<cite>'
			, '</cite>'
			, '<h2>'
			, '</h2>'
			, '<h2>'
			, '</h2>'
			, '<h3>'
			, '</h3>'
			, '<h3>'
			, '</h3>'
		);
		$comment	= str_replace($search, $replace, $comment);
		$markers['###COMMENT_CONTENT###']	= $comment;

		return $markers;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments_gravatar/class.tx_commentsgravatar.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/comments_gravatar/class.tx_commentsgravatar.php']);
}

?>
