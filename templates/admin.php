<?php
$lang = get_locale();
$langPrefix = explode('_', $lang);
global $wpdb;


$selectedOrganisations = $this->getSelectedOrganisations();
$selectedContent = $this->getSelectedContent();

switch_to_blog(4);
$organisations = get_posts(array(
    'post_type' => 'organisations',
    'lang' => $langPrefix,
    'posts_per_page' => -1,
    'order' => 'ASC',
    'orderby' => 'title'
));
$articles = get_posts(array(
    'post_type' => 'post',
    'lang' => $langPrefix,
    'posts_per_page' => -1,
));
restore_current_blog();



/*
/* Deals with the admin menus categories
*/
$categories = get_terms(
    'category',
    array('parent' => 0)
);

$menu_items = wp_get_nav_menu_items(get_nav_menu_locations()['main_menu']);

//wp_die(print_r($_POST));

$post_lists = array(
    "popular_posts" => array(
        "title" => pll__("Popular posts", "youmatter")
    ), 
    "recent_posts" => array(
        "title" => pll__("Recent posts", "youmatter")
    ), 
);

$trimmedArticles = array_slice($articles, 0, 10);

/*
/* Saving admin menus categories data
*/
if (isset($_POST) && $menu_items) {
    if ($menu_items) {
        foreach ($menu_items as $menu_item) {
            if (isset($_POST['menu_item_' . $menu_item->ID])) {
                $post_data = $_POST['menu_item_' . $menu_item->ID];
                
                if (!empty($post_data)) {
                    $complete_post_data = get_featured_post_data($post_data);
                    if ($complete_post_data) {
                        $json_data = addslashes( 
                            json_encode($complete_post_data, JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE)
                        );
                        update_post_meta($menu_item->ID, "featured_post", $json_data);  
                    }
                } else {
                    delete_post_meta($menu_item->ID, "featured_post");
                }
            }
        }
    }
    if ($post_lists) {
        foreach ($post_lists as $key => $post_list) {
            
            if (isset($_POST['post_list_' . $key])) {
                
                $post_data = $_POST['post_list_' . $key];
                
                if (!empty($post_data)) {
                    
                    $complete_post_data = get_featured_post_data($post_data);
                    
                    if ($complete_post_data) {
                        $json_data = json_encode($complete_post_data, JSON_FORCE_OBJECT|JSON_UNESCAPED_UNICODE);
                        update_option('featured_post_list_' . $key, $json_data);

                        $option = get_option('featured_post_list_' . $key);
                        //print_r($option);
                    }
                } else {
                    delete_option('featured_post_list_' . $key);
                }
            }
        }        
    }

}

function get_featured_post_data($featured_post_id) {
    
    if (!empty($featured_post_id)) {
        $lang = get_locale();
        $langPrefix = explode('_', $lang);

        // Array to return
        $data_to_save =  array(
            "ID" => null,
            "title" => null,
            "permalink" => null,
            "thumbnail_url" => null,
            "author" => null,
            "author_link" => null
        );

        // Basic post data
        switch_to_blog(4);        
        $featured_post_data = get_post($featured_post_id);       
        $data_to_save["ID"] = $featured_post_id;
        $data_to_save["title"] = $featured_post_data->post_title;
        $data_to_save["permalink"] = get_permalink($featured_post_id);
        $data_to_save["thumbnail_url"] = get_the_post_thumbnail_url($featured_post_id, 'medium');
        $data_to_save["time"] = get_the_time( 'U', $featured_post_id);
        $data_to_save["post_type"] = "transition";
        $data_to_save["post_content"] = $featured_post_data->post_content;

        // Get author loop
        $featured_post_data_author_id = get_post_field('post_author', $featured_post_id);
        $args = array(
            'post_type' => 'organisations',
            'posts_per_page' => -1,
            'post_status' => 'published'
        );

        $query = new \WP_Query($args);
  
        if (!empty($query->posts)) {
            foreach ($query->posts as $org) {
                $orgUsers = get_field('users', $org->ID);
                
                if ($orgUsers && count($orgUsers) === 0) {
                    $data_to_save["author"] = "Youmatter";
                    $data_to_save["author_link"] = "#";
                    restore_current_blog();
                    return $data_to_save;
                }
                elseif(is_array($orgUsers)) {
                    foreach ($orgUsers as $u) {
                        $localUserId = $u["user"]["ID"];
                        
                        if ($localUserId == $featured_post_data_author_id) {
                            $data_to_save["author"] = $org->post_title;
                            $data_to_save["author_link"] = get_bloginfo('url') . "/" . $langPrefix[0] . "/" . $org->post_name;
                            restore_current_blog();
                            return $data_to_save;
                        }
                    }
                } 
            }
        }        
    }
}


