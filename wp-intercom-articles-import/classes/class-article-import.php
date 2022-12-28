<?php
class Intercom_Article_Import_Handler
{
    private $access_token;
    private $post_author;
    private $post_type;
    private $taxonomy;
    private $intercom_added = 0;
    private $intercom_imported = array();

    public function __construct($zl_external_article)
    {
        $this->access_token = $zl_external_article['access_token'];
        $this->post_type    = $zl_external_article['import_post_type'];
        $this->post_author  = $zl_external_article['import_author'];
        $this->taxonomy     = $zl_external_article['import_taxonomy'];
    }

    public function import_intercom_article()
    {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.intercom.io/articles/?per_page=-1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$this->access_token.''
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = (array) json_decode($response);
        if(!isset($response['errors'])){
            foreach($response['data'] as $data){
                $data = (array) $data;
                $curl = curl_init();

                curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.intercom.io/help_center/collections/'.$data['parent_id'].'',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.$this->access_token.''
                ),
                ));

                $collection = curl_exec($curl);

                curl_close($curl);
                $collection = (array) json_decode($collection);

                $article = array(
                    'id' => $data['id'],
                    'title' => $data['title'],
                    'content' => $data['body'],
                    'status'  => $data['state'],
                    'collection' => $collection['name']
                );
                $this->create_article($article);
            }
            return array(
                'status'   => 'success',
                'message'  => 'Intercom settings updated successfully!!',
                'count'    => $this->intercom_added,
                'episodes' => $this->intercom_imported,
            );
        }else{
            return array(
                'status'   => 'errors',
                'message'  => $response['errors'][0]->message,
            );
        }
    }

    protected function create_article($article)
    {
        $post_data  = $this->get_post_data($article);
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
        
        $this->intercom_added++;
        $this->intercom_imported[] = $post_data['post_title'];
    }

    public function get_post_data($article)
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
            'zl_intercom_id' => $article['id'],
        );

        $args = array(
            'post_type' => $this->post_type,
            'meta_query' => array(
                array(
                    'key' => 'zl_intercom_id',
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