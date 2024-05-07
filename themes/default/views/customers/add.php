
<div class="page-head">
    <h2 class="pull-left"><?= $page_title; ?> <span class="page-meta"><?= lang("enter_info"); ?></span> </h2>
</div>
<div class="clearfix"></div>
<div class="matter">
    <div class="container">

        <?php $attrib = array('class' => 'form-horizontal');
        echo form_open("customers/add");?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="company"><?= lang("company"); ?></label>
                    <div class="controls"> <?= form_input('company', set_value('company', ''), 'class="form-control" id="company"');?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name"><?= lang("contact_person"); ?></label>
                    <div class="controls"> <?= form_input('name', set_value('name', ''), 'class="form-control" id="name"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="email_address"><?= lang("email_address"); ?></label>
                    <div class="controls"> <?= form_input('email', set_value('email', ''), 'class="form-control" id="email_address"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="phone"><?= lang("phone"); ?></label>
                    <div class="controls"> <?= form_input('phone', set_value('phone', ''), 'class="form-control" id="phone"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="address"><?= lang("address"); ?></label>
                    <div class="controls"> <?= form_input('address', set_value('address', ''), 'class="form-control" id="address"');?>
                    </div>
                </div>  
                <div class="form-group">
                    <label for="city"><?= lang("city"); ?></label>
                    <div class="controls"> <?= form_input('city', set_value('city', ''), 'class="form-control" id="city"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="state"><?= lang("state"); ?></label>
                    <div class="controls"> <?= form_input('state', set_value('state', ''), 'class="form-control" id="state"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="postal_code"><?= lang("postal_code"); ?></label>
                    <div class="controls"> <?= form_input('postal_code', set_value('postal_code', ''), 'class="form-control" id="postal_code"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="country"><?= lang("country"); ?></label>
                    <div class="controls"> <?= form_input('country', set_value('country', ''), 'class="form-control" id="country"');?>
                    </div>
                </div> 
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cf1">Status Pickup / Kirim</label>
                    <div class="controls"> <?= form_input('cf1', set_value('cf1', ''), 'class="form-control" id="cf1"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="cf2"><?= lang("ccf2"); ?></label>
                    <div class="controls"> <?= form_input('cf2', set_value('cf2', ''), 'class="form-control" id="cf2"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="cf3"><?= lang("ccf3"); ?></label>
                    <div class="controls"> <?= form_input('cf3', set_value('cf3', ''), 'class="form-control" id="cf3"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="cf4"><?= lang("ccf4"); ?></label>
                    <div class="controls"> <?= form_input('cf4', set_value('cf4', ''), 'class="form-control" id="cf4"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="cf5"><?= lang("ccf5"); ?></label>
                    <div class="controls"> <?= form_input('cf5', set_value('cf5', ''), 'class="form-control" id="cf5"');?>
                    </div>
                </div> 
                <div class="form-group">
                    <label for="cf6"><?= lang("ccf6"); ?></label>
                    <div class="controls"> <?= form_input('cf6', set_value('cf6', ''), 'class="form-control" id="cf6"');?>
                    </div>
                </div> 
            </div>
        </div>
        <div class="form-group">
            <div class="controls"> <?= form_submit('submit', lang("add_customer"), 'class="btn btn-primary"');?> </div>
                    </div>
                    <?= form_close();?>

                    <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>
                    </div>
