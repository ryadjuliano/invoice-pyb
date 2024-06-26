<script>
	$(document).ready(function() {
		function status(x) {
			switch (x) {
				case 'sent':
				return '<div class="text-center"><small><span class="label label-success"><?=lang('sent');?></span></small></div>';
				break;
				case 'ordered':
				return '<div class="text-center"><small><span class="label label-success"><?=lang('ordered');?></span></small></div>';
				break;
				case 'pending':
				return '<div class="text-center"><small><span class="label label-default"><?=lang('pending');?></span></small></div>';
				break;
				default:
				return '<div class="text-center"><small><span class="label'+x+' label label-default">'+x+'</span></small></div>';
			}
		}
		var table = $('#fileData').DataTable( {

			"dom": '<"text-center"<"btn-group"B>><"clear"><"row"<"col-md-6"l><"col-md-6 pr0"p>r>t<"row"<"col-md-6"i><"col-md-6"p>><"clear">',
			"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
			"order": [[ 0, "desc" ], [ 1, "desc" ]],
			"pageLength": <?=$Settings->rows_per_page;?>,
			"processing": true, "serverSide": true,
		    // 'ajax': '<?=site_url('sales/getquotes');?>',
		    'ajax' : { url: '<?=site_url('sales/getquotes');?>', type: 'POST', "data": function ( d ) {
		    	d.<?=$this->security->get_csrf_token_name();?> = "<?=$this->security->get_csrf_hash()?>";
		    }},
		    "buttons": [
		    { extend: 'copyHtml5', exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11 ] } },
		    { extend: 'excelHtml5', 'footer': true, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11 ] } },
		    { extend: 'csvHtml5', 'footer': true, exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11 ] } },
		    { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', 'footer': true, 
		    exportOptions: { columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11 ] } },
		    { extend: 'colvis', text: 'Columns'},
		    ],
		    "columns": [
		    { "data": "id", "visible": false },
		    { "data": "date", "render": fld },
		    { "data": "company_name" },
		    { "data": "reference_no" },
		    { "data": "user" },
		    { "data": "customer_name" },
		    { "data": "total", "render": cf },
		    { "data": "total_tax", "render": cf, "visible": false },
		    { "data": "shipping", "render": cf },
		    { "data": "discount", "render": cf, "visible": false },
		    { "data": "grand_total", "render": cf },
		    { "data": "expiry_date", "render": fsd, "visible": false },
		    { "data": "status", "render": status },
		    { "data": "Actions", "searchable": false, "orderable": false }
		    ],
		    "rowCallback": function( row, data, index ) {
                $(row).attr('id', data.id);
                $(row).addClass('quote_link');
            }

		});

		$('#fileData').on("click", ".st", function(){
			inv_id = $(this).attr('id');
		});

		$('#fileData tfoot th:not(:last-child)').each(function () {
			var title = $(this).text();
			$(this).html( '<input type="text" class="text_filter" placeholder="'+title+'" />' );
		});

		$('#search_table').on( 'keyup change', function (e) {
			var code = (e.keyCode ? e.keyCode : e.which);
			if (((code == 13 && table.search() !== this.value) || (table.search() !== '' && this.value === ''))) {
				table.search( this.value ).draw();
			}
		});

		table.columns().every(function () {
			var self = this;
			$( 'input', this.footer() ).on( 'keyup change', function (e) {
				var code = (e.keyCode ? e.keyCode : e.which);
				if (((code == 13 && self.search() !== this.value) || (self.search() !== '' && this.value === ''))) {
					self.search( this.value ).draw();
				}
			});
		});


		$('#fileData').on('click', '.email_inv', function() {
			var id = $(this).attr('id');
			var cid = $(this).attr('data-customer');
			var bid = $(this).attr('data-company');
			$.getJSON( "<?= site_url('sales/getCE'); ?>", { cid: cid, bid: bid, <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash() ?>' }).done(function( json ) {
				$('#customer_email').val(json.ce);
				$('#subject').val('<?= lang("quote_from"); ?> '+json.com);
			});
			$('#emailModalLabel').text('<?= lang("email")." ".lang("quote")." ".lang("no"); ?> '+id);
			//$('#subject').val('<?= lang("quote")." from ".$Settings->site_name; ?>');
			$('#qu_id').val(id);
			$('#emailModal').modal();
			return false;
		});

		$('#emailModal').on('click', '#email_now', function() {
			$(this).text('Sending...');
			var vid = $('#qu_id').val();
			var to = $('#customer_email').val();
			var subject = $('#subject').val();
			var note = $('#message').val();
			var cc = $('#cc').val();
			var bcc = $('#bcc').val();
			if(to != '') {
				$.ajax({
					type: "post",
					url: "<?= site_url('sales/send_quote'); ?>",
					data: { id: vid, to: to, subject: subject, note: note, cc: cc, bcc: bcc, <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash() ?>' },
					success: function(data) {
						alert(data);
					},
					error: function(){
						alert('<?= lang('ajax_error'); ?>');
					}
				});
			} else { alert('<?= lang('to'); ?>'); }
			$('#emailModal').modal('hide');
			$(this).text('<?= lang('send_email'); ?>');
			return false;
		});

	});

</script>

<div class="page-head">
	<h2 class="pull-left"><?= $page_title; ?> <span class="page-meta"><?= lang("list_results"); ?></span> </h2>
