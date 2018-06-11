<!-- begin navbar -->
<?php
	global $baseurl;
	/* Make sure the navbar uses the blog's locale insted of the locale of the bottom post */
	locale_temp_switch($Blog->locale);
?>

<section id="nav">
	<ul id="menu">
<li>
<ul>
<?php
		// Display container contents:
		skin_container( _T('Navbar'), array(
				// The following (optional) params will be used as defaults for widgets included in this container:
				// This will enclose each widget in a block:
				'block_start' => '<li class="$wi_class$">',
				'block_end' => '</li>',
				// This will enclose the title of each widget:
				'block_title_start' => '<h2 tabindex="65536">',
				'block_title_end' => '</h2>',
				// If a widget displays a list, this will enclose that list:
				'list_start' => '<ul>',
				'list_end' => '</ul>',
				// This will enclose each item in a list:
				'item_start' => '<li>',
				'item_end' => '</li>',
				// This will enclose sub-lists in a list:
				'group_start' => '<ul>',
				'group_end' => '</ul>',
				// This will enclose (foot)notes:
				'notes_start' => '<div class="help-inline">',
				'notes_end' => '</div>',
      ) );?>
  <li><h2 tabindex="26789"><?php echo __('Misc'); ?></h2>
 	  <ul>
<?php
global $_item_title, $_item_url;
if (empty($_item_title))
	$_item_title = $Blog->get('name');
if (empty($_item_url))
	$_item_url = $Blog->get('url');

if ($Blog->get_setting('feed_content') != 'none')
{
	if (file_exists("$skins_path/_esf"))
	{
?>
<li><a rel="alternate" type="text/plain" href="<?php echo $baseurl . $Blog->siteurl; ?>?blog=<?php echo $Blog->ID ?>&amp;tempskin=_esf" class="button feed" id="esf" title="[<?php printf($Skin->T_('%s Feed'), 'ESF')?>]"><?php printf($Skin->T_('%s <span class="button-sf">Feed</span>'), 'ESF');?></a></li>
<?php
	}
	if (file_exists("$skins_path/_rss3"))
	{
?>
<li><a rel="alternate" type="text/plain" href="<?php echo $baseurl . $Blog->siteurl; ?>?blog=<?php echo $Blog->ID ?>&amp;tempskin=_rss3" class="button feed" id="rss3" title="[<?php printf($Skin->T_('%s Feed'), 'RSS 3.0')?>]"><?php printf($Skin->T_('%s <span class="button-sf">Feed</span>'), 'RSS 3.0');?></a></li>
<?php
	}
}
?>

<li><a href="https://m.facebook.com/sharer/?u=<?php echo $_item_url; ?>&amp;t=<?php echo urlencode($_item_title); ?>" onclick="window.open(this.href.replace(/m\.(facebook\.com)\/(sharer)/, 'www.$1/$2/$2.php')); return false;" class="button" id="facebook" title="[<?php echo $Skin->T_('Share on Facebook'); ?>]"><?php echo preg_replace('/^(.*)F(acebook.*)$/', '<span class="button-sf">$1</span>F<span class="button-sf">$2</span>', $Skin->T_('Share on Facebook')); ?></a></li>
<li><a href="<?php echo "https://plus.google.com/share?url=$_item_url"; ?>" onclick="window.open(this.href); return false;" class="button" id="gplus" title="[<?php echo $Skin->T_('Share on Google+'); ?>]"><?php echo preg_replace('/^(.*)G(oogle)\+/', '<span class="button-sf">$1</span>G<span class="button-sf">$2+</span>', $Skin->T_('Share on Google+')); ?></a></li>
<li><a href="<?php echo "https://twitter.com/intent/tweet?original_referer=$_item_url&amp;url=$_item_url&amp;text=" . urlencode($_item_title); ?>" onclick="window.open(this.href); return false;" class="button" id="twitter" title="[<?php echo $Skin->T_('Share on Twitter'); ?>]"><?php echo preg_replace('/^(.*)T(witter.*)$/', '<span class="button-sf">$1</span>T<span class="button-sf">$2</span>', $Skin->T_('Share on Twitter')); ?></a></li>
<li>

<!-- Diaspora section -->
<script type="text/javascript">
var dia_text, mod_dia_text;

function get_inner_text(node, text)
{
	if (node.innerHTML)
		return node.innerHTML;

	if (node.hasChildNodes())
		for (var i = 0; i < node.childNodes.length; i++)
			text = get_inner_text(node.childNodes[i], text);
	else
		text += node.nodeValue;

	return text;
}

function diaspform_submit(obj, submit)
{
	var dia = obj.elements['diaspora'];
	var elem = document.getElementById('diafields');

	if (!dia_text)
		dia_text = get_inner_text(dia, ''); 

	if ('diahide' == elem.className)
	{
		elem.className = 'diashow';

		if (!mod_dia_text)
		{
			mod_dia_text = dia_text;
			while (mod_dia_text != (mod_dia_text = mod_dia_text.replace(/<[^>]+>/, '')));
		}
		dia.textContent = mod_dia_text;

		return false;
	}
	else
	{
		elem.className = 'diahide';
		if (dia.innerHTML)
			dia.innerHTML = dia_text;
		else
			// Not 100% right, but the closest we can get without innerHTML support
			// (which is pretty much universal in HTML5-capable browsers)
			dia.textContent = dia_text;
		return submit;
	}
}
</script>
<?php
global $diaform;
$diaform = new Form($_SERVER['PHP_SELF'], 'diaspform');
$diaform->begin_form(NULL, NULL, array(
	'onsubmit' => 'return diaspform_submit(this, true);',
));
?>

<div class="diahide" id="diafields">

<?php
$diaform->hidden('diaspora-url', urlencode($_item_url));
$diaform->hidden('diaspora-title', urlencode($_item_title));
$diaform->hidden('redir', 'no');
?>
<div>

<label>
<?php echo $Skin->T_('Pod URL: ') ?> <input type="text" name="diaspora-pod" value="https://" />
<br />

</label>

</div>
<?php
$diaform->button(array(
	'tag' => 'button',
	'type' => 'submit',
	'class' => 'button dia',
	'id' => 'diaspora',
	'title' => $Skin->T_('Share on Diaspora*'),
	'value' => preg_replace('/^(.+Diaspora)(\*)$/', '<span class="button-sf">$1</span>$2', $Skin->T_('Share on Diaspora*')),
));
?>
</div>
<script type="text/javascript">
var nopost = document.createElement('input');
nopost.className = 'button dia';
nopost.type = 'button';
nopost.value = '<?php echo __('Cancel'); ?>';
nopost.setAttribute('onclick', 'return diaspform_submit(this.form, false);');
document.getElementById('diafields').appendChild(nopost);
</script>
<?php
$diaform->end_form();
?>
</li>
	  </ul>
 </li>
	<li><h2 tabindex="36789"><?php echo __('Admin') ?></h2>
		<ul>
<?php $logged_in = is_logged_in() && $current_User->check_perm('admin', 'restricted');
?>
	<li><a href="<?php echo $baseurl ?>admin.php<?php if (!$logged_in) echo '?redirect_to=' . $_item_url . '' ?>" title="<?php if (!$logged_in) echo __('Log in to your account'); else echo __('Go to the back-office...'); ?>"><?php echo $logged_in ? $Skin->T_('Back-office') : __('Log in') ?></a></li>
<?php user_logout_link( '<li>', '</li>' ); ?>
</ul></li>
</ul>
</li>
</ul>

</section>
<!-- end navbar -->