<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Confirm Cancellation of Subscription</h4></div>
							<div class="module-content">

								<?php if($is_sub_active): ?>
								<h3>Are you sure to cancel subscription?</h3>
								<p>Warning: If you proceed with cancellation, this action cannot be undone.</p>

								<form action="<?php echo url("admin/transactions/cancel-subscription/$transaction->id/$at_period_end"); ?>" method="post">
									
									<?php if(!empty($stripe_customer_id)): ?>
										<input type="hidden" name="stripe_customer_id" value="<?php echo $stripe_customer_id; ?>" />
									<?php endif; ?>

									<input type="hidden" name="subscription_id" value="<?php echo $subscription_id; ?>" />
									<input type="hidden" name="pay_method" value="<?php echo $pay_method; ?>" />
									<input type="submit" value="Yes, Cancel Subscription" class="btn btn-x btn-danger">
									<a href="<?php echo url("admin/transactions/detail/$transaction->id"); ?>" class="btn btn-x btn-primary">Cancel</a>
								</form>
								<?php else: ?>
									<h3>Oops!</h3>
									<p>It seems this subscription has already been cancelled.</p>

									<a href="<?php echo url("admin/transactions/detail/$transaction->id"); ?>" class="btn btn-x btn-primary">Go back</a><br>
								<?php endif; ?>
								
								<br>
								<h4>Subscription Details</h4>
								<table class="table">
									<tbody>
										<tr>
											<th>Payment Method</th>
											<td><?php echo $pay_method; ?></td>
										</tr>
										<tr>
											<th>Subscription ID</th>
											<td><?php echo $subscription_id; ?></td>
										</tr>
										<tr>
											<th>Date</th>
											<td><?php echo date("m/d/Y", strtotime($transaction->created_at)); ?></td>
										</tr>
										<tr>
											<th>Product Name</th>
											<td><?php echo $transaction->purchase->product->name; ?></td>
										</tr>
										<tr>
											<th>Plan Name</th>
											<td><?php echo $transaction->plan->name; ?></td>
										</tr>
										<tr>
											<th>Amount</th>
											<td>$<?php echo $transaction->amount; ?></td>
										</tr>
										<tr>
											<th>Affiliate</th>
											<td><?php echo (!empty($transaction->purchase->affiliate->name) ? $transaction->purchase->affiliate->name : "No Affiliate"); ?></td>
										</tr>
										<tr>
											<th>Customer</th>
											<td><?php echo $transaction->purchase->buyer->first_name . " " . $transaction->purchase->buyer->last_name; ?> (<?php echo $transaction->purchase->buyer->email; ?>)</td>
										</tr>
									</tbody>
								</table>

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