</div>
<div class="clearfix"></div>
<div class="matter">
	<div class="container">
		<div class="table-responsive">
			<table id="fileData" class="table quotes table-bordered table-hover table-striped" style="margin-bottom: 5px;">
				<thead>
					<tr class="active">
						<th style="max-width:35px; text-align:center;"><?= lang("id"); ?></th>
						<th class="col-xs-1"><?= lang("date"); ?></th>
						<th class="col-xs-2"><?= lang("billing_company"); ?></th>
						<th class="col-xs-1"><?= lang("reference_no"); ?></th>
						<th class="col-xs-1"><?= lang("created_by"); ?></th>
						<th class="col-xs-2"><?= lang("customer"); ?></th>
						<th class="col-xs-1"><?= lang("total"); ?></th>
						<th class="col-xs-1"><?= lang("total_tax"); ?></th>
						<th class="col-xs-1"><?= lang("shipping"); ?></th>
						<th class="col-xs-1"><?= lang("discount"); ?></th>
						<th class="col-xs-1"><?= lang("grand_total"); ?></th>
						<th class="col-xs-1"><?= lang("expiry_date"); ?></th>
						<th class="col-xs-1"><?= lang("status"); ?></th>
						<th style="min-width:75px; text-align:center;"><?= lang("actions"); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="14" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th style="max-width:35px; text-align:center;"><?= lang("id"); ?></th>
						<th class="col-xs-1"><?= lang("date"); ?></th>
						<th class="col-xs-2"><?= lang("billing_company"); ?></th>
						<th class="col-xs-1"><?= lang("reference_no"); ?></th>
						<th class="col-xs-1"><?= lang("created_by"); ?></th>
						<th class="col-xs-2"><?= lang("customer"); ?></th>
						<th class="col-xs-1"><?= lang("total"); ?></th>
						<th class="col-xs-1"><?= lang("total_tax"); ?></th>
						<th class="col-xs-1"><?= lang("shipping"); ?></th>
						<th class="col-xs-1"><?= lang("discount"); ?></th>
						<th class="col-xs-1"><?= lang("grand_total"); ?></th>
						<th class="col-xs-1"><?= lang("expiry_date"); ?></th>
						<th class="col-xs-1"><?= lang("status"); ?></th>
						<th style="min-width:75px; text-align:center;"><?= lang("actions"); ?></th>
					</tr>
					<tr>
						<td colspan="14" class="p0"><input type="text" class="form-control b0" name="search_table" id="search_table" placeholder="<?= lang('type_hit_enter'); ?>" style="width:100%;"></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<p><a href="<?= site_url('sales/add_quote');?>" class="btn btn-primary"><?= lang("add_quote"); ?></a></p>
	</div>
</div>

<div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel"><?= lang('add_payment'); ?></h4>
			</div>
			<div class="modal-body">
				<div class="control-group">
					<label class="control-label" for="amount"><?= lang("amount_paid"); ?></label>
					<div class="controls"> <?= form_input('amount', '', 'class="input-block-level" id="amount"');?> </div>
				</div>
				<div class="control-group">
					<label class="control-label" for="note"><?= lang("note"); ?></label>
					<div class="controls"> <?= form_textarea('note', '', 'class="input-block-level" id="note" style="height:100px;"');?> </div>
				</div>
				<input type="hidden" name="cid" value="" id="cid" />
				<input type="hidden" name="vid" value="" id="vid" />
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true"><?= lang('close'); ?></button>
				<button class="btn btn-primary" id="add-payment"><?= lang('add_payment'); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="emailModalLabel"></h4>
			</div>
			<div class="modal-body">

				<div class="form-group">
					<label for="customer_email"><?= lang("to"); ?></label>
					<div class="controls"> <?= form_input('to', '', 'class="form-control" id="customer_email"');?></div>
				</div>
				<div id="extra" style="display:none;">
					<div class="form-group">
						<label for="cc"><?= lang("cc"); ?></label>
						<div class="controls"> <?= form_input('cc', '', 'class="form-control" id="cc"');?></div>
					</div>
					<div class="form-group">
						<label for="bcc"><?= lang("bcc"); ?></label>
						<div class="controls"> <?= form_input('bcc', '', 'class="form-control" id="bcc"');?></div>
					</div>
				</div>
				<div class="form-group">
					<label for="subject"><?= lang("subject"); ?></label>
					<div class="controls">
						<?= form_input('subject', '', 'class="form-control" id="subject"');?> 
					</div>
				</div>
				<div class="form-group">
					<label for="message"><?= lang("message"); ?></label>
					<div class="controls"> 
						<?= form_textarea('note', lang("find_attached_quote"), 'id ="message" class="form-control" placeholder="'.lang("add_note").'" rows="3" style="margin-top: 10px; height: 100px;"');?> 
					</div>
				</div>
				<input type="hidden" id="qu_id" value="" />  
			</div>
			<div class="modal-footer">
				<button class="btn pull-left" id="sh-btn"><?= lang('show_hide_cc'); ?></button>
				<button class="btn" data-dismiss="modal" aria-hidden="true"><?= lang('close'); ?></button>
				<button class="btn btn-primary" id="email_now"><?= lang('send_email'); ?></button>
			</div>
			<script type="text/javascript">
				$(document).ready(function() {
					$('#sh-btn').click(function(event) {
						$('#extra').toggle();
						$('#cc').val('<?= $this->session->userdata('email'); ?>');
					});
				});
			</script>
		</div>
	</div>
</div>
