<?php
/**
 * @Author: Robert Ram Bolista
 */
?>

<link rel="stylesheet" href="<?= base_url(); ?>assets/css/bootstrap-iconpicker.min.css"/>
<div class="widget-body">
    <div class="edit_role" style="display: <?= ($job == "add" ? "block" : "none") ?>" roleid='<?= $roleid ?>'>
        <form class="form-horizontal show-grid" id="edit_role" name="edit_role" method="post">
            <fieldset>
                <div class="form-group">
                    <label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Code</label>

                    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
                        <div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <input type="text" class="form-control" name="code" value="<?= $code ?>">
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <div class="form-group">
                    <label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Description</label>

                    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
                        <div class="input-group input-group col-xs-6 col-sm-6 col-md-6 col-lg-6">
                            <input class="form-control" placeholder="Description" type="text" value="<?= $description ?>" name="description">
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <div class="form-group">
                    <label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Icon</label>

                    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
                        <div class="input-group">
                            <button class="btn btn-default" data-iconset="fontawesome" data-icon="<?= $icon ?>"
                                    role="iconpicker" name='icon'></button>
                            <p class="note"><strong>Note:</strong> 'Refresh the whole page to take effect.'</p>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <div class="form-group">
                    <label class="control-label col-xs-3 col-sm-3 col-md-3 col-lg-3">Advance Properties</label>

                    <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
                        <div class="input-group">
                            <button class="btn <?= $setup['button_color'] ?> <?= $setup['button_txt_color'] ?> advance"
                                    type="button">
                                Click here to see advance properties
                            </button>
                        </div>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <div class="form-group">
                    <!-- widget div-->
                    <div class="col-md-12">

                        <!-- widget content -->
                        <div>
                            <?= form_multiselect('user_available[]', $user_list, $user_in_role_list, "id='user_available'"); ?>
                        </div>
                        <!-- end widget content -->

                    </div>
                    <!-- end widget div -->
                </div>
            </fieldset>
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn <?= $setup['button_color'] ?> <?= $setup['button_txt_color'] ?> cancel"
                                type="button" name="cancel">
                            <i class="fa fa-ban"></i>
                            Cancel
                        </button>
                        <button class="btn <?= $setup['button_color'] ?> <?= $setup['button_txt_color'] ?> save"
                                type="button" name="save">
                            <i class="fa fa-save"></i>
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="view_role" style="display: <?= ($job == "add" ? "none" : "block") ?>">
        <form class="form-horizontal">
            <fieldset>
                <!--<legend><?= $roleid ?></legend>-->
                <div class="form-group">
                    <label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Code :</label>

                    <div class="col-md-6">
                        <label class="col-md-12 control-label" style="text-align:left;"><?= $code ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Description :</label>

                    <div class="col-md-6">
                        <label class="col-md-12 control-label" style="text-align:left;"><?= $description ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Icon :</label>

                    <div class="col-md-6">
                        <label class="col-md-12 control-label" style="text-align:left;"><i
                                class="fa <?= $icon ?> fa-2x"></i></label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Assigned User(s) :</label>

                    <div class="col-md-6">
                        <label class="col-md-12 control-label"
                               style="text-align:left;"><?= $assigned_user->assigned_user ?></label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-4 col-sm-4 col-md-4 col-lg-4 control-label">Advance Properties :</label>

                    <div class="col-md-6">
                        <button class="btn <?= $setup['button_color'] ?> <?= $setup['button_txt_color'] ?> advance_view"
                                type="button">
                            Click here to see advance properties
                        </button>
                    </div>
                </div>
            </fieldset>
            <?php if ($canedit || $candelete) { ?>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if ($canedit) { ?>
                                <button class="btn <?= $setup['button_color'] ?> <?= $setup['button_txt_color'] ?> edit"
                                        type="button">
                                    <i class="fa fa-edit"></i>
                                    <?=$this->lang->line('button_edit')?>
                                </button>
                            <?php }
                            if ($candelete) {
                                if ($fixed != "YES") {
                                    ?>
                                    <button
                                        class="btn <?= $setup['button_color'] ?> <?= $setup['button_txt_color'] ?> delete"
                                        type="button">
                                        <i class="fa fa-trash-o"></i>
                                        Delete Role
                                    </button>
                            <?php
                                }
                            } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </form>
    </div>

    <a id="advance_properties" href="#advance-properties" data-toggle="modal" data-backdrop="static"
       data-keyboard="false"></a>

    <div id="advance-properties" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style="width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class='widget-header'><h3><i class='fa fa-list-alt'></i> Advance
                            Properties <?= ($description ? "($description)" : "") ?></h3></div>
                </div>
                <div class="modal-body">
                    <?= $role_edit_advance ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class='fa fa-times'></i>&nbsp;
                        close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <a id="advance_properties_view" href="#advance-properties_view" data-toggle="modal" data-backdrop="static"
       data-keyboard="false"></a>

    <div id="advance-properties_view" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" style="width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class='widget-header'><h3><i class='fa fa-list-alt'></i> Advance
                            Properties <?= ($description ? "($description)" : "") ?></h3></div>
                </div>
                <div class="modal-body">
                    <?= $role_view_advance ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class='fa fa-times'></i>&nbsp;
                        close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="<?=base_url();?>assets/js/iconset/iconset-fontawesome-4.1.0.min.js"></script>
    <script type="text/javascript" src="<?=base_url();?>assets/js/bootstrap-iconpicker.js"></script>
    <script type="text/javascript">

        $(document).ready(function () {
            $(".allchecked").click(function () {
                var syscat = $(this).attr("syscat");
                var cstat = $(this).is(":checked");
                $("input[syscat='" + syscat + "']").prop("checked", cstat);
            });
            $(".menucheck").click(function () {
                var syscat = $(this).attr("syscat");
                var uncheck = false;
                $("input[syscat='" + syscat + "']").each(function () {
                    if (!$(this).is(":checked") && !uncheck && $(this).attr("tag") != "main") uncheck = true;
                });
                $("input[syscat='" + syscat + "'][tag='main']").prop("checked", !uncheck);
            });

            function figurestat() {
                $("input[tag='main']").each(function () {
                    var syscat = $(this).attr("syscat");
                    var uncheck = false;

                    $("input[syscat='" + syscat + "']").each(function () {
                        if (!$(this).is(":checked") && !uncheck && $(this).attr("tag") != "main") uncheck = true;
                    });
                    $(this).prop("checked", !uncheck);

                });
            }

            figurestat();
            $('.advance').click(function () {
                $("#advance_properties").click();
            });

            $('.advance_view').click(function () {
                $("#advance_properties_view").click();
            });

            function load_blank_properties_role() {
                var form_data = {
                    roleid: "<?=$roleid?>",
                    function_ctrl: "role_view"
                };
                $(".role_properties").addClass("widget-body-ajax-loading");
                $.ajax({
                    url: "<?=site_url("manage_controller")?>",
                    type: "POST",
                    data: form_data,
                    success: function (msg) {
                        $("#role_properties").html("<div style='text-align: center' id='loading'><p>Select a role to display its properties.</p></div>");
                        $("#user_dt").find(".info").removeClass('info');
                        $(".role_properties").removeClass("widget-body-ajax-loading");
                    }
                });
            }

            function load_role_form() {

                var form_data = {
                    roleid: "<?=$roleid?>",
                    function_ctrl: "role_view",
                    canedit: '<?=$canedit?>',
                    candelete: '<?=$candelete?>'
                };
                $(".role_properties").addClass("widget-body-ajax-loading");
                $.ajax({
                    url: "<?=site_url("manage_controller")?>",
                    type: "POST",
                    data: form_data,
                    success: function (msg) {
                        $("#role_properties").html(msg);
                        $(".role_properties").removeClass("widget-body-ajax-loading");
                    }
                });
            }

            /** edit properties */
            $(".edit").click(function () {
                $(".view_role").css('display', 'none');
                $(".edit_role").css('display', 'block');
            });

            $(".delete").click(function () {
                confirmationmessage("Are you sure you want to continue?", "Yes", "Cancel", function () {
                    $("#modal-confirmation .close").click();
                    var form_data = {
                        roleid: "<?=$roleid?>",
                        function_ctrl: "delete_role"
                    };
                    $.ajax({
                        url: "<?=site_url("manage_controller")?>",
                        type: "POST",
                        data: form_data,
                        success: function (msg) {
                            $("#modal-confirmation .close").click();
                            $.sound_on = false;
                            /*$.smallBox({
                                title: "Successfully deleted!",
                                content: "Refresh the whole page to see changes!<br>This message will close in 5 seconds.",
                                color: "#5384AF",
                                timeout: 5000,
                                icon: "fa fa-trash-o"
                            });*/
                            load_blank_properties_role();
                            $(".refresh_role").click();
                        }
                    });
                }, function () {
                    // if you answer no
                });
            });
            /** reset and go back to displaying of properties */
            $(".cancel").click(function () {
                confirmationmessage("Confirm you want to stop editing. Unsave changes will be lost.", "Continue", "No", function () {
                    $("#modal-confirmation .close").click();
                    $(".view_role").css('display', 'block');
                    $(".edit_role").css('display', 'none');
                    if ("<?=$job?>" == "add") {
                        load_blank_properties_role();
                    } else {
                        load_role_form();
                    }
                }, function () {
                    // if you answer no
                });
            });

            /** save category */
            $(".save").click(function () {
                var accesslist = "";
                var lastroot = "";
                $(".menulist").each(function () {
                    if ($(this).find(".menucheck:eq(0)").is(":checked")) {
                        accesslist += accesslist ? "," : "";
                        accesslist += $(this).attr("roleid");
                        accesslist += ":";
                        accesslist += $(this).attr("menucatid");
                        accesslist += ":";
                        accesslist += $(this).find(".menucheck:eq(0)").is(":checked") ? 1 : 0;
                        accesslist += ":";
                        accesslist += $(this).find(".menucheck:eq(1)").is(":checked") ? 1 : 0;
                        accesslist += ":";
                        accesslist += $(this).find(".menucheck:eq(2)").is(":checked") ? 1 : 0;
                        accesslist += ":";
                        accesslist += $(this).find(".menucheck:eq(3)").is(":checked") ? 1 : 0;
                    }
                });
                $('#edit_role').data('bootstrapValidator').validate()
                if ($('#edit_role').data('bootstrapValidator').isValid()) {
                    confirmationmessage("Are you sure you want to save it?", "Yes", "No", function () {
                        var form_data = $("#edit_role").serialize();
                        form_data += '&roleid=' + '<?=$roleid?>';
                        form_data += '&function_ctrl=' + 'save_role';
                        form_data += '&user_availables=' + $('[name="user_available[]"]').val();
                        form_data += '&accesslist=' + accesslist;
                        $.ajax({
                            url: "<?=site_url("manage_controller")?>",
                            type: "POST",
                            data: form_data,
                            success: function (msg) {
                                //alert(msg);
                                $("#modal-confirmation .close").click();
                                $.sound_on = false;
                                $.smallBox({
                                    title: "Successfully Saved!",
                                    content: "Refresh the whole page to see changes!<br>This message will close in 5 seconds.",
                                    color: "#5384AF",
                                    timeout: 5000,
                                    icon: "fa fa-save"
                                });
                                load_blank_properties_role();
                                $(".refresh_role").click();
                            }
                        });
                    }, function () {
                        // if you answer no
                    });
                }
            });

            var checkform = function () {

                $('#edit_role').bootstrapValidator({
                    framework: 'bootstrap',
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        code: {
                            feedbackIcons: false,
                            validators: {
                                notEmpty: {
                                    message: 'Code is required.'
                                }
                            }
                        },
                        description: {
                            feedbackIcons: false,
                            validators: {
                                notEmpty: {
                                    message: 'Description is required.'
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
            loadScript("<?=base_url();?>assets/js/plugin/bootstrap-duallistbox/jquery.bootstrap-duallistbox.min.js", user_available);
            function user_available() {
                var user_available = $('#user_available').bootstrapDualListbox({
                    nonSelectedListLabel: 'User(s) Available',
                    selectedListLabel: 'User(s) Assigned',
                    preserveSelectionOnMove: 'moved',
                    moveOnSelect: false
                });
            }


        });
    </script>