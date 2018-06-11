<?php
/**
 * This is the HTML header include template.
 *
 * For a quick explanation of b2evo 2.0 skins, please start here:
 * {@link http://manual.b2evolution.net/Skins_2.0}
 *
 * This is meant to be included in a page template.
 * Note: This is also included in the popup: do not include site navigation!
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $Skin;
global $app_name, $app_version, $xmlsrv_url;
global $basepath, $baseurl, $content_type, $io_charset;
global $first_item, $last_item, $next_item, $prev_item;

function edk_css_include()
{
	global $Skin;
	global $current_locale, $edk_base, $headlines, $io_charset, $locales;

	$alternate_styles = array(
		'classic' => array(
			'file' => $edk_base . 'css/classic.css',
			'title' => $Skin->T_('Classic Look'),
		),
		'clear' => array(
			'file' => $edk_base . 'css/clear.css',
			'title' => $Skin->T_('Clear Look'),
		),
		'one' => array(
			'file' => $edk_base . 'css/one.css',
			'title' => $Skin->T_('One-column Look'),
		),
		'transitional' => array(
			'file' => $edk_base . 'css/transitional.css',
			'title' => $Skin->T_('Transitional Look'),
		),
	);

	/* Main styles */
	require_css($edk_base . 'css/core.css', 'relative', NULL, 'all');
	require_css($edk_base . 'css/visual.css', 'relative', NULL, 'not speech');

	/* Sort the alternate styles based on locale */
	$locales_to_try = array(
		$locales[$current_locale]['charset'],
		str_replace('-', '_', $current_locale) . '.' . $io_charset,
		'en_US.' . $io_charset,
		'',
	);
	$old_locale = setlocale(LC_ALL, 0);
	setlocale(LC_ALL, $locales_to_try);
	uasort($alternate_styles, function($a, $b) { return strcmp($a['title'], $b['title']); });
	setlocale(LC_ALL, $old_locale);

	foreach ($alternate_styles as $style)
		require_css($style['file'], 'relative', $style['title'], 'not speech');

	/* Media-specific overrides */
	require_css($edk_base . 'css/print.css', 'relative', NULL, 'print');
	require_css($edk_base . 'css/speech.css', 'relative', NULL, 'speech');

	/* Don't embed style.css, as it doesn't exist in this theme */
	unset($headlines['style.css']);

	/* Don't embed the invalid minimal CSS file */
	unset($headlines['b2evo_base.bmin.css']);

	/* Determine the default style sheet from the Default-Style cookie if available.
	 * If not, use the above arrays. */
	if (isset($_COOKIE['Default-Style']))
		$default_style = $_COOKIE['Default-Style'];
	else
	{
		global $Session;
		if (!$Session->is_desktop_session())
			$default_style = $alternate_styles['one']['file'];
		else
			$default_style = $alternate_styles['clear']['file'];
	}

	/* Get the default style sheet array from the file name */
	foreach ($alternate_styles as $style)
		if ($default_style == $style['file'])
			break;

	/* Set the default style sheet, for browsers that support it
	 * (most CSS-enabled browsers do) */
	header('Default-Style: ' . $style['title']);
}

function edk_get_meta($type, $value, $content = '', $extra = array())
{
	$r = '<meta ';
	$attrs = array(
		$type => $value,
		'content' => $content,
	);
	$attrs = array_merge($extra, $attrs);

	do
	{
		$key = key($attrs);
		$r .= $key . '="' . $attrs[$key] . '" ';
	} while(next($attrs));
	$r .= '/>';

	return $r;
}

function edk_meta($type, $value, $content, $extra = array())
{
	add_headline(edk_get_meta($type, $value, $content, $extra), $value);
}

function get_full_url($part = '')
{
	global $Blog;

	$r = $Blog->get('url');
	$r .= empty($part) ? '' : $part;
	return $r;
}

function get_other_blogs()
{
	global $Blog, $DB;
	$DB->query('SELECT * FROM ' . $Blog->dbtablename . ' WHERE blog_ID <> ' . $Blog->ID . ' AND blog_in_bloglist = 1');

	while ($row = $DB->get_row())
	{
		$curblog = new Blog($row);
		$blogs[$curblog->ID]['blog_locale'] = $curblog->locale;
		$blogs[$curblog->ID]['blog_name'] = $curblog->name;
		$blogs[$curblog->ID]['blog_siteurl'] = $curblog->get('url');
	}

	return $blogs;
}

global $edk_base, $skin;
$edk_base = $Blog->get_local_skins_url('basic').$skin.'/';

edk_css_include();
$dtd = '<!DOCTYPE html>';
$htmlelem = '<html lang="' . locale_lang(FALSE) . '">';

