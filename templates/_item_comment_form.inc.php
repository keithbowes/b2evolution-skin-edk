<?php
/**
 * This is the template that displays the comment form for a post
 *
 * This file is not meant to be called directly.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2012 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $cookie_name, $cookie_email, $cookie_url;
global $comment_allowed_tags;
global $comment_cookies, $comment_allow_msgform;
global $checked_attachments; // Set this var as global to use it in the method $Item->can_attach()
global $PageCache;
global $Blog, $dummy_fields;

// Default params:
$params = array_merge( array(
		'disp_comment_form'	   =>	true,
		'form_title_start'     => '<h3>',
		'form_title_end'       => '</h3>',
		'form_title_text'      => __('Leave a comment'),
		'form_comment_text'    => __('Comment text'),
		'form_submit_text'     => __('Send comment'),
		'form_params'          => array(), // Use to change a structre of form, i.e. fieldstart, fieldend and etc.
		'policy_text'          => '',
		'textarea_lines'       => 10,
		'default_text'         => '',
		'preview_block_start'  => '',
		'preview_start'        => '<article class="evo_comment evo_comment__preview panel panel-warning id="comment_preview">',
		'preview_end'          => '</article>',
		'preview_block_end'    => '',
		'before_comment_error' => '<p><em>',
		'comment_closed_text'  => '#',
		'after_comment_error'  => '</em></p>',
		'before_comment_form'  => '',
		'after_comment_form'   => '',
		'form_comment_redirect_to' => $Item->get_feedback_url( $disp == 'feedback-popup', '&' ),
		'comment_attach_info'      => get_icon( 'help', 'imgtag', array(
				'data-toggle'    => 'tooltip',
				'data-placement' => 'bottom',
				'data-html'      => 'true',
				'title'          => htmlspecialchars( get_upload_restriction( array(
						'block_after'     => '',
						'block_separator' => '<br /><br />' ) ) )
			) ),
		'comment_mode'         => '', // Can be 'quote' from GET request
	), $params );

$comment_reply_ID = param( 'reply_ID', 'integer', 0 );

$email_is_detected = false; // Used when comment contains an email strings

// Consider comment attachments list empty
$comment_attachments = '';

/*
 * Comment form:
 */
