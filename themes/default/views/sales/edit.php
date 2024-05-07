<script type="text/javascript">

    var counter = <?= isset($inv_products) && count($inv_products) > $Settings->no_of_rows ? count($inv_products)+1 : $Settings->no_of_rows+1; ?>, no_of_rows = <?= $Settings->no_of_rows+1; ?>,
        default_tax_rate = <?= $Settings->default_tax_rate; ?>, tax_rates = <?=json_encode($tax_rates)?>;
    var total = 0, order_tax = 0, order_discount = 0, grand_total = 0;

</script>


<div class="page-head">
    <h2 class="pull-left"><?= $page_title; ?> <span class="page-meta"><?= lang("enter_info"); ?></span> </h2>
</div>
<div class="clearfix"></div>
<div class="matter">
    <div class="container">
        <?php $attrib = array('class' => 'form-horizontal'); echo form_open("sales/edit/".$inv->id);?>

        <?php include('form.php'); ?>

        <div class="form-group">
            <?= form_submit('submit', lang("update_sale"), 'class="btn btn-primary btn-lg"');?>
        </div>
        <?= form_close();?>

        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
</div>
