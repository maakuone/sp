<?php 

//cleans a string
function clean($str) { 							
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
}

//overides the main database search function
function fb_filter_query( $query, $error = true ) {
	$solr_search_value = get_option('solr_settings');
	if($solr_search_value[solr_status]=='active'){

		if (is_search()||$_GET['sa']=='search') {
			$query->is_search = false;
			$query->query_vars[s] = false;
			$query->query[s] = false;
			add_action( 'template_redirect', 's4w_template_redirect', 11 );
		}
		
	}
}
add_action( 'parse_query', 'fb_filter_query' );

//get the total number of results for a search query from the SOLR Index
function s4w_search_results_get_num($searchTxt,$category_name){
	$solr_search_value = get_option('solr_settings');
	require __DIR__.'/assets/solarium-master/vendor/autoload.php';
	$solrconfig = array('endpoint' => array('localhost' => array('host' => $solr_search_value[solr_search_url],'port' => $solr_search_value[solr_search_port],'path' => $solr_search_value[solr_search_ext])));
	$client = new Solarium\Client($solrconfig);
	try{
		if($searchTxt=="*"){
			$query = $client->createSelect();
			if(isset($solr_search_value['sf_enable_settings'])){
				$sf_set = get_option('sf_settings');
				$dismax = $query->getDisMax();
			$feild_array= array("post_title" => "Ad Title","post_content" => "Ad Content","street" => "Street","city" => "City","state" => "State","country" => "Country","zipCode" => "Zipcode","categories" => "Categories","tags" => "Tags");
				$dismax_set="";
				foreach($feild_array as $x=>$xv){
					if($sf_set['sfs_'.$x] == 1 ){
						$dismax_set.=$x."^".$sf_set['sfw_'.$x]." ";
					}
				}
				$dismax->setQueryFields($dismax_set);
			}
			$query = $client->createQuery($client::QUERY_SELECT);
			if($category_name != 0 ){
				$cv='categories:'.$category_name->name;
				$query->createFilterQuery('categories')->setQuery($cv);
			}
			$resultset = $client->execute($query);
		}
		else{
			$query = $client->createSelect();
			if(isset($solr_search_value['sf_enable_settings'])){
				$sf_set = get_option('sf_settings');
				$dismax = $query->getDisMax();
			    $feild_array= array("post_title" => "Ad Title","post_content" => "Ad Content","street" => "Street","city" => "City","state" => "State","country" => "Country","zipCode" => "Zipcode","categories" => "Categories","tags" => "Tags");
				$dismax_set="";
				foreach($feild_array as $x=>$xv){
					if($sf_set['sfs_'.$x] == 1 ){
						$dismax_set.=$x."^".$sf_set['sfw_'.$x]." ";
					}
				}
				$dismax->setQueryFields($dismax_set);
			}
			$query->setQuery($searchTxt);
			if($category_name != 0 ){
				$cv='categories:'.$category_name->name;
				$query->createFilterQuery('categories')->setQuery($cv);
			}

			$resultset = $client->select($query);
		}
	}catch(Solarium\Exception $e){
		$query->is_404 = true;
	}
	return $resultset->getNumFound();
	
	return false;
}

