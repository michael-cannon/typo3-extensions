<?php
/**
 * Plugin Name: TYPO3 to WordPress Importer
 *
 * Importers for plugin
 *
 * @author Michael Cannon <michael@peimic.com>
 * @version $Id: importer.php,v 1.1 2011/06/07 06:58:50 peimic.comprock Exp $
 */

// for the function wp_insert_category() to work
require_once(ABSPATH . "wp-admin" . '/includes/taxonomy.php');

// for the function wp_generate_attachment_metadata() to work
require_once(ABSPATH . "wp-admin" . '/includes/image.php');

function ttw_migrate_data() {
	global $db_typo3, $db_wp, $uploadsDirTYPO3, $newlineWp, $newlineTypo3;

	// get tt_news data
	$sql = "
		SELECT uid
			, hidden
			, datetime
			, title
			, bodytext
			, tstamp
			, short
			, image
			, imagecaption
			, author
			, author_email
			, keywords
			, short
			, news_files
			, links
		FROM tt_news
		WHERE type = '0'
			AND deleted = 0
			AND pid > 0
	";
	// $sql .= "AND author LIKE '%Michael Cannon%' ";
	// $sql .= "AND bodytext NOT LIKE '%press-us@typo3.org%' ";
	// $sql .= "AND bodytext NOT LIKE '%Virgil%' ";
	// $sql .= "AND bodytext NOT LIKE '%Nicole%' ";
	// $sql .= "AND bodytext LIKE '%<img%' ";
	// ignore Acqal specific blogs
	// $sql .= 'AND uid NOT IN ( 219, 226, 230, 146, 144, 147, 202, 136, 148, 198, 184, 155, 154, 151, 138, 116, 111, 103 ) ';
	// $sql .= 'AND uid > 147 ';
	// test file download appending
	$sql .= 'AND uid = 44 ';
	$sql .= 'ORDER BY uid ASC ';
	// $sql .= 'LIMIT 50, 25 ';
	$sql .= 'LIMIT 0, 1 ';

	// echo 'sql' . ' : '; print_r($sql); echo '<br />';	
	// exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );	

	$result = mysql_query($sql, $db_typo3);

	$importCount = 0;
	while ($row = mysql_fetch_assoc($result)) {
		// echo 'row' . ' : '; print_r($row); echo '<br />';	

		$style = '';
		if ($row['hidden']) {
			$style = ' style="color: #999;"';
		}

		?>

		<p<?php echo $style ?>>
		<?php echo "Importing tt_news.uid {$row['uid']}: {$row['title']}"; ?>
		</p>
		<?php

		$date_format = 'Y-m-d H:i:s';

		// TYPO3 stores bodytext usually in psuedo HTML
		$bodytextOrig = $row['bodytext'];

		// convert LINK tags to A
		$bodytext = parseTypolinks($bodytextOrig);

		// remove broken link spans
		$bodytext = parseLinkSpans($bodytext);

		// clean up code samples
		$bodytext = parsePreCode($bodytext);

		// TODO parse out and attach images via <img tags
		// use regex to pull img src
		// foreach src push image to WP attachment
		// replace src with new WP image link
		echo 'row' . ' : '; print_r($row); echo '<br />';	
		exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );	

		// return carriage and newline used as line breaks, consolidate
		$bodytext = str_replace($newlineTypo3, $newlineWp, $bodytext);

		$post_content = $bodytext;

		// echo 'post_content' . ' : '; print_r($post_content); echo '<br />';	
		// exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );	

		$post_excerpt = $row['short'];
		$post_title = $row['title'];
		$post_date = date($date_format, $row['datetime']);
		$post_date_gmt = gmdate($date_format, $row['datetime']);
		$post_modified = date($date_format, $row['tstamp']);
		$post_modified_gmt = gmdate($date_format, $row['tstamp']);

		// TODO get post status interactively
		if ($row['hidden'] == '1') {
			$post_status = 'draft';
		} else {
			// $post_status = 'publish';
			// MLC forcing mine for now
			$post_status = 'private';
		}

		// Link each category to this post
		$catids = ttw_get_typo3_post_cats($row['uid']);
		$categoryArr = array();
		foreach ($catids as $cat_name) {
			// typo3 tt_news_cat => wp category taxomony
			$categoryArr[] = wp_create_category($cat_name);
		}

		// @ref http://codex.wordpress.org/Function_Reference/wp_insert_post
		// create post data array
		$post = array(
			'ID' => NULL,
			'post_category' => $categoryArr,
			'post_content' => $post_content,
			'post_date' => $post_date,
			'post_date_gmt' => $post_date_gmt,
			'post_excerpt' => $post_excerpt,
			'post_modified' => $post_modified,
			'post_modified_gmt' => $post_modified_gmt,
			'post_status' => $post_status,
			'post_title' => $post_title,
		);

		// Insert the post into the database
		$post_id = wp_insert_post( $post, true );

		// @ref http://codex.wordpress.org/Function_Reference/add_post_meta
		// add_post_meta($post_id, $meta_key, $meta_value, $unique);
		add_post_meta($post_id, 'typo3_uid', $row['uid'], true);
		add_post_meta($post_id, 'typo3_author', $row['author'], true);
		add_post_meta($post_id, 'typo3_author_email', $row['author_email'], true);
		add_post_meta($post_id, 'typo3_links', $row['links'], true);
		add_post_meta($post_id, 'thesis_keywords', $row['keywords'], true);
		add_post_meta($post_id, 'thesis_description', $row['short'], true);

		$image = $row['image'];
		if ( $image ) {
			// image is a CSV string, convert image to array
			$images = explode( ",", $image );
			$captions = explode( "\n", $row['imagecaption'] );

			// cycle through to create new post attachments
			foreach ( $images as $key => $file ) {
				// cp image from A to B
				// @ref http://codex.wordpress.org/Function_Reference/wp_upload_bits
				// $upload = wp_upload_bits($_FILES["field1"]["name"], null, file_get_contents($_FILES["field1"]["tmp_name"]));
				$originalFileUri	= $uploadsDirTYPO3 . 'pics/' . $file;
				$fileMove = wp_upload_bits($file, null, file_get_contents($originalFileUri));
				$filename = $fileMove['file'];

				// @ref http://codex.wordpress.org/Function_Reference/wp_insert_attachment
				$caption = isset($captions[$key]) ? $captions[$key] : '';
				$title = $caption ? $caption : sanitize_title_with_dashes($file);

				$wp_filetype = wp_check_filetype(basename($file), null);
				$attachment = array(
					'post_content' => '',
					'post_excerpt' => $caption,
					'post_mime_type' => $wp_filetype['type'],
					'post_status' => 'inherit',
					'post_title' => $title,
				);
				$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
				/*
				echo 'attachment' . ' : '; print_r($attachment); echo '<br />';	
				echo 'filename' . ' : '; print_r($filename); echo '<br />';	
				echo 'post_id' . ' : '; print_r($post_id); echo '<br />';	
				echo 'attach_id' . ' : '; print_r($attach_id); echo '<br />';	
				exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );	
				*/

				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				echo "<p>Image {$file} added to post</p>";
			}

			// insert [gallery] into content after the second paragraph
			// $bodytext = str_replace($newlineTypo3, $newlineWp, $bodytextOrig);
			$post_content_array = explode( $newlineWp, $post_content );
			// echo 'post_content_array' . ' : '; print_r($post_content_array); echo '<br />';	
			$post_content_arrSize = sizeof( $post_content_array );
			$new_post_content = '';
			$galleryCode = '[gallery link="file"]';
			$galleryInserted = false;
			for ( $i = 0; $i < $post_content_arrSize; $i++ ) {
				if ( 2 != $i ) {
					$new_post_content .= $post_content_array[$i] . "{$newlineWp}";
				} else {
					$new_post_content .= "{$galleryCode}{$newlineWp}";
					$new_post_content .= $post_content_array[$i] . "{$newlineWp}";
					$galleryInserted = true;
				}
			}

			if ( ! $galleryInserted ) {
				$new_post_content .= $galleryCode;
			}
			
			// echo 'new_post_content' . ' : '; print_r($new_post_content); echo '<br />';	
			// exit( 'File ' . __FILE__ . ' Line ' . __LINE__ . " ERROR<br />\n" );

			$post = array(
				'ID' => $post_id,
				'post_content' => $new_post_content
			);
		 
			wp_update_post( $post );
		}

		$files					= $row['news_files'];
		if ( $files ) {
			$files_arr			= explode( ",", $files );

			foreach ( $files_arr as $key => $file ) {
				$originalFileUri	= $uploadsDirTYPO3 . 'media/' . $file;
				$fileMove		= wp_upload_bits($file, null, file_get_contents($originalFileUri));
				$filename		= $fileMove['file'];
				$title			= sanitize_title_with_dashes($file);

				$wp_filetype	= wp_check_filetype(basename($file), null);
				$attachment		= array(
					'post_content' => '',
					'post_mime_type' => $wp_filetype['type'],
					'post_status' => 'inherit',
					'post_title' => $title,
				);
				$attach_id		= wp_insert_attachment( $attachment, $filename, $post_id );
				$attach_data	= wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				echo "<p>Media {$file} added to post</p>";
			}
		}

		// TODO optional import comments 
		ttw_importComments($post_id, $row['uid']);

		$importCount++;
	}

	echo "<p>{$importCount} TYPO3 tt_news items imported</p>";

	exit;
}


