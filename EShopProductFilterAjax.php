<?php

class EShopProductFilterAjax extends EShopAjax {

    protected $actions = array(
        'productFilter'
    );

    public function __construct(array $actions = array())
    {
        parent::__construct($actions);
        $this->registerActions();
    }

    private function getPostsData($query){
        return false;
    }

    public static function getFilteredPostsWpQuery($requestData, $page = 1){

        parse_str($requestData['params'], $filterParams);
        parse_str($filterParams['pre_query'], $preQuery);
        $filterUrl = $requestData['url'];
        $postPerPage = isset($filterParams['posts_per_page']) ? $filterParams['posts_per_page'] : 16;
        $filterType = $filterParams['filter_data_type'];
        $queryParams = $filterParams['filters_data'];

        $wpQueryArgs = array(
            'post_type'=>array('product', 'product_variations'),
            'post_status' => array('publish'),
            'posts_per_page' => $postPerPage,
            'paged' => $page,
            'orderby' => 'menu_order',
            'order'=>'ASC'
        );

        switch ($filterType) {
            case 'taxonomy' : {
                $wpQueryArgs['tax_query'] = $preQuery;
                foreach ($queryParams as $key =>$val){
                    $wpQueryArgs['tax_query'][] = array(
                        'taxonomy' => trim($key),
                        'field' => 'slug',
                        'operator' => 'IN',
                        'terms' => (array)$val
                    );
                }
                $wpQueryArgs['tax_query']['relation'] = 'AND';
            }
        }

        $wpQuery = new WP_Query($wpQueryArgs);

        return $wpQuery;

    }

    public static function setResponseData(&$postsData, $addFilter = true){

        ob_start();
        /*woocommerce archive product loop*/
        woocommerce_product_loop_start();

        if ( $postsData['wpQuery']->have_posts() ) {

            while ($postsData['wpQuery']->have_posts()) {
                $postsData['wpQuery']->the_post();
                do_action('woocommerce_shop_loop');
                wc_get_template_part('content', 'product');
            }

        } else {
            do_action( 'woocommerce_no_products_found' );
        }
        woocommerce_product_loop_end();

        $postsData['fragments']['div.products'] = ob_get_clean();

        $paged = $postsData['wpQuery']->get('paged', false)?:1;
        $pagArgs = array(
            'total'=> $postsData['wpQuery']->max_num_pages,
            'current' => $paged,
            'url' => $postsData['url'],
            'postType' => 'product'
        );

        ob_start();
        SwdPaginaton::showPagination($pagArgs);
        $postsData['fragments']['.section-pagination'] = ob_get_clean();

        if(!$addFilter) {
            unset($postsData['wpQuery']);
            return;
        }

        wp_reset_postdata();

        global  $taxonomyFilter;

        ob_start();

        $taxonomyFilter->filtersRender($postsData['url'], $postsData['pre_query']);

        $postsData['fragments']['.bl_filters:first-child'] = ob_get_clean();

        unset($postsData['wpQuery']);
        unset($postsData['pre_query']);
    }

    public function productFilter(){

        $data = $this->getRequestData('POST');

        $response = array('url' => $data['url']);

        parse_str($data['params'],$tempParams);

        $response['pre_query'] = $tempParams['pre_query'];

        $response['wpQuery'] = $this->getFilteredPostsWpQuery($data);

        $this->setResponseData( $response );


        echo  $this->responseFormat($response,200, __FUNCTION__);
        die();
    }

}
$eShopFilterAjax = new EShopProductFilterAjax();