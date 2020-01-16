<?php

$access=array();
$access['canread']   = $canread;
$access['canadd']    = $canadd;
$access['canedit']   = $canedit;
$access['candelete'] = $candelete;
?>
<div class="row">
	<section id="widget-grid" class="">
		<!-- NEW WIDGET START -->
		<article class="col-sm-12 col-md-12 col-lg-12">

			<!-- Widget ID (each widget will need unique ID)-->
			<div class="jarviswidget" id="menucategory" 
				data-widget-colorbutton="false"
				data-widget-editbutton="false"
				data-widget-togglebutton="false"
				data-widget-deletebutton="false"
				data-widget-fullscreenbutton="false"
				data-widget-custombutton="false"
				data-widget-collapsed="false"
				data-widget-sortable="false">

				<header>
					<ul class="nav nav-tabs pull-left in ">
						<li class="active">
							<a data-toggle="tab" href="#menu"> <i class="fa fa-list"></i> <span class="hidden-mobile hidden-tablet"> Menus </span> </a>
						</li>
						<li>
							<a data-toggle="tab" href="#category"> <i class="fa fa-tags"></i> <span class="hidden-mobile hidden-tablet"> Category </span> </a>
						</li>
					</ul>
				</header>
				<!-- widget div-->
				<div>

					<!-- widget edit box -->
					<div class="jarviswidget-editbox">
						<!-- This area used as dropdown edit box -->

					</div>
					<!-- end widget edit box -->

					<!-- widget content -->
					<div class="widget-body">

						<div class="tab-content">
							<div class="tab-pane  active" id="menu">
								<?php $this->load->view("admin/menu",$access);?>
							</div>
							<div class="tab-pane" id="category">
								<?php $this->load->view("admin/category",$access);?>
							</div>

						</div>

					</div>
					<!-- end widget content -->

				</div>
				<!-- end widget div -->

			</div>
			<!-- end widget -->

		</article>
		<!-- WIDGET END -->
	</section>
</div>