<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

			<?php if($refundQueue): ?>
			<div class="alert alert-danger alert-dismissable">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="icon-remove-sign"></i></button>
				There are <strong><?php echo $refundQueue; ?></strong> pending transactions in InfusionSoft refund queue. <a href="<?php echo url("admin/transactions/refund-queue"); ?>">Click here</a> to see all of them.
			</div>
			<?php endif; ?>
				
				<div class="row">
					<!-- Full width table -->
                    <div class="col-lg-3">
                    	<form action="<?php echo url("admin/transactions"); ?>" method="get" class="form-horizontal">
	                        <div class="input-group">
	                            <input type="text" name="q" class="form-control" value="<?php echo Input::get('q'); ?>">
	                            <input type="hidden" name="search" value="true">
	                            <input type="hidden" name="paid" value="1">
	                            <input type="hidden" name="refunded" value="1">
	                            <span class="input-group-btn">
	                                <button class="btn btn-grape" type="submit"><i class="icon-search"></i> SEARCH</button>
	                            </span>
	                        </div>
                        </form>
                    </div>
                    
                    <!-- <div class="col-lg-9">
                        <div class="col-lg-2 pull-right">
                            <div class="box-module">
                                <small>Paid to Affiliates</small>
                                <h3 class="stats-positive">$<?php echo $paidAffliates; ?></h3>
                            </div>
						</div>
                        
                        <div class="col-lg-2 pull-right">
                            <div class="box-module module-red">
                                <small>Total Revenue</small>
                                <h3 class="stats-negative">$<?php echo $revenue; ?></h3>
                            </div>
						</div>
                        
                    </div> -->
                    <!-- /Full width table -->
                </div>

                <div class="row clear">
					<!-- Full width table -->
                    <div class="col-lg-3">
                    <div class="module">
							<div class="module-header"><h4>Filter Transactions</h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/transactions"); ?>" method="get" class="form-horizontal">

									<div class="form-group">
										<label for="dateStart" class="col-lg-3 control-label">Date</label>
										<div class="col-lg-9">
											<input type="text" value="<?php echo Input::get('from'); ?>" name="from" class="form-control datepicker" placeholder="mm/dd/yyyy" id="dateStart"/>
										</div> <br /><br />
                                        <label for="dateEnd" class="col-lg-3 control-label"><span>to</span></label>
                                        <div class="col-lg-9">
											<input type="text" value="<?php echo Input::get('to'); ?>" name="to" class="form-control datepicker" placeholder="mm/dd/yyyy" id="dateEnd"/>
										</div>
									</div>

									<div class="form-group">
										<label for="range" class="col-lg-3 control-label">Recent</label>
										<div class="col-lg-9">
											<select class="form-control" name="range" id="range">
                                            	<option value="custom" <?php echo (Input::get('range') == "custom" ? 'selected="selected"' : NULL); ?>>Custom</option>
                                            	<option value="today" <?php echo (Input::get('range') == "today" ? 'selected="selected"' : NULL); ?>>Today</option>
												<option value="week" <?php echo (Input::get('range') == "week" ? 'selected="selected"' : NULL); ?>>This Week</option>
												<option value="month" <?php echo (Input::get('range') == "month" ? 'selected="selected"' : NULL); ?>>This Month</option>
												<option value="last-month" <?php echo (Input::get('range') == "last-month" ? 'selected="selected"' : NULL); ?>>Last Month</option>
												<option value="year" <?php echo (Input::get('range') == "year" ? 'selected="selected"' : NULL); ?>>This Year</option>
											</select>
										</div>
									</div>
                                    
									<div class="form-group">
										<label for="productId" class="col-lg-3 control-label">Product</label>
										<div class="col-lg-9">
											<select name="product" class="form-control" id="productId">
                                            	<option value="">All</option>
                                            	<option value="">----------</option>
												<?php if($products): ?>
                                            	<?php foreach($products as $product): ?>
												<option value="<?php echo $product->id; ?>" <?php echo (Input::get('product') == $product->id ? 'selected="selected"' : NULL); ?>><?php echo $product->name; ?></option>
												<?php endforeach; ?>
												<?php endif; ?>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label for="affiliateId" class="col-lg-3 control-label">Affiliate</label>
										<div class="col-lg-9">
											<select class="form-control" name="affiliate" id="affiliateId">
                                            	<option value="">All</option>
                                            	<option value="no-affiliate" <?php echo (Input::get('affiliate') == 'no-affiliate' ? 'selected="selected"' : NULL); ?>>No Affiliate</option>
                                            	<option value="">----------</option>
                                            	<?php if($affiliates): ?>
                                            	<?php foreach($affiliates as $affiliate): ?>
												<option value="<?php echo $affiliate->id; ?>" <?php echo (Input::get('affiliate') == $affiliate->id ? 'selected="selected"' : NULL); ?>><?php echo $affiliate->name; ?></option>
												<?php endforeach; ?>
												<?php endif; ?>
											</select>
										</div>
									</div>

									<div class="form-group">
                                    	<label class="col-lg-3 control-label">Status</label>
										<div class="col-lg-offset-2 col-lg-9">
											<div class="checkbox">
												<label>
													<input type="checkbox" name="paid" value="1" <?php echo (Input::get('paid') ? 'checked="checked"' : (!Input::get('search') ? 'checked="checked"' : NULL)); ?>> Paid
												</label>
											</div>
											<div class="checkbox">
												<label>
													<input type="checkbox" name="refunded" value="1" <?php echo (Input::get('refunded') ? 'checked="checked"' : (!Input::get('search') ? 'checked="checked"' : NULL)); ?>> Refunded
												</label>
											</div>
										</div>
									</div>

									

									<div class="form-group">
										<div class="col-lg-offset-2 col-lg-9">
											<input type="hidden" name="search" value="true">
											<input type="submit" value="Submit" class="btn btn-primary" />
										</div>
									</div>

								</form>

							</div>
						</div>
                    </div>
					<div class="col-lg-9">
						<div class="row">
                  
                		</div>
						<div class="module no-padding">
							<div class="module-header"><h4>Transactions</h4></div>
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
											<td class="text-right">
												<a href="<?php echo url("admin/transactions/detail/$transaction->id"); ?>" class="btn btn-xs btn-info">Detail</a>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>

						<div class="pagination-box">
							<?php echo $transactions->appends($searchParams)->links(); ?>
						</div>

					</div>
					<!-- /Full width table -->
                    
				</div>
				
				<div class="row">
					
					
				</div>

				

			</section>
			<!-- /Main Content -->
<?php echo $footer; ?>