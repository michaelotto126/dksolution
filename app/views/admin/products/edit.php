<?php echo $header; ?>
<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Edit Product - <?php echo $product->name; ?></h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/products/edit-product/$product->id"); ?>" method="post" class="form-horizontal">
									<div class="form-group col-lg-8 <?php echo $errors->first('name') ? 'has-error' : NULL; ?>">
									  <label for="select1" class="col-lg-2 control-label">Product Name</label>
										<div class="col-lg-4">
										  <input type="text" name="name" value="<?php echo Input::old('name', $product->name); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('code') ? 'has-error' : NULL; ?>">
									  <label for="select1" class="col-lg-2 control-label">Product Code</label>
										<div class="col-lg-4">
										  <input type="text" name="code" value="<?php echo Input::old('code', $product->code); ?>" class="form-control">
										</div>
									</div>
                                    
							  		<div class="form-group col-lg-8">
										<label class="col-lg-2 control-label">Product Type</label>
										<div class="col-lg-4">
										  <select name="type" class="form-control">
                                            	<option value="1" <?php echo (Input::old('type', $product->type) == 1 ? 'selected="selected"' : NULL); ?>>One Time</option>
												<option value="2" <?php echo (Input::old('type', $product->type) == 2 ? 'selected="selected"' : NULL); ?>>Subscription</option>
											</select>
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('has_license') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Enable License</label>
										<div class="col-lg-4">
										  <input type="checkbox" name="has_license" value="1" <?php echo (Input::old('has_license', $product->has_license) ? 'checked="checked"' : NULL); ?> class="form-control" style="width:auto;">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('logo_url') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Logo URL</label>
										<div class="col-lg-4">
										  <input type="text" name="logo_url" value="<?php echo Input::old('logo_url', $product->logo_url); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('landing_url') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Landing URL</label>
										<div class="col-lg-4">
										  <input type="text" name="landing_url" value="<?php echo Input::old('landing_url', $product->landing_url); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('aweber_list_id') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Aweber List ID</label>
										<div class="col-lg-4">
										  <input type="text" name="aweber_list_id" value="<?php echo Input::old('aweber_list_id', $product->aweber_list_id); ?>" class="form-control">
										</div>
									</div>
                                    
                                    <div class="form-group col-lg-8 <?php echo $errors->first('ipn_url') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">IPN URL</label>
										<div class="col-lg-4">
										  <input type="text" name="ipn_url" value="<?php echo Input::old('ipn_url', $product->ipn_url); ?>" class="form-control">
										</div>
									</div>
                                    
                                    <div class="form-group col-lg-8 <?php echo $errors->first('api_url') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">API URL</label>
										<div class="col-lg-4">
										  <input type="text" name="api_url" value="<?php echo Input::old('api_url', $product->api_url); ?>" class="form-control">
										</div>
									</div>
                                    
                                    <div class="form-group col-lg-8 <?php echo $errors->first('api_key') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">API Key</label>
										<div class="col-lg-4">
										  <input type="text" name="api_key" value="<?php echo Input::old('api_key', $product->api_key); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('head_code') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Head Scripts</label>
										<div class="col-lg-4">
										  <textarea name="head_code" style="width:700px;height:250px;" class="form-control"><?php echo Input::old('head_code', $product->head_code); ?></textarea>
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('body_code') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Body Scripts</label>
										<div class="col-lg-4">
										  <textarea name="body_code" style="width:700px;height:250px;" class="form-control"><?php echo Input::old('body_code', $product->body_code); ?></textarea>
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