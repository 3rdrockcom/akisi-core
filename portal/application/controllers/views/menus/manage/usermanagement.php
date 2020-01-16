<?php
/**
 * @Author: Robert Ram Bolista
 */
?>
<!-- Widget ID (each widget will need unique ID)-->
<input type="button" class="refresh_user" style="display: none">
<section id="widget-grid" class="">
<article class="col-sm-6 col-md-6 col-lg-6">
<div    class="jarviswidget <?=$setup['jarviswidget_color']?>"
		data-widget-colorbutton      ="false"
		data-widget-collapsed        ="false"
		data-widget-colorbutton      ="false"
		data-widget-editbutton       ="true"
		data-widget-togglebutton     ="false"
		data-widget-deletebutton     ="false"
		data-widget-fullscreenbutton ="false"
		data-widget-custombutton     ="false"
		data-widget-sortable         ="false">
		<!-- widget options:
		usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">

		data-widget-colorbutton="false"
		data-widget-editbutton="false"
		data-widget-togglebutton="false"
		data-widget-deletebutton="false"
		data-widget-fullscreenbutton="false"
		data-widget-custombutton="false"
		data-widget-collapsed="true"
		data-widget-sortable="false"

		-->
	<header>
		<span class="widget-icon"> <i class="fa fa-user"></i> </span>
		<h2>Users</h2>
		<?php if($canadd){?>
		<div class="widget-toolbar add_user">
			<a href="javascript:void(0);" class="btn <?=$setup['button_color']?> <?=$setup['button_txt_color']?>">
			<i class="fa fa-plus"></i>
			&nbsp; Add User
			</a>
		</div>
		<?php }?>
	</header>

	<!-- widget div-->
	<div>

		<!-- widget edit box -->
		<div class="jarviswidget-editbox">
			<!-- This area used as dropdown edit box -->

		</div>
		<!-- end widget edit box -->

		<!-- widget content -->
		<div class="widget-body no-padding">

			<table id="user_dt" class="table table-striped table-bordered table-hover" width="100%">

		        <thead>
	        		<!--
		        	<tr>
						<th class="hasinput" style="width:17%">
							<input type="text" class="form-control" placeholder="Filter Username" />
						</th>
						<th class="hasinput" style="width:16%">
							<input type="text" class="form-control" placeholder="Filter Full Name" />
						</th>
						<th class="hasinput" style="width:16%">
							<input type="text" class="form-control" placeholder="Filter Status" />
						</th>
					</tr>
					-->
					<tr>
	                    <th data-class="expand" class="col-xs-4 col-sm-4 col-md-4 col-lg-4">Username</th>
	                    <th data-class="expand" class="col-xs-6 col-sm-6 col-md-6 col-lg-6">Full Name</th>
	                    <th data-class="expand" class="col-xs-2 col-sm-2 col-md-2 col-lg-2">Status</th>

	                </tr>
		        </thead>

		        <tbody>
		        </tbody>

			</table>

		</div>
		<!-- end widget content -->

	</div>
	<!-- end widget div -->

</div>
</article>
<!-- end widget -->

<article class="col-sm-6 col-md-6 col-lg-6">
<div    class="jarviswidget <?=$setup['jarviswidget_color']?>" 
		data-widget-colorbutton      ="false"
		data-widget-collapsed        ="false"
		data-widget-colorbutton      ="false"
		data-widget-editbutton       ="true"
		data-widget-togglebutton     ="false"
		data-widget-deletebutton     ="false"
		data-widget-fullscreenbutton ="false"
		data-widget-custombutton     ="false"
		data-widget-sortable         ="false">
	<header class="user_properties">
		<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
		<h2>User Properties</h2>

	</header>
	<div class="row" id="user_properties">
		<div style="text-align: center" id="loading" >
			<p>Select a user to display his/her properties.</p>
		</div>
	</div>
