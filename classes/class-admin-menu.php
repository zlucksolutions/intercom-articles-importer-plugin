<?php
class ZIAI_Modules {
    public static function ziai_init() {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'ziai_load_admin_style' ) );
        add_action( 'admin_menu', array( __CLASS__, 'ziai_register_menu_page' ) );
        add_action('wp_ajax_ziai_category_get', array( __CLASS__, 'ziai_category_get' ));
        add_action('wp_ajax_nopriv_ziai_category_get', array( __CLASS__, 'ziai_category_get' ));
        add_action('init', array( __CLASS__, 'ziai_register_post_type' ));
    }

    public static function ziai_register_menu_page() {
        add_submenu_page(
            'edit.php?post_type=zl_ziai_article',
            'Settings',
            'Settings',
            'manage_options',
            'ziai-articles',
            array( __CLASS__, 'ziai_admin_show_data' )
        );
    }

    public static function ziai_load_admin_style() {
        wp_enqueue_style( 'ziai_admin_style_css', ZIAI_FILE_URL . 'assets/css/style-admin.css');
        wp_enqueue_script('zl_admin_custom_script', ZIAI_FILE_URL . 'assets/js/zl-admin-custom.js', array('jquery'), '1.0', true);
        wp_localize_script('zl_admin_custom_script', 'wpAjax', array('ajaxUrl' => admin_url('admin-ajax.php')));
    }

    public static function ziai_admin_show_data() {
        if ( is_file(ZIAI_FILE_PATH . 'includes/wp-ziai-article-form.php') ) {
            $token                      = get_option('zl_ziai_access_token');
            $post_type                  = get_option('zl_post_type_get');
            $category                   = get_option('zl_category_get');
            $taxonomies                 = get_option('zl_taxonomy_get');
            $zl_default_author          = get_option('zl_default_author');
            $cron_time                  = get_option('ziai_cron_time');
            $cron_start_time            = get_option('ziai_cron_start_time');
            if(!empty($_POST) && (isset($_POST['runnow']) || isset($_POST['savechanges']))){
                $ziai_access_token          = sanitize_text_field($_POST['ziai_access_token']);
                $post_type                  = sanitize_text_field($_POST['zl_post_type_get']);
                $category                   = sanitize_text_field($_POST['zl_category_get']);
                $taxonomies                 = sanitize_text_field($_POST['zl_taxonomie']);
                $zl_default_author          = sanitize_text_field($_POST['zl_default_author']);
                $cron_time_u                = sanitize_text_field($_POST['ziai_cron_time']);
                $cron_time_u                = round(($cron_time_u * 2), 0) / 2;
                if ($cron_start_time != $_POST['ziai_cron_start_time'] || $cron_time_u != $cron_time) {
                    ZIAI_CronJob::ziai_cronstarter_deactivate();
                }
                $cron_start_time = sanitize_text_field($_POST['ziai_cron_start_time']);
                $cron_time = $cron_time_u;
                update_option('zl_ziai_access_token', $ziai_access_token);
                update_option('zl_post_type_get', $post_type);
                update_option('zl_category_get', $category);
                update_option('zl_taxonomy_get', $taxonomies);
                update_option('ziai_cron_time', $cron_time);
                update_option('ziai_cron_start_time', $cron_start_time);
                update_option('zl_default_author', $zl_default_author);
                $errormsg = "Articles settings updated successfully!!";
                if (isset($_POST['runnow'])) {
                    $arry = array(
                        "access_token" => $token,
                        "import_post_type" => $post_type,
                        "import_author" => $zl_default_author,
                        "import_taxonomy" => $taxonomies,
                    );
                    $importer_article 	= new ZIAI_Handler($arry);
                    $response           = $importer_article->ziai_import_article();
                    if ($response['status'] == 'errors') {
                        $errormsg = $response['message'];
                    } else {
                        $errormsg = 'Message:- ' . $response['message'] . '<br>';
                        $errormsg .= 'Episodes:- ' . $response['count'] .' Article Import';
                        //$errormsg .= 'Episodes:- '.$response['episodes'];
                    }
                }
            }
            include_once ZIAI_FILE_PATH . 'includes/wp-ziai-article-form.php';
        }
    }

    //Post Category Get
    public static function ziai_category_get()
    {
        $post_type      = 'zl_ziai_article';
        $taxonomies     = get_object_taxonomies($post_type, 'objects');
        $category       = get_option('zl_category_get');
        echo '<label for="zl_get_post_type_category"><b>Category</b></label>';
        echo '<select name="zl_category_get"><option value="">Select</option>';
        $terms = get_terms(array(
            'taxonomy' => array_key_first($taxonomies),
            'hide_empty' => false,
        ));
        foreach ($terms as $term) {
            ?>
            <option value="<?php echo $term->term_id; ?>" <?php if ($term->term_id == $category) { echo "selected"; } ?>><?php echo $term->name; ?></option>
            <?php
        }
        echo '</select><input type="hidden" name="zl_taxonomie" value=' . array_key_first($taxonomies) . '>';
        die();
    }

    //custom Post Type
    public static function ziai_register_post_type()
    {
        $cpt_name = 'Automatic Articles Importer';
        $single_item_slug   = 'automatic-article';
        $supports = array(
            'title', // post title
            'editor', // post content
            'author', // post author
            'thumbnail', // featured images
        );
        $labels = array(
            'name' => _x($cpt_name . 's', 'plural'),
            'singular_name' => _x($cpt_name, 'singular'),
            'menu_name' => _x('Automatic Articles Importer', 'admin menu'),
            'name_admin_bar' => _x($cpt_name, 'admin bar'),
            'add_new' => _x('Add New', 'add new'),
            'add_new_item' => __('Add New ' . $cpt_name),
            'new_item' => __('New ' . $cpt_name),
            'edit_item' => __('Edit ' . $cpt_name),
            'view_item' => __('View ' . $cpt_name),
            'all_items' => __('All ' . $cpt_name . 's'),
            'archives' =>  __($cpt_name . 's'),
            'search_items' => __('Search ' . $cpt_name),
            'not_found' => __('No ' . $cpt_name . ' found.'),
        );
        $args = array(
            'menu_icon' => 'dashicons-migrate',
            'supports' => $supports,
            'labels' => $labels,
            'public' => true,
            'query_var' => true,
            'rewrite' => array('slug' => $single_item_slug),
            'has_archive' => true,
            'hierarchical' => true,
        );
        register_post_type('zl_ziai_article', $args);
        $taxonomys = 'Collection';
        $taxonomyp = 'Collections';
        $taxlabels = array(
            'name' => _x($taxonomyp, 'plural'),
            'singular_name' => _x($taxonomys, 'singular'),
            'menu_name' => _x($taxonomyp, 'admin menu'),
            'name_admin_bar' => _x($taxonomys, 'admin bar'),
            'add_new' => _x('Add New', 'add new'),
            'add_new_item' => __('Add New ' . $taxonomys),
            'new_item' => __('New ' . $taxonomys),
            'edit_item' => __('Edit ' . $taxonomys),
            'view_item' => __('View ' . $taxonomys),
            'all_items' => __('All ' . $taxonomyp),
            'search_items' => __('Search ' . $taxonomys),
            'not_found' => __('No ' . $taxonomys . ' found.'),
        );
        register_taxonomy('article-cat', array('zl_ziai_article'), array(
            'hierarchical' => true,
            'labels' => $taxlabels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'article-cat'),
        ));
    }
}