?>
<div class="wrap">

    <h1 class="wp-heading-inline"><?php _e('Featured Articles', 'youmatter'); ?></h1>

    <h2 class="wp-heading-inline"><?php _e('Featured on navigation', 'youmatter'); ?></h2>

        <form class="featured_on_nav" action="<?php echo esc_url(admin_url('admin.php?page=choose_content')); ?>" method="post" id="nds_add_user_meta_form">
            
            <?php if ($menu_items) : ?>
                <h2><?php _e("Sponsored in menu", "youmatter"); ?></h2>
                <div class="featured_menu_items">
                <?php foreach ($menu_items as $menu_item) : ?>
                    <?php 
                        if ($menu_item->type == "taxonomy" && $menu_item->menu_item_parent == 0) :
                        $menu_item_featured_post = json_decode(get_post_meta($menu_item->ID, "featured_post", true));
                    ?>
                        <fieldset>
                            <h3><?php echo $menu_item->title; ?></h3>
                            <?php if ($trimmedArticles) : ?>
                                <select name="menu_item_<?php echo $menu_item->ID; ?>">
                                    <option value=""><?php _e('Select an article', 'youmatter'); ?></option>

                                    <?php foreach ($trimmedArticles as $article) : ?>
                                        <option 
                                            value="<?php echo $article->ID; ?>"
                                            <?php if (isset($menu_item_featured_post) && $menu_item_featured_post->ID == $article->ID) echo "selected='selected'"; ?>
                                        ><?php echo $article->post_title; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            <?php endif; ?>
                        </fieldset>
                    <?php endif; ?>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($post_lists) : ?>
            <h2><?php _e("Sponsored in posts lists", "youmatter"); ?></h2>
            <div class="featured_posts_lists">
                <?php foreach ($post_lists as $key => $post_list) : ?>
                    <?php  
                        $featured_option = json_decode(get_option('featured_post_list_' . $key));
                        $decoded_featured_option = json_decode($featured_option);
                    ?>

                    <fieldset>
                            <h3><?php echo $post_list["title"]; ?></h3>
                            <?php if ($trimmedArticles) : ?>
                                <select name="post_list_<?php echo $key; ?>">
                                    <option value=""><?php _e('Select an article', 'youmatter'); ?></option>

                                    <?php foreach ($trimmedArticles as $article) : ?>
                                        <option 
                                            value="<?php echo $article->ID; ?>"
                                            <?php if (isset($featured_option) && $featured_option->ID == $article->ID) echo "selected='selected'"; ?>
                                        ><?php echo $article->post_title; ?></option>
                                    <?php endforeach; ?>

                                </select>
                            <?php endif; ?>
                        </fieldset>

                <?php endforeach; ?>
            <?php endif; ?>

            </div>
            <button class="button button-primary button-large menu-save"><?php _e('Save/update featured posts on navigation', 'youmatter'); ?></button>
        </form>


    <h2 class="wp-heading-inline"><?php _e('Featured Organisation', 'youmatter'); ?></h2>

    <div class="wp-list-table">
        <div class="section">
            <ul class="section__area organisations__list">
                <?php foreach ($organisations as $organisation) : ?>
                    <li class="section__item">
                        <a href="#" data-id="<?= $organisation->ID; ?>" class="section__link add_organisation">
                            <?= $organisation->post_title; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul class="section__area organisations__selected">
                <?php if (!empty($selectedOrganisations)) : ?>
                    <?php foreach ($selectedOrganisations as $key => $value) : ?>
                        <li class="section__item">
                            <a href="#" data-id="<?= $key; ?>" class="section__link remove_organisation">
                                <?= $value; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <h2 class="wp-heading-inline"><?php _e('Sponsored Content', 'youmatter'); ?></h2>
    <div class="wp-list-table">
        <div class="section">
            <ul class="section__area content__list">
                <?php foreach ($articles as $article) : ?>
                    <li class="section__item">
                        <a href="#" data-id="<?= $article->ID; ?>" class="section__link add_content">
                            <?= $article->post_title; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <ul class="section__area content__selected">
                <?php if (!empty($selectedContent)) : ?>
                    <?php foreach ($selectedContent as $key => $value) : ?>
                        <li class="section__item">
                            <a href="#" data-id="<?= $key; ?>" class="section__link remove_content">
                                <?= $value; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

    </div>
</div>