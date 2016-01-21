<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Generate Card Update URL</h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/utilities/card-update-url"); ?>" method="post" class="form-horizontal">

									<div class="form-group col-lg-12 m-l"><h4>Customer</h4></div>

									<?php if(Session::has('updateUrl')): ?>
										<div class="form-group col-lg-12 m-l">
											<textarea style="width:100%;height:150px;"><?php echo Session::get('updateUrl'); ?></textarea> <br><br>
											<button data-clipboard-text="<?php echo Session::get('updateUrl'); ?>" class="d_clip_button btn btn-xs btn-info">Copy Link</button>
										</div>
									<?php endif; ?>

									<div class="form-group col-lg-8 <?php echo $errors->first('email') ? 'has-error' : NULL; ?>">
									  <label for="transaction_id" class="col-lg-4 control-label">Email<br><small>(Email that customer used to buy)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  <input type="text" name="email" value="<?php echo Input::old('email'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('code') ? 'has-error' : NULL; ?>">
									  <label for="transaction_id" class="col-lg-4 control-label">Product<br><small>(What was bought?)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  	<select  name="code" class="form-control">
                                            	<option value="">Select</option>
                                            	<?php if($products): ?>
                                            		<?php foreach($products as $product): ?>
                                            			<option value="<?php echo $product->code; ?>" <?php echo (Input::old('code') == $product->code ? 'selected="selected"' : NULL); ?>><?php echo $product->name; ?></option>
                                            		<?php endforeach; ?>
                                            	<?php endif; ?>
											</select>
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