$section_title = $params['form_title_start'].$params['form_title_text'].$params['form_title_end'];
if( $params['disp_comment_form'] && $Item->can_comment( NULL, NULL, '#', $params['comment_closed_text'], $section_title, $params ) )
{ // We want to display the comments form and the item can be commented on:

	echo $params['before_comment_form'];

	// INIT/PREVIEW:
	if( $Comment = $Session->get('core.preview_Comment') )
	{	// We have a comment to preview
		if( $Comment->item_ID == $Item->ID )
		{ // display PREVIEW:

			// We do not want the current rendered page to be cached!!
			if( !empty( $PageCache ) )
			{
				$PageCache->abort_collect();
			}

			if( $Comment->email_is_detected )
			{	// We set it to define a some styles below
				$email_is_detected = true;
			}

			if( !$Blog->get_setting( 'threaded_comments' ) )
			{
				// ------------------ PREVIEW COMMENT INCLUDED HERE ------------------
				skin_include( $params['comment_template'], array(
						'Comment'              => & $Comment,
						'comment_block_start'  => $Comment->email_is_detected ? '' : $params['preview_block_start'],
						'comment_start'        => $Comment->email_is_detected ? $params['comment_error_start'] : $params['preview_start'],
						'comment_end'          => $Comment->email_is_detected ? $params['comment_error_end'] : $params['preview_end'],
						'comment_block_end'    => $Comment->email_is_detected ? '' : $params['preview_block_end'],
					) );
				// Note: You can customize the default item feedback by copying the generic
				// /skins/_item_comment.inc.php file into the current skin folder.
				// ---------------------- END OF PREVIEW COMMENT ---------------------
			}

			// Form fields:
			$comment_content = $Comment->original_content;
			// comment_attachments contains all file IDs that have been attached
			$comment_attachments = $Comment->preview_attachments;
			// checked_attachments contains all attachment file IDs which checkbox was checked in
			$checked_attachments = $Comment->checked_attachments;
			// for visitors:
			$comment_author = $Comment->author;
			$comment_author_email = $Comment->author_email;
			$comment_author_url = $Comment->author_url;

			// Display error messages again after preview of comment
			global $Messages;
			$Messages->display();
		}

		// delete any preview comment from session data:
		$Session->delete( 'core.preview_Comment' );
	}
	else
	{ // New comment:
		if( ( $Comment = get_comment_from_session() ) == NULL )
		{ // there is no saved Comment in Session
			$Comment = new Comment();
			if( ( !empty( $PageCache ) ) && ( $PageCache->is_collecting ) )
			{	// This page is going into the cache, we don't want personal data cached!!!
				// fp> These fields should be filled out locally with Javascript tapping directly into the cookies. Anyone JS savvy enough to do that?
				$comment_author = '';
				$comment_author_email = '';
				$comment_author_url = '';
			}
			else
			{
				$comment_author = isset($_COOKIE[$cookie_name]) ? trim($_COOKIE[$cookie_name]) : '';
				$comment_author_email = isset($_COOKIE[$cookie_email]) ? trim($_COOKIE[$cookie_email]) : '';
				$comment_author_url = isset($_COOKIE[$cookie_url]) ? trim($_COOKIE[$cookie_url]) : '';
			}

			$comment_content =  $params['default_text'];
		}
		elseif (is_object($Comment))
		{ // set saved Comment attributes from Session
			$comment_content = $Comment->content;
			$comment_author = $Comment->author;
			$comment_author_email = $Comment->author_email;
			$comment_author_url = $Comment->author_url;
			// comment_attachments contains all file IDs that have been attached
			$comment_attachments = $Comment->preview_attachments;
			// checked_attachments contains all attachment file IDs which checkbox was checked in
			$checked_attachments = $Comment->checked_attachments;
		}
	}

	if( ( !empty( $PageCache ) ) && ( $PageCache->is_collecting ) )
	{	// This page is going into the cache, we don't want personal data cached!!!
		// fp> These fields should be filled out locally with Javascript tapping directly into the cookies. Anyone JS savvy enough to do that?
	}
	else
	{
		// Get values that may have been passed through after a preview
		param( 'comment_cookies', 'integer', NULL );
		param( 'comment_allow_msgform', 'integer', NULL ); // checkbox

		if( is_null($comment_cookies) )
		{ // "Remember me" checked, if remembered before:
			$comment_cookies = isset($_COOKIE[$cookie_name]) || isset($_COOKIE[$cookie_email]) || isset($_COOKIE[$cookie_url]);
		}
	}

	echo $params['form_title_start'];
	echo $params['form_title_text'];
	echo $params['form_title_end'];

/*
	echo '<script type="text/javascript">
function validateCommentForm(form)
{
	if( form.'.$dummy_fields['content'].'.value.replace(/^\s+|\s+$/g,"").length == 0 )
	{
		alert("'._s('Please do not send empty comments.').'");
		return false;
	}
}
</script>';*/

echo '<div id="form_p' . $Item->ID . '">';
	global $samedomain_htsrv_url;
	$Form = new Form( $samedomain_htsrv_url.'comment_post.php', 'evo_comment_form_id_'.$Item->ID, 'post', NULL, 'multipart/form-data' );

	$Form->switch_template_parts( $params['form_params'] );

	$Form->begin_form( 'evo_comment', '', array( /*, 'onsubmit' => 'return validateCommentForm(this);'*/ ) );

	// TODO: dh> a plugin hook would be useful here to add something to the top of the Form.
	//           Actually, the best would be, if the $Form object could be changed by a plugin
	//           before display!

	$Form->add_crumb( 'comment' );
	$Form->hidden( 'comment_item_ID', $Item->ID );
	if( !empty( $comment_reply_ID ) )
	{
		$Form->hidden( 'reply_ID', $comment_reply_ID );

		// Link to scroll back up to replying comment
		echo '<a href="'.url_add_param( $Item->get_permanent_url(), 'reply_ID='.$comment_reply_ID.'&amp;redir=no' ).'#c'.$comment_reply_ID.'" class="comment_reply_current" rel="'.$comment_reply_ID.'">'.__('You are currently replying to a specific comment').'</a>';
	}
	$Form->hidden( 'redirect_to',
			// Make sure we get back to the right page (on the right domain)
			// fp> TODO: check if we can use the permalink instead but we must check that application wide,
			// that is to say: check with the comments in a pop-up etc...
			// url_rel_to_same_host(regenerate_url( '', '', $Blog->get('blogurl'), '&' ), $htsrv_url)
			// fp> what we need is a regenerate_url that will work in permalinks
			// fp> below is a simpler approach:
			$params['form_comment_redirect_to']
		);

	if( check_user_status( 'is_validated' ) )
	{ // User is logged in and activated:
		$Form->info_field( __('User'), '<strong>'.$current_User->get_identity_link( array( 'link_text' => 'text', 'display_bubbletip' => false ) ).'</strong>'
			.' '.get_user_profile_link( ' [', ']', __('Edit profile') ) );
	}
	else
	{ // User is not logged in or not activated:
		if( is_logged_in() && empty( $comment_author ) && empty( $comment_author_email ) )
		{
			$comment_author = $current_User->login;
			$comment_author_email = $current_User->email;
		}
		// Note: we use funky field names to defeat the most basic guestbook spam bots
		$Form->text( $dummy_fields[ 'name' ], $comment_author, 40, __('Name'), '', 100, 'evo_comment' );

		$Form->text( $dummy_fields[ 'email' ], $comment_author_email, 40, __('Email'), '('.__('Your email address will <strong>not</strong> be revealed on this site.').')', 100, 'evo_comment', 'email' );

		$Item->load_Blog();
		if( $Item->Blog->get_setting( 'allow_anon_url' ) )
		{
			$Form->text( $dummy_fields[ 'url' ], $comment_author_url, 40, __('Website'), '('.__('Your URL will be displayed.').')', 100, 'evo_comment', 'url' );
		}
	}

	if( $Item->can_rate() )
	{	// Comment rating:
		ob_start();
		echo $Form->begin_field( NULL, __('Your vote'), true );
		$Comment->rating_input();
		$comment_rating = ob_get_clean();
		$Form->info_field( __('Your vote'), $comment_rating );
	}

	if( !empty($params['policy_text']) )
	{	// We have a policy text to display
		$Form->info_field( '', $params['policy_text'] );
	}

	//echo '<div class="comment_toolbars">';
	// CALL PLUGINS NOW:
	//$Plugins->trigger_event( 'DisplayCommentToolbar', array( 'Comment' => & $Comment, 'Item' => & $Item ) );
	//echo '</div>';

	// Message field:
	$note = '';
	// $note = __('Allowed XHTML tags').': '.htmlspecialchars(str_replace( '><',', ', $comment_allowed_tags));
	$Form->textarea( $dummy_fields[ 'content' ], $comment_content, $params['textarea_lines'], $params['form_comment_text'], $note, 38, 'evo_comment' );

	// set b2evoCanvas for plugins
	echo '<script type="text/javascript">var b2evoCanvas = document.getElementById( "'.$dummy_fields[ 'content' ].'" );</script>';

	if (($plug = $Plugins->get_by_classname('markdown_plugin')) !== FALSE && 'enabled' == $plug->status)
	{
		echo '<div class="allowed-tags fieldset form-control">';
		echo $Skin->T_('<a href="http://en.wikipedia.org/wiki/Markdown">Markdown</a> is enabled.');
		echo "</div>\n\n";
	}

	// Attach files:
	if( !empty( $comment_attachments ) )
	{	// display already attached files checkboxes
		$FileCache = & get_FileCache();
		$attachments = explode( ',', $comment_attachments );
		$final_attachments = explode( ',', $checked_attachments );
		// create attachments checklist
		$list_options = array();
		foreach( $attachments as $attachment_ID )
		{
			$attachment_File = $FileCache->get_by_ID( $attachment_ID, false );
			if( $attachment_File )
			{
				// checkbox should be checked only if the corresponding file id is in the final attachments array
				$checked = in_array( $attachment_ID, $final_attachments );
				$list_options[] = array( 'preview_attachment'.$attachment_ID, 1, '', $checked, false, $attachment_File->get( 'name' ) );
			}
		}
		if( !empty( $list_options ) )
		{	// display list
			$Form->checklist( $list_options, 'comment_attachments', __( 'Attached files' ) );
		}
		// memorize all attachments ids
		$Form->hidden( 'preview_attachments', $comment_attachments );
	}
	if( $Item->can_attach() )
	{	// Display attach file input field
		$Form->input_field( array( 'label' => __('Attach files'), 'note' => '<br />'.get_upload_restriction(), 'name' => 'uploadfile[]', 'type' => 'file' ) );
	}

	$comment_options = array();

	if( ! is_logged_in( false ) )
	{ // User is not logged in:
		$comment_options[] = '<label class="control-label"><input type="checkbox" class="checkbox form-control" name="comment_cookies" tabindex="7"'
													.( $comment_cookies ? ' checked="checked"' : '' ).' value="1" /> '.__('Remember me').'</label>'
													.' <span class="help-inline">('.__('For my next comment on this site').')</span>';
		// TODO: If we got info from cookies, Add a link called "Forget me now!" (without posting a comment).
	}

	if( ! empty($comment_options) )
	{
		echo $Form->begin_field( NULL, __('Options'), true );
		echo implode( '<br />', $comment_options );
		echo $Form->end_field();
	}

	// Display renderers
	$comment_renderer_checkboxes = $Plugins->get_renderer_checkboxes( array( 'default' ), array( 'Blog' => & $Blog, 'setting_name' => 'coll_apply_comment_rendering' ) );
	if( !empty( $comment_renderer_checkboxes ) )
	{
		$Form->begin_fieldset();
		echo '<div class="control-label">'.__('Text Renderers').':</div>';
		echo '<div class="form-control">'.$comment_renderer_checkboxes.'</div>';
		$Form->end_fieldset();
	}

	$Plugins->trigger_event( 'DisplayCommentFormFieldset', array( 'Form' => & $Form, 'Item' => & $Item ) );

	$Form->begin_fieldset();
		echo $Form->buttonsstart;

		$preview_text = ( $Item->can_attach() ) ? __('Preview/Add file') : __('Preview');
		$Form->button_input( array( 'name' => 'submit_comment_post_'.$Item->ID.'[save]', 'class' => 'submit', 'value' => $params['form_submit_text'], 'tabindex' => 10 ) );
		$Form->button_input( array( 'name' => 'submit_comment_post_'.$Item->ID.'[preview]', 'class' => 'preview', 'value' => $preview_text, 'tabindex' => 9 ) );

		$Plugins->trigger_event( 'DisplayCommentFormButton', array( 'Form' => & $Form, 'Item' => & $Item ) );

		echo $Form->buttonsend;
	$Form->end_fieldset();
	?>

	<div class="clear"></div>

	<?php
	$Form->end_form();
	echo '</div>';

	echo $params['after_comment_form'];

	echo_comment_reply_js( $Item );
}
?>