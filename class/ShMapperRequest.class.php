<?php 

class ShMapperRequest extends SMC_Post
{
	static function init()
	{
		add_action('init',				array(__CLASS__, 'add_class'), 15 );
		parent::init();
	}
	static function get_type()
	{
		return SHM_REQUEST;
	}	
	static function add_class()
	{
		$labels = array(
			'name' => __('Map Request', SHMAPPER),
			'singular_name' => __("Map Request", SHMAPPER),
			'add_new' => __("add Map Request", SHMAPPER),
			'add_new_item' => __("add Map Request", SHMAPPER),
			'edit_item' => __("edit Map Request", SHMAPPER),
			'new_item' => __("add Map Request", SHMAPPER),
			'all_items' => __("all Map Requests", SHMAPPER),
			'view_item' => __("view Map Request", SHMAPPER),
			'search_items' => __("search Map Request", SHMAPPER),
			'not_found' =>  __("Map Request not found", SHMAPPER),
			'not_found_in_trash' => __("no found Map Request in trash", SHMAPPER),
			'menu_name' => __("all Map Requests", SHMAPPER)
		);
		$args = array(
			 'labels' => $labels
			,'public' => true
			,'show_ui' => true
			,'has_archive' => true 
			,'exclude_from_search' => false
			,'menu_position' => 19
			,'menu_icon' => "dashicons-edit"
			,'show_in_menu' => "shm_page"
			,'show_in_rest' => true
			,'supports' => array(  'title', "editor", "thumbnail" )
			,'capability_type' => 'page'
		);
		register_post_type(SHM_REQUEST, $args);
	}
	
