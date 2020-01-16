<?php
/**
 * @Author: Robert Ram Bolista
 */
?>
<link rel="stylesheet" href="<?=base_url();?>assets/css/bootstrap-iconpicker.min.css"/>
<div class="widget-body">
<div class="edit_user" style="display: <?=($job=="add"?"block":"none")?>" userid='<?=$userid?>'>
	<form class="form-horizontal show-grid" id="edit_user" name="edit_user" method="post">
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Username</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
					<div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<input type="text" class="form-control" name="username" value="<?=$user_info->username?>" <?=($job!="add"?"readonly":"")?> >
						<p class="note"><strong>Note:</strong> This value cannot be changed.</p>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">First Name</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<input class="form-control" placeholder="First Name" type="text" value="<?=$user_info->fname?>" name="fname">
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Middle Name</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<input class="form-control" placeholder="Middle Name" type="text" value="<?=$user_info->mname?>" name="mname">
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Last Name</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<input class="form-control" placeholder="Last Name" type="text" value="<?=$user_info->lname?>" name="lname">
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Email</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<input class="form-control" placeholder="Email" type="text" value="<?=$user_info->email?>" name="email">
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				&nbsp;
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3"><?=$this->lang->line('user_password')?> </label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
						<input class="form-control" type="password" value="" name="password">
						<p class="note"><strong>Note:</strong> Just leave it blank if there's no changes in password</p>
					</div>
				</div>
			</div>
		</fieldset>
		
		<fieldset>
			<div class="form-group">
				<!-- widget div-->
				<div class="col-md-12">

					<!-- widget content -->
					<div >
						<?=form_multiselect('user_role[]',$roles_list,$user_role_list,"id='user_role'");?>
					</div>
					<!-- end widget content -->

				</div>
				<!-- end widget div -->
			</div>
		</fieldset>
		<div class="form-actions">
			<div class="row">
				<div class="col-md-12">
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> cancel" type="button" name="cancel">
						<i class="fa fa-ban"></i>
						<?=$this->lang->line('button_cancel')?>
					</button>
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> save" type="button" name="save">
						<i class="fa fa-save"></i>
						<?=$this->lang->line('button_save')?>
					</button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="view_user" style="display: <?=($job=="add"?"none":"block")?>">
	<form class="form-horizontal">
		<fieldset>
			<!--<legend><?=$userid?></legend>-->
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Username :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$user_info->username?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Full Name :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$user_info->fullname?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Email :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$user_info->email?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Status :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$user_info->status?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Role(s) Assigned :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$user_info->role_assigned?></label>
				</div>
			</div>
		</fieldset>
		<?php if($canedit || $candelete){?>
		<div class="form-actions">
			<div class="row">
				<div class="col-md-12">
					<?php if($canedit){?>
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> edit" type="button" >
						<i class="fa fa-edit"></i>
						<?=$this->lang->line('button_edit')?>
					</button>
					<?php }if($candelete){?>
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> delete" type="button" stat="<?=($user_info->status=="ACTIVE"?"INACTIVE":"ACTIVE")?>">
						<?=($user_info->status=="ACTIVE"?"Disable User":"Enable User")?>

					</button>
					<?php }?>
				</div>
			</div>
		</div>
		<?php }?>
	</form>
</div>
</div>
<script type="text/javascript" src="<?=base_url();?>assets/js/iconset/iconset-fontawesome-4.1.0.min.js"></script>
<script type="text/javascript" src="<?=base_url();?>assets/js/bootstrap-iconpicker.js"></script>
<script type="text/javascript">

