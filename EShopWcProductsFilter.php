<?php

 namespace swd\eShopFilter;

 use swd\eShopFilter\Query;
 use swd\eShopFilter\FiltersHelper;
 use SwdPaginaton;

class Filters {


    /**
     * @var array
     */
    private static $options;

    /**
     * @var array
     */
    private static $filters;

    /**
     * @var Filters
     */
    private static $_instance;

    /**
     * Filters construct
     */
    public function __construct(){

    }

    /**
     * @return Filters
     * @access public
     */
    public static function getInstance(){
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return static::$_instance;
    }

    /**
     * set filters options
     */
    private function setOptions(){
        if( self::$options == null && file_exists(dirname(__FILE__)) . '/options.php'){
            require_once dirname(__FILE__) . '/options.php';
            self::$options = array(
                'isFilterParam' => SwdEShopWcFilterIsFilterParam,
                'delimiter' => SwdEShopWcFilterDelimiter,
                'paramsPrefix'=> SwdEShopWcFilterPrefix
            );
        }
    }

    /**
     * get filters options
     * @return array
     */
    public function getOptions(){
        if(self::$options == null) {
            $this->setOptions();
        }
        return self::$options;
    }

    /**
     * get filters option by name
     * @return string|array
     */
    public function getOption($name = ''){
        if(self::$options == null) {
            $this->setOptions();
        }
        return $this->getOptions()[$name];
    }

    /**
     * set all filters
     */
    private function setFilters(){
        if( self::$filters == null ) {
            self::$filters = array(
                array(
                    'title' => 'Фильтр',
                    'data' => array('taxonomy' => array('pa_gender' => '2-columns', 'pa_leather_type' => 'default', 'pa_color-variants' => 'color', 'label' => 'label'))
                )
            );
        }
    }

    /**
     * get filters register data
     * @return array
     */
    public function getFilters(){
        if(self::$filters == null) {
            $this->setFilters();
        }
        return self::$filters;
    }

    /**
     * init filters actions and function
     */
    public function init(){
        $this->setOptions();
        $this->setFilters();

        //init filters query
        Query::getInstance()->init();

        //render filters on hook
        add_action('eShopProductsFilters', array($this, 'renderAllFilters'));
    }

    /*
     * filters render
     */
    public function renderAllFilters(){
        $this->filtersRender();
    }

    /**
     * Get item name considering $options['paramsPrefix']
     */
    public static function getItemName($param){
        return self::$options['paramsPrefix'].$param;
    }

    /**
     * Get term name excluding $options['paramsPrefix']
     */
    public function getTermName($term){
        if( strpos($term, $this->getOption('paramsPrefix') ) === 0 ){
            return substr($term, strlen( $this->getOption('paramsPrefix')) );
        }
        return $term;
    }

    public function hasGenderParam(){
        global $wp_query;
        $queryVars = $wp_query->query_vars;
        $genderItemName = eShopFilters()->getItemName('pa_gender');
        if(isset( $queryVars[$genderItemName]) && $queryVars[$genderItemName] ){
            return $queryVars[$genderItemName];
        }
        return false;
    }

    /**
     * build array with query string params include passed filter param
     * @param array $queryParam contain 2 items: 1-st - param name; 2-nd - param key
     * @param bool $unset set or unset $queryParam
     * @return array
     */
    private function buildFilterQuery( $queryParam, $unset = false, $queryString = null, $empty = false){

        $queryString = $queryString !== null ? $queryString : $_SERVER['QUERY_STRING'];

        $currentParams = FiltersHelper::getFilterQueryStringParams($queryString);
        $otherParams = FiltersHelper::getOtherQueryStringParams($queryString);

        $gender = $this->hasGenderParam();

        if($gender){
            $currentParams[$this->getItemName('pa_gender')] = $gender;
        }


        if($empty) return $otherParams;

        if(!count($currentParams)) {
            if (!$unset) {
                return array_merge($otherParams, array($this->getOption('isFilterParam') => 1, self::getItemName($queryParam[0]) => $queryParam[1]));
            } else {
                return $otherParams;
            }
        }

        if( array_key_exists( self::getItemName($queryParam[0]), $currentParams )) {
            $val = explode( $this->getOption('delimiter'), $currentParams[$this->getItemName($queryParam[0])]);
            if( ($searchKey = array_search($queryParam[1], $val)) !== false){
                if($unset){
                    unset($val[$searchKey]);
                    $val = array_values($val);
                }
            } else {
                if(!$unset){
                    $val[] = $queryParam[1];
                }
            }
            if(count($val)>0){
                $currentParams[$this->getItemName($queryParam[0])] =implode($this->getOption('delimiter'),$val);
            } else {
                unset($currentParams[$this->getItemName($queryParam[0])]);
            }
        } else {
            if(!$unset){
                $currentParams[ $this->getItemName($queryParam[0]) ] = $queryParam[1];
            }
        }
        if( !count( $currentParams ) ) return $otherParams ; else
        return array_merge($otherParams, array( $this->getOption('isFilterParam') => 1 ), $currentParams );
    }

