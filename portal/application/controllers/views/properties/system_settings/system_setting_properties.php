<?php
/**
 * @Author: Robert Ram Bolista
 */
?>


<script src="<?=base_url();?>assets/js/plugin/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
<script src="<?=base_url();?>assets/js/plugin/ckeditor/ckeditor.js"></script>
<form class="form-horizontal show-grid" id="form_settings" name="form_settings" method="post" enctype="multipart/form-data">
    <fieldset>
        <div class="form-group">
            <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">Name:</label>
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
                <div class="input-group col-xs-4 col-sm-4 col-md-4 col-lg-4">
                    <input type="text" class="form-control" name="f_name" value="<?=$name?>" readonly> 
                    <input type="hidden" class="form-control" name="f_setting_type" value="<?=$setting_type?>"> 
                </div>
            </div>
        </div>
        <?php if($setting_type=="OTHERS"){?>
        <div class="form-group">
            <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">Automatic:</label>
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
                <div class="input-group col-xs-1 col-sm-1 col-md-1 col-lg-1">
                <span class="input-group-addon">
                    <span class="onoffswitch">
                        <input type="checkbox" name="f_value" class="onoffswitch-checkbox" id="f_value" <?=$checked?> value="1"> 
                        <label class="onoffswitch-label" for="f_value"> 
                            <span class="onoffswitch-inner" data-swchon-text="YES" data-swchoff-text="NO"></span> 
                            <span class="onoffswitch-switch"></span> 
                        </label> 
                    </span>
                </span>
                
                    <!-- <input type="text" class="form-control" name="f_value" value="<?=$value?>">-->
                </div>
            </div>
        </div>
        <?php }?>
        <?php if($setting_type=="SCHEDULE"){?>
        <div class="form-group">
            <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">Time:</label>
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
                <div class="input-group col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <input class="form-control" id="timepicker" type="text" name="f_value" placeholder="Select time" value="<?=$value?>" readonly>
                    <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
                </div>
            </div>
        </div>
        <?php }?>
        <?php if($setting_type=="NOTIFICATION"){?>
        <div class="form-group">
            <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">Subject:</label>
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
                <div class="input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
                    <input type="text" class="form-control" name="subject" value="<?=$subject?>">
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">Email Message:</label>
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
                <div class="input-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <textarea name="ckeditor_email" id="ckeditor_email">  
                    <?=$email_message?>
                </textarea>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">SMS Message:</label>
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
                <div class="input-group col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <textarea name="ckeditor_sms" id="ckeditor_sms">  
                    <?=$sms_message?>
                </textarea>
                </div>
            </div>
        </div>
        <?php }?>
    </fieldset>

    <fieldset>
        <div class="form-group">
            <label class="control-label col-xs-2 col-sm-2 col-md-2 col-lg-2">&nbsp;</label>
            <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
            <button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> cancel_settings" type="button" name="cancel">
                <i class="fa fa-ban"></i>
                <?=$this->lang->line('button_cancel')?>
            </button>
            <button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> save_settings" type="button" name="save">
                <i class="fa fa-save"></i>
                <?=$this->lang->line('button_save')?>
            </button>
            </div>
        </div>
        </div>
    </fieldset>
</form>

<script type="text/javascript">
$('#timepicker').timepicker();
<?php if($setting_type=="NOTIFICATION"){?>
CKEDITOR.replace( 'ckeditor_email', { 
    height: '200px', startupFocus : true, 
    toolbar: [
                [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],
                [ 'Link', 'Unlink', 'Anchor' ],
                [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat' ],
                [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ],
                [ 'Styles', 'Format' ],
                [ 'Table', 'HorizontalRule', 'SpecialChar' ],
                [ 'FontFamily','FontSize', 'TextColor', 'BGColor' ]
            ]
    });

CKEDITOR.replace( 'ckeditor_sms', { 
    height: '200px', 
    toolbar: [
                [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],
                [ 'Link', 'Unlink', 'Anchor' ],
                [ 'Bold', 'Italic', 'Strike', '-', 'RemoveFormat' ],
                [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ],
                [ 'Styles', 'Format' ],
                [ 'Table', 'HorizontalRule', 'SpecialChar' ],
                [ 'FontFamily','FontSize', 'TextColor', 'BGColor' ]
            ]
    });
<?php } ?>
$(".cancel_settings").click(function(){
	confirmationmessage("<?=$this->lang->line('confirm_edit')?>","<?=$this->lang->line('button_continue')?>","<?=$this->lang->line('button_cancel')?>",function(){
		$("#modal-confirmation .close").click();
		$("#f_settings").data("settings_id","");
		$("#settings_add").html();
		$("#settings_add").css('display', 'none');
        $(".refresh_settings").click();
        //if (CKEDITOR.instances.ckeditor_email) CKEDITOR.instances.ckeditor_email.destroy();
        //if (CKEDITOR.instances.ckeditor_sms) CKEDITOR.instances.ckeditor_sms.destroy();
	},function(){
		// if you answer no
    });
});

/** save settings */
$(".save_settings").click(function() {
	/* Act on the event */
	$('#form_settings').data('bootstrapValidator').validate();
	if($('#form_settings').data('bootstrapValidator').isValid()){
		confirmationmessage("<?=$this->lang->line('confirm_save')?>","<?=$this->lang->line('button_yes')?>","<?=$this->lang->line('button_no')?>",function(){
            <?php if($setting_type=="NOTIFICATION"){?>
            CKEDITOR.instances.ckeditor_email.updateElement();
            CKEDITOR.instances.ckeditor_sms.updateElement();
            <?php } ?>
			var form_data = $("#form_settings").serialize();
          	form_data += '&function_ctrl='+'save_settings';
            form_data += '&settings_id=<?=$settings_id?>';
            //return false;
          	$.ajax({
				url: "<?=site_url("setup_process")?>",
				type: "POST",
				data: form_data,
				success: function(msg){
                    $("#modal-confirmation .close").click();
					$.sound_on = false;
                    $.smallBox({
                        title : "<?=$this->lang->line('success_title')?>",
                        content : "<?=$this->lang->line('success_save')?><br><?=$this->lang->line('notification_close')?>", //Refresh the whole page to see changes!<br>
                        color : "<?=$this->lang->line('color_success')?>",
                        timeout: 5000,
                        icon : "fa fa-save"
                    });
                    $("#f_settings").data("settings_id","");
					$("#settings_add").html();
					$("#settings_add").css('display', 'none');
					$(".refresh_settings").click();
				}
			});
		},function(){
			// if you answer no
        });
	}
});

var check_settings = function() {

$('#form_settings').bootstrapValidator({
		framework: 'bootstrap',
		feedbackIcons : {
			valid : 'glyphicon glyphicon-ok',
			invalid : 'glyphicon glyphicon-remove',
			validating : 'glyphicon glyphicon-refresh'
		},
		fields : {
			name : {
				feedbackIcons: false,
				validators : {
					notEmpty : {
						message : 'Name is Required'
					}
				}
			}
		}
	});
}
loadScript("<?=base_url();?>assets/js/plugin/bootstrapvalidator/bootstrapValidator.min.js", check_settings);
</script>