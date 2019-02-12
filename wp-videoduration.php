<?php
/**
 * Plugin Name:       WP Video Duration
 * Plugin URI:        https://coresol.pk
 * Description:       A small and lightweight video duration plugin for first youtube and vimeo videos in post description .
 * Version:           3.3.2
 * Author:            Hassan Ali
 * Author URI:        https://coresol.pk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-videoduration
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_shortcode('duration-video-post-editing-shortcode', 'wp_videoduration_shortcodes');
function wp_videoduration_shortcodes(){
	global $post;
	
	if(!empty($post)){
		$duration = get_post_meta($post->ID, 'time_duration', true);
	}
	return $duration;
}
add_action('admin_menu', 'custom_wp_videoduration_menu');
function custom_wp_videoduration_menu(){
	add_menu_page( 
      __('API Setting', 'wp-videoduration'), 
      __('API Setting', 'wp-videoduration'), 
      'edit_posts', 
      'duration-setting', 
      'wp_videoduration_callback_function', 
      'dashicons-media-spreadsheet'
     );
}
function wp_videoduration_callback_function(){
	if(isset($_POST['duration_submit'])){
		update_option( 'duration_youtube_key', (isset($_POST['duration_youtube_key']) ? $_POST['duration_youtube_key'] : '') );
		update_option( 'duration_vimeo_key', (isset($_POST['duration_vimeo_key']) ? $_POST['duration_vimeo_key'] : '') );
	}
	$duration_youtube_key = get_option( 'duration_youtube_key');
	$duration_vimeo_key = get_option( 'duration_vimeo_key');
	?>
	<h1> Video Duration Setting </h1>
	<h3> Shortcode : [duration-video-post-editing-shortcode] </h3>
	<form action="" method="post" style="padding:30px;">
	<label> Youtube API Key</label><br />
	<input type="text" name="duration_youtube_key" class="regular-text" value="<?php echo $duration_youtube_key; ?>" placeholder="Youtube API Key" /> <a href="https://www.slickremix.com/docs/get-api-key-for-youtube/" target="_blank"> How to get API Key</a><br />
	<label> Vimeo API Authorization Key</label><br />
	<input type="text" name="duration_vimeo_key" class="regular-text" value="<?php echo $duration_vimeo_key; ?>" placeholder="Vimeo Pubic Authorization key" /><a href="https://developer.vimeo.com/apps/" target="_blank"> Create Authorization Key (Public) </a><br /><br />
	<input type="submit" value="Save Setting" name="duration_submit" class="button button-primary"/>
	</form>
	<?php
}
add_action( 'save_post', 'wp_videoduration_metaboxes', 1, 2 );
function wp_videoduration_metaboxes($post_id, $post){
	$post_content = $post->post_content;

	preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $post_content, $match);
	if( !empty($match) && !empty($match[0]) ){
		$all_links = $match[0];
		if(!empty($all_links)){
			foreach($all_links as $first_link){
				$provider = videoduration_check_video_provider($first_link);
				if($provider == 'youtube'){
					$time_duration_link = get_post_meta($post_id, 'time_duration_link', true);
					if($time_duration_link !== $first_link){
						$api_key = get_option( 'duration_youtube_key');
						videoduration_youtube_video_duration($first_link, $api_key, $post_id);
						update_post_meta($post_id, 'time_duration_link', $first_link);
					}
					break;
				} else if($provider == 'vimeo'){
					$time_duration_link = get_post_meta($post_id, 'time_duration_link', true);
					if($time_duration_link !== $first_link){
						videoduration_vimeo_video_duration($first_link, $post_id);
						update_post_meta($post_id, 'time_duration_link', $first_link);
					}
					break;
				}				
			}
		}
	}
}
function videoduration_check_video_provider($url){
    //This is a general function for generating an embed link of an FB/Vimeo/Youtube Video.
    $provider = '';
    if(strpos($url, 'vimeo.com/') !== false) {
        $provider = 'vimeo';
    }else if(strpos($url, 'youtube.com/') !== false || strpos($url, 'youtu.be/') !== false) {
		$provider = 'youtube';
    }
	return $provider;
}

function videoduration_youtube_video_duration($video_url, $api_key, $post_id) {

    // video id from url
    parse_str(parse_url($video_url, PHP_URL_QUERY), $get_parameters);
    $video_id = $get_parameters['v'];

    // video json data
    $json_result = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=$video_id&key=$api_key");
    $result = json_decode($json_result, true);

    // video duration data
    if (!count($result['items'])) {
        return null;
    }
    $duration_encoded = $result['items'][0]['contentDetails']['duration'];

    // duration
    $interval = new DateInterval($duration_encoded);
    $seconds = $interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s;
	if($seconds){
		$duration = gmdate("i:s", $seconds);
		update_post_meta($post_id, 'time_duration', $duration);
	}
}
function videoduration_vimeo_video_duration($video_url, $post_id) {
	$parsed = parse_url($video_url, PHP_URL_PATH);
	$parsed_url = explode("/", $parsed);
	$video_id = 0;
	if(!empty($parsed_url)){
		$video_id = (int) $parsed_url[count($parsed_url) - 1];
	}
	try {
		$authorization = get_option( 'duration_vimeo_key');
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => "https://api.vimeo.com/videos/$video_id?fields=duration",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"authorization: Bearer {$authorization}",
				"cache-control: no-cache",
			),
		));

		$res = curl_exec($ch);
		$obj = json_decode($res, true);
		if(!empty($obj)){
			$duration = gmdate("i:s", $obj['duration']);
			update_post_meta($post_id, 'time_duration', $duration);
		}
	} catch (Exception $e) {
	   # returning 0 if the Vimeo API fails for some reason.
	   return "0";
	}
}


if(!function_exists('videoduration_plugin_action_links')){
	function videoduration_plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( 'admin.php?page=duration-setting' ) ) . '">' . __( 'Settings', 'wp-videoduration' ) . '</a>'
		), $links );
		return $links;
	}
	add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'videoduration_plugin_action_links' );
}


$duration_youtube_key = get_option( 'duration_youtube_key');
$duration_vimeo_key = get_option( 'duration_vimeo_key');
if(!$duration_youtube_key) {
  add_action( 'admin_notices', 'duration_youtube_inactive_notice' );
}
if(!$duration_vimeo_key) {
  add_action( 'admin_notices', 'duration_vimeo_inactive_notice' );
}
if(!function_exists('duration_youtube_inactive_notice')) {
	function duration_youtube_inactive_notice() {
		$youtube_url = admin_url( 'admin.php?page=duration-setting' );
		?>
		<div id="message" class="error">
			<p><?php echo  __( 'Please Enter <a target="_blank" href="' . $youtube_url . '"> Youtube API Key </a>', 'wp-videoduration' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('duration_vimeo_inactive_notice')) {
	function duration_vimeo_inactive_notice() {
		$youtube_url = admin_url( 'admin.php?page=duration-setting' );
		?>
		<div id="message" class="error">
			<p><?php echo  __( 'Please Enter <a target="_blank" href="' . $youtube_url . '"> Vimeo API Authorization Key </a>', 'wp-videoduration' ); ?></p>
		</div>
		<?php
	}
}