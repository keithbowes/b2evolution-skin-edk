<?php
/**
 * This is the template that displays the feedback for a post
 * (comments, trackback, pingback...)
 *
 * You may want to call this file multiple time in a row with different $c $tb $pb params.
 * This allow to seprate different kinds of feedbacks instead of displaying them mixed together
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template.
 * To display a feedback, you should call a stub AND pass the right parameters
 * For example: /blogs/index.php?p=1&more=1&c=1&tb=1&pb=1
 * Note: don't code this URL by hand, use the template functions to generate it!
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2010 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// Default params:
$params = array_merge( array(
		'Item'                  => NULL,
		'disp_comments'         => true,
		'disp_comment_form'     => true,
		'disp_trackbacks'       => true,
		'disp_trackback_url'    => true,
		'disp_pingbacks'        => true,
		'disp_meta_comments'    => false,
		'disp_section_title'    => true,
		'disp_meta_comment_info' => true,
		'disp_rating_summary'   => true,
		'before_section_title'  => '<div class="clearfix"></div><h2 class="evo_comment__list_title">',
		'after_section_title'   => '</h2>',
		'comments_title_text'   => '',
		'comment_list_start'    => "\n\n",
		'comment_list_end'      => "\n\n",
		'comment_start'         => '<article class="evo_comment panel panel-default">',
		'comment_end'           => '</article>',
		'comment_post_display'	=> false,	// Do we want ot display the title of the post we're referring to?
		'comment_post_before'   => '<h2 class="evo_comment_post_title">',
		'comment_post_after'    => '</h2>',
		'comment_title_before'  => '<div class="panel-heading"><h3 class="evo_comment_title panel-title">',
		'comment_title_after'   => '</h3></div><div class="panel-body">',
		'comment_avatar_before' => '<span class="evo_comment_avatar">',
		'comment_avatar_after'  => '</span>',
		'comment_rating_before' => '<div class="evo_comment_rating">',
		'comment_rating_after'  => '</div>',
		'comment_text_before'   => '<div class="evo_comment_text">',
		'comment_text_after'    => '</div>',
		'comment_info_before'   => '<footer class="evo_comment_footer clear text-muted">',
		'comment_info_after'    => '</footer></div>',
		'preview_start'         => '<article class="evo_comment evo_comment__preview panel panel-warning" id="comment_preview">',
		'preview_end'           => '</article>',
		'comment_error_start'   => '<article class="evo_comment evo_comment__error panel panel-default" id="comment_error">',
		'comment_error_end'     => '</article>',
		'comment_template'      => 'templates/_item_comment.inc.php',	// The template used for displaying individual comments (including preview)
		'comment_image_size'    => 'fit-1280x720',
		'author_link_text'      => 'auto', // avatar_name | avatar_login | only_avatar | name | login | nickname | firstname | lastname | fullname | preferredname
		'link_to'               => 'userurl>userpage',		    // 'userpage' or 'userurl' or 'userurl>userpage' or 'userpage>userurl'
		// Comment notification functions:
		'disp_notification'     => true,
		'notification_before'   => '<nav class="evo_post_comment_notification">',
		'notification_text'     => T_( 'This is your post. You are receiving notifications when anyone comments on your posts.' ),
		'notification_text2'    => T_( 'You will be notified by email when someone comments here.' ),
		'notification_text3'    => T_( 'Notify me by email when someone comments here.' ),
		'notification_after'    => '</nav>',
		'feed_title'            => '#',
		'disp_nav_top'          => true,
		'disp_nav_bottom'       => true,
		'nav_top_inside'        => false, // TRUE to display it after start of comments list (inside), FALSE to display a page navigation before comments list
		'nav_bottom_inside'     => false, // TRUE to display it before end of comments list (inside), FALSE to display a page navigation after comments list
		'nav_block_start'       => '<div class="text-center"><ul class="pagination">',
		'nav_block_end'         => '</ul></div>',
		'nav_prev_text'         => '<i class="fa fa-angle-double-left"></i>',
		'nav_next_text'         => '<i class="fa fa-angle-double-right"></i>',
		'nav_prev_class'        => '',
		'nav_next_class'        => '',
		'nav_page_item_before'  => '<li>',
		'nav_page_item_after'   => '</li>',
		'nav_page_current_template' => '<span><b>$page_num$</b></span>',
		'comments_per_page'     => NULL, // Used instead of blog setting "comments_per_page"
		'pagination'            => array(),
	), $params );

global $c, $tb, $pb, $redir;


if( ! $Item->can_see_comments() )
{	// Comments are disabled for this post
	return;
}

if( empty($c) )
{	// Comments not requested
	$params['disp_comments'] = false;					// DO NOT Display the comments if not requested
	$params['disp_comment_form'] = false;			// DO NOT Display the comments form if not requested
}

if( empty($tb) || !$Blog->get( 'allowtrackbacks' ) )
{	// Trackback not requested or not allowed
	$params['disp_trackbacks'] = false;				// DO NOT Display the trackbacks if not requested
	$params['disp_trackback_url'] = false;		// DO NOT Display the trackback URL if not requested
}

if( empty($pb) )
{	// Pingback not requested
	$params['disp_pingbacks'] = false;				// DO NOT Display the pingbacks if not requested
}

if( ! ($params['disp_comments'] || $params['disp_comment_form'] || $params['disp_trackbacks'] || $params['disp_trackback_url'] || $params['disp_pingbacks'] ) )
{	// Nothing more to do....
	return false;
}

$type_list = array();
$disp_title = array();

if( $params['disp_comments'] )
{	// We requested to display comments
	if( $Item->can_see_comments() )
	{ // User can see a comments
		$type_list[] = 'comment';
		if( $title = $Item->get_feedback_title( 'comments' ) )
		{
			$disp_title[] = $title;
		}
	}
	else
	{ // Use cannot see comments
		$params['disp_comments'] = false;
	}
}

if( $params['disp_trackbacks'] )
{
	$type_list[] = 'trackback';
	if( $title = $Item->get_feedback_title( 'trackbacks' ) )
	{
		$disp_title[] = $title;
	}
	echo '<span id="trackbacks"></span>';
}

if( $params['disp_pingbacks'] )
{
	$type_list[] = 'pingback';
	if( $title = $Item->get_feedback_title( 'pingbacks' ) )
	{
		$disp_title[] = $title;
	}
	echo '<span id="pingbacks"></span>';
}

if( $params['disp_trackback_url'] )
{ // We want to display the trackback URL:

	echo $params['before_section_title'];
	echo T_('Trackback address for this post');
	echo $params['after_section_title'];

	/*
	 * Trigger plugin event, which could display a captcha form, before generating a whitelisted URL:
	 */
	if( ! $Plugins->trigger_event_first_true( 'DisplayTrackbackAddr', array('Item' => & $Item, 'template' => '<code>%url%</code>') ) )
	{ // No plugin displayed a payload, so we just display the default:
		echo '<p class="trackback_url"><a href="'.$Item->get_trackback_url().'">'.T_('Trackback URL (right click and copy shortcut/link location)').'</a></p>';
	}
}


