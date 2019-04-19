<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_blog
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Blog Component HTML Helper
 *
 * @since  1.5
 */
abstract class BlogHelperEmbedVideo
{
	public static function embedUrl($uri = '')
	{
		if(stripos($uri, 'youtube.com'))
		{
			$type        = 'youtube';
			$video_arr   = parse_url($uri);
			parse_str($video_arr['query'], $query_str);
			$video_id    = $query_str['v'];
			$video_image = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
			$video_uri   = 'https://www.youtube.com/embed/' . $video_id . '?controls=0&amp;rel=0';
		}
		elseif(stripos($uri, 'youtu.be'))
		{
			$type        = 'youtube';
			$video_id    = preg_replace('(https?://youtu.be/)', '', $uri);
			$video_image = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
			$video_uri   = 'https://www.youtube.com/embed/' . $video_id . '?controls=0&amp;rel=0';
		}
		elseif(stripos($uri, '//vimeo.com'))
		{
			$type        = 'vimeo';
			$video_id    = preg_replace( '(https?://vimeo.com/)', '', $uri );
			$vimeo       = unserialize(@file_get_contents('https://vimeo.com/api/v2/video/' . $video_id . '.php'));
			$video_image = $vimeo[0]['thumbnail_large'];
			$video_uri   = 'https://player.vimeo.com/video/' . $video_id;
		}
		else
		{
			$type        = '';
			$video_id    = '';
			$video_image = '';
			$video_uri   = $uri;
		}

		$placeholder   = 'http://placehold.it/1200x680/ccc/333/&text=Thumbnail%20not%20available';
		$result        = new stdClass();
		$result->type  = $type;
		$result->id    = $video_id;
		$result->image = !empty($video_image) ? $video_image : $placeholder;
		$result->uri   = $video_uri;

		return $result;
	}
}
