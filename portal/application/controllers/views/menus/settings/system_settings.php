<?php
/**
 * @Author: Robert Ram Bolista
 */

?>
<input type="button" class="refresh_settings" style="display: none">
<section id="widget-grid" class="">
<article class="col-sm-12 col-md-12 col-lg-12">
<div class="well" id="settings_add" style="display:none;"></div>
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
		<span class="widget-icon"> <i class="fa fa-wrench"></i> </span>
		<h2>System Settings</h2>
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

			<table id="ram" class="table table-striped table-bordered table-hover" width="100%" >

		        <thead>
		        	<tr>
                        <th width="3%"></th>
	                    <th data-class="expand" width="24%">Name</th>
	                    <th data-class="expand" width="62%">Description</th>
	                    <th data-class="expand" width="16%">Type</th>
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

</section>
<script type="text/javascript">

	/* DO NOT REMOVE : GLOBAL FUNCTIONS!
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
        function format ( d ) {
        return '<table cellpadding="5" cellspacing="0" border="0" class="table table-hover table-condensed">'+
		        '<tr>'+
		            '<td style="width:100px">Code:</td>'+
		            '<td>'+d.code+'</td>'+
		        '</tr>'+(
                (d.value!=null)?
                '<tr>'+
		            '<td>Value:</td>'+
		            '<td>'+d.value+'</td>'+
		        '</tr>':
                '<tr>'+
		            '<td style="width:100px">Subject:</td>'+
		            '<td>'+(d.subject==null?"":d.subject)+'</td>'+
		        '</tr>'+
		        '<tr>'+
		            '<td>Email Message:</td>'+
		            '<td>'+(d.email_message==null?"":d.email_message)+'</td>'+
		        '</tr>'+
                '<tr>'+
		            '<td>Text Message:</td>'+
		            '<td>'+(d.sms_message==null?"":d.sms_message)+'</td>'+
		        '</tr>'
                )+
		        '<tr>'+
		            '<td>Action:</td>'+
		            '<td>'+d.id+'</td>'+
		        '</tr>'+
		    '</table>';
		}
			var responsiveHelper_datatable_fixed_column = undefined;
			var breakpointDefinition = {
				tablet : 1024,
				phone : 480
			};
		/* COLUMN FILTER  */

	    var otable = $('#ram').DataTable({
			"bProcessing": true,
			"sPaginationType": "full_numbers",
			"bServerSide": true,
			"sAjaxSource": "<?=site_url("datatable_controller")?>",
			"fnServerParams": function( aoData ){
				aoData.push( { "name": "function_ctrl", "value": "settings_list" },
							 { "name": "canedit", "value": "<?=$canedit?>" },
							 { "name": "candelete", "value": "<?=$candelete?>" });
			},
			"sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>>"+
			"rt"+
			"<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
			"autoWidth" : true,
			"sServerMethod": "POST",
			"columns": [
                    { "data": "updated_by","bSortable": false,"bSearchable": false  },
					{ "data": "name" },
					{ "data": "description" },
					{ "data": "setting_type" }
			],"language": {
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
                $(nRow).attr("settings_id",aData['settings_id']);
                $(nRow).parent().find(".info").removeClass('info');
            }
		});
		$(".jarviswidget-refresh-btn").click(function(){
			//return false;
		});

        // Add event listener for opening and closing details
	    $('#ram tbody').on('click', 'td div.details-control', function () {
	        var tr = $(this).closest('tr');
	        var row = otable.row( tr );

	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( format(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    });

		$(".refresh_settings").click(function(){
			otable.draw();
		});

	    // custom toolbar
	    //$("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');
	    //
	    // Apply the filter
		$("#ram thead th input[type=text]").on( 'keyup change', function () {
			otable
	            .column( $(this).parent().index()+':visible' )
	            .search( this.value )
	            .draw();

	    } );
	    /* END COLUMN FILTER */
	};
	$(".add_settings").click(function(){
		add_settings("","add");
	});

	function edit_settings(settings_id){
		add_settings(settings_id,"edit");
	}

	function delete_settings(settings_id){
		confirmationmessage("<?=$this->lang->line('confirm_delete')?>","<?=$this->lang->line('button_delete')?>","<?=$this->lang->line('button_cancel')?>",function(){
			$("#modal-confirmation .close").click();
			var form_data = {
				settings_id:settings_id,
				function_ctrl:"delete_settings"
			};
			$.ajax({
				url: "<?=site_url("setup_process")?>",
				type: "POST",
				data: form_data,
				dataType:'JSON',
				success: function(msg){
					if(msg.code!=""){
						$("#modal-confirmation .close").click();
						$.sound_on = true;
						$.smallBox({
							title : "<?=$this->lang->line('error_title')?>",
							content : "<?=$this->lang->line('error_delete')?><br><?=$this->lang->line('notification_close')?>",//Refresh the whole page to see changes!
							color : "<?=$this->lang->line('color_error')?>",
							timeout: 5000,
							icon : "fa fa-warning shake animated"
						});
					}else{
						$("#modal-confirmation .close").click();
						$.sound_on = false;
						$.smallBox({
							title : "<?=$this->lang->line('success_title')?>",
							content : "<?=$this->lang->line('success_delete')?><br><?=$this->lang->line('notification_close')?>",//Refresh the whole page to see changes!<br>
							color : "<?=$this->lang->line('color_success')?>",
							timeout: 5000,
							icon : "fa fa-trash-o shake animated"
						});
					}
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

	function add_settings(settings_id,job){
		if($("#f_settings").data("settings_id")==null || $("#f_settings").data("settings_id")=="undefined"){
			var hidden_settings_id = "";
		}else{
			 var hidden_settings_id = $("#f_settings").data("settings_id");
		}
		/** checking for editing of setting while still editing another setting*/
		if((settings_id != hidden_settings_id) && $("#settings_add").css('display')=="block"){
			confirmationmessage("<?=$this->lang->line('confirm_edit')?>","<?=$this->lang->line('button_continue')?>","<?=$this->lang->line('button_cancel')?>",function(){
				$("#modal-confirmation .close").click();
				$("#settings_add").html();
				$("#settings_add").css('display', 'block');
				var form_data = {
					settings_id:settings_id,
					function_ctrl:"settings_view",
					job:job
				};
				$(".settings_add").addClass("widget-body-ajax-loading");
				$.ajax({
					url: "<?=site_url("setup_properties")?>",
					type: "POST",
					data: form_data,
					success: function(msg){
						$("#settings_add").html(msg);
						$("#settings_add").removeClass("widget-body-ajax-loading");
					}
				});
			});
		}else{
			/** for adding setting */
			$("#settings_add").html();
			$("#settings_add").css('display', 'block');
			var form_data = {
				settings_id:settings_id,
				function_ctrl:"settings_view",
				job:job
			};
			$(".settings_add").addClass("widget-body-ajax-loading");
			$.ajax({
				url: "<?=site_url("setup_properties")?>",
				type: "POST",
				data: form_data,
				success: function(msg){
					$("#settings_add").html(msg);
					$("#settings_add").removeClass("widget-body-ajax-loading");
				}
			});
		}
	}
	// load related plugins
	loadScript("<?=base_url()?>assets/js/plugin/datatables/jquery.dataTables.min.js", function(){
		loadScript("<?=base_url()?>assets/js/plugin/datatables/jquery.jeditable.js", function(){
			loadScript("<?=base_url()?>assets/js/plugin/datatables/dataTables.colVis.min.js", function(){
				loadScript("<?=base_url()?>assets/js/plugin/datatables/dataTables.tableTools.min.js", function(){
					loadScript("<?=base_url()?>assets/js/plugin/datatables/dataTables.bootstrap.min.js", function(){
						loadScript("<?=base_url()?>assets/js/plugin/datatable-responsive/datatables.responsive.min.js", pagefunction)
					});
				});
			});
		});
	});
</script>