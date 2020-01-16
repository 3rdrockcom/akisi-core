<?php
/**
 * @Author: Robert Ram Bolista
 */
?>

<link rel="stylesheet" href="<?=base_url();?>assets/css/bootstrap-iconpicker.min.css"/>
<div class="widget-body">
<div class="edit_menu_form" style="display: <?=($job=="add"?"block":"none")?>" menuid='<?=$menuid?>'>
	<form class="form-horizontal show-grid" id="edit_menu" name="edit_menu" method="post">
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Title</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
					<div class="input-group col-xs-5 col-sm-5 col-md-5 col-lg-5">
						<input type="text" class="form-control" name="menu_title" value="<?=$menu_info->title?>"/>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Icon</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group">
						<button class="btn btn-default" data-iconset="fontawesome" data-icon="<?=$menu_info->icon?>" role="iconpicker" name='menu_icon'></button>
						<p class="note"><strong>Note:</strong> 'Refresh the whole page to take effect.'</p>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Status</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group col-xs-3 col-sm-3 col-md-3 col-lg-3">
						<?=form_dropdown('menu_status',array("SHOW"=>"SHOW","HIDDEN"=>"HIDDEN"),$menu_info->status,"id='menu_status' class='form-control'");?>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Sort</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group col-xs-3 col-sm-3 col-md-3 col-lg-3">
						<input type="text" class="form-control" name="menu_sort" maxlength="3" value="<?=$menu_info->arranged?>">
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Assigned Category</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group col-xs-5 col-sm-5 col-md-5 col-lg-5">
						<?=form_dropdown('assign_cat',$category_list,$menu_info->category,"id='assign_cat' class='select2'");?>
					</div>
				</div>
			</div>
		</fieldset>
		<div class="form-actions">
			<div class="row">
				<div class="col-md-12">
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> cancel" type="button" name="cancel">
						<i class="fa fa-ban"></i>
						Cancel
					</button>
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> save" type="button" name="save">
						<i class="fa fa-save"></i>
						Save
					</button>
				</div>
			</div>
		</div>
	</form>
</div>

<div class="view_menu_form" style="display: <?=($job=="add"?"none":"block")?>">
	<form class="form-horizontal">
		<fieldset>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Title :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$menu_info->title?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Icon :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><i class="fa <?=$menu_info->icon?> fa-2x"></i></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Status :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$menu_info->status?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Sort :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$menu_info->arranged?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Assigned Category :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$menu_info->assigned_category?></label>
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
						Edit
					</button>
					<?php }/*
					<?if($candelete){?>
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> delete" type="button" >
						<i class="fa fa-trash-o"></i>
						Delete
					</button>
					<?}?>
					*/?>
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
pageSetUp();
$(document).ready(function(){
	function load_blank_menu_properties(){
		var form_data = {
		menuid:"<?=$menuid?>",
		function_ctrl:"menu_view"
		};
		$(".menu_properties").addClass("widget-body-ajax-loading");
		$.ajax({
			url: "<?=site_url("manage_controller")?>",
			type: "POST",
			data: form_data,
			success: function(msg){
				$("#menu_properties").html("<div style='text-align: center' id='loading'><p>Select a menu to display its properties.</p></div>");
				$("#menu_dt").find(".info").removeClass('info');
				$(".menu_properties").removeClass("widget-body-ajax-loading");
			}
		});
	}
	function load_menu_form(){
		var form_data = {
		menuid:"<?=$menuid?>",
		function_ctrl:"menu_view",
		canedit:'<?=$canedit?>',
		candelete:'<?=$candelete?>'
		};
		$(".menu_properties").addClass("widget-body-ajax-loading");
			$.ajax({
				url: "<?=site_url("manage_controller")?>",
				type: "POST",
				data: form_data,
				success: function(msg){
					$("#menu_properties").html(msg);
					$(".menu_properties").removeClass("widget-body-ajax-loading");
				}
			});
	}

	/** edit properties */
	$(".edit").click(function() {
		$(".view_menu_form").css('display', 'none');
		$(".edit_menu_form").css('display', 'block');
	});

	/** reset and go back to displaying of properties */
	$(".cancel").click(function(){
		confirmationmessage("Confirm you want to stop editing. Unsave changes will be lost.","Continue","No",function(){
			$("#modal-confirmation .close").click();
			$(".view_menu_form").css('display', 'block');
			$(".edit_menu_form").css('display', 'none');
			if("<?=$job?>"=="add"){
				load_blank_menu_properties();
			}else{
				load_menu_form();
			}
		},function(){
			// if you answer no
	    });
	});

	/** save menu */
	$(".save").click(function(){
		$('#edit_menu').data('bootstrapValidator').validate()
		if($('#edit_menu').data('bootstrapValidator').isValid()){
			confirmationmessage("Are you sure you want to save it?","Yes","No",function(){
				var form_data = $("#edit_menu").serialize();
	          	form_data += '&menuid='+'<?=$menuid?>';
	          	form_data += '&function_ctrl='+'save_menu';
	          	$.ajax({
					url: "<?=site_url("manage_controller")?>",
					type: "POST",
					data: form_data,
					success: function(msg){
						$("#modal-confirmation .close").click();
						$.sound_on = false;
						$.smallBox({
							title : "Successfully Saved!",
							content : "Refresh the whole page to see changes!<br>This message will close in 5 seconds.",
							color : "#5384AF",
							timeout: 5000,
							icon : "fa fa-save"
						});
						load_blank_menu_properties();
						$(".refresh_menu").click();
					}
				});
			},function(){
				// if you answer no
	        });
		}
	});

	var check_menu_form = function() {

	$('#edit_menu').bootstrapValidator({
			framework: 'bootstrap',
			feedbackIcons : {
				valid : 'glyphicon glyphicon-ok',
				invalid : 'glyphicon glyphicon-remove',
				validating : 'glyphicon glyphicon-refresh'
			},
			fields : {
				menu_title : {
					feedbackIcons: true,
					validators : {
						notEmpty : {
							message : 'Title is required.'
						}
					}
				},
				menu_sort : {
					feedbackIcons: false,
					validators : {
						notEmpty : {
							message : 'Sort is required.'
						},
						numeric : {
							message : 'Sort must be a number.'
						}
					}
				}
			}
		});
	}
	loadScript("<?=base_url();?>assets/js/plugin/bootstrapvalidator/bootstrapValidator.min.js", check_menu_form);
});
</script>