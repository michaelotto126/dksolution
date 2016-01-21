<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
                    <div class="col-lg-3">
                    <div class="module">
							<div class="module-header"><h4>Filter Customers</h4></div>
							<div class="module-content">

								<form action="<?php echo url('admin/customers') ?>" method="get" class="form-horizontal">

									

									<div class="form-group">
										<label for="select1" class="col-lg-3 control-label">Search</label>
										<div class="col-lg-9">
											<input type="text" name="q" class="form-control" id="input1" placeholder="Search text">
										</div>
									</div>
                                    
									<div class="form-group">
										<label for="select1" class="col-lg-3 control-label">By</label>
										<div class="col-lg-9">
											<select class="form-control" name="param">
												<option value="email">Email</option>
												<option value="fname">First Name</option>
												<option value="lname">Last Name</option>
											</select>
										</div>
									</div>

									<div class="form-group">
										<div class="col-lg-offset-3 col-lg-9">
											<button class="btn btn-primary">Submit</button>
										</div>
									</div>

								</form>

							</div>
						</div>
                    </div>
                    <!-- /Full width table -->
					<!-- Full width table -->
					<div class="col-lg-9">
						
						<div class="module no-padding">
							<div class="module-header"><h4>Customers</h4></div>
							<div class="module-content table-responsive">
								<table class="table table-striped">
									<thead>
										<tr>
											<th>First Name</th>
											<th>Last Name</th>
											<th>Email</th>
											<th class="text-right">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php if($buyers): ?>
										<?php foreach($buyers as $buyer): ?>
										<tr>
											<td><?php echo $buyer->first_name; ?></td>
											<td><?php echo $buyer->last_name; ?></td>
											<td><?php echo $buyer->email; ?></td>
											<td class="text-right">
												<a href="<?php echo url("admin/transactions?q=".urlencode($buyer->email)."&search=true&paid=1&refunded=1"); ?>" class="btn btn-xs btn-info">View Transactions</a>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>

						<div class="pagination-box">
							<?php echo $buyers->links(); ?>
						</div>

					</div>
					<!-- /Full width table -->
                    
					
                    
				</div>
				
				<div class="row">
					
					
				</div>

				

			</section>
			<!-- /Main Content -->
<?php echo $footer; ?>