$(document).ready(function(){

	function load_blank_properties_user(){
		var form_data = {
		userid:"<?=$userid?>",
		function_ctrl:"user_view"
		};
		$(".user_properties").addClass("widget-body-ajax-loading");
		$.ajax({
			url: "<?=site_url("manage_controller")?>",
			type: "POST",
			data: form_data,
			success: function(msg){
				$("#user_properties").html("<div style='text-align: center' id='loading'><p>Select a user to display his/her properties.</p></div>");
				$("#user_dt").find(".info").removeClass('info');
				$(".user_properties").removeClass("widget-body-ajax-loading");
			}
		});
	}
	function load_user_form(){
		var form_data = {
		userid:"<?=$userid?>",
		function_ctrl:"user_view",
		canedit:'<?=$canedit?>',
		candelete:'<?=$candelete?>'
		};
		$(".user_properties").addClass("widget-body-ajax-loading");
			$.ajax({
				url: "<?=site_url("manage_controller")?>",
				type: "POST",
				data: form_data,
				success: function(msg){
					$("#user_properties").html(msg);
					$(".user_properties").removeClass("widget-body-ajax-loading");
				}
			});
	}

	/** edit properties */
	$(".edit").click(function() {
		$(".view_user").css('display', 'none');
		$(".edit_user").css('display', 'block');
	});

	$(".delete").click(function(){
		userstat = $(this).attr("stat");
		confirmationmessage("<?=$this->lang->line('confirm_continue')?>","<?=$this->lang->line('button_yes')?>","<?=$this->lang->line('button_cancel')?>",function(){
			$("#modal-confirmation .close").click();
			var form_data = {
				userid:"<?=$userid?>",
				function_ctrl:"disable_user",
				status:userstat
			};
			$.ajax({
				url: "<?=site_url("manage_controller")?>",
				type: "POST",
				data: form_data,
				success: function(msg){
					$("#modal-confirmation .close").click();
					$.sound_on = false;
					$.smallBox({
						title : "<?=$this->lang->line('success_update')?>",
						content : "<?=$this->lang->line('notification_close')?>",
						color : "#5384AF",
						timeout: 5000,
						icon : "fa fa-save"
					});
					load_blank_properties_user();
					$(".refresh_user").click();
				}
			});
		},function(){
			// if you answer no
	    });
	});
	/** reset and go back to displaying of properties */
	$(".cancel").click(function(){
		confirmationmessage("<?=$this->lang->line('confirm_edit')?>","<?=$this->lang->line('button_continue')?>","<?=$this->lang->line('button_no')?>",function(){
			$("#modal-confirmation .close").click();
			$(".view_user").css('display', 'block');
			$(".edit_user").css('display', 'none');
			if("<?=$job?>"=="add"){
				load_blank_properties_user();
			}else{
				load_user_form();
			}
		},function(){
			// if you answer no
	    });
	});

	/** save category */
	$(".save").click(function(){
		$('#edit_user').data('bootstrapValidator').validate()
		if($('#edit_user').data('bootstrapValidator').isValid()){
			confirmationmessage("<?=$this->lang->line('confirm_save')?>","<?=$this->lang->line('button_yes')?>","<?=$this->lang->line('button_no')?>",function(){
				var form_data = $("#edit_user").serialize();
	          	form_data += '&userid='+'<?=$userid?>';
	          	form_data += '&function_ctrl='+'save_user';
	          	form_data += '&user_roles='+$('[name="user_role[]"]').val();
	          	$.ajax({
					url: "<?=site_url("manage_controller")?>",
					type: "POST",
					data: form_data,
					success: function(msg){

						$("#modal-confirmation .close").click();
						$.sound_on = false;
						$.smallBox({
							title : "<?=$this->lang->line('success_save')?>",
							content : "<?=$this->lang->line('notification_close')?>",
							color : "#5384AF",
							timeout: 5000,
							icon : "fa fa-save"
						});
						load_blank_properties_user();
						$(".refresh_user").click();
					}
				});
			},function(){
				// if you answer no
	        });
		}
	});

	var checkform = function() {

	$('#edit_user').bootstrapValidator({
			framework: 'bootstrap',
			feedbackIcons : {
				valid : 'glyphicon glyphicon-ok',
				invalid : 'glyphicon glyphicon-remove',
				validating : 'glyphicon glyphicon-refresh'
			},
			fields : {
				username : {
					feedbackIcons: false,
					validators : {
						notEmpty : {
							message : 'Username is required'
						}
					}
				},
				fname : {
					feedbackIcons: false,
					validators : {
						notEmpty : {
							message : 'First Name is required.'
						}
					}
				},
				lname : {
					feedbackIcons: false,
					validators : {
						notEmpty : {
							message : 'Last Name is required.'
						}
					}
				},
				email: {
					feedbackIcons: false,
	                validators: {
	                    emailAddress: {
	                        message: 'The value is not a valid email address'
	                    }
	                }
            	},
				password : {
					feedbackIcons: false,
					validators : {
						callback: {
                        	message : 'Password is required.',
                            callback: function(value, validator, $field) {
                                if ("<?=$job?>" == 'add') {
                                    if (value == '') {
		                                return false;
		                            }else{
		                            	return true;
		                            }
                                }else{
                                	return true;
                                }
							}
                        }
					}
				}
			}
		});
	}
	loadScript("<?=base_url();?>assets/js/plugin/bootstrapvalidator/bootstrapValidator.min.js", checkform);
	/*
	* BOOTSTRAP DUALLIST BOX
	*/
	loadScript("<?=base_url();?>assets/js/plugin/bootstrap-duallistbox/jquery.bootstrap-duallistbox.min.js", user_role);
	function user_role(){
		var user_role = $('#user_role').bootstrapDualListbox({
          nonSelectedListLabel: 'Role(s) Available',
          selectedListLabel: 'Role(s) Assigned',
          preserveSelectionOnMove: 'moved',
          moveOnSelect: false
        });
	}



});
</script>