//Gets all the results from the SOLR Index
function s4w_search_results($searchTxt, $category_name, $ipp, $page_num, $sort_type){
	
	$solr_search_value = get_option('solr_settings');
	$startpost_num = (($page_num - 1) * $ipp) ;
	require __DIR__.'/assets/solarium-master/vendor/autoload.php';
	$solrconfig = array('endpoint' => array('localhost' => array('host' => $solr_search_value[solr_search_url],'port' => $solr_search_value[solr_search_port],'path' => $solr_search_value[solr_search_ext])));
	$client = new Solarium\Client($solrconfig);
	try{
		if($searchTxt == "*"){
			$query = $client->createSelect();
			if(isset($solr_search_value['sf_enable_settings'])){
				$sf_set = get_option('sf_settings');
				$dismax = $query->getDisMax();
			$feild_array= array("post_title" => "Ad Title","post_content" => "Ad Content","street" => "Street","city" => "City","state" => "State","country" => "Country","zipCode" => "Zipcode","categories" => "Categories","tags" => "Tags");
				$dismax_set="";
				foreach($feild_array as $x=>$xv){
					if($sf_set['sfs_'.$x] == 1 ){
						$dismax_set.=$x."^".$sf_set['sfw_'.$x]." ";
					}
				}
				$dismax->setQueryFields($dismax_set);
			}
			$query = $client->createQuery($client::QUERY_SELECT);
			if($category_name != 0 ){
				$cv='categories:'.$category_name->name;
				$query->createFilterQuery('categories')->setQuery($cv);
			}
			$query->setStart($startpost_num)->setRows($ipp);
			if($sort_type=='high' || $sort_type=='low'){
				if($sort_type=='low'){
					$query->addSort('price', $query::SORT_ASC);
				}
				else{
					$query->addSort('price', $query::SORT_DESC);
				}
			}
			$resultset = $client->execute($query);
		}
		else{
			$query = $client->createSelect();
			if(isset($solr_search_value['sf_enable_settings'])){
				$sf_set = get_option('sf_settings');
				$dismax = $query->getDisMax();
			$feild_array= array("post_title" => "Ad Title","post_content" => "Ad Content","street" => "Street","city" => "City","state" => "State","country" => "Country","zipCode" => "Zipcode","categories" => "Categories","tags" => "Tags");
				$dismax_set="";
				foreach($feild_array as $x=>$xv){
					if($sf_set['sfs_'.$x] == 1 ){
						$dismax_set.=$x."^".$sf_set['sfw_'.$x]." ";
					}
				}
				$dismax->setQueryFields($dismax_set);
			}
			$query->setQuery($searchTxt);
			if($category_name != 0 ){
				$cv='categories:'.$category_name->name;
				$query->createFilterQuery('categories')->setQuery($cv);
			}
			$query->setStart($startpost_num)->setRows($ipp);
			if($sort_type=='high' || $sort_type=='low'){
				if($sort_type=='low'){
					$query->addSort('price', $query::SORT_ASC);
				}
				else{
					$query->addSort('price', $query::SORT_DESC);
				}
			}
			$resultset = $client->select($query);
		}
	
	}catch(Solarium\Exception $e){
		$query->is_404 = true;
	}
	return $resultset;
}

//Redirects to new Search Template instead of that of the Database search
function s4w_template_redirect() {
    if (file_exists(TEMPLATEPATH . '/s4w_search.php')) {
        include_once(TEMPLATEPATH . '/s4w_search.php');
    } else if (file_exists(dirname(__FILE__) . '/template/s4w_search.php')) {
        include_once(dirname(__FILE__) . '/template/s4w_search.php');
    } else {
        return;
    }
    exit;
}
function cp_ad_loop_meta_modified($post_id,$author_id,$listedDate) {
	global $cp_options;
	if ( is_singular( APP_POST_TYPE ) )
		return;
	?>

<p class="post-meta"> <span class="folder">
  <?php if ( $post->post_type == 'post' ){ the_category(',',$post_id);} else {echo get_the_term_list($post_id,APP_TAX_CAT,'',',','');} ?>
  </span> | <span class="owner">
  <?php if ( $cp_options->ad_gravatar_thumb) {appthemes_get_profile_pic( get_the_author_meta('ID',$author_id), get_the_author_meta('user_email',$author_id), 16 );} ?>
  <?php the_author_posts_link_modified($author_id); ?>
  </span> | <span class="clock"><span><?php echo appthemes_date_posted_modi($listedDate);?></span></span> </p>
<?php
}

function appthemes_date_posted_modi( $date ) {
	$time = strtotime($date);
	$time_diff = time() - $time;
	if ( $time_diff > 0 && $time_diff < 24*60*60 ){
		printf( __( '%s ago', APP_TD ), human_time_diff( $time ) );
	}
	else{
		echo mysql2date( get_option('date_format'), $date );
	}

}

//Template Function to display content data
function cp_get_content_preview_modified( $charlength = 160 ,$post_id ) {
	$post = get_post( $post_id ); 
	$excerpt = $post->post_excerpt;
	$content = !empty($excerpt) ? $excerpt : get_post_field('post_content', $post_id);;
	$content = strip_tags( $content );
	$content = strip_shortcodes( $content );
	if ( mb_strlen( $content ) > $charlength )
		$content = mb_substr( $content, 0, $charlength ) . '...';
	return $content;
}

