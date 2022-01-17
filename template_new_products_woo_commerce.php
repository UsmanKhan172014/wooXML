<?php
/* Template Name: Example Template */
//get_header();

$url = "Your-xml-products-feed";
function addTerms($url){
    $rss = simplexml_load_file($url);
    foreach ($rss->channel->item as $node) {
        $categories = (array)$node->categories;
        $categories = getExploded($categories['category']);
        for($i=0;$i<sizeof($categories);$i++){
            $counter=0;
            foreach ($categories[$i] as $cat){
                $parent_id=0;
                if ($counter==0){
                    $term=term_exists($cat);
                    if ($term!=null){
                        $parent_id=(int)term_exists($cat);
                    }
                    else{
                        $parent_id=wp_insert_term($cat,"product_cat");
                    }
                }
                else{
                    $term=term_exists($cat);
                    $parent_term=term_exists($categories[$i][$counter-1]);
                    if ($term!=null && $parent_term!=null){
                        if ((int)$parent_term==$parent_id){

                        }
                        else{
                            $parent_id=(int)$parent_term;
                            $args=array(
                                'parent'=>$parent_id,
                            );
                            $term=wp_update_term($cat,'product_cat',$args);
                        }
                    }
                    else{
                        $args=array(
                            'parent'=>$parent_id,
                        );
                        $term=wp_insert_term($cat,'product_cat',$args);
                        $parent_id=getParentID($categories[$i][$counter]);
                        echo "New term $cat Inserted";
                    }

                }
                ++$counter;
            }

        }
    }
}

function getParentID($term)
{
	global $wpdb;
	return (int)$parent_id = $wpdb->get_var("select term_id from wp_terms where name='$term'");
}
function getPostId($title){
	global $wpdb;
	$id=$wpdb->get_var("select ID from wp_posts where post_title='$title' LIMIT 1");
	return (int)$id;
}
function getCategoriesIDsArray($categories){
	$categoryID=array();
	foreach ($categories as $cat){
		$categoryID=array_push($categoryID,getParentID($cat));
	}
	return $categoryID;
}

