<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Confirm Refund of Sale</h4></div>
							<div class="module-content">

								<h3>Are you sure to refund?</h3>
								<p>Warning: If you proceed with refund, this action cannot be undone.</p>

								<form action="<?php echo url("admin/transactions/refund/$transaction->id"); ?>" method="post">
									<input type="submit" value="Yes, Refund Sale" class="btn btn-x btn-danger">
									<a href="<?php echo url("admin/transactions/detail/$transaction->id"); ?>" class="btn btn-x btn-primary">Cancel</a>
								</form>
								
								<br>
								<h4>Transaction Details</h4>
								<table class="table">
									<tbody>
										<tr>
											<th>Transaction ID</th>
											<td><?php echo $transaction->pay_id; ?></td>
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