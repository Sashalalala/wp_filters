<?php

/*
 * Template for displaying filter data item color
 *
 *  $data = array(
 *              'id'=>int
 *              'name'=>str,
 *              'slug'=>str,
 *              'url'=>str
 *          )
 */
$checked = isset($data['unset']) && $data['unset'] ? 'checked' : '';
$attrName = $data['parentName'];
$colorCode = get_term_meta($data['id'], 'color-code', true)?:'#fff';

?>


<li style="list-style: none" >
    <input data-url="<?php echo $data['url'] ?>" id="<?php echo $attrName.'_'.$data['slug'] ?>" class="productFilterAjaxItem" data-url="<?php echo  $data['url'] ?>" style="display: none"  type="checkbox" <?php echo $checked ?> name="<?php echo 'filters_data['.$attrName.'][]' ?>" value="<?php echo $data['slug'] ?>" >
    <label for="<?php echo $attrName.'_'.$data['slug'] ?>" class="item <?php echo $checked; ?>" style="background-color: <?php echo $colorCode ?>"></label>
</li>