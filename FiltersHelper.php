<?php

namespace swd\eShopFilter;

use swd\eShopFilter\Filters;

class FiltersHelper {

    /**
     * @static
     * @return mixed
     * @param array $items
     * @param string $templateType
     * @param bool $return
     */
    public static function getTemplatePart($data = null, $templateType = 'default', $return=false){

        $templatePath = dirname(__FILE__). '/templates';
        $located = $templatePath .'/'. $templateType . '-part.php';
        if ( !file_exists($located) ) {
            if($return) return 'Template not exist';
            echo 'Template not exist'; return 0;
        }
        if($return) ob_start();
        include $located;
        if($return) return ob_get_clean();
    }

    /**
     * Get filter params from query string passed by param or with $_SERVER variable
     * @return array
     * @param string $queryStr
     */
    public static function getFilterQueryStringParams($queryStr = null, $isFilterParam = null){

        if(!$isFilterParam) $isFilterParam = eShopFilters()->getOption('isFilterParam');
        $queryStr = $queryStr !== null ? $queryStr : $_SERVER['QUERY_STRING'];
        parse_str($queryStr, $query);
        $res = array();
        if(isset($query[ $isFilterParam ])){
            $isFilter = false;
            foreach ( $query as $key=>$item ){
                if($isFilter) $res[$key] = $item;
                if($key === $isFilterParam && $item) $isFilter = true;
            }
        }
        return $res;
    }

    /**
     * Get params from query string passed by param or with $_SERVER variable excluded filters params
     * @return array
     * @param string $queryStr
     */
    public static function getOtherQueryStringParams($queryStr = '', $isFilterParam = null){

        if(!$isFilterParam) $isFilterParam = eShopFilters()->getOption('isFilterParam');

        $queryStr = $queryStr !== null ? $queryStr : $_SERVER['QUERY_STRING'];
        if(!$queryStr) return array();
        parse_str($queryStr, $query);
        $res = array();
        foreach ($query as $key =>$item){
            if($key===$isFilterParam && $item) break;
            $res[$key] = $item;
        }
        return $res;
    }

    public static function isParamSetted($param, $url = ''){
        $queryString = $url ? parse_url( $url )['query'] : $_SERVER['QUERY_STRING'];
        $params = FiltersHelper::getFilterQueryStringParams($queryString);

        if( ( $genderParam = eShopFilters()->hasGenderParam() ) && !isset($params[eShopFilters()->getItemName( 'pa_gender' )]) ) {
            $params[eShopFilters()->getItemName( 'pa_gender' )] =  $genderParam;
        }

        $urlParam = Filters::getInstance()->getItemName($param[0]);
        if( isset( $params[$urlParam] ) ){
            $values = explode(eShopFilters()->getOption('delimiter'), $params[$urlParam]);
            if(in_array( $param[1] ,$values) !== false) return true;
        }

        return false;
    }

    /**
     * Return Is current page is a filter page
     * @return bool
     */
    public static function isFilter($url = null){
        if($url){
            $parseUrl = parse_url($url);
            if(!isset($parseUrl['query '])) return false;
            $parseStr = parse_str($parseUrl['query']);
            if(isset($parseStr[eShopFilters()->getOption('isFilterParam')]) && $parseStr[eShopFilters()->getOption('isFilterParam')]) return true;
            return false;
        }
        if(isset($_GET[eShopFilters()->getOption('isFilterParam')]) && $_GET[eShopFilters()->getOption('isFilterParam')] ) return true;
        return false;
    }
}