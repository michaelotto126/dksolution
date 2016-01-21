<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
						
						<div class="module no-padding">
							<div class="module-header"><h4>Utilities</h4></div>
                            
                            <div style="clear:both;"></div>
							<div class="module-content table-responsive">
								<a href="<?php echo url("admin/utilities/manual-transaction"); ?>" class="btn btn-success pull-left up-mrg" style="padding:30px 20px;">Add Manual Transaction</a>
								<a href="<?php echo url("admin/utilities/generate-license"); ?>" class="btn btn-success pull-left up-mrg" style="margin-left:10px;padding:30px 20px;">Generate License</a>
								<a href="<?php echo url("admin/utilities/card-update-url"); ?>" class="btn btn-success pull-left up-mrg" style="margin-left:10px;padding:30px 20px;">Generate Card Update URL</a>
							</div>
						</div>

					</div>
					<!-- /Full width table -->
                    
					
                    
				</div>
				
				<div class="row">
					
					
				</div>

				

			</section>
			<!-- /Main Content -->
<?php echo $footer; ?>