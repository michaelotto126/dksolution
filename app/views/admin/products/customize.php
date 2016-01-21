<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Checkout page customization - <?php echo $product->name; ?></h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/products/customize/$product->id"); ?>" method="post" class="form-horizontal">
									<div class="form-group col-lg-8 <?php echo $errors->first('cta_btn_color') ? 'has-error' : NULL; ?>">
									  <label for="select1" class="col-lg-4 control-label">CTA Button Color</label>
										<div class="col-lg-4">
										  <input type="text" name="cta_btn_color" value="<?php echo Input::old('cta_btn_color', (!empty($customize->cta_btn_color) ? $customize->cta_btn_color : NULL)); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('cta_btn_hover_color') ? 'has-error' : NULL; ?>">
									  <label for="select1" class="col-lg-4 control-label">CTA Hover Button Color</label>
										<div class="col-lg-4">
										  <input type="text" name="cta_btn_hover_color" value="<?php echo Input::old('cta_btn_hover_color', (!empty($customize->cta_btn_hover_color) ? $customize->cta_btn_hover_color : NULL)); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('background_color') ? 'has-error' : NULL; ?>">
									  <label for="select1" class="col-lg-4 control-label">Background Color</label>
										<div class="col-lg-4">
										  <input type="text" name="background_color" value="<?php echo Input::old('background_color', (!empty($customize->background_color) ? $customize->background_color : NULL)); ?>" class="form-control">
										</div>
									</div>
                                    
							  		<div class="form-group col-lg-8 <?php echo $errors->first('sidebar_text') ? 'has-error' : NULL; ?>">
										<label class="col-lg-4 control-label">Sidebar Text</label>
										<div class="col-lg-4">
										  <textarea name="sidebar_text" style="width:700px;height:250px;" class="form-control"><?php echo Input::old('sidebar_text', (!empty($customize->sidebar_text) ? $customize->sidebar_text : NULL)); ?></textarea>
										</div>
									</div>

									<div class="form-group">
										<div class="col-lg-offset-3a col-lg-9">
											<input type="submit" value="Submit" class="btn btn-primary" />
										</div>
									</div>

								</form>

							</div>
						</div>
                    </div>
					<!-- /Full width table -->
                    
					
                    
				</div>
				
				<div class="row"></div>

			</section>
			<!-- /Main Content -->
<?php echo $footer; ?>