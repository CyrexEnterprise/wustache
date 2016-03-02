<?php
namespace Cloudoki\Wustache;

use Cloudoki\Wustache\Template;

class Publication
{
	/**
	 * Parameters
	 * Caching, To be used in filtering
	 */
	public $cache;
	public $finished;
	
	
	public function __construct ()
	{
		$this->mustache = new Template ();
	}
	
	/**
	 * Wustache Content Render.
	 *
	 * @since    1.0.0
	 */
	public function template_content ($params = [], $template = null)
	{
		# sugar the parameters
		if (is_array ($params)) $params = (object) $params;	

		$params->get_field	= $this->get_field ();
		$params->get_option = $this->get_option ();
		$params->iterate	= $this->iterate ();
		$params->spill		= $this->spill ();
		$params->images		= [];

		if (!$params->post) $params->post = get_post ();

			$wulist = get_post_meta ($params->post->ID, '_thumbnail_list', true);

			if( $wulist == '[]' ){
				$wulist = get_post_meta ($params->post->ID, '_scheduled_thumbnail_list', true);
			}

		$images = [];
		# All image data
		if ( $wulist && $wulist != '[]' ){
			
			$wulist = json_decode ($wulist);
			
			$results = get_posts (['posts_per_page' => count ($wulist), 'post_type' => 'attachment', 'post__in' => $wulist]);
			foreach ($results as $img) $images[$img->ID] = $img;
			
			foreach($wulist as $wuid)
			{	
				if ( !empty( $images ) && $images[$wuid]->guid ){
					$images[$wuid]->url = $images[$wuid]->guid;
					$params->images[] = $images[$wuid];
				}
				
			}
			/*$raw = get_posts (['post_type' => 'attachment', 'post__in' => json_decode ($wulist)]);
		
			$params->images = array_map (function ($img) 
			{ 
				$details = wp_get_attachment_image_src ((int) $img->ID, 'full');
				$img->url = $details[0];
				
				return $img;
			}, $raw);*/
		} else if ( get_post_meta ($params->post->ID, '_thumbnail_id', true) || get_post_meta ($params->post->ID, '_scheduled_thumbnail_id', true) ) {
			$image = get_post( get_post_meta ($params->post->ID, '_thumbnail_id', true) ) || get_post_meta ($params->post->ID, '_scheduled_thumbnail_id', true);
			if( $image ){ 
				$params->images[$image->ID] = $image;
				if( $image->guid ){
					$params->images[$image->ID]->url = $image->guid;
				}
			}
		}

		# The Author
		$author = $params->post->post_author;
		$params->author = (object)
		[
			'display_name'	=> get_the_author_meta ('display_name', $author),
			'description'	=> get_the_author_meta ('description', $author),
			'avatar'		=> get_field ('author_image', 'user_'. $author),
			'url'			=> get_author_posts_url ($author)
		];
		
		# Break content
		$params->paragraphs = array_values (array_filter (explode ("\n", $params->post->post_content), function ($prg) 
		{ 
			return (trim ($prg)) && trim ($prg) != '&nbsp;'; 
		}));
		
		# Quotes example
		$params->quotes = [
		/*	"It is my conviction that this project remains problematic.",
			"How can we reconcile the apparent contradiction between the resolute essence of design and the indefinite aim of non-calibration?"*/
		];

		# Store params
		$this->cache = $params;

		# Render template
		echo $this->mustache->render ($params->template, $params);
	}
	
	public function get_field ()
	{
		return function ($key) { return get_field($key); };
	}
	
	public function get_option ()
	{
		return function ($key) { return get_option($key, $id); };
	}
	
	public function iterate ()
	{
		return function ($block, $lambda) {     
				
			$identifier = '\.#/';
			$trigger = null;
			# This needs a better solution
			foreach ($this->cache as $key => $param)
			
				if (@!$this->finished[$key] && is_array ($param) && count ($param) && preg_match ('/\b' . $key . $identifier, $block))
				{
					$trigger = true;
					$block = preg_replace ('/'. $key . $identifier, $key . '.' . key ($param), $block);
					
					if(next ($this->cache->{$key}) === false)
						$this->finished[$key] = true;
				}
			
			return $trigger? $lambda->render ($block): false;
		};
	}
	
	public function spill ()
	{
		return function ($block, $lambda) {     
				
			$identifier = '/{{\b';
			$cummul = '';
			
			# This needs a better solution
			foreach ($this->cache as $key => $param)
				
				if (@!$this->finished[$key] && is_array ($param) && count ($param) && preg_match ($identifier . $key . '/', $block))
				{
					for (key($this->cache->{$key}); key($this->cache->{$key}) !== null; next($this->cache->{$key}))
						
						$cummul .= preg_replace ($identifier . $key . '/', '{{' . $key . '.' . key($this->cache->{$key}), $block);
				}

			return $lambda->render ($cummul);
		};
	}
	
}

?>