if( $params['disp_comments'] || $params['disp_trackbacks'] || $params['disp_pingbacks']  )
{
	if( empty($disp_title) )
	{	// No title yet
		if( $title = $Item->get_feedback_title( 'feedbacks', '', T_('Feedback awaiting moderation'), T_('Feedback awaiting moderation'), 'draft' ) )
		{ // We have some feedback awaiting moderation: we'll want to show that in the title
			$disp_title[] = $title;
		}
	}

	if( empty($disp_title) )
	{	// Still no title
		$disp_title[] = T_('No feedback yet');
	}

	echo '<div id="comments">';
	echo $params['before_section_title'];
	echo implode( ', ', $disp_title);
	echo $params['after_section_title'];

	$comments_per_page = !$Blog->get_setting( 'threaded_comments' ) ? $Blog->get_setting( 'comments_per_page' ) : 1000;
	$CommentList = new CommentList2( $Blog, $Blog->get_setting( 'comments_per_page' ), 'CommentCache', 'c_' );

	// Filter list:
	$CommentList->set_default_filters( array(
			'types' => $type_list,
			'statuses' => array ( 'published' ),
			'post_ID' => $Item->ID,
			'order' => $Blog->get_setting( 'comments_orderdir' ),
      'threaded_comments' => $Blog->get_setting( 'threaded_comments' ),
		) );

	$CommentList->load_from_Request();

	// Get ready for display (runs the query):
	$CommentList->display_init();

	// Set redir=no in order to open comment pages
	$old_redir = $redir;
	memorize_param( 'redir', 'string', $old_redir, 'no' );

	// Prev/Next page navigation
	if (isset($CommentList) && isset($CommentList->page_links))
	$CommentList->page_links( array(
			'page_url' => url_add_tail( $Item->get_permanent_url(), '#comments' ),
		) );


		if( $Blog->get_setting( 'threaded_comments' ) )
		{	// Array to store the comment replies
			global $CommentReplies;
			$CommentReplies = array();

			if( $Comment = $Session->get('core.preview_Comment') )
			{	// Init PREVIEW comment
				if( $Comment->item_ID == $Item->ID )
				{
					$CommentReplies[ $Comment->in_reply_to_cmt_ID ] = array( $Comment );
				}
			}
		}

		if( ! $params['nav_top_inside'] )
		{ // To use comments page navigation before list
			echo $params['comment_list_start'];
		}

		// Set number of comment depending on current page
		$comment_number = ( ( $CommentList->page - 1 ) * $CommentList->limit ) + 1;

	/**
	 * @var Comment
	 */
	while( $Comment = & $CommentList->get_next() )
	{	// Loop through comments:

			if( $Blog->get_setting( 'threaded_comments' ) && $Comment->in_reply_to_cmt_ID > 0 )
			{	// Store the replies in a special array
				if( !isset( $CommentReplies[ $Comment->in_reply_to_cmt_ID ] ) )
				{
					$CommentReplies[ $Comment->in_reply_to_cmt_ID ] = array();
				}
				$CommentReplies[ $Comment->in_reply_to_cmt_ID ][] = $Comment;
				continue; // Skip dispay a comment reply here in order to dispay it after parent comment by function display_comment_replies()
			}

		// ------------------ COMMENT INCLUDED HERE ------------------
		skin_include( $params['comment_template'], array(
				'Comment'              => & $Comment,
			  'comment_start'        => $params['comment_start'],
			  'comment_end'          => $params['comment_end'],
			) );
		// Note: You can customize the default item feedback by copying the generic
		// /skins/_item_comment.inc.php file into the current skin folder.
		// ---------------------- END OF COMMENT ---------------------

		// End of comment list loop.

			if( $Blog->get_setting( 'threaded_comments' ) )
			{	// Display the comment replies
				display_comment_replies( $Comment->ID, $params );
			}
}
	echo "</div>"; // #comments
		if( ! $params['nav_bottom_inside'] )
		{ // To use comments page navigation after list
			echo $params['comment_list_end'];
		}

	// Prev/Next page navigation
	if (isset($CommentList) && isset($CommentList->page_links))
	$CommentList->page_links( array(
			'page_url' => url_add_tail( $Item->get_permanent_url(), '#comments' ),
		) );

	// Restore "redir" param
	forget_param('redir');

	// _______________________________________________________________

	// Display count of comments to be moderated:
	$Item->feedback_moderation( 'feedbacks', '<div class="moderation_msg"><p>', '</p></div>', '',
 T_('This post has 1 feedback awaiting moderation... %s'),
 T_('This post has %d feedbacks awaiting moderation... %s') );

	// _______________________________________________________________


// ----------- Register for item's comment notification -----------
if( is_logged_in() && $Item->can_comment( NULL ) )
{
	global $DB, $htsrv_url;
	global $UserSettings;

	$not_subscribed = true;
	$creator_User = $Item->get_creator_User();

	if( $Blog->get_setting( 'allow_subscriptions' ) )
	{
		$sql = 'SELECT count( sub_user_ID ) FROM T_subscriptions
					WHERE sub_user_ID = '.$current_User->ID.' AND sub_coll_ID = '.$Blog->ID.' AND sub_comments <> 0';
		if( $DB->get_var( $sql ) > 0 )
		{
			echo '<p>'.T_( 'You are receiving notifications when anyone comments on any post.' );
			echo ' <a href="'.$Blog->get('subsurl').'">'.T_( 'Click here to manage your subscriptions.' ).'</a></p>';
			$not_subscribed = false;
		}
	}

	if( $params['disp_notification'] )
	{	// Display notification link
		echo $params['notification_before'];

		$notification_icon = get_icon( 'notification' );

		if( $not_subscribed && ( $creator_User->ID == $current_User->ID ) && ( $UserSettings->get( 'notify_published_comments', $current_User->ID ) != 0 ) )
		{
			echo ''.$notification_icon.' <span>'.$params['notification_text'];
			echo ' <a href="'.$Blog->get('subsurl').'">'.T_( 'Click here to manage your subscriptions.' ).'</a></span><';
			$not_subscribed = false;
		}
		if( $not_subscribed && $Blog->get_setting( 'allow_item_subscriptions' ) )
		{
			if( get_user_isubscription( $current_User->ID, $Item->ID ) )
			{
				echo $notification_icon.' <span>'.$params['notification_text2'];
				echo ' <a href="'.$samedomain_htsrv_url.'action.php?mname=collections&action=isubs_update&amp;p='.$Item->ID.'&amp;notify=0&amp;'.url_crumb( 'collections_isubs_update' ).'">'.T_( 'Click here to unsubscribe.' ).'</a></span>';
			}
			else
			{
				echo $notification_icon.' <span><a href="'.$samedomain_htsrv_url.'action.php?mname=collections&amp;action=isubs_update&amp;p='.$Item->ID.'&amp;notify=1&amp;'.url_crumb( 'collections_isubs_update' ).'">'.$params['notification_text3'].'</a></span>';
			}
		}

		echo $params['notification_after'];
	}
}
	// _______________________________________________________________
}


if( $Item->can_see_comments( false ) && ( $params['disp_comments'] || $params['disp_trackbacks'] || $params['disp_pingbacks'] ) )
{	// user is allowed to see comments
	// Display link for comments feed:
	$Item->feedback_feed_link( '_esf', '<div class="feedback_feed_msg">', '</div>', $params['feed_title'] );
}

// ------------------ COMMENT FORM INCLUDED HERE ------------------
skin_include( 'templates/_item_comment_form.inc.php', $params );
// Note: You can customize the default item feedback by copying the generic
// /skins/_item_comment_form.inc.php file into the current skin folder.
// ---------------------- END OF COMMENT FORM ---------------------


?>