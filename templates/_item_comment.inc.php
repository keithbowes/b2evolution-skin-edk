<?php
/**
 * This is the template that displays a single comment
 *
 * This file is not meant to be called directly.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2008 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


global $comment_template_counter;


// Default params:
$params = array_merge( array(
		'comment_start'         => '<article class="evo_comment panel panel-default">',
		'comment_end'           => '</article>',
		'comment_post_display'	=> false,	// Do we want ot display the title of the post we're referring to?
		'comment_post_before'   => '<h3 class="evo_comment_post_title">',
		'comment_post_after'    => '</h3>',
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
		'link_to'               => 'userurl>userpage', // 'userpage' or 'userurl' or 'userurl>userpage' or 'userpage>userurl'
		'author_link_text'      => 'preferredname', // avatar_name | avatar_login | only_avatar | name | login | nickname | firstname | lastname | fullname | preferredname
		'before_image'          => '<figure class="evo_image_block">',
		'before_image_legend'   => '<figcaption class="evo_image_legend">',
		'after_image_legend'    => '</figcaption>',
		'after_image'           => '</figure>',
		'image_size'            => 'fit-1280x720',
		'image_class'           => 'img-responsive',
		'Comment'               => NULL, // This object MUST be passed as a param!
	), $params );

if( ! isset( $comment_template_counter ) )
{	// Initialize global comment counter:
	$comment_template_counter = isset( $params['comment_number'] ) ? $params['comment_number'] : 1;
}

/**
 * @var Comment
 */
$Comment = & $params['Comment'];

?>
<!-- ========== START of a COMMENT/TB/PB ========== -->
<?php
if(!empty($Comment->author_user_ID) && is_object($Item) && $Comment->author_user_ID == $Item->get_creator_User()->ID) $author_class = ' class="author-comment"';
else $author_class = ' class="user-comment"';
?>
	<div<?php echo $author_class; ?> id="<?php echo $Comment->get_anchor(); ?>">
<?php
  echo $params['comment_start'];
echo $params['comment_title_before'];

global $after_user_text;
$after_user_text = '';

if (!function_exists('showCommentAuthor'))
{
	function showCommentAuthor($Comment, $params)
	{
		global $after_user_text;
		$Comment->author2( array(
				'before'       => ' ',
				'after'        => $after_user_text,
				'before_user'  => '',
				'after_user'   => $after_user_text,
				'format'       => 'htmlbody',
				'link_to'	   => $params['link_to'],		// 'userpage' or 'userurl' or 'userurl>userpage' or 'userpage>userurl'
				'link_text'    => 'preferredname',
			));
	}
}

		switch( $Comment->get( 'type' ) )
		{
			case 'comment': // Display a comment:
				if( ! empty($Comment->ID) )
				{
					global $DB;
					$DB->query('SELECT comment_in_reply_to_cmt_ID FROM T_comments WHERE comment_ID=' . $Comment->ID);
					$refcomment = $DB->get_row(NULL, ARRAY_N)[0];

					if (0 != $refcomment)
					{
						$DB->query('SELECT comment_author, comment_author_user_ID FROM T_comments WHERE comment_ID=' . $refcomment);
						$row = $DB->get_row(NULL, ARRAY_N);
						$refname = $row[0];
						if (!$refname)
						{
							$DB->query('SELECT user_nickname, user_firstname, user_lastname, user_login FROM T_users WHERE user_ID=' . $row[1]);
							$row = $DB->get_row(NULL, ARRAY_N, 0);
							$refname = $row[0];
							if (!$refname)
							{
								$refname = $row[1] . ' ' . $row[2];
								if (!$refname)
									$refname = $row[3];
							}
						}
						$after_user_text = sprintf($Skin->T_(' (in response to <a href="%s">%s</a>)'), htmlentities(get_full_url()) . '#c' . $refcomment, $refname);
					}

					// Normal comment
					$Comment->permanent_link( array(
							'before'    => '',
							'after'     => ' '.$Skin->T_('from').' ',
							'text' 		=> __('Comment'),
							'title'     => '#',
							'class'     => 'evo_comment_type',
							'nofollow'	=> true,
						) );
				}
				else
				{	// PREVIEW comment
					echo __('PREVIEW Comment from:').' ';
				}


				showCommentAuthor($Comment, $params);
				if ( ! $Comment->get_author_User() )
					$Comment->msgform_link( $Blog->get('msgformurl') );
				break;

			case 'trackback': // Display a trackback:
				$Comment->permanent_link( array(
						'before'    => '',
						'after'     => ' '.__('from:').' ',
						'text' 		=> __('Trackback'),
						'nofollow'	=> true,
					) );
				showCommentAuthor($Comment, $params);
				break;

			case 'pingback': // Display a pingback:
				$Comment->permanent_link( array(
						'before'    => '',
						'after'     => ' '.__('from:').' ',
						'text' 		=> __('Pingback'),
						'class'     => 'evo_comment_type',
						'nofollow'	=> true,
					) );
				showCommentAuthor($Comment, $params);
				break;
		}

// Status
if( $Comment->status != 'published' )
{ // display status of comment (typically an angled banner in the top right corner):
	$Comment->format_status( array(
			'template' => '<div class="evo_status evo_status__$status$ badge pull-right">$status_title$</div>',
		) );
}

echo $params['comment_title_after'];
echo '</div>';

// Avatar:
echo $params['comment_avatar_before'];
$Comment->avatar();
echo $params['comment_avatar_after'];

// Rating:
$Comment->rating( array(
		'before' => $params['comment_rating_before'],
		'after'  => $params['comment_rating_after'],
	) );

// Text:
echo $params['comment_text_before'];
$Comment->content( 'htmlbody', false, true, $params );
echo $params['comment_text_after'];

// Info:
echo $params['comment_info_before'];

$commented_Item = & $Comment->get_Item();
		
$Comment->date();
echo $Skin->T_(' at ');
$Comment->time();

		$Comment->reply_link(' <span style="display: none">|</span> '); /* Link for replying to the Comment */
		$Comment->vote_helpful( '', '', '&amp;', true, true );


			$Comment->edit_link( ' <span style="display: none">|</span> ', '', '#', '#', 'permalink_right' ); /* Link to backoffice for editing */
			$Comment->delete_link( ' <span style="display: none">|</span> ', '', '#', '#', 'permalink_right' ); /* Link to backoffice for deleting */

echo $params['comment_info_after'];

echo $params['comment_end'];

// Decrease a counter for meta comments:
$comment_template_counter--;
?>