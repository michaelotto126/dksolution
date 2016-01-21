<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Add Manual Transaction</h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/utilities/manual-transaction"); ?>" method="post" class="form-horizontal">
									<div class="form-group col-lg-8 m-l"><h4>Customer</h4></div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('first_name') ? 'has-error' : NULL; ?>">
									  <label for="buyer-fname" class="col-lg-4 control-label">First Name</label>
										<div class="col-lg-4">
										  <input id="buyer-fname" type="text" name="first_name" value="<?php echo Input::old('first_name'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('last_name') ? 'has-error' : NULL; ?>">
									  <label for="buyer-lname" class="col-lg-4 control-label">Last Name</label>
										<div class="col-lg-4">
										  <input id="buyer-lname" type="text" name="last_name" value="<?php echo Input::old('last_name'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('email') ? 'has-error' : NULL; ?>">
									  <label for="buyer-email" class="col-lg-4 control-label">Email</label>
										<div class="col-lg-4">
										  <input id="buyer-email" type="text" name="email" value="<?php echo Input::old('email'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('password') ? 'has-error' : NULL; ?>">
									  <label for="buyer-password" class="col-lg-4 control-label">Password <br><small>(May leave blank, if adding an OTO)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  <input id="buyer-password" type="text" name="password" value="<?php echo Input::old('password'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 m-l"><h4>Transaction Details</h4></div>

									<div class="form-group col-lg-8 <?php echo $errors->first('product_id') ? 'has-error' : NULL; ?>">
										<label for="mt_product_id" class="col-lg-4 control-label">Choose Product</label>
										<div class="col-lg-4">
										  <select id="mt_product_id" name="product_id" class="form-control">
                                            	<option value="">Select</option>
                                            	<?php if($products): ?>
                                            		<?php foreach($products as $product): ?>
                                            			<option value="<?php echo $product->id; ?>" <?php echo (Input::old('product_id') == $product->id ? 'selected="selected"' : NULL); ?>><?php echo $product->name; ?></option>
                                            		<?php endforeach; ?>
                                            	<?php endif; ?>
											</select>
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('plan_id') ? 'has-error' : NULL; ?>">
										<label for="mt_plan_id" class="col-lg-4 control-label">Choose Plan</label>
										<div class="col-lg-4">
										  <select id="mt_plan_id" name="plan_id" class="form-control">
                                            	<option value="">Select</option>
                                            	<?php if($plans): ?>
                                            		<?php foreach($plans as $plan): ?>
                                            			<option value="<?php echo $plan->id; ?>" <?php echo (Input::old('plan_id') == $plan->id ? 'selected="selected"' : NULL); ?>><?php echo $plan->name; ?></option>
                                            		<?php endforeach; ?>
                                            	<?php endif; ?>
											</select>
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('pay_id') ? 'has-error' : NULL; ?>">
									  <label for="pay_id" class="col-lg-4 control-label">Transaction ID <br><small>(Leave blank to auto-generate)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  <input id="pay_id" type="text" name="pay_id" value="<?php echo Input::old('pay_id'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('stripe_token') ? 'has-error' : NULL; ?>">
									  <label for="stripe_token" class="col-lg-4 control-label">Stripe Customer ID <br><small>(Leave blank if not available)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  <input id="stripe_token" type="text" name="stripe_token" value="<?php echo Input::old('stripe_token'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('paypal_sub_id') ? 'has-error' : NULL; ?>">
									  <label for="paypal_sub_id" class="col-lg-4 control-label">PayPal Subscription ID <br><small>(Leave blank if not available)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  <input id="paypal_sub_id" type="text" name="paypal_sub_id" value="<?php echo Input::old('paypal_sub_id'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('amount') ? 'has-error' : NULL; ?>">
									  <label for="amount" class="col-lg-4 control-label">Amount ($) <br><small>(May put $0 if free user)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  <input id="amount" type="text" name="amount" value="<?php echo Input::old('amount'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('affiliate_id') ? 'has-error' : NULL; ?>">
										<label for="mt_plan_id" class="col-lg-4 control-label">Choose Affiliate</label>
										<div class="col-lg-4">
										  <select id="mt_plan_id" name="affiliate_id" class="form-control">
                                            	<option value="">Select</option>
                                            	<?php if($affiliates): ?>
                                            		<?php foreach($affiliates as $affiliate): ?>
                                            			<option value="<?php echo $affiliate->id; ?>" <?php echo (Input::old('affiliate_id') == $affiliate->id ? 'selected="selected"' : NULL); ?>><?php echo $affiliate->name; ?></option>
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