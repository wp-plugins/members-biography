<?php
/*
 * Plugin Name: Biography
 * Description: This Plugin should use to store couple of member information.
 * Version: 1.0.0
 * Author: Tanmoy Dhara
 * License: MBio
 */
wp_enqueue_style( 'member-biography-style', plugins_url('plugin.css', __FILE__), false, '1.0.0', 'all' );
?>
<script>
	$(document).ready(function(){
		$("#notupload").show(600);
	});
</script>
<?php

$your_db_name = $wpdb->prefix . 'biography';
 
// function to create the DB / Options / Defaults					
function bio_plgn_inst() {
   	global $wpdb;
  	global $your_db_name;
 
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '$your_db_name'") != $your_db_name) 
	{
		$sql = "CREATE TABLE " . $your_db_name . " (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`author_name` mediumtext NOT NULL,
		`author_designation` tinytext NOT NULL,
		`author_details` tinytext NOT NULL,
		`author_image` tinytext NOT NULL,
		UNIQUE KEY id (id)
		);";
 
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
 
}
// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'bio_plgn_inst');
 
echo bio_plgn_inst();

////////////////////
if($_POST['submit'] == 'Save Changes'){
	extract($_POST);
	$time = time();
	$upload_dir = wp_upload_dir();
	$target_url = $upload_dir['url'].'/'.$time.basename($_FILES["author_image"]["name"]);
	$target_dir = $upload_dir['path'];
	$target_file = $target_dir .'/'.$time.basename($_FILES["author_image"]["name"]);
	$type = $_FILES['author_image']['type'];
	// Check if $uploadOk is set to 0 by an error
	if ($_FILES["author_image"]["error"] > 0) { ?>
		
		<script>
        	$(document).ready(function(){
        		$("#notupload").show(600);
        	});
        </script>
	<?php
	    //echo "Sorry, your file was not uploaded.";
	// if everything is ok, try to upload file
	}
	// Allow certain file formats
	elseif($type != 'image/jpeg' && $type != 'image/png' && $type != 'image/jpg' && $type != 'image/gif' && $type != 'image/bmp' ) { ?>
	   <script>
        	$(document).ready(function(){
        		$("#imgformat").show(600);
        	});
        </script>
   <?php
	}
	 else {
	    if (move_uploaded_file($_FILES["author_image"]["tmp_name"], $target_file)) {
	        ?>
	        
	        <script>
	        	$(document).ready(function(){
	        		$("#success").show(600);
	        	});
	        </script>
	        <?php
				$wpdb->insert("wp_biography", array(
				   "author_name" => $author_name,
				   "author_designation" => $author_designation,
				   "author_details" => $author_details,
				   "author_image" => $target_url
				));
	    } else {
	        echo "Sorry, there was an error uploading your file.";
	    }
	}
}


 
 
add_action( 'wp_head', 'my_fcb_tg' );
function my_fcb_tg() {
if( is_single() ) {
?>
<meta property="og:title" content="<?php the_title() ?>" />
<meta property="og:site_name" content="<?php bloginfo( 'name' ) ?>" />
<meta property="og:url" content="<?php the_permalink() ?>" />
<meta property="og:description" content="<?php the_excerpt() ?>" />
<meta property="og:type" content="article" />

<?php
if ( has_post_thumbnail() ) :
$image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
?>
<meta property="og:image" content="<?php echo $image[0]; ?>"/>
<?php endif; ?>
<?php
}
} 

add_action( 'publish_post', 'post_published_notification', 10, 2 );
function post_published_notification( $ID, $post ) {
	$email = get_the_author_meta( 'user_email', $post->post_author );
	$subject = 'Published ' . $post->post_title;
	$message = 'We just published your post: ' . $post->post_title . ' take a look: ' . get_permalink( $ID );
	wp_mail( $email, $subject, $message );
} 
 

add_action('admin_menu', 'biog_menu');
function biog_menu() {
	add_menu_page('Biography Settings', 'Biography', 'administrator', 'biography_options', 'biog_set_page', 'dashicons-admin-generic');
	add_submenu_page( 'biography_options', 'Add new biography', 'Add New', 'administrator', 'add_new_biography', 'biography_submenu' ); 
}

add_action( 'admin_init', 'biography_settings' );
function biography_settings() {
	register_setting( 'biog-sets-group', 'author_name' );
	register_setting( 'biog-sets-group', 'author_designation' );
	register_setting( 'biog-sets-group', 'author_details' );
	register_setting( 'biog-sets-group', 'author_image' );
} 

