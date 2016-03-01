<?php
namespace Cloudoki\Wustache;

use Cloudoki\Wustache\Template;

class Admin
{
	public function __construct ()
	{
		$this->mustache = new Template (__DIR__.'/../views');
	}
	
	/**
	 * Wustache Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function dashboard ()
	{
		wp_add_dashboard_widget( 'wustache-widget', 'Mustache Templates', array( $this, 'dashboard_widget' ));
	}
	
	/**
	 * Wustache Dashboard Widget.
	 *
	 * @since    1.0.0
	 */
	public function dashboard_widget ()
	{
		# Current templates
		$list = $this->listTemplates ();
		$count = count ($list);

		# Render template
		echo $this->mustache->render ('dashboard-widget', ['count'=> $count, 'templates'=> $count == 1? __('template'): __('templates')]);
	}
	
	/**
	 * Wustache Edit Post Widget.
	 *
	 * @since    1.0.0
	 */
	public function post_edit ()
	{
		# Hide views
		// remove_meta_box ('postimagediv', null, null);	

		# Render template
		$blocked_cpts = array('acf-field-group', 'attachment', 'page' );

		if ( !in_array( get_post_type(), $blocked_cpts ) ){
			add_meta_box ('wustache-edit-post', 'Template', array( $this, 'post_edit_metabox' ), null, 'side', 'high');
		}

	}
	
	/**
	 * Metabox display.
	 *
	 * @since    1.0.0
	 */
	public function post_edit_metabox ()
	{	
		$post_id = get_the_ID ();
		
		$params = 
		[
			'basepath'=> '',
			'images'=> $this->listImages (),
			'template'=> get_post_meta( $post_id, '_template_slug', true),
			'templates'=> $this->listTemplates ($post_id),
			'dark' => get_post_meta( $post_id, '_template_contrast', true) == 'dark',
			'cpt'=> ucfirst (get_post_type ($post_id))
		];
		
		
		// Add Media scripts
		add_thickbox();
		wp_enqueue_media (['post'=> $post_id]);	
		
		echo $this->mustache->render ('edit-post', $params);
	}
	
	/**
	 * Wustache Edit Post Save state.
	 *
	 * @since    1.0.0
	 */
	public function post_edit_submit ( $post_id, $post = null, $update = null )
	{
		$post = $post?: $_POST;

		// Update contrast
		update_post_meta( $post_id, '_template_contrast', isset( $post['dark'] )? 'dark': 'light');

		// Update template
		if( isset( $post['template'] ) ) {
			update_post_meta( $post_id, '_template_slug', $post['template']);
		}

		// Get attachment ids
		$current = $this->listImages (true);

		$newids = isset( $post['attachments'] ) ? explode (',', $post['attachments']): [];


		// Featured image
		if (count($newids)){
			update_post_meta( $post_id, '_thumbnail_id', $newids[0]);
		} else {
			delete_post_meta( $post_id, '_thumbnail_id');
		} 
		// Update meta
		update_post_meta( $post_id, '_thumbnail_list', json_encode ($newids));

		if( get_post_meta( $post_id, 'scheduled' ) ){
			if ( count($newids) ){
				update_post_meta( $post_id, '_scheduled_thumbnail_list', json_encode ($newids) );
				update_post_meta( $post_id, '_scheduled_thumbnail_id',  $newids[0] );
			}
		}
	}
	
	/**
	 * Wustache Filters.
	 *
	 * @since    1.0.0
	 */
	/*
	public function filter_image_downsize ($wp_crap, $attachId, $size)
	{
		$attach = get_post ($attachId);
		
		return ($attach && strpos ($attach->guid, 'https://googledrive.com') !== false)?
			
			[$attach->guid, $size[0], $size[1], false]
			: null;
		
	}
	*/


	public 	function on_all_status_transitions( $new_status, $old_status, $post ) {

		if (  $new_status == 'future' ){
			update_post_meta( $post->ID, 'scheduled', true);
		}
	}


	public function listTemplates ( $id = null )
	{
		$files = [];
		
		# Current templates
		$public = new Template ();
		$path = $public->cwd . '/' . get_post_type ();
		$list = scandir ($path);
		
		
		
		foreach($list as $file) 
			
			if(substr ($file, 0, 1) != '.' && is_file ($path . '/' . $file))
			{
				$file = pathinfo ($file);
	
				if ($file['extension'] == 'mustache') $files[] = $file['filename'];
			}
		
		return $files;
	}
	
	public function listImages ($ids_only = null)
	{

		// what about: _wp_attached_file ?
		$post_id = get_the_ID ();


		$wulist = get_post_meta ($post_id, '_thumbnail_list', true);

		if ( $wulist == '[]' && get_post_meta ($post_id, '_thumbnail_id') ){
			$wulist = get_post_meta($post_id, '_thumbnail_id', true);
		} else if ( $wulist == '[]' ){
			$wulist = get_post_meta ($post_id, '_scheduled_thumbnail_list', true);
		}

		if ( $wulist && $ids_only ) {
			return json_decode ($wulist);
		} else if ($wulist) {
			$images = [];
			$response = [];		
			$wulist = json_decode ($wulist);

			if( is_array( $wulist ) ){
				$results = get_posts( array( 'posts_per_page' => count ($wulist), 'post_type' => 'attachment', 'post__in' => $wulist ) );
			} else {
				$results = get_post( $wulist );
			}

			if( $results ) {

				if ( is_array( $wulist ) ){
					foreach ( $results as $img ){
						$images[$img->ID] = $img;
					}

					foreach( $wulist as $wuid ) {
						$response[] = (object)[
							'title'=> $images[$wuid]->post_title,
							'name'=> $images[$wuid]->post_name,
							'url'=> $images[$wuid]->guid,
							'id'=> $wuid
						];
					}
				} else {
					$response[] = (object)[
							'title'=> $results->post_title,
							'name'=> $results->post_name,
							'url'=> $results->guid,
							'id'=> $results->ID,
						];
				}

				return $response;
			}

		} 
		// New list
		else 
		{
			$list = [];
			
			// Featured image
			if($thumbnail = get_post_thumbnail_id ($post_id))
			{
				if (!$ids_only) $img = get_post ($thumbnail);
				
				$list[] = $ids_only? $thumbnail:
					
					(object)[
							'title'=> $img->post_title,
							'name'=> $img->post_name,
							'url'=> $img->guid,
							'id'=> $thumbnail
						];
			}
			// Attachments
			$images = get_children( 'post_type=attachment&post_mime_type=image&post_parent=' . $post_id);
			
			foreach ($images as $img)
				
				if($img->ID != $thumbnail)
				
					$list[] = $ids_only? $img->ID:
					
						(object)[
							'title'=> $img->post_title,
							'name'=> $img->post_name,
							'url'=> $img->guid,
							'id'=> $img->ID
						];
		
			return $list;
		
		}
	}
}

?>