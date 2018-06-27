<?php

/*
 * Template for displaying default filter data item
 *
 *  $data = array(
 *              'id'=>int
 *              'name'=>str,
 *              'slug'=>str,
 *              'url'=>str
 *          )
 */

$label = new SwdLabel($data['id']);

$checked = isset($data['unset']) && $data['unset'] ? 'checked' : '';
$attrName = $data['parentName'];

?>

<div class="input_group small">
    <input style="display: none" id="custom-label-<?php echo $label->id ?>" data-url="<?php echo $data['url'] ?>" class="productFilterAjaxItem" data-checked="<?php echo (isset($data['unset']) && $data['unset'] ? 'true' : 'false') ?>" type="checkbox" <?php echo $checked ?> name="<?php echo 'filters_data['.$attrName.'][]' ?>" value="<?php echo $data['slug'] ?>" >
    <label style="cursor: pointer" for="custom-label-<?php echo $label->id ?>">
        <?php echo $label->html ?>
    </label>
</div>