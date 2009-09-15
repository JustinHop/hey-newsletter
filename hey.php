<?php
/*
Plugin Name: HEY Newsletter
Plugin URI: http://code.justinhoppensteadt.com/heynl
Description: HEY Newsletter hack
Author: Justin Hoppensteadt
Version: 0.1
Author URI: http://justinhoppensteadt.com
Generated At: www.wp-fun.co.uk;
*/ 


add_action( 'init', 'hey_head' );
add_action( 'admin_menu', 'hey_menu' );
add_action( 'admin_menu', 'hey_create_meta_box' );
add_action('save_post', 'hey_save_postdata');  


$wp_root = ABSPATH;

include_once($wp_root.'/wp-load.php');
include_once($wp_root.'/wp-includes/wp-db.php');

function hey_menu(){
    if(function_exists('add_menu_page')) {
        add_menu_page('HEY Newsletter','HEY Newsletter',7,__FILE__,'hey_new_newsletter');
        add_submenu_page(__FILE__,'HEY Newsletter','New',7,__FILE__,'hey_new_newsletter');
        add_submenu_page(__FILE__,'HEY Newsletter','View',7, dirname(__FILE__) . 'hey_edit.php','hey_edit_newsletter');
    }
}

function hey_new_newsletter(){
    $action_url = $_SERVER['REQUEST_URI'];
    include('hey_new.php');
}

function hey_edit_newsletter(){
    $action_url = $_SERVER['REQUEST_URI'];
    include('hey_edit.php');
}

function hey_head(){
    if (is_admin()
       // && 
//        (   preg_match('/(hey|post).*php/',basename( $_SERVER[ 'REQUEST_URI' ] ) )
        //      )  
        )
    {
        wp_enqueue_script('hey', WP_PLUGIN_URL.'/hey-newsletter/hey.js');
        wp_enqueue_style('hey', WP_PLUGIN_URL.'/hey-newsletter/hey.css');
        wp_enqueue_script('jquery-ui', WP_PLUGIN_URL.'/hey-newsletter/js/jquery-ui-1.7.2.custom.min.js');
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('inline-edit-post');
        wp_enqueue_style('jquery-ui', WP_PLUGIN_URL.'/hey-newsletter/css/ui-lightness/jquery-ui-1.7.2.custom.css');
    }


}

$new_meta_boxes =  
array(  
    "newsletter_date" => array(  
        "name" => "newsletter_date",  
        "std" => "0000-00-00",  
        "title" => "Newsletter Date",  
        "description" => "Select the date of the newsletter to add this article to."),
    "newsletter_order" => array(
        "name" => "newsletter_order",  
        "std" => "0",  
        "title" => "Newsletter Order",  
        "description" => "The order in which the article will display in its newsletter"),
    "original_author" => array(  
        "name" => "original_author",  
        "std" => "",  
        "title" => "Original Author",  
        "description" => "The original author of the post, if any."),
    "original_url" => array(  
        "name" => "original_url",  
        "std" => "",  
        "title" => "Original URL",  
        "description" => "The original URL of the post, if any.")
);  

function hey_meta_boxes() {  
    global $post, $new_meta_boxes;  

    foreach($new_meta_boxes as $meta_box) {  
        $meta_box_value = get_post_meta($post->ID, $meta_box['name'].'_value', true);  

        if($meta_box_value == "")  
            $meta_box_value = $meta_box['std'];  

        echo'<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';  

        echo'<div class="heyoutline">'.$meta_box['title'];  

        if($meta_box['name'] !== "newsletter_order") {
            echo'<input type="text" id="'.$meta_box['name'].'_value" name="'.$meta_box['name'].'_value" value="'.$meta_box_value.'" size="25" /><br />';  
        } else {
            echo'<select id="'.$meta_box['name'].'_value" name="'.$meta_box['name'].'_value">';  
            echo '<option value="'.$meta_box_value.' selected="selected">'.$meta_box_value.'</option>';
            for ($hcnt=0;$hcnt<=15;$hcnt++){
                if ($hcnt != $meta_box_value){
                    echo '<option value="'.$hcnt.'">'.$hcnt.'</option>';
                }
            }
            echo '</select><br />';



        }  
        echo'<label for="'.$meta_box['name'].'_value">'.$meta_box['description'].'</label></div>';  
    }
} 

function hey_create_meta_box() {  
    global $theme_name;  
    if ( function_exists('add_meta_box') ) {  
        add_meta_box( 'hey-meta-boxes', 'HEY Custom Post Settings', 'hey_meta_boxes', 'post', 'side', 'high' );  
    }  
}  

