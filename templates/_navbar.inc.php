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