	static function add_views_column( $columns )
	{
		$columns = parent::add_views_column( $columns );
		unset($columns['contacts']);
		unset($columns['location']);
		unset($columns['longitude']);
		unset($columns['notify_date']);
		unset($columns['notify_user']);
		unset($columns['author']);
		$columns['thumb'] = "<div class='shm-camera' title='" . __("Image", SHMAPPER) ."'></div>";
		$new = [];
		foreach($columns as $key => $val)
		{
			switch($key)
			{
				case "notified":
					$new[$key] = __("Approving", SHMAPPER);
					break;
				case "session":
					$new["session"] = __("Author");
				case "latitude":
					$new["location"] = __("GEO location", SHMAPPER);
					break;
				default:
					$new[$key] = $val;
			}
		}
		return $new;
	}
	static function fill_views_column($column_name, $post_id) 
	{	
		$obj = static::get_instance( $post_id );
		switch($column_name)
		{
			case "location":
				echo __("Latitude", SHMAPPER).": <strong>" . $obj->get_meta("latitude") ."</strong>".
				"<br>".
				 __("Longitude", SHMAPPER).": <strong>" . $obj->get_meta("longitude") ."</strong>".
				"<br>".
				 __("Location", SHMAPPER).": <strong>" . $obj->get_meta("location") ."</strong>";
				break;
			case "session":
				$contacts = $obj->get_meta("contacts");
				echo implode("<br>", $contacts);
				break;
			case "type":
				$term = get_term($obj->get_meta("type"), SHM_POINT_TYPE);
				echo ShMapPointType::get_icon($term , $obj->get_meta("notified"));
				break;
			case "notified":
				echo $obj->get_notified_form();
				break;
			case "thumb":
				echo "<div class='shm_type_icon2' style='background-image:url(" . get_the_post_thumbnail_url( $post_id, [75, 75] ) .");'></div>" ;
				break;
			default:
				parent::fill_views_column($column_name, $post_id);
				break;
		}
	}
	static function view_admin_edit($obj)
	{			
		require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
		$SMC_Object_type	= SMC_Object_Type::get_instance();
		$bb				= $SMC_Object_type->object [forward_static_call_array( array( get_called_class(),"get_type"), array()) ];	
		foreach($bb as $key=>$value)
		{
			if($key == 't' || $key == 'class' || $key == 'contacts' || $key == 'notify_user' ) continue;
			$meta = get_post_meta( $obj->id, $key, true);
			$$key = $meta;
			switch( $value['type'] )
			{
				case "number":
					$h = "<input type='number' name='$key' id='$key' value='$meta' class='sh-form'/>";
					break;
				case "boolean":
					$h = "<input type='checkbox' class='checkbox' name='$key' id='$key' value='1' " . checked(1, $meta, 0) . "/><label for='$key'></label>";
					break;
				case "post":
					$h = "$meta";
					break;
				default:
					$h = "<input type='' name='$key' id='$key' value='$meta' class='sh-form'/>";
			}
			switch($key)
			{
				case "map":
					$h = ShmMap::wp_dropdown([
						"selected"	=> $meta,
						"class"		=> "sh-form",
						"name"		=> "map",
						"id"		=> "map",
					]);
					break;
				case "type":
					$h = ShMapPointType::get_ganre_swicher([
						'selected' 	=> $meta,
						'prefix'	=> "type",
						'col_width'	=> 3
					], 'radio' );
					
					
					break;
				case "description":
					$h = "<textarea name='$key' id='$key' class='sh-form'>$meta</textarea>";
					break;
			}
			$html .="<div class='shm-row'>
				<div class='shm-3 shm-md-12 sh-right sh-align-middle'>".$value['name'] . "</div>
				<div class='shm-9 shm-md-12 '>
					$h
				</div>
			</div>
			<div class='spacer-5'></div>";
		}
		echo $html;
	}
	static function save_admin_edit($obj)
	{
		require_once(SHM_REAL_PATH."class/SMC_Object_type.php");
		$SMC_Object_type	= SMC_Object_Type::get_instance();
		$_obj				= $SMC_Object_type->object [static::get_type()];
		$arr 				= [];
		foreach($_obj as $key=>$value)
		{
			if( $key == 't' || $key == 'class'  || $key == 'contacts' || $key == 'notify_user' ) continue;
			$arr[$key] = $_POST[$key];
		}
		
		return $arr;
	}
	static function insert($data)
	{
		$h = [];
		$map 			= ShmMap::get_instance((int)$data['id']);
		$h['map_id'] 	= $map->get("post_title");
		//$h['form_title']= $map->get_meta("form_title");
		$contents		= [];
		$form			= $map->get_meta("form_forms");
		$emails			= [];
		$contacts		= [];
		foreach($form as $key => $val)
		{
			if($val['type'] == SHMAPPER_EMAIL_TYPE_ID)
			{
				$emails[] 	= $data['elem'][$key];
				$contacts[] = $data['elem'][$key];					
			}	
			if(
				$val['type'] == SHMAPPER_PHONE_TYPE_ID ||
				$val['type'] == SHMAPPER_NAME_TYPE_ID 
			)
				$contacts[] = $data['elem'][$key];
			if($val['type'] == SHMAPPER_NAME_TYPE_ID)
				$author		= $data['elem'][$key];
			if($val['type'] == SHMAPPER_TEXTAREA_TYPE_ID)
			{
				$description .= "<p>" . $data['elem'][$key];
			}
			if($key == 1)
			{
				//$description .= $data['elem'][1] . ", title type=". SHMAPPER_TITLE_TYPE_ID;
			}
			if($val['type'] == SHMAPPER_TITLE_TYPE_ID)
			{
				$title .= $data['elem'][$key];
			}
			$tpp  = ShmForm::get_type_by( "id", $val['type'] );
			if(SHMAPPER_IMAGE_TYPE_ID != $val['type'] )
				$contents[] =  "<small>".$tpp['title'].":</small> <strong>".$data['elem'][$key]."</strong>";
		}
		$contents[] =  "<div>" . $data['shm_point_loc'] . "</div>";
		$h['contents'] 		= implode("<br>", $contents);
		$arr = [
			"post_type" 	=> static::get_type(),
			"post_name" 	=> $title ? $title : $map->get("post_name"),
			"post_title" 	=> $title ? $title : $map->get("post_title"),
			"post_content"	=> $h['contents'],
			"map"			=> (int)$data['id'],
			"location"		=> $data['shm_point_loc'],
			"latitude"		=> ( (int) ($data['shm_point_lat'] * 10000)) / 10000,
			"longitude"		=> ( (int) ($data['shm_point_lon'] * 10000)) / 10000,
			"type"			=> $data['shm_point_type'],
			"contacts"		=> $contacts,
			"description"	=> $description,
			"author"		=> $author
		];
		/**/
		$new_req = parent::insert($arr);
		
		//notify map owner
		if($notify_owner = $map->get_meta("notify_owner"))
		{
			$author_id 	= $map->get("post_author");
			$user 		= get_userdata($author_id);
			$email	 	= $user->get('user_email');
			$semail  	= $emails[0] ? $emails[0] : get_bloginfo( "admin_email" );
			$suser		= $author ? $author : __("Uknown User", SHMAPPER);
			$site		= get_bloginfo("name");
			$headers = array(
				"From: $site <$semail>",
				'content-type: text/html',
			);
			wp_mail(
				$email,
				sprintf(__("<%s> Request to your Map '%s'", SHMAPPER), $suser, $map->get("post_title")),
				$h['contents'],
				$headers
			);
		}
		
		//SESSION if session-plugin active
		if(!shm_is_session())	return $new_req;
		$shm_reqs 		= $_SESSION['shm_reqs'];
		if(!is_array(shm_reqs))
			$shm_reqs 	= [ ];
		$shm_reqs[] 	= $new_req->id;
		$_SESSION['shm_reqs'] = $shm_reqs;
		return $new_req;
	}
	function get_notified_form()
	{
		if($notify = $this->get_meta("notified"))
		{
			$user = get_user_by("id", $this->get_meta("notify_user"));
			$html = "<p>" . $user->display_name . "</p><p>" . date("j.n.Y H:m", $this->get_meta("notify_date"));
		}
		else
		{
			$html = "
			<div clas='shm-row'>
				<div class='shm-12'>
					<div class='button button-large button-primary ' shm_notify_req='$this->id'>".
						__("Approve", SHMAPPER). 
					"</div>
					<div class='button button-large button-alert' shm_trash_req='$this->id' title='".__("Trash", SHMAPPER)."'>
						<span class='dashicons dashicons-trash' style='margin-top: 4px;'></span>
					</div>
				</div>
			</div>";
		}
		return $html;
	}
	function notify()
	{
		$this->update_meta("notify_user", get_current_user_id());
		$this->update_meta("notify_date", time());
		$this->update_meta("notified", true);
		$point = ShmPoint::insert([
			"post_title"	=> (string)$this->get("post_title"),
			"post_name"		=> (string)$this->get("post_name"),
			"post_content"	=> (string)$this->get_meta("description"),
			"latitude"		=> $this->get_meta("latitude"),
			"longitude"		=> $this->get_meta("longitude"),
			"location"		=> $this->get_meta("location"),
			"type"			=> (int)$this->get_meta("type"),
			"map_id"		=> (int)$this->get_meta("map"),
		]);
		if($attach_id = get_post_thumbnail_id($this->id))
		{
			set_post_thumbnail($point->id, (int)$attach_id);
		}
		return $point;
	}
}