<?php 
	global $app_abbr,$cp_options;
	$category_name=0;
	if($_GET['scat'] != 0){
		$category_name = get_term( $_GET['scat'], 'ad_cat' );
	}
 	if(isset($_GET['s'])){
		$searchTxt = esc_attr($_GET['s'] );
	}else{
		$searchTxt = "*";	
	}
	if ( $searchTxt ==  __( 'What are you looking for?', APP_TD ) || $searchTxt==''){
		$searchTxt = '*';
	}
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<title>
<?php wp_title(printf( __( "Search for '%s'", APP_TD ), $searchTxt))?>
</title>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo appthemes_get_feed_url(); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php if ( is_singular() && get_option('thread_comments') ) wp_enqueue_script('comment-reply'); ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php appthemes_before(); ?>
<div class="container">
  <?php if ( $cp_options->debug_mode ) { ?>
  <div class="debug">
    <h3>
      <?php _e( 'Debug Mode On', APP_TD ); ?>
    </h3>
    <?php print_r( $wp_query->query_vars ); ?></div>
  <?php } ?>
  <?php appthemes_before_header(); ?>
  <?php get_header( app_template_base() ); ?>
  <?php appthemes_after_header(); ?>
  <div id="search-bar">
    <div class="searchblock_out">
      <div class="searchblock">
        <form action="<?php echo home_url('/'); ?>" method="get" id="searchform" class="form_search">
          <div class="searchfield">
            <input name="s" type="text" id="s" tabindex="1" class="editbox_search" style=" <?php cp_display_style( 'search_field_width' ); ?>" <?php if ($_GET['s']){ echo 'value="'.trim(strip_tags($_GET['s'])).'"'; } else { ?> value="<?php _e( 'What are you looking for?', APP_TD ); ?>" onfocus="if (this.value == '<?php _e( 'What are you looking for?', APP_TD ); ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e( 'What are you looking for?', APP_TD ); ?>';}" <?php } ?> />
          </div>
          <div class="searchbutcat">
            <button class="btn-topsearch" type="submit" tabindex="3" title="<?php _e( 'Search Ads', APP_TD ); ?>" id="go" value="search" name="sa">
            <?php _e( 'Search Ads', APP_TD ); ?>
            </button>
            <?php wp_dropdown_categories('show_option_all='. __( 'All Categories', APP_TD ) .'&hierarchical='. $cp_options->cat_hierarchy .'&hide_empty='. $cp_options->cat_hide_empty .'&depth='. $cp_options->search_depth .'&show_count='. $cp_options->cat_count .'&pad_counts='. $cp_options->cat_count .'&orderby=name&title_li=&use_desc_for_title=1&tab_index=2&name=scat&selected='.$_GET['scat'].'&class=searchbar&taxonomy='.APP_TAX_CAT); ?>
          </div>
        </form>
      </div>
      <!-- /searchblock --> 
    </div>
    <!-- /searchblock_out --> 
  </div>
  <!-- /search-bar -->
  <div class="content">
    <div class="content_botbg">
      <div class="content_res">
        <div id="breadcrumb">
          <?php if ( function_exists('cp_breadcrumb_modified') ) cp_breadcrumb_modified(); ?>
        </div>
        <!-- /breadcrumb --> 
        <!-- left block -->
        <div class="content_left">
          <div class="shadowblock_out">
            <div class="shadowblock">
              <h1 class="single dotted">
                <?php 
					$resultsetnum = s4w_search_results_get_num($searchTxt,$category_name);
					$_SESSION['total_records'] = $resultsetnum;
			  		printf( __( "Search for '%s' returned %s results", APP_TD ), $searchTxt, $_SESSION['total_records'] );
			  ?>
              </h1>
              <div class="pagingblock">
                
                  <?php 
					$adjacents=2;
					
					$targetpageinitial = get_site_url()."/?s=".$_GET['s']."&sa=".$_GET['sa']."&scat=".$_GET['scat']; 	
					$targetpage=$targetpageinitial;
					$ipp = 5;
					if(isset($_GET['ipp'])){
						if($_GET['ipp']==5 || $_GET['ipp']==10 || $_GET['ipp']==15 || $_GET['ipp']==15 || $_GET['ipp']==25){
							$targetpage.="&ipp=".$_GET['ipp'];
							$ipp = $_GET['ipp']; 
						}else{
							$targetpage.="&ipp=5";
							$ipp = 5; 
						}
					}
					if(isset($_GET['sort'])){
						if($_GET['sort']=='best' || $_GET['sort']=='high' || $_GET['sort']=='low'){
							$targetpage.="&sort=".$_GET['sort'];
							$sort = $_GET['sort']; 
						}else{
							$targetpage.="&sort=best";
							$sort ='best'; 
						}
					}
					
					$page_num =clean($_GET['page_num']);
					
					if ($page_num == 0) $page_num = 1;				//if no page var is given, default to 1.
					
					$prev = $page_num - 1;							//previous page is page - 1
					$next = $page_num + 1;							//next page is page + 1
					
					$numpages1 = $_SESSION['total_records'] / $ipp;
					
					$lastpage = ceil($numpages1);					//lastpage is = total pages / items per page, rounded up.
					$lpm1 = $lastpage - 1;							//last page minus 1
					
					$pagination = ""; 
					
					if($lastpage > 1)
					{	
						$pagination .= "<div class=\"pagination\">";
						
						if ($page_num > 1) 
							$pagination.= "<a href=\"$targetpage&page_num=$prev\">< Previous</a>";
						else
							$pagination.= "<span class=\"disabled\">< Previous</span>";	
						
						if ($lastpage < 7 + ($adjacents * 2))	
						{	
							for ($counter = 1; $counter <= $lastpage; $counter++)
							{
								if ($counter == $page_num)
									$pagination.= "<span class=\"current\">$counter</span>";
								else
									$pagination.= "<a href=\"$targetpage&page_num=$counter\">$counter</a>";					
							}
						}
						elseif($lastpage > 5 + ($adjacents * 2))	
						{
							
							if($page_num < 1 + ($adjacents * 2))		
							{
								
								for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
								{
									if ($counter == $page_num)
										$pagination.= "<span class=\"current\">$counter</span>";
									else
										$pagination.= "<a href=\"$targetpage&page_num=$counter\">$counter</a>";					
								}
								$pagination.= "...";
								$pagination.= "<a href=\"$targetpage&page_num=$lpm1\">$lpm1</a>";
								$pagination.= "<a href=\"$targetpage&page_num=$lastpage\">$lastpage</a>";		
							}
							elseif($lastpage - ($adjacents * 2) > $page_num && $page_num > ($adjacents * 2))
							{
								$pagination.= "<a href=\"$targetpage&page_num=1\">1</a>";
								$pagination.= "<a href=\"$targetpage&page_num=2\">2</a>";
								$pagination.= "...";
								for ($counter = $page_num - $adjacents; $counter <= $page_num + $adjacents; $counter++)
								{
									if ($counter == $page_num)
										$pagination.= "<span class=\"current\">$counter</span>";
									else
										$pagination.= "<a href=\"$targetpage&page_num=$counter\">$counter</a>";					
								}
								$pagination.= "...";
								$pagination.= "<a href=\"$targetpage&page_num=$lpm1\">$lpm1</a>";
								$pagination.= "<a href=\"$targetpage&page_num=$lastpage\">$lastpage</a>";		
							}
							else
							{
								$pagination.= "<a href=\"$targetpage&page_num=1\">1</a>";
								$pagination.= "<a href=\"$targetpage&page_num=2\">2</a>";
								$pagination.= "...";
								for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
								{
									if ($counter == $page_num)
										$pagination.= "<span class=\"current\">$counter</span>";
									else
										$pagination.= "<a href=\"$targetpage&page_num=$counter\">$counter</a>";					
								}
							}
						}
						if ($page_num < $counter - 1) 
							$pagination.= "<a href=\"$targetpage&page_num=$next\">Next ></a>";
						else
							$pagination.= "<span class=\"disabled\">Next ></span>";
						$pagination.= "</div>\n";		
					}else{
						$pagination .= "<div class=\"pagination\"><h3 style='margin-top:5px;'>Showing page 1 of 1 </h3></div>";
					}
					 echo $pagination;
			   ?>
              </div>
              <div class="clr"></div>
            </div>
            <!-- /shadowblock --> 
          </div>
          <!-- /shadowblock_out -->
          <div class="clr"></div>
          
          <?php 
		  	$target_ipp=$targetpageinitial;
			if(isset($_GET['sort'])){
				if($_GET['sort']=='best' || $_GET['sort']=='high' || $_GET['sort']=='low'){
					$target_ipp.='&sort='.$_GET['sort'];
				}else{
					$target_ipp.='&sort=best';
				}
			}
		  ?>
          <div class="shadowblock_out">
              <div class="shadowblock">
              <div class="pagingblock">
                <div class="search_param">
                  <select name="items_per_page" id="items_per_page" onchange="if (this.value) window.location.href=this.value">
                    <option class="level-0" value="<?php echo $target_ipp."&ipp=5";?>" <?php if($_GET['ipp'] == 5 || $_GET['ipp']!= 10 || $_GET['ipp']!= 15 || $_GET['ipp']!= 20 || $_GET['ipp']!= 25){?>selected="selected"<?php } ?> value="5">5 Results/Page</option>
                    <option class="level-0" value="<?php echo $target_ipp."&ipp=10";?>" <?php if($_GET['ipp'] == 10){?>selected="selected"<?php } ?> <?php if( $_SESSION['total_records']<5){echo "disabled";}?>>10 Results/Page</option>
                    <option class="level-0" value="<?php echo $target_ipp."&ipp=15";?>" <?php if($_GET['ipp'] == 15){?>selected="selected"<?php } ?><?php if( $_SESSION['total_records']<10){echo "disabled";}?>>15 Results/Page</option>
                    <option class="level-0" value="<?php echo $target_ipp."&ipp=20";?>" <?php if($_GET['ipp'] == 20){?>selected="selected"<?php } ?> <?php if( $_SESSION['total_records']<15){echo "disabled";}?>>20 Results/Page</option>
                    <option class="level-0" value="<?php echo $target_ipp."&ipp=25";?>" <?php if($_GET['ipp'] == 25){?>selected="selected"<?php } ?> <?php if( $_SESSION['total_records']<20){echo "disabled";}?>>25 Results/Page</option>
                  </select>
                </div>
                <?php 
					$target_sort=$targetpageinitial;
					if(isset($_GET['ipp'])){
						if($_GET['ipp']==5 || $_GET['ipp']==10 || $_GET['ipp']==15 || $_GET['ipp']==15 || $_GET['ipp']==25){
							$target_sort.='&ipp='.$_GET['ipp'];
						}else{
							$target_sort.='&ipp=5';
						}
					}
				?>
                <div class="search_param">
                  <select name="items_sort" id="items_sort" onchange="if (this.value) window.location.href=this.value" >
                    <option class="level-0" value="<?php echo $target_sort."&sort=best";?>" <?php if(!isset($_GET['sort']) || $_GET['sort'] == 'best' || $_GET['sort']!='high' || $_GET['sort']!='low'){?>selected="selected"<?php } ?> >Sort: Best Match </option>
                    <option class="level-0" value="<?php echo $target_sort."&sort=high";?>" <?php if($_GET['sort'] == 'high'){?>selected="selected"<?php } ?> >Sort: Highest Price</option>
                    <option class="level-0" value="<?php echo $target_sort."&sort=low";?>" <?php if($_GET['sort'] == 'low'){?>selected="selected"<?php } ?>>Sort: Lowest Price</option>
                  </select>
                </div>
                
              </div>
               <div class="clr"></div>
              </div>
            <!-- /shadowblock --> 
          </div>
          <!-- /shadowblock_out -->
          <div class="clr"></div>
          <?php $resultset = s4w_search_results($searchTxt, $category_name, $ipp, $page_num ,$sort);?>
          <?php appthemes_before_loop(); ?>
          <?php if ($resultset->getNumFound() != 0) : ?>
          <?php 
			foreach($resultset->getDocuments() as $result_array)
			{
				$post_id=$result_array['id'];
				appthemes_before_post();
		  ?>
          <div class="post-block-out <?php if($result_array['featuredFlag']==true){cp_display_style( 'featured' );} ?>">
            <div class="post-block">
              <div class="post-left">
                <?php if ( $cp_options->ad_images ) cp_ad_loop_thumbnail_modified($post_id); ?>
              </div>
              <div class="<?php cp_display_style( array( 'ad_images', 'ad_class' ) ); ?>">
                <div class="price-wrap"> <span class="tag-head">&nbsp;</span>
                  <p class="post-price">
                    <?php cp_get_price( $post_id, 'cp_price' );?>
                  </p>
                </div>
                <h3><a href="<?php echo get_permalink($post_id); ?>">
                  <?php if ( mb_strlen( get_the_title($post_id) ) >= 75 ) echo mb_substr( get_the_title($post_id), 0, 75 ).'...'; else echo get_the_title($post_id); ?>
                  </a></h3>
                <div class="clr"></div>
                <?php cp_ad_loop_meta_modified($post_id,$result_array['post_author_id'],$result_array['listedDate']); ?>
                <div class="clr"></div>
                <?php appthemes_before_post_content(); ?>
                <p class="post-desc"><?php echo cp_get_content_preview_modified( 160,$post_id ); ?></p>
                <?php //appthemes_after_post_content(); ?>
                <div class="clr"></div>
              </div>
              <div class="clr"></div>
            </div>
            <!-- /post-block --> 
          </div>
          <!-- /post-block-out -->
          <?php appthemes_after_post(); ?>
          <?php 
			}
          ?>
          <?php endif; ?>
          <?php appthemes_after_loop(); ?>
        </div>
        <!-- /content_left -->
        <?php get_sidebar(); ?>
        <div class="clr"></div>
      </div>
      <!-- /content_res --> 
    </div>
    <!-- /content_botbg --> 
  </div>
  <!-- /content -->
  <?php appthemes_before_footer(); ?>
  <?php get_footer( app_template_base() ); ?>
  <?php appthemes_after_footer(); ?>
</div>
<!-- /container -->
<?php wp_footer(); ?>
<?php appthemes_after(); ?>
</body>
</html>