//Add New Member Form

function biography_submenu() {
?>


<div class="wrap">
<h2>Add New Member </h2>
 
<form method="post" action="" enctype="multipart/form-data">
	<?php settings_fields( 'biog-sets-group' ); ?>
	<?php do_settings_sections( 'biog-sets-group' ); ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row">Author Name</th>
			<td><input type="text" name="author_name" value="" required="" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Author Designation</th>
			<td><input type="text" name="author_designation" value="" required="" /></td>
		</tr>
		<tr valign="top">
			<th scope="row">Author Description</th>
			<td><textarea name="author_details"></textarea></td>
		</tr>
		<tr valign="top">
			<th scope="row">Author Image</th>
			<td><input type="file" name="author_image" /></td>
		</tr>
	</table>
	<div>
		<input type="submit" value="Save Changes" class="button button-primary" style="margin-right: 7%;float:left;" id="submit" name="submit">
		<!----- Data Entry Successfully--------->
		<div class="scces_msg" id="success">
			Data Entry Successfully
		</div>
		
		<div class="not_insrt" id="notupload">
			Data not inserted
		</div>
		<div class="wrong_file" id="imgformat">
			Sorry, only JPG, JPEG, PNG & GIF files are allowed.
		</div>
	</div>



</form>

</div> 

<?php } 

//All Member List

function biog_set_page(){ ?>
	<div class="list_outer">
		<span>
			<h3><strong>Shortcode</strong></h3>
			<p style="">Use this shortcode in your PHP page only.</p>
			<strong><?php echo "&lt;&#63php echo do_shortcode('[MemberAll]'); &#63&gt"; ?></strong>  
		</span>
		<div class="tablenav top">
			<br class="clear">
		</div>
		<table width="100%" cellpadding="10px" cellspacing="0">
			<thead style="background: #ffffff;box-shadow: 0px -1px 2px rgba(0,0,0,0.1);" class="thd">
				<tr>
					<th style="border-bottom: 2px #0074A2 solid;" class="manage-column column-cb check-column" id="cb" scope="col">
						<label for="cb-select-all-1" class="screen-reader-text">Select All</label>
						<input type="checkbox" id="cb-select-all-1">
					</th>
					<td width="5%" style="border-bottom: 2px #0074A2 solid; font-size: 15px;">Sl no</td>
					<td width="25%" style="border-bottom: 2px #0074A2 solid; font-size: 15px;">Name</td>
					<td width="20%" style="border-bottom: 2px #0074A2 solid; font-size: 15px;">Designation</td>
					<td width="30%" style="border-bottom: 2px #0074A2 solid; font-size: 15px;">Details</td>
					<td width="20%" style="border-bottom: 2px #0074A2 solid; font-size: 15px;">Image</td>
				</tr>
			</thead>
			<tbody>
				<?php 
					global $wpdb;
					$results = $wpdb->get_results("SELECT * FROM `wp_biography`");
					$i = 1;
		        	foreach ( $results as $row ){
	        	?>
				<tr <?php if($i%2==0){ ?> class="alternate" <?php } ?>>
					<th class="check-column" scope="row">
						<label for="cb-select-1" class="screen-reader-text">Select Hello world!</label>
						<input type="checkbox" value="1" name="post[]" id="cb-select-1">
						<div class="locked-indicator"></div>
					</th>
					<td width=" 5%" style=""><?php echo $row->id; ?></td>
					<td width="25%" style="">
						<?php echo $row->author_name; ?>
					</td>
					<td width="20%" style=""><?php echo $row->author_designation; ?></td>
					<td width="30%" style=""><?php echo $row->author_details; ?></td>
					<td width="20%" style=""><img src="<?php echo $row->author_image; ?>" width="40px" height="40px" /></td>
				</tr>
				<?php $i++; } ?>
			</tbody>
			
		</table>
	</div>
<?php	
	}

function memr_biog(){

	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM `wp_biography`");
	$i = 1;
	foreach ( $results as $row ){
?>
	<div class="bio_outer">
		<h3><?php echo $row->author_name; ?></h3>
		<h4><?php echo $row->author_designation; ?></h4>
		<span><img src="<?php echo $row->author_image; ?>" /></span>
		<p><?php echo $row->author_details; ?></p>
	</div>
	<?php $i++; } 

}

add_shortcode( 'MemberAll' , 'memr_biog' );


