<?php
class ZIAI_Handler
{
    private $access_token;
    private $post_author;
    private $post_type;
    private $taxonomy;
    private $ziai_added = 0;
    private $ziai_imported = array();

    public function __construct($zl_external_article)
    {
        $this->access_token = $zl_external_article['access_token'];
        $this->post_type    = $zl_external_article['import_post_type'];
        $this->post_author  = $zl_external_article['import_author'];
        $this->taxonomy     = $zl_external_article['import_taxonomy'];
    }

    public function get_articles($page=null, $per_page=null){
        $endpoint = 'https://api.intercom.io/articles/';
        $params = http_build_query(array(
            'page' => $page,
            'per_page' => $per_page
        ));
        if($params){
            $endpoint .= '?'.$params;
        }
        $response = wp_remote_get( $endpoint,
            array(
                'method' => 'GET',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->access_token
                )
            )
        );

        $response = json_decode($response['body'], true);
        if(!isset($response['errors'])){
            $this->ziai_import_articles($response);
            return $response;
        }
    }

    public function ziai_import_articles($response)
    {
        if(!isset($response['errors'])){
            foreach($response['data'] as $data){
                $collection = wp_remote_get( 'https://api.intercom.io/help_center/collections/'.$data['parent_id'].'',
                    array(
                        'method' => 'GET',
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $this->access_token
                        )
                    )
                );

                $collection = json_decode($collection['body'], true);
                $article = array(
                    'id' => $data['id'],
                    'title' => $data['title'],
                    'content' => $data['body'],
                    'status'  => $data['state'],
                    'collection' => $collection['name']
                );
                $this->ziai_create_article($article);
            }
            return array(
                'status'   => 'success',
                'message'  => 'Articles settings updated successfully!!',
                'count'    => $this->ziai_added,
                'episodes' => $this->ziai_imported,
            );
        }else{
            return array(
                'status'   => 'errors',
                'message'  => $response['errors'][0]->message,
            );
        }
    }

    public function ziai_import_article()
    {
        $response = wp_remote_get( 'https://api.intercom.io/articles/',
            array(
                'method' => 'GET',
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->access_token
                )
            )
        );

        $response = json_decode($response['body'], true);
        if(!isset($response['errors'])){
            foreach($response['data'] as $data){
                $collection = wp_remote_get( 'https://api.intercom.io/help_center/collections/'.$data['parent_id'].'',
                    array(
                        'method' => 'GET',
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $this->access_token
                        )
                    )
                );
                
                $collection = json_decode($collection['body'], true);
                $article = array(
                    'id' => $data['id'],
                    'title' => $data['title'],
                    'content' => $data['body'],
                    'status'  => $data['state'],
                    'collection' => $collection['name']
                );
                $this->ziai_create_article($article);
            }
            return array(
                'status'   => 'success',
                'message'  => 'Articles settings updated successfully!!',
                'count'    => $this->ziai_added,
                'episodes' => $this->ziai_imported,
            );
        }else{
            return array(
                'status'   => 'errors',
                'message'  => $response['errors'][0]->message,
            );
        }
    }

    protected function ziai_create_article($article)
    {
        $post_data  = $this->ziai_get_post_data($article);
        $post_id    = wp_insert_post($post_data);
        /**
         * If an error occurring adding a post, continue the loop
         */
        if (is_wp_error($post_id)) {
            return;
        }

        if (!empty($post_data['post_category'])) {
            wp_set_post_terms($post_id, $post_data['post_category'][0], $this->taxonomy);
        }
        
        $this->ziai_added++;
        $this->ziai_imported[] = $post_data['post_title'];
    }

    public function ziai_get_post_data($article)
    {
        $term_a      = term_exists($article['collection'], $this->taxonomy);
        $term_a_id   = $term_a['term_id'];
        if(empty($term_a_id)){
            wp_insert_term(
                $article['collection'],
                $this->taxonomy,
                array(
                    // 'description'=> 'Some description.',
                    'slug' => str_replace(" ", "-", $article['collection']),
                )
            );
        }

        $status                     = ($article['status'] == 'published') ? $article['status'] = 'publish' : $article['status'];
        $term_a                     = term_exists($article['collection'], $this->taxonomy);
        $term_id                    = $term_a['term_id'];
        $post_data                  = array();
        $post_data['post_content']  = $article['content'];
        $post_data['post_title']    = $article['title'];
        $post_data['post_status']   = $status;
        $post_data['post_author']   = $this->post_author;
        $post_data['post_type']     = $this->post_type;
        $post_data['post_category'] = array($term_id);
        $post_data['meta_input']    = array(
            'zl_ziai_id' => $article['id'],
        );

        $args = array(
            'post_type' => $this->post_type,
            'post_status' => array('publish', 'draft'),
            'meta_query' => array(
                array(
                    'key' => 'zl_ziai_id',
                    'value' => $article['id'],
                    'compare' => '=',
                )
            )
        );

        $already_added = new WP_Query($args);
        if ($already_added->have_posts()) {
            $already_added->the_post();
            $post_id = get_the_ID();
            $post_data['ID'] = $post_id;
        }

        return $post_data;
    }
}