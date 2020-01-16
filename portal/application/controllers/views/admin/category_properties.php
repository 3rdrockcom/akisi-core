<?php
/**
 * @Author: Robert Ram Bolista
 */
?>
<link rel="stylesheet" href="<?=base_url();?>assets/css/bootstrap-iconpicker.min.css"/>
<div class="widget-body">
<div class="editform" style="display: <?=($job=="add"?"block":"none")?>" syscatid='<?=$syscatid?>'>
	<form class="form-horizontal show-grid" id="edit_category" name="edit_category" method="post">
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Category Name</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9" >
					<div class="input-group input-group col-xs-5 col-sm-5 col-md-5 col-lg-5">
						<input type="text" class="form-control" name="catname" value="<?=$categoryinfo->description?>"/>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Icon</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group">
						<button class="btn btn-default" data-iconset="fontawesome" data-icon="<?=$categoryinfo->icon?>" role="iconpicker" name='icon'></button>
						<p class="note"><strong>Note:</strong> 'Refresh the whole page to take effect.'</p>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<div class="form-group">
				<label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Sort</label>
				<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
					<div class="input-group col-xs-3 col-sm-3 col-md-3 col-lg-3">
						<input type="text" class="form-control" name="sort" maxlength="3" value="<?=$categoryinfo->arranged?>">
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
						<?=form_multiselect('categorymenu[]',$menuslist,$menuscategory,"id='categorymenu'");?>
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

<div class="viewform" style="display: <?=($job=="add"?"none":"block")?>">
	<form class="form-horizontal">
		<fieldset>
			<!--<legend><?=$syscatid?></legend>-->
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Category Name :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$categoryinfo->description?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Icon :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><i class="fa <?=$categoryinfo->icon?> fa-2x"></i></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Sort :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$categoryinfo->arranged?></label>
				</div>
			</div>
			<div class="form-group">
				<label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Menu(s) Assigned :</label>
				<div class="col-md-6">
					<label class="col-md-12 control-label" style="text-align:left;"><?=$categoryinfo->menuassigned?></label>
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
					<?php }if($candelete){?>
					<button class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?> delete" type="button" >
						<i class="fa fa-trash-o"></i>
						Delete
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

	function load_blank_properties(){
		var form_data = {
		syscat_id:"<?=$syscatid?>",
		function_ctrl:"categoryview"
		};
		$(".properties").addClass("widget-body-ajax-loading");
		$.ajax({
			url: "<?=site_url("manage_controller")?>",
			type: "POST",
			data: form_data,
			success: function(msg){
				$("#properties").html("<div style='text-align: center' id='loading'><p>Select a category to display its properties.</p></div>");
				$("#ram").find(".info").removeClass('info');
				$(".properties").removeClass("widget-body-ajax-loading");
			}
		});
	}
	function load_form(){
		var form_data = {
		syscat_id:"<?=$syscatid?>",
		function_ctrl:"categoryview",
		canedit:'<?=$canedit?>',
		candelete:'<?=$candelete?>'
		};
		$(".properties").addClass("widget-body-ajax-loading");
			$.ajax({
				url: "<?=site_url("manage_controller")?>",
				type: "POST",
				data: form_data,
				success: function(msg){
					$("#properties").html(msg);
					$(".properties").removeClass("widget-body-ajax-loading");
				}
			});
	}

	/** edit properties */
	$(".edit").click(function() {
		$(".viewform").css('display', 'none');
		$(".editform").css('display', 'block');
	});

	$(".delete").click(function(){
		confirmationmessage("Are you sure you want to delete this category?","Delete","Cancel",function(){
			$("#modal-confirmation .close").click();
			var form_data = {
				syscatid:"<?=$syscatid?>",
				function_ctrl:"delete_category"
			};
			$.ajax({
				url: "<?=site_url("manage_controller")?>",
				type: "POST",
				data: form_data,
				success: function(msg){
					$("#modal-confirmation .close").click();
					$.sound_on = false;
					$.smallBox({
						title : "Successfully Deleted!",
						content : "Refresh the whole page to see changes!<br>This message will close in 5 seconds.",
						color : "#5384AF",
						timeout: 5000,
						icon : "fa fa-trash-o"
					});
					load_blank_properties();
					$(".refreshdt").click();
				}
			});
		},function(){
			// if you answer no
	    });
	});
	/** reset and go back to displaying of properties */
	$(".cancel").click(function(){
		confirmationmessage("Confirm you want to stop editing. Unsave changes will be lost.","Continue","No",function(){
			$("#modal-confirmation .close").click();
			$(".viewform").css('display', 'block');
			$(".editform").css('display', 'none');
			if("<?=$job?>"=="add"){
				load_blank_properties();
			}else{
				load_form();
			}
		},function(){
			// if you answer no
	    });
	});

	/** save category */
	$(".save").click(function(){
		$('#edit_category').data('bootstrapValidator').validate()
		if($('#edit_category').data('bootstrapValidator').isValid()){
			confirmationmessage("Are you sure you want to save it?","Yes","No",function(){
				var form_data = $("#edit_category").serialize();
	          	form_data += '&syscatid='+'<?=$syscatid?>';
	          	form_data += '&function_ctrl='+'save_category';
	          	form_data += '&categorymenus='+$('[name="categorymenu[]"]').val();
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
						load_blank_properties();
						$(".refreshdt").click();
					}
				});
			},function(){
				// if you answer no
	        });
		}
	});

	var checkform = function() {

	$('#edit_category').bootstrapValidator({
			framework: 'bootstrap',
			feedbackIcons : {
				valid : 'glyphicon glyphicon-ok',
				invalid : 'glyphicon glyphicon-remove',
				validating : 'glyphicon glyphicon-refresh'
			},
			fields : {
				catname : {
					feedbackIcons: false,
					validators : {
						notEmpty : {
							message : 'The Category Name is required.'
						}
					}
				},
				sort : {
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
	loadScript("<?=base_url();?>assets/js/plugin/bootstrapvalidator/bootstrapValidator.min.js", checkform);
	/*
	* BOOTSTRAP DUALLIST BOX
	*/
	loadScript("<?=base_url();?>assets/js/plugin/bootstrap-duallistbox/jquery.bootstrap-duallistbox.min.js", categorymenu);
	function categorymenu(){
		var categorymenu = $('#categorymenu').bootstrapDualListbox({
          nonSelectedListLabel: 'Menus Available',
          selectedListLabel: 'Menus Assigned',
          preserveSelectionOnMove: 'moved',
          moveOnSelect: false
        });
	}



});
</script>