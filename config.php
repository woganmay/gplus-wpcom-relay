<?php
/*
 * gplus-wpcom-relay
 * Configuration
 * https://github.com/woganmay/gplus-wpcom-relay
 */

// Suppress those annoying PHP date() errors, plus 
date_default_timezone_set("Africa/Johannesburg");

$Config = array(
	"historyRoot" => __DIR__ . "/history/", // Path to history folder
	"apiUrl" => "https://www.googleapis.com/plus/v1/people/", // Hardcoded, trailing slash important
	"apiKey" => "XXXXXXXXXXXXXXXXXXXXXXX", // The Simple API Access Key from your API project @ google
	"profileId" => "110555803212391859805", // Your Google+ User ID (from your Profile page URL)
	"emailTo" => "XXXXXXXXXXXXXXX@post.wordpress.com", // The unique post-by-email address configured on wordpress.com
	"wpCategory" => "Google+ Shares" // The category to put the post in (has to exist on your blog)
);

// Neater CLI output to stdout
function stdout($str) {
	echo "[".date("H:i:s")."] $str\n";
}