//Template Function to display Author Information
function the_author_posts_link_modified($author_id) {
	$authordata = get_userdata( $author_id );
	//print_r( $authordata);
	//die();
	if ( !is_object( $authordata ) )
		return false;
	$link = sprintf('<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
					esc_url( get_author_posts_url( $authordata->ID, $authordata->user_nicename ) ),
					esc_attr( sprintf( __( 'Posts by %s' ), $authordata->user_nicename) ),
					$authordata->user_nicename
	);
	echo $link;
}

//Modifies Default Functionality to display thumbnails
function cp_ad_loop_thumbnail_modified($post_id) {
		global $cp_options;
		$image_id = cp_get_featured_image_id($post_id);
		$prevclass = ( $cp_options->ad_image_preview ) ? 'preview' : 'nopreview';
		if ( $image_id > 0 ) {
			$adthumbarray = wp_get_attachment_image( $image_id, 'ad-thumb' );
			$adlargearray = wp_get_attachment_image_src( $image_id, 'large' );
			$img_large_url_raw = $adlargearray[0];
			if ( $adthumbarray ) {
				echo '<a href="'. get_permalink($post_id) .'" title="'. get_the_title($post_id).'" class="'.$prevclass.'" data-rel="'.$img_large_url_raw.'">'.$adthumbarray.'</a>';
			} else {
				$adthumblegarray = wp_get_attachment_image_src($image_id, 'thumbnail');
				$img_thumbleg_url_raw = $adthumblegarray[0];
			}
		} else {
			echo '<a href="' . get_permalink($post_id) . '" title="' . get_the_title($post_id) . '"><img class="attachment-medium" alt="" title="" src="'.appthemes_locate_template_uri('images/no-thumb-75.jpg') . '" /></a>';
		}

}

//formats Date for it to be stored in SOLR index
function s4w_format_date( $thedate ) {
    $datere = '/(\d{4}-\d{2}-\d{2})\s(\d{2}:\d{2}:\d{2})/';
    $replstr = '${1}T${2}Z';
    return preg_replace($datere, $replstr, $thedate);
}

//Overides the Breadcrumb functionality of the default template
function cp_breadcrumb_modified() {
	global $app_abbr, $post;
	$solr_search_value = get_option('solr_settings');
	$delimiter = '&raquo;';
	$currentBefore = '<span class="current">';
	$currentAfter = '</span>';
	if ( !is_front_page() || is_paged() ) :
		$flag = 1;
		echo '<div id="crumbs">';
		echo '<a href="' . home_url('/') . '">' . __( 'Home', APP_TD ) . '</a> ' . $delimiter . ' ';

		// figure out what to display

			if($solr_search_value[solr_status]=='active'){
				if($_GET['s']){$st=$_GET['s'];}else{$st="*";}
				echo $currentBefore . __( 'Search results for', APP_TD ) . ' &#39;' . $st. '&#39;' . $currentAfter;
			}

		echo '</div>';

	endif;
	
}

function post_unpublished( $new_status, $old_status, $post ) {
    if ( $old_status == 'publish' && $new_status != 'publish' ) {
		$solr_search_value = get_option('solr_settings');
		if($solr_search_value[solr_status]=='active'){
			require __DIR__.'/assets/solarium-master/vendor/autoload.php';
			$solrconfig = array('endpoint' => array('localhost' => array('host' => $solr_search_value[solr_search_url],'port' => $solr_search_value[solr_search_port],'path' => $solr_search_value[solr_search_ext])));
			$client = new Solarium\Client($solrconfig);
			try{
				$update = $client->createUpdate();
				$update->addDeleteById($post->ID);
				$update->addCommit();
				$result = $client->update($update);
			}
			catch(Solarium\Exception $e){
			}
		}
    }
}

add_action( 'transition_post_status', 'post_unpublished', 10, 3 );

