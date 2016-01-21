<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Payload for - <?php echo $license_key; ?></h4></div>
							<div class="module-content">

							<?php if($payload): ?>
								<h4>Payload Data</h4>
								<p><textarea style="width:500px;height:130px;" id="clipboard_text"><?php echo $payload; ?></textarea></p>
								<p>
									<button href="javascript:void(0);" data-clipboard-target="clipboard_text" class="d_clip_button btn btn-x btn-info">Copy Payload</button>
									<a href="<?php echo url($activate_url); ?>" class="btn btn-x btn-danger">Activate License</a>
								</p>
							<?php else: ?>
								<h3>No payload was found</h3>
								<p>It seems this license key has some problem with it.</p>
							<?php endif; ?>

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