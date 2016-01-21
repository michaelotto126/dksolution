<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Transaction Detail</h4></div>
							<div class="module-content table-responsive">
								<table class="table">
									<tbody>
										<tr>
											<th>Transaction ID</th>
											<td><?php echo $transaction->pay_id; ?></td>
										</tr>
										<tr>
											<th>InfusionSoft ID</th>
											<td><a href="https://fo123.infusionsoft.com/Job/manageJob.jsp?view=edit&amp;ID=<?php echo $transaction->invoice_id; ?>" target="_blank"><?php echo $transaction->invoice_id; ?></a></td>
										</tr>
										<tr>
											<th>Payment Gateway</th>
											<td><?php echo $pay_method; //($transaction->purchase->pay_method == 1 ? "Stripe" : "PayPal"); ?></td>
										</tr>
										<tr>
											<th>Buyer IP</th>
											<td><?php echo $transaction->buyer_ip ? $transaction->buyer_ip : "-"; ?></td>
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
										<tr>
											<th>Status</th>
											<td><?php echo ($transaction->is_refunded ? "Refunded" : "Paid"); ?></td>
										</tr>
										<?php if($transaction->is_refunded): ?>
										<tr>
											<th>Refund Date</th>
											<td><?php echo date("m/d/Y", strtotime($transaction->updated_at)); ?></td>
										</tr>
										<?php endif; ?>
										<?php if($transaction->purchase->product->type == 2): ?>
										<tr>
											<th>Recurring</th>
											<td>Yes, <a href="#">cancel future payments</a></td>
										</tr>
										<?php endif; ?>
									</tbody>
								</table>
								
								<a href="<?php echo url("admin/transactions"); ?>" class="btn btn-info">Return to Transactions</a>
								
								<?php if(!$transaction->is_refunded): ?>
									<a href="<?php echo url("admin/transactions/refund/$transaction->id"); ?>" class="btn btn-danger">Refund This Sale</a>
								<?php endif; ?>

								<?php if($transaction->plan->is_recurring AND $pay_method == 'Stripe'): ?>
									<a href="<?php echo url("admin/transactions/cancel-subscription/$transaction->id/0"); ?>" class="btn btn-danger">Cancel Subscription Immediately</a>
									<a href="<?php echo url("admin/transactions/cancel-subscription/$transaction->id/1"); ?>" class="btn btn-danger">Cancel Subscription at Period End</a>
								<?php endif; ?>

								<?php if($transaction->plan->is_recurring AND $pay_method == 'PayPal'): ?>
									<a href="<?php echo url("admin/transactions/cancel-subscription/$transaction->id/0"); ?>" class="btn btn-danger">Cancel Subscription</a>
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