add_action( 'wp_insert_post', 'cp_update_solr_index' );
add_action( 'wp_update_post', 'cp_update_solr_index' );
//Inserts, deletes or update ad from the SOLR index
function cp_update_solr_index( $post_id ) {
	$solr_search_value = get_option('solr_settings');
	if($solr_search_value[solr_status]=='active'){
		if ( !wp_is_post_revision( $post_id ) ) {
			$abc = get_post($post_id);
			if($abc->post_type="ad_listing"){
				$solr_search_value = get_option('solr_settings');
				require __DIR__.'/assets/solarium-master/vendor/autoload.php';
				$solrconfig = array('endpoint' => array('localhost' => array('host' => $solr_search_value[solr_search_url],'port' => $solr_search_value[solr_search_port],'path' => $solr_search_value[solr_search_ext])));
				$client = new Solarium\Client($solrconfig);
				$tags_array = wp_get_object_terms($post_id,'ad_tag');
				$category_array = wp_get_object_terms($post_id,'ad_cat');
				$meta_values = get_post_meta($post_id);
				if($abc->post_status=="publish"){	//add to listing
					$files = get_children('post_parent='.$post_id.'&post_type=attachment&post_mime_type=image');
					if($files) :
						$keys = array_reverse(array_keys($files));
						$j=0;
						$num = $keys[$j];
						$image=wp_get_attachment_image($num, 'large', false);
						$imagepieces = explode('"', $image);
						$imagepath = $imagepieces[1];
						$thumb=wp_get_attachment_thumb_url($num);
					endif;
					try{
						$update = $client->createUpdate();
						$doc = $update->createDocument();			
						$doc->id = $abc->ID;
						$doc->post_id = $abc->ID;
						$doc->post_title = $abc->post_title;
						$doc->post_content=$abc->post_content;
						$doc->post_permalink = get_permalink( $post_id );
						$doc->street = $meta_values['cp_street'][0];
						$doc->city = $meta_values['cp_city'][0];
						$doc->state = $meta_values['cp_state'][0];
						$doc->country = $meta_values['cp_country'][0];
						$doc->zipCode = $meta_values['cp_zipcode'][0];
						$doc->post_author_id = $abc->post_author;
						$doc->post_author_name = get_userdata($abc->post_author)->display_name ;
						if(is_numeric ($meta_values['cp_price'][0])){
							$doc->price = $meta_values['cp_price'][0];
						}
						$doc->listedDate =   s4w_format_date($abc->post_date);
						$doc->modifiedDate =  s4w_format_date($abc->post_modified);
						$doc->primaryImage = $thumb;
						if(isset($_POST['action'])){
							if(isset($_POST['sticky'])){
								$doc->featuredFlag = 1;
							}
							else{
								$doc->featuredFlag = 0;
							}
						}else{
							$doc->featuredFlag = is_sticky($abc->ID);
						}
						foreach ($category_array as $cat){
							$xcat =(int)$cat->term_id;
							$catb=explode(",",get_custom_category_parents( $xcat, 'ad_cat', TRUE, '', FALSE ));
							foreach($catb as $catbx){
								$doc->addField('categories', $catbx);
							}
						}
						foreach ($tags_array as $tag)
							$doc->addField('tags', $tag->name);
						$update->addDocuments(array($doc));
						$update->addCommit();
						$result = $client->update($update);
					}catch(Solarium\Exception $e){
					}
				}else{
					if(wp_is_post_revision( $post_id ) != false){
						try{
							$update = $client->createUpdate();
							$update->addDeleteById($post_id);
							$update->addCommit();
							$result = $client->update($update);
						}
						catch(Solarium\Exception $e){
						}
					}
				}
			}
		}
	}
}

function get_custom_category_parents( $id, $taxonomy = false, $link = false, $separator = '/', $nicename = false, $visited = array() ) {

	if(!($taxonomy && is_taxonomy_hierarchical( $taxonomy )))
		return '';

	$chain = '';
	// $parent = get_category( $id );
	$parent = get_term( $id, $taxonomy);
	if ( is_wp_error( $parent ) )
		return $parent;

	if ( $nicename )
		$name = $parent->slug;
	else
		$name = $parent->name;

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		// $chain .= get_category_parents( $parent->parent, $link, $separator, $nicename, $visited );
		$chain .= get_custom_category_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
	}

	if ( $link ) {
		// $chain .= '<a href="' . esc_url( get_category_link( $parent->term_id ) ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $parent->name ) ) . '">'.$name.'</a>' . $separator;
		$chain .= $parent->name."," ;
	} else {
		$chain .= $name.$separator;
	}
	return $chain;
}


