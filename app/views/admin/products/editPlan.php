<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Edit Plan - <?php echo $plan->name . " - " . $product->name; ?></h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/products/edit-plan/$product->id/$plan->id"); ?>" method="post" class="form-horizontal">
									<div class="form-group col-lg-8 <?php echo $errors->first('name') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Plan Name</label>
										<div class="col-lg-4">
										  <input type="text" name="name" value="<?php echo Input::old('name', $plan->name); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('code') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Plan Code</label>
										<div class="col-lg-4">
										  <input type="text" name="code" value="<?php echo Input::old('code', $plan->code); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('is_oto') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">OTO</label>
										<div class="col-lg-4">
										  <input type="checkbox" name="is_oto" value="1" <?php echo (Input::old('is_oto', $plan->is_oto) ? 'checked="checked"' : NULL); ?> class="form-control" style="width:auto;">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('is_recurring') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Recurring</label>
										<div class="col-lg-4">
										  <input type="checkbox" name="is_recurring" data-section="is_recurring" value="1" <?php echo (Input::old('is_recurring', $plan->is_recurring) ? 'checked="checked"' : NULL); ?> class="form-control manageSection" style="width:auto;">
										</div>
									</div>

									<div id="is_recurring_child" class="<?php echo (Input::old('is_recurring', $plan->is_recurring) ? NULL : 'hide'); ?>">
										<div class="form-group col-lg-8">
											<label class="col-lg-2 control-label">Recurring Frequency</label>
											<div class="col-lg-4">
											  <select name="recurring_freq" class="form-control">
	                                            	<option value="1" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 1 ? 'selected="selected"' : NULL); ?>>Every 1 month</option>
													<option value="2" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 2 ? 'selected="selected"' : NULL); ?>>Every 2 months</option>
													<option value="3" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 3 ? 'selected="selected"' : NULL); ?>>Every 3 months</option>
													<option value="4" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 4 ? 'selected="selected"' : NULL); ?>>Every 4 months</option>
													<option value="5" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 5 ? 'selected="selected"' : NULL); ?>>Every 5 months</option>
													<option value="6" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 6 ? 'selected="selected"' : NULL); ?>>Every 6 months</option>
													<option value="7" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 7 ? 'selected="selected"' : NULL); ?>>Every 7 months</option>
													<option value="8" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 8 ? 'selected="selected"' : NULL); ?>>Every 8 months</option>
													<option value="9" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 9 ? 'selected="selected"' : NULL); ?>>Every 9 months</option>
													<option value="10" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 10 ? 'selected="selected"' : NULL); ?>>Every 10 months</option>
													<option value="11" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 11 ? 'selected="selected"' : NULL); ?>>Every 11 months</option>
													<option value="12" <?php echo (Input::old('recurring_freq', $plan->recurring_freq) == 12 ? 'selected="selected"' : NULL); ?>>Every 12 months</option>
												</select>
											</div>
										</div>

										<div class="form-group col-lg-8 <?php echo $errors->first('allow_paypal_sub') ? 'has-error' : NULL; ?>">
											<label class="col-lg-2 control-label">PayPal Sub.?</label>
											<div class="col-lg-4">
											  <input type="checkbox" name="allow_paypal_sub" value="1" <?php echo (Input::old('allow_paypal_sub', $plan->allow_paypal_sub) ? 'checked="checked"' : NULL); ?> class="form-control" style="width:auto;">
											</div>
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('has_license') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Enable License</label>
										<div class="col-lg-4">
										  <input type="checkbox" name="has_license" data-section="has_license" value="1" <?php echo (Input::old('has_license', $plan->has_license) ? 'checked="checked"' : NULL); ?> class="form-control manageSection" style="width:auto;">
										</div>
									</div>

									<div id="has_license_child" class="<?php echo (Input::old('has_license', $plan->has_license) ? NULL : 'hide'); ?>">
									<div class="form-group col-lg-8 <?php echo $errors->first('license_allowed_usage') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">License Usage</label>
										<div class="col-lg-4">
										  <input type="text" name="license_allowed_usage" value="<?php echo Input::old('license_allowed_usage', $plan->license_allowed_usage); ?>" class="form-control">
										</div>
									</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('show_at_checkout') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Show in Available Plans</label>
										<div class="col-lg-4">
										  <input type="checkbox" name="show_at_checkout" value="1" <?php echo (Input::old('has_license', $plan->show_at_checkout) ? 'checked="checked"' : NULL); ?> class="form-control" style="width:auto;">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('show_available_plans') ? 'has-error' : NULL; ?>">
										<label class="col-lg-2 control-label">Show Available Plans</label>
										<div class="col-lg-4">
										  <input type="checkbox" name="show_available_plans" value="1" <?php echo (Input::old('show_available_plans', $plan->show_available_plans) ? 'checked="checked"' : NULL); ?> class="form-control" style="width:auto;">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('has_split_pay') ? 'has-error' : NULL; ?>" id="has_split_pay_section">
										<label class="col-lg-2 control-label">Enable Split Payment</label>
										<div class="col-lg-4">
										  <input type="checkbox" name="has_split_pay" data-section="has_split_pay" value="1" <?php echo (Input::old('has_split_pay', $plan->has_split_pay) ? 'checked="checked"' : NULL); ?> class="form-control manageSection" style="width:auto;">
										</div>
									</div>

									<div id="has_split_pay_child" class="<?php echo (Input::old('has_split_pay', $plan->has_split_pay) ? NULL : 'hide'); ?>">
									<div class="form-group col-lg-8">
										<label class="col-lg-2 control-label">Total Monthly Installments</label>
										<div class="col-lg-4">
										  <select name="total_installments" class="form-control">
                                            	<option value="1" <?php echo (Input::old('total_installments', $plan->total_installments) == 1 ? 'selected="selected"' : NULL); ?>>1</option>
												<option value="2" <?php echo (Input::old('total_installments', $plan->total_installments) == 2 ? 'selected="selected"' : NULL); ?>>2</option>
												<option value="3" <?php echo (Input::old('total_installments', $plan->total_installments) == 3 ? 'selected="selected"' : NULL); ?>>3</option>
												<option value="4" <?php echo (Input::old('total_installments', $plan->total_installments) == 4 ? 'selected="selected"' : NULL); ?>>4</option>
												<option value="5" <?php echo (Input::old('total_installments', $plan->total_installments) == 5 ? 'selected="selected"' : NULL); ?>>5</option>
												<option value="6" <?php echo (Input::old('total_installments', $plan->total_installments) == 6 ? 'selected="selected"' : NULL); ?>>6</option>
												<option value="7" <?php echo (Input::old('total_installments', $plan->total_installments) == 7 ? 'selected="selected"' : NULL); ?>>7</option>
												<option value="8" <?php echo (Input::old('total_installments', $plan->total_installments) == 8 ? 'selected="selected"' : NULL); ?>>8</option>
												<option value="9" <?php echo (Input::old('total_installments', $plan->total_installments) == 9 ? 'selected="selected"' : NULL); ?>>9</option>
												<option value="10" <?php echo (Input::old('total_installments', $plan->total_installments) == 10 ? 'selected="selected"' : NULL); ?>>10</option>
												<option value="11" <?php echo (Input::old('total_installments', $plan->total_installments) == 11 ? 'selected="selected"' : NULL); ?>>11</option>
												<option value="12" <?php echo (Input::old('total_installments', $plan->total_installments) == 12 ? 'selected="selected"' : NULL); ?>>12</option>
											</select>
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('split_pay_desc') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Split Pay Description</label>
										<div class="col-lg-4">
										  <textarea name="split_pay_desc" class="form-control"><?php echo Input::old('split_pay_desc', $plan->split_pay_desc); ?></textarea>
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('price_per_installment') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Price Per Installment ($)</label>
										<div class="col-lg-4">
										  <input type="text" name="price_per_installment" value="<?php echo Input::old('price_per_installment', $plan->price_per_installment); ?>" class="form-control">
										</div>
									</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('next_page_url') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Next URL</label>
										<div class="col-lg-4">
										  <input type="text" name="next_page_url" value="<?php echo Input::old('next_page_url', $plan->next_page_url); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('price') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Price ($)</label>
										<div class="col-lg-4">
										  <input type="text" name="price" value="<?php echo Input::old('price', $plan->price); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group col-lg-8 <?php echo $errors->first('setup_fee') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Setup Fee ($)</label>
										<div class="col-lg-4">
										  <input type="text" name="setup_fee" value="<?php echo Input::old('setup_fee', $plan->setup_fee); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('stripe_id') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Stripe ID</label>
										<div class="col-lg-4">
										  <input type="text" name="stripe_id" value="<?php echo Input::old('stripe_id', $plan->stripe_id); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('infusion_id') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Infusion ID</label>
										<div class="col-lg-4">
										  <input type="text" name="infusion_id" value="<?php echo Input::old('infusion_id', $plan->infusion_id); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('description') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Description</label>
										<div class="col-lg-4">
										  <textarea name="description" class="form-control"><?php echo Input::old('description', $plan->description); ?></textarea>
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('order_btn_text_1') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Button Text 1</label>
										<div class="col-lg-4">
										  <input type="text" name="order_btn_text_1" value="<?php echo Input::old('order_btn_text_1', $plan->order_btn_text_1); ?>" class="form-control">
										</div>
									</div>
									
									<div class="form-group col-lg-8 <?php echo $errors->first('order_btn_text_2') ? 'has-error' : NULL; ?>">
									  <label class="col-lg-2 control-label">Button Text 2</label>
										<div class="col-lg-4">
										  <input type="text" name="order_btn_text_2" value="<?php echo Input::old('order_btn_text_2', $plan->order_btn_text_2); ?>" class="form-control">
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