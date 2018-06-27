<?php
/*
 * Default template for displaying filter all items
 *
 * variables :
 *      $data - all filter items
 *
 * $data = array(
 *           'name' => str,
 *           'label'=> str,
 *           'templateType' =>str,
 *           'itemsType' =>str
 *           'items'=> array
 *          )
 */

?>

<div class="filter">
    <h5>
        <?php echo __swd($data['label']) ?>:
    </h5>
    <div class="filter_list" data-items_type="<?php echo $data['itemsType']?>">
        <?php
        foreach ($data['items'] as $item){
            $item['parentName'] = $data['name'];
            swd\eShopFilter\FiltersHelper::getTemplatePart($item, $data['templateType'] . '-item' );
        }
        ?>
    </div>
    <div class="bttn_close"></div>
</div>
<div class="mb-30"></div>