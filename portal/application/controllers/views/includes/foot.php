  </div>
	<!-- END MAIN CONTENT -->

</div>
<!-- END MAIN PANEL -->

<!-- ==========================CONTENT ENDS HERE ========================== -->

<?php
	//include("inc/footer.php");
?>

</body>
<script>
		
    <?php
    /** This function check the session every 10 seconds */
    if($this->session->userdata("logged_in")){
    ?>
           $(document).ready(function() {
             checkSessionTimeEvent = setInterval("checkphpsession()",5*1000);
           });
           function checkphpsession(){
             $.ajax({
                url: "<?=site_url("main/sessionchecker")?>",
                type: "POST",
                success: function(msg){
                     if($(msg).find("result").text()==1){
                        location.href = "<?=base_url()?>";
                     }
                }
             });
           }
		<?php }?>
		checkURL();
		
    </script>

</html>
<?php // if (isset($js_file)) {echo '<script src="../page_assets/js/' . $js_file .'"></script>';}
