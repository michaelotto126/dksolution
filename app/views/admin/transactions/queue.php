<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
                    
					<div class="col-lg-12">
						<div class="row">
                  
                		</div>
						<div class="module no-padding">
							<div class="module-header"><h4>Transactions in Refund Queue</h4></div>
							<div class="module-content table-responsive">
								<table class="table table-striped">
									<thead>
										<tr>
                                            <th>Order Date</th>
                                            <th>Product</th>
                                            <th>Plan</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Customer</th>
											<th>Affiliate</th>
											<th>Invoice</th>
											<th class="text-right">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php if($transactions): ?>
                         				<?php foreach($transactions as $transaction): ?>
										<tr>
                                            <td><?php echo date("m/d/Y", $transaction->created_at); ?></td>
                                            <td><?php echo $transaction->product_name; ?></td>
                                            <td><?php echo $transaction->plan_name; ?></td>
                                            <td>$<?php echo $transaction->amount; ?></td>
                                            <td><?php echo $transaction->is_refunded ? "Refunded" : "Paid"; ?></td>
                                            <td><?php echo $transaction->buyer_first_name . " " . $transaction->buyer_last_name; ?> (<?php echo $transaction->buyer_email; ?>)</td>
											<td><?php echo (!empty($transaction->affiliate_name) ? $transaction->affiliate_name : "No Affiliate"); ?></td> <?php //$transaction->purchase->affiliate->name ?>
											<td><a target="_blank" href="<?php echo Config::get('project.infusion_soft_invoice_url') . $transaction->invoice_id; ?>">View Invoice</a></td>
											<td class="text-right">
												<a href="<?php echo url("admin/transactions/mark-refund/$transaction->id"); ?>" class="btn btn-xs btn-danger">Mark as Refunded</a>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>

						<div class="pagination-box">
							<?php echo $transactions->links(); ?>
						</div>

					</div>
					<!-- /Full width table -->
                    
				</div>
				
				<div class="row">
					
					
				</div>

				

			</section>
			<!-- /Main Content -->
<?php echo $footer; ?>