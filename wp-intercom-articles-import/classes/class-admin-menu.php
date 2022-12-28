<?php
class ZL_Intercom_Article_Import_Modules {
    public static function init() {
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'intercom_load_admin_style' ) );
        add_action( 'admin_menu', array( __CLASS__, 'zl_register_my_custom_menu_page' ) );
        add_action('wp_ajax_zl_imtercom_category_get', array( __CLASS__, 'zl_imtercom_category_get' ));
        add_action('wp_ajax_nopriv_zl_imtercom_category_get', array( __CLASS__, 'zl_imtercom_category_get' ));
        add_action('init', array( __CLASS__, 'zl_intercom_register_post_type_articles' ));
    }

    public static function zl_register_my_custom_menu_page() {
        add_submenu_page(
            'edit.php?post_type=zl_intercom_article',
            'Settings',
            'Settings',
            'manage_options',
            'intercom-articles',
            array( __CLASS__, 'zl_intercom_articles_import' )
        );
    }

    public static function intercom_load_admin_style() {
        wp_enqueue_style( 'intercom_admin_style_css', INTERCOM_FILE_URL . 'assets/css/style-admin.css');
        wp_enqueue_script('zl_admin_custom_script', INTERCOM_FILE_URL . 'assets/js/zl-admin-custom.js', array('jquery'), '1.0', true);
        wp_localize_script('zl_admin_custom_script', 'wpAjax', array('ajaxUrl' => admin_url('admin-ajax.php')));
    }

    public static function zl_intercom_articles_import() {
        if ( is_file(INTERCOM_FILE_PATH . 'includes/wp-intercom-article-form.php') ) {
            $token                      = get_option('zl_intercom_access_token');
            $post_type                  = get_option('zl_post_type_get');
            $category                   = get_option('zl_category_get');
            $taxonomies                 = get_option('zl_taxonomy_get');
            $zl_default_author          = get_option('zl_default_author');
            $cron_time                  = get_option('zl_intercom_cron_time');
            $cron_start_time            = get_option('zl_intercom_cron_start_time');
            if(!empty($_POST) && (isset($_POST['runnow']) || isset($_POST['savechanges']))){
                $zl_access_token            = sanitize_text_field($_POST['zl_access_token']);
                $post_type                  = sanitize_text_field($_POST['zl_post_type_get']);
                $category                   = sanitize_text_field($_POST['zl_category_get']);
                $taxonomies                 = sanitize_text_field($_POST['zl_taxonomie']);
                $zl_default_author          = sanitize_text_field($_POST['zl_default_author']);
                $cron_time_u                = sanitize_text_field($_POST['zl_intercom_cron_time']);
                $cron_time_u                = round(($cron_time_u * 2), 0) / 2;
                if ($cron_start_time != $_POST['zl_intercom_cron_start_time'] || $cron_time_u != $cron_time) {
                    ZLIntercom_Article_Import_CronJob::zl_intercom_cronstarter_deactivate();
                }
                $cron_start_time = sanitize_text_field($_POST['zl_intercom_cron_start_time']);
                $cron_time = $cron_time_u;
                update_option('zl_intercom_access_token', $zl_access_token);
                update_option('zl_post_type_get', $post_type);
                update_option('zl_category_get', $category);
                update_option('zl_taxonomy_get', $taxonomies);
                update_option('zl_intercom_cron_time', $cron_time);
                update_option('zl_intercom_cron_start_time', $cron_start_time);
                update_option('zl_default_author', $zl_default_author);
                $errormsg = "Intercom settings updated successfully!!";
                if (isset($_POST['runnow'])) {
                    $arry = array(
                        "access_token" => $token,
                        "import_post_type" => $post_type,
                        "import_author" => $zl_default_author,
                        "import_taxonomy" => $taxonomies,
                    );
                    $importer_article 	= new Intercom_Article_Import_Handler($arry);
                    $response           = $importer_article->import_intercom_article();
                    if ($response['status'] == 'errors') {
                        $errormsg = $response['message'];
                    } else {
                        $errormsg = 'Message:- ' . $response['message'] . '<br>';
                        $errormsg .= 'Episodes:- ' . $response['count'] .' Article Import';
                        //$errormsg .= 'Episodes:- '.$response['episodes'];
                    }
                }
            }
            include_once INTERCOM_FILE_PATH . 'includes/wp-intercom-article-form.php';
        }
    }

    //Post Category Get
    public static function zl_imtercom_category_get()
    {
        $post_type      = 'zl_intercom_article';
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
    public static function zl_intercom_register_post_type_articles()
    {
        $cpt_name = 'Intercom Article';
        $single_item_slug   = 'intercom-article';
        $supports = array(
            'title', // post title
            'editor', // post content
            'author', // post author
            'thumbnail', // featured images
        );
        $labels = array(
            'name' => _x($cpt_name . 's', 'plural'),
            'singular_name' => _x($cpt_name, 'singular'),
            'menu_name' => _x('Intercom Articles Importer', 'admin menu'),
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
            'menu_icon' => plugins_url( 'wp-intercom-articles-import/images/intercom-svg.svg' ),
            'supports' => $supports,
            'labels' => $labels,
            'public' => true,
            'query_var' => true,
            'rewrite' => array('slug' => $single_item_slug),
            'has_archive' => true,
            'hierarchical' => true,
        );
        register_post_type('zl_intercom_article', $args);
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
        register_taxonomy('article-cat', array('zl_intercom_article'), array(
            'hierarchical' => true,
            'labels' => $taxlabels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'article-cat'),
        ));
    }
}