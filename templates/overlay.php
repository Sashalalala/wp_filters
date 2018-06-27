<?php
/*
 * Template for displaying filters overlay (swd)
 *
 * variables: $title, $filters
 *
 * $filters =array( array(
 *                      'name' => str,
 *                      'label'=> str,
 *                      'templateType' =>atr,
 *                      'items'=> array
 *                      )...
 *                )
 * $dataType - filters data type
 */

$productsPerPage = wc_get_default_products_per_row()* wc_get_default_product_rows_per_page();
?>
<div class="bl_filters">
    <div class="title">
        <h4>
            <?php  e_swd($title)  ?>
        </h4>
    </div>
    <form action="/" class="filtersForm">
        <input type="hidden" name="posts_per_page" value="<?php echo $productsPerPage ?>" >

    <div class="bl_in">
        <?php foreach ($filter['filterItems'] as $key=>$item) {
            swd\eShopFilter\FiltersHelper::getTemplatePart($item, $item['templateType'] );
        } ?>

        <hr>

        <button data-url="<?php echo $clearUrl?>" style="width: 100%;" class="ajax_clear_product_filters bttn_phone"><?php e_swd('Очистить фильтр') ?></button>

    </div>

    </form>
</div>
