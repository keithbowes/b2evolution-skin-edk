<?php
/* Functions to avoid redundant translations of core phrases */
function __($str)
{
	return T_($str);
}

function _s($str)
{
	return TS_($str);
}

function _t($str)
{
	return NT_($str);
}

function diaspora_share()
{
	global $cookie_path, $diaspora_pod;

	$nine_weeks = time() + 9 * 7 * 24 * 60 * 60;
	setcookie('Diaspora-Pod', $diaspora_pod,  $nine_weeks, $cookie_path);
	header('Location: ' . $diaspora_pod . '/bookmarklet?url=' . param('diaspora-url') . '&title=' . param('diaspora-title'));
	die();
}

function get_copyright($params = array())
{
	global $Blog, $Skin;
	global $first_item;

	$params = array_merge(
		array(
			'display' => TRUE,
			'license' => TRUE,
		),
		$params
	);

	$fmt = str_replace(
		array('(C)', '-'),
		array('©', '–'),
		$params['license'] ?
		# TRANS: Params: Start year, end year, license
		$Skin->T_('(C) %1$d-%2$d under %3$s') :
		# TRANS: Params: Start year, end year
		$Skin->T_('(C) %1$d-%2$d')
	);

	$func = $params['display'] ? 'printf' : 'sprintf';
	return $func($fmt, strftime('%Y', $first_item['post_datestart']), strftime('%Y'), get_license(array('display' => FALSE)));
}

/* Get the DB info about the first or last item of the current blog.
 * If you can think of a better way to do this, you're my hero.
 *
 * @param string The direction to go. Can be ASC (ascending) for the first item or DESC (descending) for the last item.
 * @return array An array containing the DB fields.
 */
function get_item($dir)
{
	global $Blog, $DB, $Item;
	$blogid = is_object($Item) && $Item->blog_ID ? $Item->blog_ID : is_object($Blog) && $Blog->ID ? $Blog->ID : -1;
	$blogslug = $Item ? $Item->urltitle : '';
	$categorytablename = is_object($Item) && is_object($Item->main_Chapter) ? $Item->main_Chapter->dbtablename : 'T_categories';
	$itemtablename = $Item ? $Item->dbtablename : 'T_items__item';
	if (!$categorytablename || !$itemtablename) return;
	$row = 0;
	// Here we iterate through the items until we can get an item with a category associated with the current blog
	while (true) {
		/* I do feel dirty about direct DB access (not exactly future-proof),
		 * but I see no alternative. */
		$item_data = $DB->get_row("SELECT post_datestart, post_ID, post_main_cat_ID, post_title, post_urltitle FROM $itemtablename ORDER BY UNIX_TIMESTAMP(post_datestart) $dir", ARRAY_A, $row);
		if (!is_valid_query($item_data))
			return NULL;
		$item_data['post_datestart'] = strtotime($item_data['post_datestart']);

		$cat_data = $DB->get_row("SELECT cat_parent_ID, cat_name, cat_blog_ID FROM $categorytablename WHERE cat_ID = " . $item_data['post_main_cat_ID'], ARRAY_A, 0);
		if (!is_valid_query($cat_data))
			return NULL;
		elseif ($cat_data['cat_blog_ID'] == $blogid)
		{
			if ($item_data['post_urltitle'] != $blogslug)
			{
				$item_data['post_urltitle'] = get_post_urltitle($dir, $row);
				return $item_data;
			}
			else
				return NULL;
		}

		$row++;
	}
}

function get_license($params = array())
{
	global $Skin;

	$params = array_merge(
		array(
			'display' => TRUE,
			'format' => 'html',
		),
		$params
	);

	$fmt ='<a rel="license" href="http://creativecommons.org/licenses/by/4.0/deed.' . preg_replace('/^([^-]+).*$/', '$1', locale_lang(FALSE)) . '"><span class="button" id="cc"><span id="cc-lic" title="' . $Skin->T_('Creative Commons') . '">' . $Skin->T_('C<span class="button-sf">reative </span>C<span class="button-sf">ommons</span>') . '</span><span class="button-sf"> </span><span id="cc-lim" title="' . $Skin->T_('Attribution, Sharealike license') . '">' . $Skin->T_('BY-SA') . '</span></span></a>';
	$func = $params['display'] ? 'printf' : 'sprintf';
	return $func(($params['format'] == 'html') ? $fmt : $Skin->T_('Creative Commons'));
}

function get_meta($Item)
{
	global $Skin;
	$flag = strpos($f = locale_flag($Item->locale, 'h10px', 'flag' , '', FALSE),
		'background-position') !== FALSE ? $f : '';

	echo $Skin->T_('Posted in ');
	$Item->categories();
	# TRANS: The last two %s are icons	
	printf($Skin->T_(' by <a href="%s">%s</a> on %s at %s %s %s'),
		$Item->get_creator_User()->url,
		$Item->get_creator_User()->firstname,
		$Item->get_issue_date(),
		$Item->get_issue_date(array('date_format' => locale_timefmt())),
		$flag,
		preg_replace('/(\s*alt=)"[^"]*"/', '$1""', $Item->get_edit_link(array('title' => '#')))
	);
}

function get_prevnext_item($which)
{
	global $disp;
	if ('single' == $disp)
	{
		global $MainList;
		if (!$MainList)
			init_MainList(1);

		return $MainList->get_prevnext_item($which);
	}

	return NULL;
}