</div>
</article>
</section>
<script type="text/javascript">

	/* DO NOT REMOVE : GLOBAL FUNCTIONS!
	 *
	 * pageSetUp(); WILL CALL THE FOLLOWING FUNCTIONS
	 *
	 * // activate tooltips
	 * $("[rel=tooltip]").tooltip();
	 *
	 * // activate popovers
	 * $("[rel=popover]").popover();
	 *
	 * // activate popovers with hover states
	 * $("[rel=popover-hover]").popover({ trigger: "hover" });
	 *
	 * // activate inline charts
	 * runAllCharts();
	 *
	 * // setup widgets
	 * setup_widgets_desktop();
	 *
	 * // run form elements
	 * runAllForms();
	 *
	 ********************************
	 *
	 * pageSetUp() is needed whenever you load a page.
	 * It initializes and checks for all basic elements of the page
	 * and makes rendering easier.
	 *
	 */

	pageSetUp();
	/*
	 * ALL PAGE RELATED SCRIPTS CAN GO BELOW HERE
	 * eg alert("my home function");
	 *
	 * var pagefunction = function() {
	 *   ...
	 * }
	 * loadScript("js/plugin/_PLUGIN_NAME_.js", pagefunction);
	 *
	 */

	// PAGE RELATED SCRIPTS

	// pagefunction	
	var pagefunction = function() {
		//console.log("cleared");

		/* // DOM Position key index //

			l - Length changing (dropdown)
			f - Filtering input (search)
			t - The Table! (datatable)
			i - Information (records)
			p - Pagination (paging)
			r - pRocessing 
			< and > - div elements
			<"#id" and > - div with an id
			<"class" and > - div with a class
			<"#id.class" and > - div with an id and class

			Also see: http://legacy.datatables.net/usage/features
		*/

			var responsiveHelper_datatable_fixed_column = undefined;
			var breakpointDefinition = {
				tablet : 1024,
				phone : 480
			};
			
		/* COLUMN FILTER  */
			
	    var otable = $('#user_dt').DataTable({
			"bProcessing": true,
			//"sPaginationType": "full_numbers",
			"bServerSide": true,
			"sAjaxSource": "<?=site_url("datatable_controller")?>",
			"fnServerParams": function( aoData ){
				aoData.push( { "name": "function_ctrl", "value": "dbuserlist" });
			},
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>>"+
			"rt"+
			"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"sServerMethod": "POST",
			"columns": [
					{ "data": "userno" },
					{ "data": "name" },
					{ "data": "status" }
			],
			"language": {
	            "zeroRecords": "<?=$this->lang->line('datatable_zerorecords')?>",
	            "info": "<?=$this->lang->line('datatable_pageinfo')?>",
	            "infoEmpty": "<?=$this->lang->line('datatable_infoempty')?>",
	            "infoFiltered": "<?=$this->lang->line('datatable_info_filtered')?>"
	        },
	        "oLanguage": {
				"sSearch": '<span class="input-group-addon"><i class="fa fa-search"></i></span>',
		        "oPaginate": {
		        	"sFirst": "<?=$this->lang->line('datatable_page_first')?>",
			        "sNext": "<?=$this->lang->line('datatable_page_next')?>",
			        "sPrevious":"<?=$this->lang->line('datatable_page_previous')?>",
			        "sLast": "<?=$this->lang->line('datatable_page_last')?>"
			    }
			},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
								$(nRow).css('cursor', 'pointer');
								$(nRow).click(function(){
									objrow = $(this);
									$(this).addClass('info');
									/** check first if the user is in the middle of editing */
									if($("#user_properties").find(".edit_user").css('display')=="block" && $("#user_properties").find(".edit_user").attr("userid")!=aData['userid']){
										confirmationmessage("<?=$this->lang->line('confirm_edit')?>","<?=$this->lang->line('button_continue')?>","<?=$this->lang->line('button_cancel')?>",function(){
											$("#modal-confirmation .close").click();
											$(nRow).parent().find(".info").removeClass('info');
				                            objrow.addClass('info');
											view_user(aData['userid']);
										},function(){ // if you answer no
											objrow.removeClass('info');
											$("#modal-confirmation .close").click();
											return false;
									    });
									}else if($("#user_properties").find(".edit_user").attr("userid")==aData['userid']){
										return false;
									}else{
										$(nRow).parent().find(".info").removeClass('info');
			                            $(this).addClass('info');
			                            view_user(aData['userid']);
									}
								});
                            }

	    });
		$(".jarviswidget-refresh-btn").click(function(){
			//return false;
		});
		$(".refresh_user").click(function(){
			otable.draw();
		});
	    // custom toolbar
	    //$("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');
	    //
	    // Apply the filter
		$("#user_dt thead th input[type=text]").on( 'keyup change', function () {

	    	otable
	            .column( $(this).parent().index()+':visible' )
	            .search( this.value )
	            .draw();

	    } );
	    /* END COLUMN FILTER */
	};

	function view_user(user_id){
		var form_data = {
				userid:user_id,
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
	function add_user(){
		var form_data = {
			userid:"",
			function_ctrl:"user_view",
			job:"add"
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

	$(".add_user").click(function() {
		/** check first if the user is in the middle of editing */
		if($("#user_properties").find(".edit_user").css('display')=="block"){
			confirmationmessage("<?=$this->lang->line('confirm_edit')?>","<?=$this->lang->line('button_continue')?>","<?=$this->lang->line('button_cancel')?>",function(){
				add_user();
				$("#modal-confirmation .close").click();
			},function(){
				// if you answer no
				$(this).removeClass('info');
				$("#modal-confirmation .close").click();
				return false;
		    });
		}else{
			add_user();
		}
	});

	// load related plugins
	loadScript("<?=base_url()?>assets/js/plugin/datatables/jquery.dataTables.min.js", function(){
		loadScript("<?=base_url()?>assets/js/plugin/datatables/dataTables.colVis.min.js", function(){
			loadScript("<?=base_url()?>assets/js/plugin/datatables/dataTables.tableTools.min.js", function(){
				loadScript("<?=base_url()?>assets/js/plugin/datatables/dataTables.bootstrap.min.js", function(){
					loadScript("<?=base_url()?>assets/js/plugin/datatable-responsive/datatables.responsive.min.js", pagefunction)
				});
			});
		});
	});
</script>