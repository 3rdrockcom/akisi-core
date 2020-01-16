<?php
/**
 * @Author: Robert Ram Bolista
 */
?>
<!-- Widget ID (each widget will need unique ID)-->
<input type="button" class="refresh_menu" style="display: none">
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
		<span class="widget-icon"> <i class="fa fa-list"></i> </span>
		<h2>Menus</h2>
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

			<table id="menu_dt" class="table table-striped table-bordered table-hover" width="100%" >

		        <thead>
					<!--
		        	<tr>
						<th class="hasinput" >
							<input type="text" class="form-control" placeholder="Filter Title" />
						</th>
						<th class="hasinput" >
							<input type="text" class="form-control" placeholder="Filter Status" />
						</th>
						<th class="hasinput" >
							<input type="text" class="form-control" placeholder="Filter Arrange" />
						</th>
					</tr>
					-->
					<tr>
	                    <th data-class="expand" class="col-xs-4 col-sm-4 col-md-4 col-lg-4">Title</th>
	                    <th data-class="expand" class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Status</th>
	                    <th data-class="expand" class="col-xs-2 col-sm-2 col-md-2 col-lg-2">Arrange</th>
	                    <th data-class="expand" class="col-xs-3 col-sm-3 col-md-3 col-lg-3">Assigned Category</th>
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
	<header class="menu_properties">
		<span class="widget-icon"> <i class="fa fa-edit"></i> </span>
		<h2>Properties</h2>

	</header>
	<div class="row" id="menu_properties">
		<div style="text-align: center">
			<p>Select a menu to display its properties.</p>
		</div>
	</div>
</div>
</article>
</section>
<script type="text/javascript">

	/* DO NOT REMOVE : GLOBAL FUNCTIONS!
	 *
	 * pageSetUp(); WILL CALL THE FOLLOWING FUNCTIONS
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
	var pagemenu = function() {
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
	    var menutable = $('#menu_dt').DataTable({
			"bProcessing": true,
			//"sPaginationType": "full_numbers",
			"bServerSide": true,
			"sAjaxSource": "<?=site_url("datatable_controller")?>",
			"fnServerParams": function( aoData ){
				aoData.push( { "name": "function_ctrl", "value": "menu_list" });
			},
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>>"+
			"rt"+
			"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"sServerMethod": "POST",
			"columns": [
					{ "data": "title" },
					{ "data": "status" },
					{ "data": "arranged" },
					{ "data": "description" }
			],
			"oLanguage": {
				"sSearch": '<span class="input-group-addon"><i class="fa fa-search"></i></span>',
			},
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
								$(nRow).css('cursor', 'pointer');
								$(nRow).click(function(){
									objrow = $(this);
									$(this).addClass('info');
									/** check first if the user is in the middle of editing */
									if($("#menu_properties").find(".edit_menu_form").css('display')=="block" && $("#menu_properties").find(".edit_menu_form").attr("syscatid")!=aData['menuid']){
										confirmationmessage("Confirm you want to stop editing. Unsave changes will be lost.","Continue","Cancel",function(){
											$("#modal-confirmation .close").click();
											$(nRow).parent().find(".info").removeClass('info');
				                            objrow.addClass('info');
											view_menu(aData['menuid']);
										},function(){ // if you answer no
											objrow.removeClass('info');
											$("#modal-confirmation .close").click();
											return false;
									    });
									}else if($("#menu_properties").find(".edit_menu_form").attr("syscatid")==aData['menuid']){
										return false;
									}else{
										$(nRow).parent().find(".info").removeClass('info');
			                            $(this).addClass('info');
			                            view_menu(aData['menuid']);
									}
								});
                            }

	    });
		$(".jarviswidget-refresh-btn").click(function(){
			//return false;
		});
		$(".refresh_menu").click(function(){
			menutable.draw();
		});

	    // custom toolbar
	    //$("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');
	    //
	    // Apply the filter
		$("#menu_dt thead th input[type=text]").on( 'keyup change', function () {
			menutable
	            .column( $(this).parent().index()+':visible' )
	            .search( this.value )
	            .draw();

	    } );
	    /* END COLUMN FILTER */
	};
	function view_menu(menu_id){
		var form_data = {
				menuid:menu_id,
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

	// load related plugins
	loadScript("<?=base_url();?>assets/js/plugin/datatables/jquery.dataTables.min.js", function(){
		loadScript("<?=base_url();?>assets/js/plugin/datatables/dataTables.colVis.min.js", function(){
			loadScript("<?=base_url();?>assets/js/plugin/datatables/dataTables.tableTools.min.js", function(){
				loadScript("<?=base_url();?>assets/js/plugin/datatables/dataTables.bootstrap.min.js", function(){
					loadScript("<?=base_url();?>assets/js/plugin/datatable-responsive/datatables.responsive.min.js", pagemenu)
				});
			});
		});
	});
</script>