    /**
     * Build url with passed data of filterItem
     * @return string $itemUrl
     */
    private function buildItemUrl($value, $parentName, $unset = false, $curUrl = null, $empty = false){

        if(!$curUrl) $curUrl = $_SERVER['REQUEST_URI'];

        $curUrl = parse_url( $curUrl );
        $query = isset($curUrl['query']) ? $curUrl['query'] : '';
        $queryParams = $this->buildFilterQuery(array($parentName, $value ), $unset, $query, $empty);

        $pathArray = explode('/',trim($curUrl['path'], '/'));

        $buildQuery = count($queryParams)>1 ? '?' . http_build_query($queryParams) : '';

        $itemUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . '/'. implode('/',$pathArray) . '?' . $buildQuery;
        if(class_exists('SwdPaginaton'))
        $itemUrl = SwdPaginaton::getPageNumLink(1, $itemUrl );
        return $itemUrl;
    }

    /**
     * sett url for filters items
     */
    private function setFilterItemsUri( &$filtersData, $curUrl = null ){
        foreach ($filtersData as $key=>&$filter){
            foreach ($filter['filterItems'] as &$items){
                foreach ( $items['items'] as $taxKey=>&$taxValue ){
                    $unset = FiltersHelper::isParamSetted( array( $items['name'], $taxValue['slug'] ) , $curUrl);
                    $taxValue['url'] = $this->buildItemUrl($taxValue['slug'], $items['name'], $unset, $curUrl);
                    if($unset) $taxValue['unset'] =  true;
                }
            }
        }
    }

    /**
     * function return array data which need for tender filters
     * @return array
     */
    public function getFiltersData( $url = '' ){
        $filterItems = array();
        $genderParam = explode($this->getOption('delimiter'),$this->hasGenderParam());
        foreach ($this->getFilters() as $pos => $filter){
            $filterItems[$pos]['title'] = $filter['title'];
            $filterItems[$pos]['filterItems'] = array();

            $data = $filter['data'];
            foreach ($data as $key => $dataItems){
                switch ($key){
                    case 'taxonomy':{
                        $counter = 0;
                        foreach ($dataItems as $taxKey => $templateType ){
                            $filterItems[$pos]['filterItems'][$counter] = array(
                                'name' => $taxKey,
                                'label'=> get_taxonomy($taxKey)->labels->singular_name,
                                'templateType' => $templateType,
                                'itemsType'=>'taxonomy',
                                'items' => array()
                            );

                            $taxonomyArgs = array(
                                'hierarchical' => false,
                                'suppress_filter'=>false,
                                'taxonomy' => $taxKey
                            );
                            $terms = get_terms($taxonomyArgs);
                            foreach ($terms as $term) {
                                $filterItems[$pos]['filterItems'][$counter]['items'][] = array(
                                    'id' => $term->term_id,
                                    'name' => $term->name,
                                    'slug' => $term->slug,
                                );
                            }
                            $counter++;
                        }
                    }
                }
            }
        }
        $this->setFilterItemsUri($filterItems, $url);
        return $filterItems;
    }

    public function filtersRender( $url = '', $preQuery = '' ){
        $filters = $this->getFiltersData( $url );
        foreach ($filters as $filter){
            $title = $filter['title'];
            $clearUrl = SwdPaginaton::getPageNumLink(1, $this->buildItemUrl('', '', false, null, true));
            include dirname(__FILE__) . '/templates/overlay.php';
        }
    }

}

 function eShopFilters(){
     return Filters::getInstance();
 }

Filters::getInstance()->init();
