<?php
/*
 * gplus-wpcom-relay
 * Main script
 * https://github.com/woganmay/gplus-wpcom-relay
 */

// Wake up
require_once __DIR__."/config.php";
stdout("Starting gplus-wpcom-relay");
stdout("Profile ID: ".$Config['profileId']);

// Fetch public stream from Google+
$call = $Config['apiUrl'] . $Config['profileId'] . "/activities/public?key=" . $Config['apiKey'];
$json = @file_get_contents($call);

// Prepare for the main loop
stdout("Fetched Activities (public)");

$object = json_decode($json);
$count = count($object->items);
$proc = 0;
$x = 0;

stdout("Found ".$count." items - iterating");

// Check every item
	// If it looks like a share (note and attachment), post it
	// Otherwise, disregard
	// Either way, write it to the history folder
foreach($object->items as $item) {
		
	$x++; // This iteration
	$id = $item->id; // The unique ID of the post object
	
	stdout("[$x] Item ID: $id");
	
	// Where the history file will be located (guaranteed to be unique)
	$historyFile = $Config['historyRoot'] . $id . ".history";
	
	if (file_exists($historyFile)) {
		
		// This ID has already been dealt with - nothing to do here.
		stdout("[$x] Post has already been processed, nothing to do.");
		
	} else {
		
		// We haven't seen this one before!
		stdout("[$x] Processing post...");
		
		// This is what a Share looks like
		if ($item->object->objectType == "note" && @$item->object->attachments[0]->objectType == "article") {
		
			stdout("[$x] Looks like a valid share (type==note with article attachment)");
			
			// Split out everything before the first dash as the title
			// For instance:
			//     Saw this cool thing today - It was a banana on rollerskates! (post content on G+)
			//     $title => "Saw this cool thing today"
			//     $content => "It was a banana on rollerskates!"
			
			$post = $item->object->content;
			$p = strpos($post, " - ");
			if ($p === false) {
				// No dash found. Set a generic date/time title 
				$title = "Shared on ".date("j-M g:ia", strtotime($item->published));
				$content = $post;
			} else {
				// Split them out
				$title = trim(substr($post, 0, $p));
				$content = trim(substr($post, $p+3));
			}
			
			stdout("[$x]    Title: $title");
			
			// The first "attachment" will be a link to whatever was shared
			// TODO handle empty attachment array gracefully
			$linkName = $item->object->attachments[0]->displayName;
			$linkUrl  = $item->object->attachments[0]->url;
			
			stdout("[$x]    Link Title: $linkName");
			stdout("[$x]    Link URL: $linkUrl");
			
			if ($content !== "") {
				$body = $content . "\n\n";
				$body .= "<a href='$linkUrl'>$linkName</a>";
			} else {
				$body = "<a href='$linkUrl'>$linkName</a>";
			}
			
			// Put together the email that gets sent to post-by-email
			// Details: http://en.support.wordpress.com/post-by-email/#shortcodes
			stdout("[$x] Composing Email");
			$email = $body . "\n\n";
			$email .= "[title $title]\n";
			$email .= "[category ".$Config['wpCategory']."]\n";
			$email .= "[comments on]\n";
			$email .= "[status publish]\n";
			$email .= "[end]";
			
			if (mail($Config['emailTo'], $title, $email)) {
				// No issues, the mail should have made it to that side successfully
				stdout("[$x] Mail sent successfully!");
				$proc++; // We've processed a G+ post
				
				// Record this run in the history, to prevent it from going again
				$h = fopen($historyFile, "w+");
				fwrite($h, $email);
				fclose($h);
				
			} else {
				// Unable to send the mail - even though it's a valid post etc
				// Don't record this run in history, so that it gets retried next time
				stdout("[$x] Error while sending email!");
			}
			
		} else {
		
			// Not a G+ Share
			stdout("[$x] Not a Gplus share - skipping and marking in history");
			$h = fopen($historyFile, "w+");
			fwrite($h, "Not a share - nothing done");
			fclose($h);
			
		}
		
	}
	
}

stdout("Complete - checked $count posts, dispatched $proc");

// And we're done!