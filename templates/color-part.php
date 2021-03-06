<?php
/*
 * Template for displaying filter color items
 *
 * variables :
 *      $data - all filter items
 *
 * $data = array(
 *           'name' => str,
 *           'label'=> str,
 *           'templateType' =>atr,
 *           'items'=> array
 *          )
 */

?>

<div class="filter" data-items_type="<?php echo $data['itemsType']?>">
    <h5>
        <?php echo __swd($data['label']) ?>:
    </h5>
    <div class="filter_list">
        <ul class="filter_colors">
        <?php
        foreach ($data['items'] as $item){
            $item['parentName'] = $data['name'];
            swd\eShopFilter\FiltersHelper::getTemplatePart($item, $data['templateType'] . '-item' );
        }
        ?>
        </ul>
    </div>
    <div class="bttn_close"></div>
</div>

