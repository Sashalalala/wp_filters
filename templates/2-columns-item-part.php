<?php

/*
 * Template for displaying filter data item in columns
 *
 *  $data = array(
 *              'id'=>int
 *              'name'=>str,
 *              'slug'=>str,
 *              'url'=>str,
 *              'parentName'=>str
 *          )
 */

$checked = isset($data['unset']) && $data['unset'] ? 'checked' : '';
$attrName = $data['parentName']

?>

<div class="input_group small">
    <input id="<?php echo $data['slug'].$data['id'];?>" data-url="<?php echo $data['url'] ?>" class="custom_checkbox productFilterAjaxItem" data-checked="<?php echo (isset($data['unset']) && $data['unset'] ? 'true' : 'false') ?>" type="checkbox" <?php echo $checked ?> name="<?php echo 'filters_data['.$attrName.'][]' ?>" value="<?php echo $data['slug'] ?>" >
    <label style="cursor: pointer" for="<?php echo $data['slug'].$data['id'];?>">
        <?php echo __swd($data['name']); ?>
    </label>
</div>
