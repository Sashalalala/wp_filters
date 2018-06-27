<?php

namespace swd\eShopFilter;

use swd\eShopFilter\Filters;
use swd\eShopFilter\FiltersHelper;

class Query {

    /**
     * @var Query
     */
    private static $_instance;

    /**
     * @var array
     */
    private static $queryVars;

    /**
     * @return Query
     */
    public static function getInstance(){
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return static::$_instance;
    }

    /**
     * return query vars by filer
     * @return array
     */
    public function getQueryVars(){
        if( empty(self::$queryVars) ){
            $filters = Filters::getInstance()->getFilters();
            foreach ($filters as $filter){
                foreach ($filter['data'] as $dataType => $filterData) {
                    foreach (array_keys($filterData) as $itemSlug){
                        self::$queryVars[$dataType][] = Filters::getItemName($itemSlug);
                    }
                }
            }
        }
        return self::$queryVars;
    }

    /**
     * Filters Query construct
     */
    public function __construct(){

    }

    /**
     * Init Filters Query
     */
    public function init(){
        add_filter('query_vars', array($this,'registerQueryVars'));
        add_action('pre_get_posts',array( $this, 'preGetPosts'), 100);
    }

    /**
     * Action for register query vars
     */
    public function registerQueryVars($vars){
        $filterVars = [];
        foreach ($this->getQueryVars() as $var){
            $filterVars = array_merge($filterVars, $var);
        }
        return array_merge($vars, $filterVars);
    }

    /**
     * Set taxonomy query to wpQuery
     * @param string $taxName
     * @param array $termValues
     */

    public function setTaxonomyQuery($taxName, $termValues = array(), &$wpQuery){
        $visibility = $wpQuery->query_vars['tax_query'][0];
        if(is_array($visibility) && !empty($visibility)) $taxQuery[] = $visibility;
        $taxQuery = $wpQuery->query_vars['tax_query'];
        $taxQuery[] = array(
            'taxonomy'=> $taxName,
            'field' => 'slug',
            'terms'=>$termValues,
            'operator'=>'IN'
        );
        $wpQuery->set( 'tax_query', $taxQuery  );
    }

    /**
     * Action for get filtered products in wc loop
     */
    public function preGetPosts($query){

        if ( is_admin() || ! $query->is_main_query() || !( is_shop() || is_product_category() || is_product_tag() || !FiltersHelper::isFilter() ) ){
            return;
        }

        $queryVars = $this->getQueryVars();
        foreach ($queryVars as $varType => $vars){
            foreach ($vars as $queryVar){
                switch ($varType) {
                    case 'taxonomy':{
                        $var = get_query_var($queryVar);
                        if( !isset( $var ) || !$var ) continue;
                        $termValues = explode(eShopFilters()->getOption('delimiter'), $var);
                        $this->setTaxonomyQuery(eShopFilters()->getTermName($queryVar), $termValues,$query);
                    }
                }
            }
        }
    }

    public function getFilteredPosts(){

    }

}

