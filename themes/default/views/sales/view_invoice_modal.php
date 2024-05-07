<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <div class="modal-body">
    <img src="<?= $assets; ?>img/<?= $inv->status; ?>.png" alt="<?= $inv->status; ?>" style="float: right; position: absolute; top:0; right: 0;"/>
    <div id="wrap">
        
        
          <div class="row">
            <div class="col-xs-6">
                <div>
                    <img src="<?= base_url(); ?>uploads/<?= $biller->logo ? $biller->logo : $Settings->logo; ?>" width="180px" height="180px" />
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
        
         <div style="clear: both; height: 15px;"></div>

        
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
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" style="margin-bottom: 5px;">

                        <thead>

                            <tr>
                                <th style="max-width: 30px;"><?= lang('no'); ?></th>
                                <th><?= lang('description'); ?></th>
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
                                <td class="text-center"><?= $r; ?></td>
                                <td><?= $row->details ? '<strong>' . $row->product_name . '</strong><br>' . $row->details : $row->product_name; ?></td>
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
              
            </div>
            <div class="col-xs-12" style="margin-top: 15px;">
            <?php
            if ((isset($client) && !empty($client)) || (isset($spay) && !empty($spay))) {
                ?>
                <div class="no-print">
                    <?php $grand_total = ($inv->grand_total - $paid);
                if ($inv->status != 'paid') { ?>
                    <div class="well well-sm">
                        <div id="payment_buttons" class="text-center margin010" style="display:flex;align-items: center;justify-content:space-between;">
                        <?php
                        if ($stripe->active == 1 && $grand_total != '0.00') {
                            $stripe_fee = 0;
                            if (trim(strtolower($customer->country)) == $biller->country) {
                                $stripe_fee = $stripe->fixed_charges + ($grand_total * $stripe->extra_charges_my / 100);
                            } else {
                                $stripe_fee = $stripe->fixed_charges + ($grand_total * $stripe->extra_charges_other / 100);
                            } ?>
                            <div style="max-width:250px;margin-right:20px;display:inline-block;float:left;">
                                <a class="btn btn-primary btn-block no-print" style="border-radius:5px!important;padding:1rem 2rem;font-size:2rem!important;font-weight:bold;" href="<?= site_url('payments/stripe/' . $inv->id) . '/' . base64_encode($inv->id); ?>"><?=lang('pay_with_cc'); ?></a>
                            </div>
                            <?php
                        }
                        ?>
                            <?php
                            if (!isset($spay) && $paypal->active == 1 && $grand_total != '0.00') {
                                $paypal_fee = 0;
                                if (trim(strtolower($customer->country)) == $biller->country) {
                                    $paypal_fee = $paypal->fixed_charges + ($grand_total * $paypal->extra_charges_my / 100);
                                } else {
                                    $paypal_fee = $paypal->fixed_charges + ($grand_total * $paypal->extra_charges_other / 100);
                                } ?>

                                <div style="width:150px;margin-right:10px;display:inline-flex;align-items:center;">
                                    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                        <input type="hidden" name="cmd" value="_xclick">
                                        <input type="hidden" name="business" value="<?=  $paypal->account_email; ?>">
                                        <input type="hidden" name="item_name" value="<?=  $inv->reference_no; ?>">
                                        <input type="hidden" name="item_number" value="<?=  $inv->id; ?>">
                                        <input type="hidden" name="image_url" value="<?=  base_url() . 'uploads/logos/' . ($biller->logo ? $biller->logo : $Settings->logo); ?>">
                                        <input type="hidden" name="amount" value="<?=  $grand_total + $paypal_fee; ?>">
                                        <input type="hidden" name="no_shipping" value="1">
                                        <input type="hidden" name="no_note" value="1">
                                        <input type="hidden" name="currency_code" value="<?= $Settings->currency_prefix; ?>">
                                        <input type="hidden" name="bn" value="FC-BuyNow">
                                        <input type="image"  id="no-pdf" src="<?= base_url('uploads/btn-paypal.png'); ?>" name="submit" alt="Pay by PayPal" style="border:0 !important;">
                                        <input type="hidden" name="rm" value="2">
                                        <input type="hidden" name="return" value="<?=  site_url('clients/view_invoice?id=' . $inv->id); ?>">
                                        <input type="hidden" name="cancel_return" value="<?=  site_url('clients/view_invoice?id=' . $inv->id); ?>">
                                        <input type="hidden" name="notify_url" value="<?=  site_url('payments/paypalipn'); ?>" />
                                        <input type="hidden" name="custom" value="<?=  $inv->reference_no . '__' . $grand_total . '__' . $paypal_fee; ?>">
                                    </form>
                                </div>
                                <?php
                            }
                            if (!isset($spay) && $skrill->active == 1 && $grand_total != '0.00') {
                                $skrill_fee = 0;
                                if (trim(strtolower($customer->country)) == $biller->country) {
                                    $skrill_fee = $skrill->fixed_charges + ($grand_total * $skrill->extra_charges_my / 100);
                                } else {
                                    $skrill_fee = $skrill->fixed_charges + ($grand_total * $skrill->extra_charges_other / 100);
                                } ?>
                                <div style="width:170px;margin-left:10px;display:inline-block;">
                                    <form action="https://www.moneybookers.com/app/payment.pl" method="post">
                                        <input type="hidden" name="pay_to_email" value="<?= $skrill->account_email; ?>">
                                        <input type="hidden" name="status_url" value="<?= site_url('payments/skrillipn'); ?>">
                                        <input type="hidden" name="cancel_url" value="<?= site_url('clients/view_invoice?id=' . $inv->id); ?>">
                                        <input type="hidden" name="return_url" value="<?= site_url('clients/view_invoice?id=' . $inv->id); ?>">
                                        <input type="hidden" name="language" value="EN">
                                        <input type="hidden" name="ondemand_note" value="<?=  $inv->reference_no; ?>">
                                        <input type="hidden" name="merchant_fields" value="item_name,item_number">
                                        <input type="hidden" name="item_name" value="<?= $inv->reference_no; ?>">
                                        <input type="hidden" name="item_number" value="<?= $inv->id; ?>">
                                        <input type="hidden" name="amount" value="<?= $grand_total + $skrill_fee; ?>">
                                        <input type="hidden" name="currency" value="<?= $Settings->currency_prefix; ?>">
                                        <input type="hidden" name="detail1_description" value="<?=  $inv->reference_no; ?>">
                                        <input type="hidden" name="detail1_text" value="Payment for the sale invoice <?= $inv->reference_no . ': ' . $grand_total . '(+ fee: ' . $skrill_fee . ') = ' . ($grand_total + $skrill_fee); ?>">
                                        <input type="hidden" name="logo_url" value="<?= base_url() . 'uploads/logos/' . ($biller->logo ? $biller->logo : $Settings->logo); ?>">
                                        <input type="image" id="no-pdf" src="<?= base_url('uploads/btn-skrill.png'); ?>" name="submit" alt="Pay by Skrill" style="border:0 !important;">
                                    </form>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php
                echo '<a class="btn btn-primary btn-block no-print" href="' . site_url('clients/pdf?id=' . $inv->id) . '">' . lang('download_pdf') . '</a>';
            }
            ?>
                <?php
                if ($Settings->print_payment) {
                    if (!empty($payment)) {
                        ?>
                    <div class="page-break"></div>
                    <h4><?= lang('payment_details'); ?> (<?= $page_title . ' ' . lang('no') . ' ' . $inv->reference_no; ?>)</h4>
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
                    <?php
                    }
                }
            ?>
        </div>
    </div>
</div>
</div>
</div>
</div>