$params = array_merge( array(
	'auto_pilot'    => 'seo_title',
	'body_class'    => NULL,
	'generator_tag' => edk_get_meta('name', 'generator', sprintf('%s %s', $app_name, $app_version)) . '<!-- ' . $Skin->T_('Please leave this for stats') . " -->\n",
	'html_tag'      => "$dtd\n$htmlelem\n",
	'viewport_tag'  => '#responsive#',
), $params );


	edk_meta('name', 'author', $Blog->get_owner_User()->get('fullname'));
	edk_meta('property', 'copyright', get_copyright(array('display' =>  FALSE, 'license' =>  FALSE)));
	edk_meta('property', 'license', get_license(array('display' => FALSE, 'format' =>  'text')));

	global $cookie_path;
	add_js_headline('var cookie_path = "' .  $cookie_path . '";');
	require_js($edk_base . 'js/styleprefs.js', TRUE);

	add_headline(sprintf('<link rel="jslicense" href="%s" title="%s" />', $Skin->T_('https://www.gnu.org/licenses/lgpl-3.0.en.html'), $Skin->T_('GNU General Public License Version 3')));

	/* Hold this info in a variable instead of querying the DB multiple times */
	$canonical_url = get_full_url(get_post_urltitle());
	if ('single' == $disp)
	{
		add_headline(sprintf('<link rel="canonical" href="%s" title="%s" />%s', $canonical_url, $Skin->T_('Canonical Permalink'), "\n"));
		add_headline(sprintf('<link rel="shortlink" href="%s" title="%s" />%s', get_tinyurl(), $Skin->T_('Shortened Permalink'),  "\n"));
	}

	add_headline(sprintf('<link rel="bookmark" href="%s#content" title="%s" />', $canonical_url, $Skin->T_('Main Content')));
	add_headline(sprintf('<link rel="bookmark" href="%s#menu" title="%s" />', $canonical_url, $Skin->T_('Menu')));

if ('single' == $disp)
		{
  add_headline(sprintf('<link rel="bookmark" href="%s#comments" title="%s" />', $canonical_url, __('Comments')));
		}
  add_headline(sprintf('<link rel="top" href="%sdefault.php" title="%s" />', $baseurl, __('Go back to home page')));

if ('posts' != $disp)
{
	add_headline(sprintf('<link rel="up" href="%s%s" title="%s" />', $baseurl, $Blog->siteurl, htmlspecialchars($Blog->name)));
}
else
{
foreach (get_other_blogs() as $blog)
	add_headline(sprintf('<link rel="alternate" href="%1$s" title="%2$s" lang="%3$s" hreflang="%3$s" />%4$s', $blog['blog_siteurl'], $blog['blog_name'], $blog['blog_locale'], "\n"));
}

if (NULL !== $first_item)
{
	add_headline(sprintf('<link rel="first" href="%s" title="↓ %s" />', get_full_url($first_item['post_urltitle']), htmlspecialchars($first_item['post_title'])));
}
if (NULL !== $last_item)
{
	add_headline(sprintf('<link rel="last" href="%s" title="%s ↑" />', get_full_url($last_item['post_urltitle']), htmlspecialchars($last_item['post_title'])));
}

if (NULL !== $prev_item)
{
  add_headline(sprintf('<link rel="prev" href="%s" title="← %s" />', $prev_item->get_permanent_url(), htmlspecialchars($prev_item->title)));
}
if (NULL !== $next_item)
{
  add_headline(sprintf('<link rel="next" href="%s" title="%s →" />', $next_item->get_permanent_url(), htmlspecialchars($next_item->title)));
}
if ($Blog->get_setting('feed_content') != 'none')
{
	if (file_exists("$skins_path/_esf"))
	{
	add_headline(sprintf('<link rel="alternate" type="text/plain" title="ESF 1.0" href="%s%s?blog=%d&amp;tempskin=_esf" />', $baseurl, $Blog->siteurl, $Blog->ID));
	}
	if (file_exists("$skins_path/_rss3"))
	{
  add_headline(sprintf('<link rel="alternate" type="text/plain" title="RSS 3.0" href="%s%s?blog=%d&amp;tempskin=_rss3" />', $baseurl, $Blog->siteurl, $Blog->ID));
	}
}

/* Must be included this way rather than by skin_include so that $params will be correctly passed */
require_once $basepath . 'skins_fallback_v6/_html_header.inc.php';

printf('%s<h1><a href="%s">%s</a></h1>', PHP_EOL, $Blog->dget('url'), $Blog->dget('name'));

?>

<main id="content">