add_action( 'before_delete_post', 'my_delete_function' );
function my_delete_function($post_id) { 
    $solr_search_value = get_option( 'solr_settings');
	require __DIR__.'/assets/solarium-master/vendor/autoload.php';
	$solrconfig = array('endpoint' => array('localhost' => array('host' => $solr_search_value[solr_search_url],'port' => $solr_search_value[solr_search_port],'path' => $solr_search_value[solr_search_ext])));
	$client = new Solarium\Client($solrconfig);
	try{
		$update = $client->createUpdate();
		$update->addDeleteById($post_id);
		$update->addCommit();
		$result = $client->update($update);
	}catch(Solarium\Exception $e){
	}
}

/*
 * Add the admin page
*/
add_action('admin_menu', 'solr_admin_page');

function solr_admin_page(){
    add_menu_page('SOLR Settings', 'SOLR Settings', 'administrator', 'solr-settings', 'solr_admin_page_callback');
}

/*
 * Register the settings
*/
 if(isset($_POST['s4w_indexall'])){ 
	add_action('admin_init', 'solr_index_validate');
}elseif(isset($_POST['s4w_pruneall'])){ 
	add_action('admin_init', 'cp_check_expired_cron');
}else{
	add_action('admin_init', 'solr_register_settings');
}

//Callback Functions		  
function solr_register_settings(){
	if(isset($_POST['sf_submit'])){
		register_setting('sf_settings', 'sf_settings', 'sf_settings_validate');	
	}
	else{
		register_setting('solr_settings', 'solr_settings', 'solr_settings_validate');
	}
}

//Callback function to register settings for searchable feilds and their weights
function sf_settings_validate($args){
	$feild_array= array("post_title" => "Ad Title","post_content" => "Ad Content","street" => "Street","city" => "City","state" => "State","country" => "Country","zipCode" => "Zipcode","categories" => "Categories","tags" => "Tags");
	foreach($feild_array as $fa => $fa_value){
		if(!isset($args['selectall'])){
			if(!isset($args['sfs_'.$fa])){
				$args['sfs_'.$fa]=0;
				$args['sfw_'.$fa]=0;
			}else{
				if(!isset($args['sfw_'.$fa])){
					$args['sfw_'.$fa]=0;
				}
			}
		}
		else{
			$args['sfs_'.$fa]=1;
			if(!isset($args['sfw_'.$fa])){
				$args['sfw_'.$fa]=0;
			}
		}
	}
	return $args;
}

//Callback Function to validate SOLR Settings
function solr_settings_validate($args){
    if(isset($args['solr_status']) && ($args['solr_status']=="active")){
    	if(!isset($args['solr_search_url'])||!isset($args['solr_search_port'])||!isset($args['solr_search_ext'])||$args['solr_search_url']==""||$args['solr_search_port'] == ''||$args['solr_search_ext'] == ''){
    		$args['solr_status'] = 'inactive';
    		$args['solr_search_url'] = '';
			$args['solr_search_port'] = '';
			$args['solr_search_ext'] = '';
			$args['sf_enable_settings'] = '';
    		add_settings_error('solr_settings', 'solr_invalid_search_param', 'Please fill all the fields!', $type = 'error');
    	}
		else{
			require __DIR__.'/assets/solarium-master/vendor/autoload.php';
			$solrconfig1 = array('endpoint' => array('localhost' => array('host' => $args['solr_search_url'],'port' =>$args['solr_search_port'],'path' => $args['solr_search_ext'])));
			$client = new Solarium\Client($solrconfig1);			
			// create a ping query
			$ping = $client->createPing();
			// execute the ping query
			try{
				$result = $client->ping($ping);
				add_settings_error('solr_settings', 'solr_invalid_search_param', 'Settings Saved ! Ping to SOLR Search Successful !', $type = 'updated');
			}catch(Exception $e){
				$args['solr_status'] = 'inactive';
	    		$args['solr_search_url'] = '';
				$args['solr_search_port'] = '';
				$args['solr_search_ext'] = '';
				$args['sf_enable_settings'] = '';
				add_settings_error('solr_settings', 'solr_invalid_search_param', 'Please check all the feilds!', $type = 'error');
			}
		}
    }
    else{
    	$args['solr_status'] = 'inactive';
    	$args['solr_search_url'] = '';
		$args['solr_search_port'] = '';
		$args['solr_search_ext'] = '';
		$args['sf_enable_settings'] = '';
    }
    return $args;
}

