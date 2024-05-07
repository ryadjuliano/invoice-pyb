<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel"><?= lang('add_payment'); ?></h4>
        </div>
        <?=form_open('sales/add_sale_payment/' . $sale->id); ?>
        <div class="modal-body">
            <div class="">
                <table class="table table-striped table-condensed">
                    <tr>
                        <td style="width:50%;"><?= lang('invoice_no'); ?></td>
                        <td style="width:50%;font-weight:bold;"><?= $sale->id; ?></td>
                    </tr>
                    <tr>
                        <td style="width:50%;"><?= lang('reference_no'); ?></td>
                        <td style="width:50%;font-weight:bold;"><?= $sale->reference_no; ?></td>
                    </tr>
                    <tr>
                        <td style="width:50%;"><?= lang('total'); ?></td>
                        <td style="width:50%;font-weight:bold;text-align:right;"><?= $this->sim->formatNumber($sale->total); ?></td>
                    </tr>
                    <tr>
                        <td style="width:50%;"><?= lang('paid'); ?></td>
                        <td style="width:50%;font-weight:bold;text-align:right;"><?= $this->sim->formatNumber($sale->paid); ?></td>
                    </tr>
                    <tr>
                        <td style="width:50%;"><?= lang('balance'); ?></td>
                        <td style="width:50%;font-weight:bold;text-align:right;"><?= $this->sim->formatNumber($sale->total - $sale->paid); ?></td>
                    </tr>
                </table>
            </div>
            <div class="form-group">
                <label for="date"><?= lang('date'); ?></label>
                <div class="controls"> <?= form_input('date', ($_POST['date'] ?? $this->sim->hrsd(date('Y-m-d H:i'))), 'class="form-control date" id="date" required');?> </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="amount"><?= lang('amount_paid'); ?></label>
                <div class="controls"> <?= form_input('amount', $sale->total - $sale->paid, 'class="form-control" id="amount" required');?> </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="note"><?= lang('note'); ?></label>
                <div class="controls"> <?= form_textarea('note', '', 'class="form-control" id="note" style="height:100px;"');?> </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true"><?= lang('close'); ?></button>
            <button class="btn btn-primary" id="add_payment"><?= lang('add_payment'); ?></button>
        </div>
        <?=form_close();?>
    </div>
</div>
