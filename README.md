<div align="center">

  <small><p><em><g-emoji class="g-emoji" alias="dart" fallback-src="https://assets-cdn.github.com/images/icons/emoji/unicode/1f3af.png" title=":dart:">🎯</g-emoji> — Made with Love.</em></p></small>


  <h1><code>Video Duration Plugin for WordPress (Youtube | Vimeo)</code></h1>

[![GitHub](https://img.shields.io/wordpress/v/akismet.svg?colorA=D14543&colorB=21759B&maxAge=2592000&style=flat&label=WordPress)](https://github.com/creativehassan/wp-videoduration)

</div>
⚡️ Video Duration Plugin for (Youtube | Vimeo)

This WordPress plugin allows you to grab time duration of any video from youtube or video plateform.

## How it works ##
Plugin find the first video from the post content either it is youtube or vimeo video and collect its time duration from corresponding API's and save it as custom post meta. You can use it anywhere for post as 

$ $duration = get_post_meta($post->ID, 'time_duration', true);

## Shortcode ##
Shortcode to use it on post [duration-video]


## License ##

This plugin is free software licensed under the [BSD 2-Clause](http://www.opensource.org/licenses/bsd-license.php) license.

## Installation ##

1. Upload the plugin files to the `/wp-content/plugins/wp-videoduration` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Use the API Setting screen to configure the API's of Youtube | Vimeo .

_Find me 👋 on WordPress for more updates and questions_ → https://profiles.wordpress.org/creativehassan/