<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $page_title . ' ' . lang('no') . ' ' . $inv->id; ?></title>
    <link rel="shortcut icon" href="<?= $assets; ?>img/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="<?= $assets; ?>style/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets; ?>style/style.css" rel="stylesheet">
    <style>@page { margin: 0 !important; padding: 0 !important; } html, body { margin: 0 !important; padding: 0 !important; } body, div, span, * { font-family: DejaVu Sans, sans-serif !important; } #wrap { padding: 20px 40px !important; }</style>
</head>

<body>
    <div id="wrap">
        <img src="<?= $this->sim->get_image(base_url('uploads/' . ($biller->logo ? $biller->logo : $Settings->logo))); ?>"  width="180px" height="180px" alt="<?= $biller->company ? $biller->company : $Settings->site_name ?>" style="margin-top: 20px;" />
          <div class="row">
            <div class="col-xs-6">
                <div>
                    <!--<img src="<?= base_url(); ?>uploads/<?= $biller->logo ? $biller->logo : $Settings->logo; ?>" width="100px" height="100px" />-->
                    <!--<br />-->
                    <!--<strong>+62 851-7405-9595</strong>-->
                    <!--<br />-->
                    <!--<strong>@pickyourballon</strong>-->
                    <!--<?= $biller->address ;?>-->
                    <!--<p><?= $biller->city ;?></p>-->
                </div>
                
            </div>

            <div class="col-xs-6">
              <b>INVOICE</b>
               <p>
                    <strong>Invoice Number</strong>
                    <strong><?= $inv->reference_no; ?></strong>
                </p>
                <p>
                   <strong> <?= lang('date'); ?>:
                    <?= $this->sim->hrsd($inv->date); ?> </strong>
                </p>
                <p>
                    <strong>Due Date:
                    <?= $this->sim->hrsd($inv->due_date); ?></strong>
                </p>

            </div>
        </div>
        <div style="clear: both; height: 5px;"></div>
         <div class="row">
            <div class="col-xs-6">
               <strong> <?= lang('billed_to'); ?>:</strong>
                
            <strong    <?php echo $customer->name; ?></strong>
                </h3>
               <?php
                if ($customer->company != '-') {
                    echo '<p><strong>' . lang('attn') . ': ' . $customer->name . '</strong></p>';
                }

                if ($customer->address != '-') {
                    echo '<strong>' . lang('address') . ': ' . $customer->address . '</strong>';
                }
                ?>
                <br>
                <!--Status  :  <?= $customer->cf1; ?><br>-->
                Jam Kirim :  <?php 
                
                                if ($inv->shipment === null || $inv->shipment === "") {
                                    echo $customer->cf1;
                                } else {
                                    echo $inv->shipment;
                                }
                
                                ?><br>
                
                <?= lang('tel') . ': ' . $customer->phone; ?><br>
                
                

            </div>

            <div class="col-xs-6">
                <strong>Payment Method</strong>
                <br />
                <strong>Bank BCA</strong>
                <br />
                <strong>549-515-5363</strong>
                <br />
                <strong> A/N RENY PUTERI</strong>
            </div>
           
        </div>
        <div style="clear: both; height: 15px;"></div>

        <div class="row">
            <!--<div class="col-xs-5">-->
            <!--    <h3 class="inv"><?= lang('invoice') . ' ' . lang('no') . ' ' . $inv->id; ?></h3>-->
            <!--</div>-->
            <!--<div class="col-xs-6">-->

            <!--    <p>-->
            <!--        <?= lang('reference_no'); ?>:-->
            <!--        <?= $inv->reference_no; ?>-->
            <!--    </p>-->
            <!--    <p>-->
            <!--        <?= lang('date'); ?>:-->
            <!--        <?= $this->sim->hrsd($inv->date); ?>-->
            <!--    </p>-->

            <!--</div>-->
            <p>&nbsp;</p>
            <div style="clear: both; height: 15px;"></div>
            <div class="col-xs-12">
                <table class="table table-bordered table-hover table-striped" style="margin-bottom: 5px;">

                    <thead>

                        <tr>
                            <th class="col-xs-1" style="max-width: 40px !important;"><?= lang('no'); ?></th>
                            <th style="width: 30% !important; max-width:300px !important;"><?= lang('description'); ?></th>
                            <th class="col-xs-1" style="min-width:80px;padding-left:0;padding-right:0;"><?= lang('unit_price'); ?></th>
                            <th class="col-xs-1" style="min-width:100px;padding-left:0;padding-right:0;"><?= lang('quantity'); ?></th>
                            <?php
                            if ($Settings->product_discount) {
                                ?>
                                <th class="col-xs-1" style="min-width:100px;padding-left:0;padding-right:0;"><?= lang('discount'); ?></th>
                                <?php
                            }
                            ?>
                            <?php
                            if ($Settings->default_tax_rate) {
                                ?>
                                <th class="col-xs-1" style="min-width:100px;padding-left:0;padding-right:0;"><?= lang('tax'); ?></th>
                                <?php
                            }
                            ?>
                            <th class="col-xs-2"><?= lang('subtotal'); ?></th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php
                        $r = 1;
                        foreach ($rows as $row):
                            ?>
                        <tr>
                            <td style="max-width: 30px !important;" class="text-center"><?= $r; ?></td>
                            <td style="width: 30% !important; max-width:300px !important;">
                                <?= $row->product_name . ($row->details ? '<div style="white-space:normal;word-wrap:break-word;word-break:break-all;">' . $this->sim->decode_html($row->details) . '</div>' : ''); ?>
                            </td>
                            <td class="text-right"><?= $this->sim->formatMoney($row->real_unit_price); ?></td>
                            <td class="text-center"><?= $row->quantity; ?></td>
                            <?php
                            if ($Settings->product_discount) {
                                ?>
                                <td class="text-right">
                                    <?= $row->discount_amt > 0 ? '<small>(' . $row->discount . ')</small>' : ''; ?>
                                    <?= $this->sim->formatMoney($row->discount_amt); ?>
                                </td>
                                <?php
                            }
                            ?>
                            <?php
                            if ($Settings->default_tax_rate) {
                                ?>
                                <td class="text-right">
                                    <?= $row->tax_amt > 0 ? '<small>(' . $row->tax . ')</small>' : ''; ?>
                                    <?= $this->sim->formatMoney($row->tax_amt); ?>
                                </td>
                                <?php
                            }
                            ?>
                            <td class="text-right"><?= $this->sim->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php
                        $r++;
                        endforeach;
                        ?>
                    </tbody>

                    <tfoot>
                        <?php
                        $cols = 4;
                        ?>
                        <tr class="totals">
                            <th colspan="<?= $cols; ?>"><?= lang('total'); ?></th>
                            <?php
                            if ($Settings->product_discount) {
                                ?>
                                <th class="text-right"><?= $this->sim->formatMoney($inv->product_discount); ?></th>
                                <?php
                            }
                            ?>
                            <?php
                            if ($Settings->default_tax_rate) {
                                ?>
                                <th class="text-right"><?= $this->sim->formatMoney($inv->product_tax); ?></th>
                                <?php
                            }
                            ?>
                            <th class="text-right"><?= $this->sim->formatMoney($inv->total); ?></th>
                        </tr>

                        <?php
                        $cols = $cols - 2;
                        if ($Settings->product_discount) {
                            $cols++;
                        }
                        if ($Settings->default_tax_rate) {
                            $cols++;
                        }

                        if ($inv->order_discount > 0) {
                            ?>
                            <tr>
                                <th class="word_text" colspan="<?= $cols; ?>">
                                    <?= $this->mywords->c2w($inv->order_discount); ?>
                                </th>
                                <th class="text-right" colspan="2">
                                    <?= lang('order_discount'); ?> (<?= $Settings->currency_prefix; ?>)
                                </th>
                                <th class="text-right">
                                    <?= $this->sim->formatMoney($inv->order_discount); ?>
                                </th>
                            </tr>
                            <?php
                        }
                        ?>
                        <?php
                        if ($inv->order_tax > 0) {
                            ?>
                            <tr>
                                <th class="word_text" colspan="<?= $cols; ?>">
                                    <?= $this->mywords->c2w($inv->order_tax); ?>
                                </th>
                                <th class="text-right" colspan="2">
                                    <?= lang('order_tax'); ?> (<?= $Settings->currency_prefix; ?>)
                                </th>
                                <th class="text-right">
                                    <?= $this->sim->formatMoney($inv->order_tax); ?>
                                </th>
                            </tr>
                            <?php
                        }
                        ?>
                        <?php
                        if ($inv->shipping > 0) {
                            ?>
                            <tr>
                                <th class="word_text" colspan="<?= $cols; ?>">
                                    <?= $this->mywords->c2w($inv->shipping); ?>
                                </th>
                                <th class="text-right" colspan="2">
                                    <?= lang('shipping'); ?> (<?= $Settings->currency_prefix; ?>)
                                </th>
                                <th class="text-right">
                                    <?= $this->sim->formatMoney($inv->shipping); ?>
                                </th>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr class="primary">
                            <th class="word_text text-primary" colspan="<?= $cols; ?>">
                                <?= $this->mywords->c2w($inv->grand_total); ?>
                            </th>
                            <th class="text-right text-primary" colspan="2">
                                <?= lang('grand_total'); ?> (<?= $Settings->currency_prefix; ?>)
                            </th>
                            <th class="text-right text-primary">
                                <?= $this->sim->formatMoney($inv->grand_total); ?>
                            </th>
                        </tr>
                        <tr>
                            <th class="word_text text-success" colspan="<?= $cols; ?>">
                                <?= $this->mywords->c2w($inv->paid); ?>
                            </th>
                            <th class="text-right text-success" colspan="2">
                                <?= lang('paid'); ?> (<?= $Settings->currency_prefix; ?>)
                            </th>
                            <th class="text-right text-success">
                                <?= $this->sim->formatMoney($inv->paid); ?>
                            </th>
                        </tr>
                        <tr>
                            <th class="word_text text-warning" colspan="<?= $cols; ?>">
                                <?= $this->mywords->c2w($inv->grand_total - $inv->paid); ?>
                            </th>
                            <th class="text-right text-warning" colspan="2">
                                <?= (isset($client) && !empty($client)) ? lang('due') : lang('balance'); ?>
                                (<?= $Settings->currency_prefix; ?>)
                            </th>
                            <th class="text-right text-warning">
                                <?= $this->sim->formatMoney($inv->grand_total - $inv->paid); ?>
                            </th>
                        </tr>

                    </tfoot>

                </table>
            </div>

            <div style="clear: both;"></div>
           
            <div class="col-xs-12" style="margin-top: 15px;">
            <?php
            if (isset($client) && !empty($client)) {
                echo '<a class="btn btn-primary btn-block" href="' . site_url('clients/pdf?id=' . $inv->reference_no) . '">' . lang('download_pdf') . '</a>';
            }
            ?>
            </div>
                <?php
                if ($Settings->print_payment) {
                    if (!empty($payment)) {
                        ?>
                    <div class="clearfix"></div>
                    <div style="page-break-after: always;"></div>
                    <div class="col-xs-12" style="margin-top: 15px;">
                        <h4><?= lang('payment_details'); ?> (<?= $page_title . ' ' . lang('no') . ' ' . $inv->id; ?>)</h4>
                        <table class="table table-bordered table-condensed table-hover table-striped" style="margin-bottom: 5px;">

                            <thead>
                                <tr>
                                    <th><?= lang('date'); ?></th>
                                    <th><?= lang('amount'); ?></th>
                                    <th><?= lang('note'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($payment as $p) {
                                    ?>
                                    <tr>
                                        <td><?= $this->sim->hrsd($p->date); ?></td>
                                        <td><?= $this->sim->formatMoney($p->amount); ?></td>
                                        <td><?= $p->note; ?></td>
                                    </tr>
                                    <?php
                                } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                    }
                }
            ?>
        </div>
    </div>
</div>
</body>
</html>