//Function to Index and Update the SOLR Index.
function solr_index_validate($args){
	$solr_search_value = get_option( 'solr_settings');
	if($solr_search_value[solr_status]=='active'){
	$args1 = array('post_type'=> 'ad_listing','posts_per_page' => -1);
	$the_query = new WP_Query( $args1 );
	while ($the_query->have_posts()){ 
			$the_query->the_post();
			$post_id = $the_query->post->ID;
			$abc = get_post($post_id);
			$tags_array = wp_get_object_terms($post_id,'ad_tag');
			$category_array = wp_get_object_terms($post_id,'ad_cat');
			$meta_values = get_post_meta($post_id);
			require __DIR__.'/assets/solarium-master/vendor/autoload.php';
			$solrconfig = array('endpoint' => array('localhost' => array('host' => $solr_search_value[solr_search_url],'port' => $solr_search_value[solr_search_port],'path' => $solr_search_value[solr_search_ext])));
			$client = new Solarium\Client($solrconfig);
			if($abc->post_status=="publish" || $abc->post_status==" publish" ){
				$files = get_children('post_parent='.$post_id.'&post_type=attachment&post_mime_type=image');
				if($files) :
					$keys = array_reverse(array_keys($files));
					$j=0;
					$num = $keys[$j];
					$image=wp_get_attachment_image($num, 'large', false);
					$imagepieces = explode('"', $image);
					$imagepath = $imagepieces[1];
					$thumb=wp_get_attachment_thumb_url($num);
				endif;	
				try{
					$update = $client->createUpdate();
					$doc = $update->createDocument();
					$doc->id = $abc->ID;
					$doc->post_id = $abc->ID;
					$doc->post_title = $abc->post_title;
					$doc->post_content=$abc->post_content;
					$doc->post_permalink = get_permalink( $post_id );
					$doc->street = $meta_values['cp_street'][0];
					$doc->city = $meta_values['cp_city'][0];
					$doc->state = $meta_values['cp_state'][0];
					$doc->country = $meta_values['cp_country'][0];
					$doc->zipCode = $meta_values['cp_zipcode'][0];
					$doc->post_author_id = $abc->post_author;
					$doc->post_author_name = get_userdata($abc->post_author)->display_name ;
					if(is_numeric( $meta_values['cp_price'][0])){
						$doc->price = $meta_values['cp_price'][0];
					}
					$doc->listedDate =   s4w_format_date($abc->post_date);
					$doc->modifiedDate =  s4w_format_date($abc->post_modified);
					$doc->primaryImage = $thumb;
					$doc->featuredFlag = is_sticky($post_id);

					foreach ($category_array as $cat){
						$xcat =(int)$cat->term_id;
						$catb=explode(",",get_custom_category_parents( $xcat, 'ad_cat', TRUE, '', FALSE ));
						foreach($catb as $catbx){
							$doc->addField('categories', $catbx);
						}
					}
					foreach ($tags_array as $tag)
						$doc->addField('tags', $tag->name);
					$update->addDocuments(array($doc));
					$update->addCommit();
					$result = $client->update($update);
				}catch(Solarium\Exception $e){
					add_settings_error('solr_settings', 'solr_invalid_search_param', 'Error Occured! Please Try Again Latter...', $type = 'error');
				}
			}else{
				try{
					$update = $client->createUpdate();
					$update->addDeleteById($post_id);
					$update->addCommit();
					$result = $client->update($update);
				}catch(Solarium\Exception $e){
					add_settings_error('solr_settings', 'solr_invalid_search_param', 'Error Occured! Please Try Again Latter...', $type = 'error');
				}
			}
		}
	}
	add_settings_error('solr_settings', 'solr_invalid_search_param', 'All Ads Indexed!', $type = 'updated');
	//unset($_POST['s4w_indexall']);
	return true;
}
//Display the validation errors and update messages
/*
* Admin notices
*/
add_action('admin_notices', 'solr_admin_notices');
function solr_admin_notices(){
   settings_errors();
}

