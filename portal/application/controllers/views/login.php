<?php
require_once 'includes/init.php';

$page_title = "Login";

/* ---------------- END PHP Custom Scripts ------------- */

//include header
//you can add your custom css in $page_css array.
//Note: all css files are inside css/ folder
$page_css[] = "";
$no_main_header = true;
#$page_body_prop = array("id"=>"extr-page", "class"=>"animated fadeInDown");
include("includes/inc/header.php");
?>
<!-- ==========================CONTENT STARTS HERE ========================== -->

<div id="main" role="main">

	<!-- MAIN CONTENT -->
	<div id="content" class="container">

		<div class="row">
			<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"></div>
			<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
				<div class="well no-padding">
					<form action="" id="login-form" class="smart-form client-form">
						<header>
							Sign In
						</header>

						<fieldset>
							<section>
                                <div id="status"></div>
								<label class="label">Username</label>
								<label class="input"> <i class="icon-append fa fa-user"></i>
									<input type="text" name="fusername" id="fusername">
									<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> Please enter username</b></label>
							</section>

							<section>
								<label class="label">Password</label>
								<label class="input"> <i class="icon-append fa fa-lock"></i>
									<input type="password" name="fpassword" id="fpassword">
									<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> Enter your password</b> </label>
							</section>

						</fieldset>
						<footer>
							<button type="button" class="btn btn-primary" id="logsubmit">
								Sign in
							</button>
						</footer>
					</form>
				</div>
			</div>
			<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4"></div>
		</div>
	</div>

</div>
<!-- END MAIN PANEL -->
<!-- ==========================CONTENT ENDS HERE ========================== -->

<?php
	//include required scripts
	include("includes/inc/scripts.php");
?>

<!-- PAGE RELATED PLUGIN(S) -->

<script type="text/javascript">
	runAllForms();

	$(function() {
		// Validation
		$("#login-form").validate({
			// Rules for form validation
			rules : {
				fusername : {
					required : true
					//email : true
				},
				fpassword : {
					required : true,
					minlength : 3,
					maxlength : 20
				}
			},

			// Messages for form validation
			messages : {
				fusername : {
					required : 'Please enter your Username'
					//email : 'Please enter a VALID username'
				},
				fpassword : {
					required : 'Please enter your password'
				}
			},

			// Do not change code below
			errorPlacement : function(error, element) {
				error.insertAfter(element.parent());
			}
		});
	});
 $('#logsubmit').click(function(){
        if($("#login-form").valid()){
        var uname = $('#fusername').val();
        var upass = $('#fpassword').val();
        var form_data = {
            fusername: uname,
            fpassword: upass,
            function_ctrl: 'login',
            verify: "1"
        }
            $.ajax({
                url: "<?=site_url('login')?>",
                type: "POST",
                data: form_data,
                success: function(msg){
                	  if($(msg).find("result").text()!=1){
                        $('#status').hide().html('<div class="alert alert-danger fade in" id="wrongpass">'+$(msg).find("message").text()+'</div> ').fadeIn(1000);
                        $("body").html();
                        $('#fpassword').focus();
                        $('#fpassword').select();
                      }else{
                        window.location.href = window.location;
                      }
                }
            });

        return false;
        }
});

$("#fusername").focus();
$("#fusername,#fpassword").keypress(function(e){
    if(e.keyCode==13){
      $("#logsubmit").click();
    }
});
</script>

<?php
	//include footer
	//include("includes/google-analytics.php");
?>