function hey_save_postdata( $post_id ) {  
    global $post, $new_meta_boxes;  

    foreach($new_meta_boxes as $meta_box) {  
        // Verify  
        if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {  
            return $post_id;  
        }  

        if ( 'page' == $_POST['post_type'] ) {  
            if ( !current_user_can( 'edit_page', $post_id ))  
                return $post_id;  
        } else {  
            if ( !current_user_can( 'edit_post', $post_id ))  
                return $post_id;  
        }  

        $data = $_POST[$meta_box['name'].'_value'];  

        if(get_post_meta($post_id, $meta_box['name'].'_value') == "")  
            add_post_meta($post_id, $meta_box['name'].'_value', $data, true);  
        elseif($data != get_post_meta($post_id, $meta_box['name'].'_value', true))  
            update_post_meta($post_id, $meta_box['name'].'_value', $data);  
        elseif($data == "")  
            delete_post_meta($post_id, $meta_box['name'].'_value', get_post_meta($post_id, $meta_box['name'].'_value', true));  
    }  
}  

/**
 * {@internal Missing Short Description}}
 *
 * Outputs the quick edit and bulk edit table rows for posts and pages
 *
 * @since 2.7
 *
 * @param string $type 'post' or 'page'
 */
function hey_inline_edit_row( $type ) {
	global $wpdb, $current_user, $mode;

	$is_page = 'page' == $type;
	if ( $is_page ) {
		$screen = 'edit-pages';
		$post = get_default_page_to_edit();
	} else {
		$screen = 'edit';
		$post = get_default_post_to_edit();
	}

	$columns = $is_page ? wp_manage_pages_columns() : wp_manage_posts_columns();
	$hidden = array_intersect( array_keys( $columns ), array_filter( get_hidden_columns($screen) ) );
	$col_count = count($columns) - count($hidden);
	$m = ( isset($mode) && 'excerpt' == $mode ) ? 'excerpt' : 'list';
	$can_publish = current_user_can("publish_{$type}s");
	$core_columns = array( 'cb' => true, 'date' => true, 'title' => true, 'categories' => true, 'tags' => true, 'comments' => true, 'author' => true );

?>

<form method="post" action=""><table style="display: none"><tbody id="inlineedit">
	<?php
	$bulk = 0;
	while ( $bulk < 2 ) { ?>

	<tr id="<?php echo $bulk ? 'bulk-edit' : 'inline-edit'; ?>" class="inline-edit-row inline-edit-row-<?php echo "$type ";
		echo $bulk ? "bulk-edit-row bulk-edit-row-$type" : "quick-edit-row quick-edit-row-$type";
	?>" style="display: none"><td colspan="<?php echo $col_count; ?>">

	<fieldset class="inline-edit-col-left"><div class="inline-edit-col">
		<h4><?php echo $bulk ? ( $is_page ? __( 'Bulk Edit Pages' ) : __( 'Bulk Edit Posts' ) ) : __( 'Quick Edit' ); ?></h4>


<?php if ( $bulk ) : ?>
		<div id="bulk-title-div">
			<div id="bulk-titles"></div>
		</div>

<?php else : // $bulk ?>

		<label>
			<span class="title"><?php _e( 'Title' ); ?></span>
			<span class="input-text-wrap"><input type="text" name="post_title" class="ptitle" value="" /></span>
		</label>

<?php endif; // $bulk ?>


<?php if ( !$bulk ) : ?>

		<label>
			<span class="title"><?php _e( 'Slug' ); ?></span>
			<span class="input-text-wrap"><input type="text" name="post_name" value="" /></span>
		</label>

		<label><span class="title"><?php _e( 'Date' ); ?></span></label>
		<div class="inline-edit-date">
			<?php touch_time(1, 1, 4, 1); ?>
		</div>
		<br class="clear" />

<?php endif; // $bulk

		ob_start();
		$authors = get_editable_user_ids( $current_user->id, true, $type ); // TODO: ROLE SYSTEM
		if ( $authors && count( $authors ) > 1 ) :
			$users_opt = array('include' => $authors, 'name' => 'post_author', 'class'=> 'authors', 'multi' => 1);
			if ( $bulk )
				$users_opt['show_option_none'] = __('- No Change -');
?>
		<label>
			<span class="title"><?php _e( 'Author' ); ?></span>
			<?php wp_dropdown_users( $users_opt ); ?>
		</label>

<?php
		endif; // authors
		$authors_dropdown = ob_get_clean();
?>

<?php if ( !$bulk ) : echo $authors_dropdown; ?>

		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php _e( 'Password' ); ?></span>
				<span class="input-text-wrap"><input type="text" name="post_password" class="inline-edit-password-input" value="" /></span>
			</label>

			<em style="margin:5px 10px 0 0" class="alignleft">
				<?php
				/* translators: Between password field and private checkbox on post quick edit interface */
				echo __( '&ndash;OR&ndash;' );
				?>
			</em>
			<label class="alignleft inline-edit-private">
				<input type="checkbox" name="keep_private" value="private" />
				<span class="checkbox-title"><?php echo $is_page ? __('Private page') : __('Private post'); ?></span>
			</label>
		</div>

<?php endif; ?>

	</div></fieldset>

<?php if ( !$is_page && !$bulk ) : ?>

	<fieldset class="inline-edit-col-center inline-edit-categories"><div class="inline-edit-col">
		<span class="title inline-edit-categories-label"><?php _e( 'Categories' ); ?>
			<span class="catshow"><?php _e('[more]'); ?></span>
			<span class="cathide" style="display:none;"><?php _e('[less]'); ?></span>
		</span>
		<ul class="cat-checklist">
			<?php wp_category_checklist(); ?>
		</ul>
	</div></fieldset>

<?php endif; // !$is_page && !$bulk ?>

	<fieldset class="inline-edit-col-right"><div class="inline-edit-col">

<?php
	if ( $bulk )
		echo $authors_dropdown;
?>

<?php if ( $is_page ) : ?>

		<label>
			<span class="title"><?php _e( 'Parent' ); ?></span>
<?php
	$dropdown_args = array('selected' => $post->post_parent, 'name' => 'post_parent', 'show_option_none' => __('Main Page (no parent)'), 'option_none_value' => 0, 'sort_column'=> 'menu_order, post_title');
	if ( $bulk )
		$dropdown_args['show_option_no_change'] =  __('- No Change -');
	$dropdown_args = apply_filters('quick_edit_dropdown_pages_args', $dropdown_args);
	wp_dropdown_pages($dropdown_args);
?>
		</label>

<?php	if ( !$bulk ) : ?>

		<label>
			<span class="title"><?php _e( 'Order' ); ?></span>
			<span class="input-text-wrap"><input type="text" name="menu_order" class="inline-edit-menu-order-input" value="<?php echo $post->menu_order ?>" /></span>
		</label>

<?php	endif; // !$bulk ?>

		<label>
			<span class="title"><?php _e( 'Template' ); ?></span>
			<select name="page_template">
<?php	if ( $bulk ) : ?>
				<option value="-1"><?php _e('- No Change -'); ?></option>
<?php	endif; // $bulk ?>
				<option value="default"><?php _e( 'Default Template' ); ?></option>
				<?php page_template_dropdown() ?>
			</select>
		</label>

<?php elseif ( !$bulk ) : // $is_page ?>

		<label class="inline-edit-tags">
			<span class="title"><?php _e( 'Tags' ); ?></span>
			<textarea cols="22" rows="1" name="tags_input" class="tags_input"></textarea>
		</label>

<?php endif; // $is_page  ?>

<?php if ( $bulk ) : ?>

		<div class="inline-edit-group">
		<label class="alignleft">
			<span class="title"><?php _e( 'Comments' ); ?></span>
			<select name="comment_status">
				<option value=""><?php _e('- No Change -'); ?></option>
				<option value="open"><?php _e('Allow'); ?></option>
				<option value="closed"><?php _e('Do not allow'); ?></option>
			</select>
		</label>

		<label class="alignright">
			<span class="title"><?php _e( 'Pings' ); ?></span>
			<select name="ping_status">
				<option value=""><?php _e('- No Change -'); ?></option>
				<option value="open"><?php _e('Allow'); ?></option>
				<option value="closed"><?php _e('Do not allow'); ?></option>
			</select>
		</label>
		</div>

<?php else : // $bulk ?>

		<div class="inline-edit-group">
			<label class="alignleft">
				<input type="checkbox" name="comment_status" value="open" />
				<span class="checkbox-title"><?php _e( 'Allow Comments' ); ?></span>
			</label>

			<label class="alignleft">
				<input type="checkbox" name="ping_status" value="open" />
				<span class="checkbox-title"><?php _e( 'Allow Pings' ); ?></span>
			</label>
		</div>

<?php endif; // $bulk ?>


		<div class="inline-edit-group">
			<label class="inline-edit-status alignleft">
				<span class="title"><?php _e( 'Status' ); ?></span>
				<select name="_status">
<?php if ( $bulk ) : ?>
					<option value="-1"><?php _e('- No Change -'); ?></option>
<?php endif; // $bulk ?>
				<?php if ( $can_publish ) : // Contributors only get "Unpublished" and "Pending Review" ?>
					<option value="publish"><?php _e( 'Published' ); ?></option>
					<option value="future"><?php _e( 'Scheduled' ); ?></option>
<?php if ( $bulk ) : ?>
					<option value="private"><?php _e('Private') ?></option>
<?php endif; // $bulk ?>
				<?php endif; ?>
					<option value="pending"><?php _e( 'Pending Review' ); ?></option>
					<option value="draft"><?php _e( 'Draft' ); ?></option>
				</select>
			</label>

<?php if ( !$is_page && $can_publish && current_user_can( 'edit_others_posts' ) ) : ?>

<?php	if ( $bulk ) : ?>

			<label class="alignright">
				<span class="title"><?php _e( 'Sticky' ); ?></span>
				<select name="sticky">
					<option value="-1"><?php _e( '- No Change -' ); ?></option>
					<option value="sticky"><?php _e( 'Sticky' ); ?></option>
					<option value="unsticky"><?php _e( 'Not Sticky' ); ?></option>
				</select>
			</label>

<?php	else : // $bulk ?>

			<label class="alignleft">
				<input type="checkbox" name="sticky" value="sticky" />
				<span class="checkbox-title"><?php _e( 'Make this post sticky' ); ?></span>
			</label>

<?php	endif; // $bulk ?>

<?php endif; // !$is_page && $can_publish && current_user_can( 'edit_others_posts' ) ?>

		</div>

	</div></fieldset>

<?php
	foreach ( $columns as $column_name => $column_display_name ) {
		if ( isset( $core_columns[$column_name] ) )
			continue;
		do_action( $bulk ? 'bulk_edit_custom_box' : 'quick_edit_custom_box', $column_name, $type);
	}
?>
	<p class="submit inline-edit-save">
		<a accesskey="c" href="#inline-edit" title="<?php _e('Cancel'); ?>" class="button-secondary cancel alignleft"><?php _e('Cancel'); ?></a>
		<?php if ( ! $bulk ) {
			wp_nonce_field( 'inlineeditnonce', '_inline_edit', false );
			$update_text = ( $is_page ) ? __( 'Update Page' ) : __( 'Update Post' );
			?>
			<a accesskey="s" href="#inline-edit" title="<?php _e('Update'); ?>" class="button-primary save alignright"><?php echo esc_attr( $update_text ); ?></a>
			<img class="waiting" style="display:none;" src="images/wpspin_light.gif" alt="" />
		<?php } else {
			$update_text = ( $is_page ) ? __( 'Update Pages' ) : __( 'Update Posts' );
		?>
			<input accesskey="s" class="button-primary alignright" type="submit" name="bulk_edit" value="<?php echo esc_attr( $update_text ); ?>" />
		<?php } ?>
		<input type="hidden" name="post_view" value="<?php echo $m; ?>" />
		<br class="clear" />
	</p>
	</td></tr>
<?php
	$bulk++;
	} ?>
	</tbody></table></form>
<?php
}



/*
<!--
 10 add_action('init', 'wdp_ajaxcomments_load_js', 10);
 11 function wdp_ajaxcomments_load_js(){
 12     if(!is_admin()){
 13         wp_enqueue_script('ajaxValidate', WP_PLUGIN_URL.'/wdp-ajax-comments/jquery.validate.min.js', array('jquery'), '1.5.5');
 14         wp_enqueue_script('ajaxcomments', WP_PLUGIN_URL.'/wdp-ajax-comments/ajax-comments.js',  array('jquery', 'ajaxValidate'), '1.2');
 15     }
 16 }

281 function tern_wp_event_menu() {
282     if(function_exists('add_menu_page')) {
283         add_menu_page('Event Page','Event Page',10,__FILE__,'tern_wp_event_options');
284         add_submenu_page(__FILE__,'Event Page','Settings',10,__FILE__,'tern_wp_event_options');
285         add_submenu_page(__FILE__,'Date Time Setings','Date Time Setings',10,'Date Time Setings','tern_wp_event_date_options');
286         add_submenu_page(__FILE__,'Configure Mark-Up','Configure Mark-Up',10,'Configure Mark-Up','tern_wp_event_markup_options');
287     }
288 }

--!>
 */
?>
