<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
                    <div class="col-lg-3">
                    <div class="module">
							<div class="module-header"><h4>Search Licenses</h4></div>
							<div class="module-content">

								<form action="<?php echo url('admin/licenses') ?>" method="get" class="form-horizontal">

									

									<div class="form-group">
										<label for="select1" class="col-lg-3 control-label">Search</label>
										<div class="col-lg-9">
											<input type="text" name="q" class="form-control" placeholder="Search text">
										</div>
									</div>
                                    
									<div class="form-group">
										<label for="select1" class="col-lg-3 control-label">By</label>
										<div class="col-lg-9">
											<select class="form-control" name="param">
												<option value="key">License Key</option>
												<option value="email">Buyer Email</option>
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
							<div class="module-header"><h4>Licenses</h4></div>
							<div class="module-content table-responsive">
								<table class="table table-striped">
									<thead>
										<tr>
											<th>License Key</th>
											<th>Buyer</th>
											<th class="text-center">Usage</th>
											<th class="text-right">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php if($licenses): ?>
										<?php foreach($licenses as $license): ?>
										<tr>
											<td><?php echo $license->license_key; ?></td>
											<td><?php echo "$license->first_name $license->last_name ($license->email)"; ?></td>
											<td class="text-center"><?php echo $license->totalUsed . '/' . $license->allowed_usage; ?></td>
											<td class="text-right">
												<a href="<?php echo url("admin/licenses/payload?code=".$license->code."&api_key=".$license->api_key."&license=".urlencode($license->license_key)); ?>" class="btn btn-xs btn-info">Get Payload</a>
												<a href="<?php echo url("admin/licenses/usage/".urlencode($license->license_key)); ?>" class="btn btn-xs btn-info">Usage</a>
												<a href="<?php echo url("admin/licenses/change-usage/".urlencode($license->license_key)); ?>" class="btn btn-xs btn-primary">Max. Usage</a>
												<?php if($license->status == 1): ?>
												<a href="<?php echo url("admin/licenses/revoke/".urlencode($license->license_key)); ?>" class="btn btn-xs btn-danger">Revoke</a>
												<?php else: ?>
												<a href="<?php echo url("admin/licenses/activate/".urlencode($license->license_key)); ?>" class="btn btn-xs btn-success">Activate</a>
											<?php endif; ?>
											</td>
										</tr>
										<?php endforeach; ?>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>

						<div class="pagination-box">
							<?php echo $licenses->links(); ?>
						</div>

					</div>
					<!-- /Full width table -->
                    
					
                    
				</div>
				
				<div class="row">
					
					
				</div>

				

			</section>
			<!-- /Main Content -->
<?php echo $footer; ?>