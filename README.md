# TGL Instagram API

Version 1.0

This is an ExpressionEngine 2 module for interacting with the Instagram API.  Currently, the module only retrieves information from the authenticated user's feed and a specified user's feed.  The module has a CP backend, which directs the user through the oAuth process.

Follow me on Twitter here: http://twitter.com/bryant_

# Installation 

* Move tgl_instagram folder into your EE third-party directory
* Install Module
* Follow steps to authenticating
* When registering a new client with Instagram, make sure the OAuth redirect_uri value is set correct.  For more information see here: http://instagram.com/developer/auth/

# Tag Pairs

_The following tags can be used for looping through images:_

## Feed

	{exp:tgl_instagram:feed}{/exp:tgl_instagram:feed}

This tag loops through the authenticated user's feed.  The feed displays pictures from other instagram user's the authenticated user follows as well as their own images.
	
__Parameters__

	limit

the number of images you want to see

## User Feed

	{exp:tgl_instagram:user_feed}{/exp:tgl_instagram:user_feed}

This tag loops though a specified user's feed, and only displays image they have shared.

__Parameters__

	limit

the number of images you want to display

	username

the username of the specific user, who you want to display images from.


## Image Data

These tags can be used within the above tag pairs.

	{filter}

The filer used on the image

	{created_at}

Timestamp of when the image was created

	{link}

Link to Instagram's site displaying the picture

	{caption}

Image's caption

	{username}

The username of the user who submitted the image

	{website}

The website of the user who submitted the image

	{bio}

The bio of the user who submitted the image

	{profile_picture}

The profile picture of the user who submitted the image

	{full_name}

The full name of the user who sumitted the image

	{thumbnail_url}

The url to the thumbnail version of the image (150px x 150px)

	{thumbnail}

The thumbnail version of the image, wrapped in an <img> tag (150px x 150px)

	{low_resolution_url}

The url to the low resolution version of the image (306px x 306px)

	{low_resolution}

The low resolution version of the image, wrapped in an <img> tag (306px x 306px)

	{standard_resolution_url}

The url to the standard resolution version of the image (612px x 612px)

	{standard_resolution}

The standard resolution version of the image, wrapped in an <img> tag (612px x 612px)

	{tag_count}

Number of tags for the specific images

	{likes_count}

Number of "likes" for the specific image

	{comment_count}

Number of comments for the specific image


## Image Data Tag Pairs

Within each image's loop, there are a few more items you can loop over to get more information about each image



### _Comments_

	{comments}{/comments}

Loop over the comments for each picture

#### Single Tags

	{comment_text}

Text from a specific comment

	{comment_username}

Username from the user who submitted the comment

	{comment_profile_picture}

The profile picture from the user who submitted the comment

	{comment_full_name}

The full name of the user who submitted the comment



### _Likes_

	{likes}{/likes}

Loop over each image's likes

#### Single Tags

	{like_username}

Username of the user who liked the photo

	{like_full_name}

Full name of the user who liked the photo

	{like_profile_picture}

Profile picture of the user who liked the photo



### _Tags_

	{tags}{/tags}

Loop over each images tags

#### Single Tags

	{tag}

The tag name































