function getExploded($categories){
	$category=array();

	foreach ($categories as $catUn){
		$i=2;
		$temp=array();
		$exploded=explode("||",$catUn);
		for (;$i<sizeof($exploded);$i++){
			array_push($temp,$exploded[$i]);
		}
		array_push($category,$temp);
		$temp="";
	}

	return $category;
}
function get_rss_feed_as_html($feed_url, $max_item_cnt = 1000, $show_date = true, $show_description = true, $max_words = 0, $cache_timeout = 7200, $cache_prefix = "/tmp/rss2html-")
{
	$rss = simplexml_load_file($feed_url);
	$feed = array();
	$total_posts=0;
	foreach ($rss->channel->item as $node) {

		$title = (array)$node->title;
		$description = (array)$node->description;
		$content = (array)$node->content;
		$link = (array)$node->link;
		$pubDate = (array)$node->pubDate;
		$orig_price = (array)$node->orig_price;
		$price = (array)$node->price;

		$main_image = (array)$node->main_image;
		$additional = (array)$node->addtl_images;
		$sku = (array)$node->sku;
		$categories = (array)$node->categories;
		$quantity_available = (array)$node->quantity_available;
		$manufacturer = (array)$node->manufacturer;
		$color = (array)$node->color;
		$item_condition = (array)$node->item_condition;
		$item_size = (array)$node->item_size;
		$item_type = (array)$node->item_type;
		$id = (array)$node->id;
		$material = (array)$node->material;
		$categories = getExploded($categories['category']);
		$item = array(
			'title' => $title[0],
			'description' => $description[0],
			'content' => $content[0],
			'link' => $link[0],
			'pubDate' => $pubDate[0],
			'orig_price' => $orig_price[0],
			'price' => $price[0],
			'main_image' => $main_image[0],
			'additional' => $additional,
			'sku' => $sku[0],
			'categories' => $categories,
			'quantity_available' => $quantity_available[0],
			'manufacturer' => $manufacturer[0],
			'color' => $color[0],
			'item_condition' => $item_condition[0],
			'item_size' => $item_size[0],
			'item_type' => $item_type[0],
			'material' => $material[0],
			'id' => $id[0],
		);

		array_push($feed, $item);
		++$total_posts;
	}
	$total_posts;

	if ($max_item_cnt > count($feed)) {
		$max_item_cnt = count($feed);
	}


	for ($x = 0; $x < $max_item_cnt; $x++) {
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
		$link = $feed[$x]['link'];
		$pubDate = $feed[$x]['pubDate'];

		$description = $feed[$x]['description'];
		$content = $feed[$x]['content'];
		$orig_price = $feed[$x]['orig_price'];
		$price = $feed[$x]['price'];
		$main_image = $feed[$x]['main_image'];
        $additional=$feed[$x]['additional'];
		$categories = $feed[$x]['categories'];
		$quantity_available = $feed[$x]['quantity_available'];
		$manufacturer = $feed[$x]['manufacturer'];
		$color = $feed[$x]['color'];
		$item_condition = $feed[$x]['item_condition'];
		$item_size = $feed[$x]['item_size'];
		$item_type = $feed[$x]['item_type'];
        $material=$feed[$x]['material'];
		$id = $feed[$x]['id'];
		$sku = $feed[$x]['sku'];


		$post_description= $description;
		$post_description .="<p><b>Quantity Available </b> : ". $quantity_available;
		$post_description .="<br><b>Manufacturer</b> : ". $manufacturer;
		$post_description .="<br><b>Color</b> : ".$color;
		$post_description .="<br><b>Condition</b> : ".$item_condition;
		$post_description .="<br><b>Item Size</b> : ".$item_size;
		$post_description .="<br><b>Item Type</b> : ".$item_type;
		$post_description .="<br><b>Item Material</b> : ".$material."</p>";
		global $wpdb;
		$rst = $wpdb->get_var("SELECT count(post_id) as total FROM wp_postmeta WHERE meta_key='_pid' and meta_value='$id'");
        if ($rst == 0) {
            $post_title=$sku." - ".$title;
			$post = array(
				'post_author' => 1,
				'post_content' => $post_description,
				'post_status' => "publish",
				'post_title' => $post_title,
				'post_parent' => '',
				'post_excerpt' => $post_description,
				'post_type' => "product"
			);
			echo $post_title."<br>";
						$post_id = wp_insert_post($post);
//						wp_set_object_terms( $post_id, 'simple', 'product_type');
						Generate_Featured_Image( $main_image, $post_id );


						//to generate the additional images
                        foreach ($additional as $add){
                            Generate_additional_Images($add,$post_id);
                        }
                        if((int)$price==0){
                            update_post_meta( $post_id, '_visibility', 'hidden' );
                        }
                        else{
                            update_post_meta( $post_id, '_visibility', 'visible' );
                        }

						update_post_meta( $post_id, '_stock_status', 'instock');
						update_post_meta( $post_id, 'total_sales', '0');
						update_post_meta( $post_id, '_downloadable', 'no');
						update_post_meta( $post_id, '_virtual', 'yes');
						update_post_meta( $post_id, '_regular_price', $orig_price );
						update_post_meta( $post_id, '_sale_price', $price );
						update_post_meta( $post_id, '_purchase_note', "" );
						update_post_meta( $post_id, '_featured', "no" );
						update_post_meta( $post_id, '_weight', "" );
						update_post_meta( $post_id, '_length', $item_size );
						update_post_meta( $post_id, '_width', "" );
						update_post_meta( $post_id, '_height', "" );
						update_post_meta( $post_id, '_sku',$sku);
						update_post_meta( $post_id, '_product_attributes', array());
						update_post_meta( $post_id, '_sale_price_dates_from', "" );
						update_post_meta( $post_id, '_sale_price_dates_to', "" );
						update_post_meta( $post_id, '_price', $price);
						update_post_meta( $post_id, '_stock', $quantity_available );
						update_post_meta( $post_id, '_item_type', $item_type );
						update_post_meta( $post_id, '_material', $item_condition );
						update_post_meta( $post_id, '_pid', $id );
			for ($i=0;$i<=sizeof($categories);$i++){
				foreach ($categories[$i] as $cat){
							wp_set_object_terms( $post_id,$cat,'product_cat',true );
				}
			}
			echo "The new post inserted<br>";
		}
		else if ($rst==1){

		    $post_title=$sku." - ".$title;

		    $post = array(
		        'ID' => getPostId($post_title),
                'post_title'   => $post_title,
                'post_content' => $post_description,
            );
		    wp_update_post($post);

            foreach ($additional as $add){
                   Generate_additional_Images($add,$post_title);
            }
            if((int)$price==0){
                update_post_meta( getPostId($post_title), '_visibility', 'hidden' );
            }
            else{
                update_post_meta( getPostId($post_title), '_visibility', 'visible' );
            }
            update_post_meta( getPostId($post_title), '_price', $price);
            update_post_meta( getPostId($post_title), '_stock', $quantity_available );
			for ($i=0;$i<=sizeof($categories);$i++){
				foreach ($categories[$i] as $cat){
				    wp_set_object_terms( getPostId($post_title), $cat,'product_cat',true );
				}
			}

			echo "post got updated<br>";
		}
		exit;
	}

	echo "script completed";
	//return $result;
}

	function output_rss_feed($feed_url, $max_item_cnt = 10, $show_date = true, $show_description = true, $max_words = 0)
	{
		echo get_rss_feed_as_html($feed_url, $max_item_cnt, $show_date, $show_description, $max_words);
	}

//	addTerms($url);
    output_rss_feed($url, 577, true, true, 200);
?>