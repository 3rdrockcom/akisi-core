<?php
/**
 * @Author: Robert Ram Bolista
 */
?>
<!-- checking of greed border
<style>
	.show-grid [class^="col-"] {
		background-color: rgba(61, 106, 124, 0.15);
		border: 1px solid rgba(61, 106, 124, 0.2);
	}
</style>
-->

<a id="confirmationmessage" href="#modal-confirmation" data-toggle="modal" data-backdrop="static" data-keyboard="false"></a>
<div id="modal-confirmation" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button id="closeconfirmation" type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i><?=$this->lang->line('confirm_title')?></h4>
            </div>
            <div class="modal-body">
                Message here.....
             </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->lang->line('button_yes')?></button>
                <button type="button" class="btn btn-primary"><?=$this->lang->line('button_no')?></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function confirmationmessage(message,okbutton,cancelbutton,obj_function,obj_cancel){
	  $("#confirmationmessage").click();
	  var obj = $("#modal-confirmation").find(".modal-content");
	  obj.find(".modal-body").html(message);
	  obj.find(".btn-default").html(cancelbutton).unbind("click").on("click",obj_cancel);
	  obj.find(".btn-primary").html(okbutton).unbind("click").on("click",obj_function);
	}
</script>