function get_post_urltitle($dir = '', $row = 0)
{
	global $Blog, $DB, $Item;

	$blogid = is_object($Item) ? $Item->get_blog_ID() : is_object($Blog) ? $Blog->ID : -1;
	$blogslug = is_object($Item) ? $Item->urltitle : '';
	$categorytablename = is_object($Item) && is_object($Item->main_Chapter) ? $Item->main_Chapter->dbtablename : 'T_categories';
	$itemtablename = is_object($Item) && isset($Item->dbtablename) ? $Item->dbtablename : 'T_items__item';
	$postid = is_object($Item) && isset($Item->ID) ? $Item->ID : 0;

	if (!empty($dir))
		$item_data = $DB->get_row('SELECT post_datestart, post_ID, post_main_cat_ID, post_title, post_urltitle FROM ' . $itemtablename . ' ORDER BY UNIX_TIMESTAMP(post_datestart) ' . $dir, ARRAY_A, $row);
	elseif (is_object($Item))
		$item_data = $DB->get_row('SELECT post_datestart, post_ID, post_main_cat_ID, post_title, post_urltitle FROM ' . $itemtablename . ' WHERE post_ID=' . $postid, ARRAY_A, 0);
	else
		return '';

	if (!is_valid_query($item_data)) return NULL;
	$item_data['post_datestart'] = strtotime($item_data['post_datestart']);

	$cat_data = $DB->get_row('SELECT cat_parent_ID, cat_name, cat_blog_ID FROM ' . $categorytablename . ' WHERE cat_ID = ' . $item_data['post_main_cat_ID'], ARRAY_A, 0);
	if (!is_valid_query($cat_data)) return NULL;

	$pathinfo = $DB->get_row('SELECT cset_value from T_coll_settings WHERE cset_coll_ID = ' . $blogid . ' AND cset_name = \'single_links\'', ARRAY_A, 0);
	if (!is_valid_query($pathinfo)) return NULL;

	$cat_data['cat_name'] = strtolower($cat_data['cat_name']);

	switch ($pathinfo['cset_value'])
	{
		case 'param_num':
			$item_data['post_urltitle'] = '?p=' . $item_data['post_ID'];
			break;
		case 'param_title':
			$item_data['post_urltitle'] = '?title=' . $item_data['post_urltitle'];
		case 'short':
			// Do nothing
			break;
		case 'y':
			$item_data['post_urltitle'] = strftime('%Y/', $item_data['post_datestart']) . $item_data['post_urltitle'];
			break;
		case 'ym':
			$item_data['post_urltitle'] = strftime('%Y/%m/', $item_data['post_datestart']) . $item_data['post_urltitle'];
			break;
		case 'ymd':
			$item_data['post_urltitle'] = strftime('%Y/%m/%d/', $item_data['post_datestart']) . $item_data['post_urltitle'];
			break;
		case 'subchap':
			$item_data['post_urltitle'] = $cat_data['cat_name'] . '/' . $item_data['post_urltitle'];
			break;
		case 'chapters':
			if (isset($cat_data['cat_parent_ID']))
			{
				$parent_cat = $DB->get_row("SELECT cat_name FROM $categorytablename WHERE cat_ID = " . $cat_data['cat_parent_ID'], ARRAY_A, 0);
				$item_data['post_urltitle'] = $cat_data['cat_name'] . '/' . strtolower($parent_cat['cat_name']) .' /' . $item_data['post_urltitle'];
			}
			else
				$item_data['post_urltitle'] = $cat_data['cat_name'] . '/' . $item_data['post_urltitle'];
			break;
	}

	return $item_data['post_urltitle'];
}


function get_tinyurl()
{
	global $Item;
	global $disp;

	if (!is_object($Item)) 
	{
		global $MainList;
		if (is_object($MainList))
			$Item = $MainList->get_Item();
	}

	if (is_object($Item) && 'single' == $disp)
		return $Item->get_tinyurl();
	else
		return '';
}

function is_valid_query($result)
{
	return ($result !== FALSE && is_array($result) && count($result) > 0);
}

/* Show the footer */
function show_footer()
{
	global $Blog, $Plugins, $Skin;
	global $app_name, $app_version;

	// SkinEndHtmlBody hook -- could be used e.g. by a google_analytics plugin to add the javascript snippet here:
	$Plugins->trigger_event('SkinEndHtmlBody');
	modules_call_method( 'SkinEndHtmlBody' );
	$Blog->disp_setting( 'footer_includes', 'raw' );

	printf($Skin->T_('<div>Powered by <cite><a href="http://www.duckduckgo.com/?q=!+%1$s">%1$s</a> %2$s</cite>.</div>'), $app_name, $app_version);
	printf('<div id="copyright">%s</div>', get_copyright(array('display' => FALSE)));
}

global $baseurl, $collection_path;
$collection_path = parse_url($baseurl, PHP_URL_PATH);

global $content_type;

global $first_item, $last_item;
$first_item = get_item('ASC');
$last_item = get_item('DESC');

/* b2evolution's idea of prev and next seems backwards to me */
global $next_item, $prev_item;
$next_item = get_prevnext_item('prev');
$prev_item = get_prevnext_item('next');

?>