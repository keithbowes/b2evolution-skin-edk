<?php

skin_init( $disp );
skin_include( 'templates/_html_header.inc.php' );

display_if_empty();
while( $Item = & mainlist_get_item() ):

$_item_title = ($disp == 'single') ? $Item->title : '';
$_item_url = get_tinyurl();

if ('single' == $disp)
{
?>
<div id="prevnext">
<?php
		global $first_item, $last_item, $next_item, $prev_item;
		if (NULL !== $first_item)
		{
?>
		<div id="firstitem">
			<a href="<?php echo get_full_url($first_item['post_urltitle']) ?>">↓ <?php echo htmlspecialchars($first_item['post_title']) ?></a>
		</div>
<?php
		}
		if (NULL !== $last_item)
		{
?>
		<div id="lastitem">
			<a href="<?php echo get_full_url($last_item['post_urltitle']) ?>"><?php echo htmlspecialchars($last_item['post_title']) ?> ↑</a>
		</div>
<?php
		}
		if (NULL !== $prev_item)
		{
?>
		<div id="previtem">
			<a rel="prev" href="<?php echo $prev_item->get_permanent_url() ?>">← <?php echo htmlspecialchars($prev_item->title) ?></a>
		</div>
<?php
		}
		if (NULL !== $next_item)
		{
?>
		<div id="nextitem">
			<a rel="next" href="<?php echo $next_item->get_permanent_url() ?>"><?php echo htmlspecialchars($next_item->title) ?> →</a>
		</div>
<?php
		}
?>
</div>

<?php
}
?>
	<article id="<?php $Item->anchor_id(); ?>" <?php printf('lang="%s"', locale_lang(FALSE)); ?>>
<?php
	$Item->locale_temp_switch();
	printf('<h2 class="storytitle"><a rel="%4$s" href="%1$s"  title="%3$s">%2$s</a></h2>', $Item->get_single_url(), $Item->title, __('Permanent link to full entry'), 'bookmark');
?>
	<div class="meta"><?php get_meta($Item); ?></div>


<div class="storycontent">
		<?php skin_include('templates/_item_content.inc.php'); ?>
	</div>

<?php
	$Item->tags(
		array(
			'after' => "\n</li>\n</ul>\n</div>\n",
			'before' => "<div class=\"meta\">\n<h3 class=\"tag-list-header\">" . __('Tags') . "</h3>\n<ul class=\"tag-list\">\n<li>",
			'separator' => '</li><li>',
		)
	);

	$Item->feedback_link(
		array(
			'link_after' => '</div>',
			'link_anchor_more' => '',
			'link_anchor_one' => '',
			'link_anchor_zero' => '',
			'link_before' => '<div class="postmetadata">',
			'type' => 'comments',
			'url' => $Item->get_feedback_url() . '#comments',
			)
		);
?>

	</article>
<?php

skin_include( 'templates/_item_feedback.inc.php');
endwhile;

skin_include('$disp$', array(
  'disp_404' => 'templates/_404_not_found.disp.php',
  'disp_403' => 'templates/_403_forbidden.disp.php',
  'disp_comments' => 'templates/_comments.disp.php',
  'disp_login' => 'templates/_login.disp.php',
  'disp_register' => '_register.disp.php',
  'disp_msgform' => 'templates/_msgform.disp.php',
  'disp_posts' => '',
  'disp_single' => '',
  'disp_page' => '',
  'disp_profile' => 'templates/_profile.disp.php',
  'disp_user' => '',
));

if ($MainList)
{
	$row = $DB->get_row('SELECT COUNT(*) FROM ' . $MainList->ItemQuery->dbtablename .
		' WHERE post_main_cat_ID IN (SELECT cat_ID FROM T_categories' .
		' WHERE cat_blog_ID=' . $Blog->ID . ')', ARRAY_A, 0);

	$MainList->page_links(array(
			'block_start' => "\n" . '<!-- begin footer -->' . "\n" . '<footer id="page-links">',
			'block_end' => '</footer>' . "\n" . '<!-- end footer -->' . "\n",
			'prev_text' => '<span class="sago">←</span>',
			'next_text' => '<span class="sago">→</span>',
			'list_span' => ceil($row['COUNT(*)'] / $Blog->get_setting('posts_per_page')),
		)
	);
}

?>

</main>

<?php
	skin_include( 'templates/_navbar.inc.php' );
	skin_include( 'templates/_html_footer.inc.php' );
?>