//SOLR Settings Page in the WP-ADMIN Area.
function solr_admin_page_callback(){ 
?>
<div class="wrap">
  <div class="icon32" id="icon-themes"><br>
  </div>
  <h2>SOLR Settings</h2>
  <div id="tabs-wrap">
    <ul class="tabs" role="tablist">
    </ul>
    <form action="options.php" method="post">
      <?php 
    	settings_fields( 'solr_settings' );
        do_settings_sections( __FILE__ );
        $options = get_option( 'solr_settings' );
    ?>
      <table class="widefat fixed" style="width:60%; margin-bottom:20px;">
        <tr class="form_label_row">
          <td class="titledesc">SOLR Status</td>
          <td><fieldset>
              <label>
                <select name="solr_settings[solr_status]" id="solr_status">
                  <option value="active" <?php if(isset($options['solr_status']) && $options['solr_status'] != '' && $options['solr_status']!='active'){?>selected="selected"<?php }?>>Active</option>
                  <option value="inactive" <?php if((isset($options['solr_status']) && $options['solr_status'] == 'inactive') || !isset($options['solr_status'])){?>selected="selected"<?php }?>>Inactive</option>
                </select>
                <br />
                <span class="description">Please select an option.</span> </label>
            </fieldset></td>
        </tr>
        <tr class="form_label_row">
          <td class="titledesc">SOLR Search Url</td>
          <td><fieldset>
              <label>
                <input name="solr_settings[solr_search_url]" type="text" id="solr_search_url" value="<?php echo (isset($options['solr_search_url']) && $options['solr_search_url'] != '') ? $options['solr_search_url'] : ''; ?>"/>
                <br />
                <span class="description">Please enter a valid API URL.</span> </label>
            </fieldset></td>
        </tr>
        <tr class="form_label_row">
          <td class="titledesc">SOLR Search Port</td>
          <td><fieldset>
              <label>
                <input name="solr_settings[solr_search_port]" type="text" id="solr_search_port" maxlength="5" value="<?php echo (isset($options['solr_search_port']) && $options['solr_search_port'] != '') ? $options['solr_search_port'] : ''; ?>"/>
                <br />
                <span class="description">Please enter a valid port.</span> </label>
            </fieldset></td>
        </tr>
        <tr class="form_label_row">
          <td class="titledesc">SOLR Search Extension</td>
          <td><fieldset>
              <label>
                <input name="solr_settings[solr_search_ext]" type="text" id="solr_search_ext" value="<?php echo (isset($options['solr_search_ext']) && $options['solr_search_ext'] != '') ? $options['solr_search_ext'] : ''; ?>"/>
                <br />
                <span class="description">Please enter a valid extension.</span> </label>
            </fieldset></td>
        </tr>
        <tr class="form_label_row">
          <td class="titledesc">Enable Settings for Searchable fields and their Weights</td>
          <td><fieldset>
              <label>
                <input name="solr_settings[sf_enable_settings]" type="checkbox" id="sf_enable_settings" value="1"  <?php echo checked(1,$options['sf_enable_settings'],false);?> />
              </label>
            </fieldset></td>
        </tr>
        <tr class="form_label_row">
          <td class="titledesc">Save Settings</td>
          <td><input type="submit" class="button-primary" value="<?php _e('Save', 'solr4wp') ?>" /></td>
        </tr>
      </table>
    </form>
    <form method="post" action="options.php?page=solr-settings" name="indexallform">
      <h3>
        <?php _e('Actions', 'solr4wp') ?>
      </h3>
      <table class="widefat fixed" style="width:60%; margin-bottom:20px;">
        <tr class="form_label_row">
          <td class="titledesc">Index All Ads</td>
          <td><input type="submit" class="button-primary" name="s4w_indexall" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
        </tr>
        <tr class="form_label_row">
          <td class="titledesc">Prune Ads</td>
          <td><input type="submit" class="button-primary" name="s4w_pruneall" value="<?php _e('Execute', 'solr4wp') ?>" /></td>
        </tr>
      </table>
    </form>
    <?php if($options['solr_status']=='active' && isset($options['sf_enable_settings'])){?>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.6.2.js"></script>
    <form method="post" action="options.php" name="setsearchableform">
      <h3>
        <?php _e('Searchable Fields and Weights', 'solr4wp') ?>
      </h3>
      <?php 
            settings_fields( 'sf_settings' );
            do_settings_sections( __FILE__ );
            $foptions = get_option( 'sf_settings' );
      ?>
      <table class="widefat fixed" style="width:60%; margin-bottom:20px;">
        <tr class="form_label_row">
          <td colspan="2"><table class="widefat fixed">
              <thead>
                <tr>
                  <th width="10%"> <script>
					    $(function(){
 							$("#selectall").click(function () {
								  $('.child').attr('checked', this.checked);
							});
						 	$(".child").click(function(){
								if($(".child").length == $(".child:checked").length) {
									$("#selectall").attr("checked", "checked");
								} else {
									$("#selectall").removeAttr("checked");
								}
							});
						});
			        </script>
                    <input type="checkbox" id="selectall"  name="sf_settings[selectall]" value="1" <?php echo checked(1,$foptions["selectall"],false);?>/>
                  </th>
                  <th width="25%">Searchable Field Name</th>
                  <th>Field Weight</th>
                </tr>
              </thead>
              <tbody>
                <?php 
				$feild_array= array("post_title" => "Ad Title","post_content" => "Ad Content","street" => "Street","city" => "City","state" => "State","country" => "Country","zipCode" => "Zipcode","categories" => "Categories","tags" => "Tags");
foreach($feild_array as $fa => $fa_value){
			?>
                <tr>
                  <td><input type="checkbox" class="child" name="sf_settings[sfs_<?php echo $fa;?>]" value="1" id="sfs_<?php echo $fa;?>" <?php echo checked(1,$foptions["sfs_".$fa.""],false);?>/></td>
                  <td><?php echo $fa_value;?></td>
                  <td><script type="text/javascript">//<![CDATA[ 
						jQuery(document).ready(function(){
							$("#slider_<?php echo $fa;?>").slider({
								range: "min",
								value: <?php echo (isset($foptions["sfw_".$fa.""])&$foptions["sfw_".$fa.""]!='')? $foptions["sfw_".$fa.""]:0; ?>,
								step: 0.1,
								min: 0,
								max:9,
								slide: function( event, ui ) {
									$( "#sfw_<?php echo $fa;?>" ).val( ui.value );
								}
							});
							$("#sfw_<?php echo $fa;?>").change(function () {
								var value = this.value.substring(1);
								console.log(value);
								$("#slider_<?php echo $fa;?>").slider("value", parseFloat(value));
							});
						});//]]>  
					</script>
                    <div>
                      <div style="width:40%;float:left">
                        <input type="text" maxlength="4" name="sf_settings[sfw_<?php echo $fa;?>]" id="sfw_<?php echo $fa;?>" value="<?php echo (isset($foptions["sfw_".$fa.""])&&$foptions["sfw_".$fa.""]!='')?$foptions["sfw_".$fa.""]:0 ;?>"/>
                      </div>
                      <div style="width:55%;float:left;margin-top: 6px;;margin-left:4%">
                        <div id="slider_<?php echo $fa;?>"></div>
                      </div>
                    </div></td>
                </tr>
                <?php }?>
              </tbody>
            </table></td>
        </tr>
        <tr class="form_label_row">
          <td class="titledesc">Save Settings</td>
          <td><input type="submit" class="button-primary" name="sf_submit" value="<?php _e('Save Settings', 'solr4wp') ?>" /></td>
        </tr>
      </table>
    </form>
    <?php }?>
  </div>
</div>
<?php 
}
?>
