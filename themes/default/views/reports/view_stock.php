<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Helo</title>
    <link rel="shortcut icon" href="<?= $assets; ?>img/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="<?= $assets; ?>style/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets; ?>style/style.css" rel="stylesheet">
    <style>@page { margin: 0 !important; padding: 0 !important; } html, body { margin: 0 !important; padding: 0 !important; } body, div, span, * { font-family: DejaVu Sans, sans-serif !important; } #wrap { padding: 20px 40px !important; }</style>
    
</head>

<body>
    
    <script>
        window.onload = function() {
            // Trigger the print dialog
            window.print();
        };
    </script>
   <div id="wrap">
        <!--<img src="<?= $this->sim->get_image(base_url('uploads/' . ($biller->logo ? $biller->logo : $Settings->logo))); ?>"  width="180px" height="180px" alt="<?= $biller->company ? $biller->company : $Settings->site_name ?>" style="margin-top: 20px;" />-->
          <div class="row">
            <div class="col-xs-6">
                <div>
                  <p>
                <strong><h3>PYB Checking Ordered</h3></strong>
                    <!--<strong><?= $inv->reference_no; ?></strong>-->
                </p>
                <p>
                   <strong>From Date : <?= DateTime::createFromFormat('Y-m-d', $fromdate)->format('d/m/Y'); ?></strong>
                </p>
                <p>
                   <strong>End Date : <?= DateTime::createFromFormat('Y-m-d', $enddate)->format('d/m/Y'); ?></strong>
                </p>
                </div>
                
            </div>

        </div>
        <div style="clear: both; height: 5px;"></div>
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
                            <th class="col-xs-1" style="max-width: 40px !important;"><?= lang('no'); ?></th>
                            <!--<th style="width: 30% !important; max-width:300px !important;">Tanggal Order</th>-->
                             <th class="col-xs-2">Tanggal Order</th>
                             <th class="col-xs-2">Customer</th>
                            <th style="width: 30% !important; max-width:300px !important;">Details</th>
                            <th class="col-xs-2">Pengiriman</th>
                    </thead>

                    <tbody>
                        
                        <?php  $r = 1; foreach ($combinedData as $data): ?>
                        <tr>
                            <td style="max-width: 30px !important;" class="text-center"><?= $r; ?></td>
                            <td><?= DateTime::createFromFormat('Y-m-d', $data['sale']->due_date)->format('d/m/Y'); ?></td>
                            <td><?= $data['sale']->customer_name?></td>
                            <td style="width: 30% !important; max-width:300px !important;"> 
                            <ul>
                                <?php foreach ($data['rows'] as $items): ?>
                                    <li><?= $items->product_name . $items->details ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                            
                            <td> 
                            <?php if($data['sale']->shipment === "" || $data['sale']->shipment === null ) {
                               echo $data['customer']->cf1;
                            } else {
                                echo $data['sale']->shipment;
                            }
                           ?></td>
                        </tr>
                        
                            <!-- Access other properties as needed -->
                        <?php $r++; endforeach; ?>
                       
                    </tbody>

                

                </table>
            </div>

            <div style="clear: both;"></div>
          
        </div>
    </div>
</div>
</body>
</html>