// import comments from TYPO3 into WordPress
function ttw_importComments($postId = 0, $newsId = 0) {
	// grab db links
	global $db_typo3, $db_wp;

	// query for TYPO3 comments
	$query		= <<<EOD
/* Transition TYPO3 tx_comments to WordPress post comments */
SELECT
	{$postId} comment_post_ID
	, REPLACE(c.external_ref,'tt_news_','') tt_news_uid
	, CONCAT(c.firstname, ' ', c.lastname) comment_author
	, c.email comment_author_email
	, c.homepage comment_author_url
	, c.content comment_content
	, IF(c.email = 'michael@peimic.com', 1, 0) user_id
	, c.remote_addr comment_author_IP
	, FROM_UNIXTIME(c.tstamp) comment_date
	, c.approved comment_approved
FROM tx_comments_comments c
WHERE
	1 = 1
	AND c.deleted = 0
	AND c.hidden = 0
	AND c.approved = 1
EOD;

	if ( $newsId ) {
		$query	.= "	AND REPLACE(c.external_ref,'tt_news_','') = {$newsId}";
	}

	$result = mysql_query($query, $db_typo3);

	// cycle through comments to pull tt_news_uid
	while ($row = mysql_fetch_assoc($result)) {
		echo 'row' . ' : '; print_r($row); echo '<br />';	

		if ( $newsId ) {
			$postIdQuery				= <<<EOD
SELECT post_id
FROM wp_postmeta
WHERE meta_value = {$row['tt_news_uid']}
	AND meta_key = 'typo3_uid'
EOD;

			// look up post id with tt_news_uid
			$postIdResult = mysql_query($postIdQuery, $db_wp);

			// set comment_post_ID
			$row['comment_post_ID']		= mysql_result($postIdResult, 0);
		}

		unset($row['tt_news_uid']);

		// call wp_insert_comment() to insert the TYPO3 comment as the data array
		// wp_insert_comment( $row );
		echo 'comment_post_ID' . ' : '; print_r($row['comment_post_ID']); echo '<br />';	
	}
}

?>
