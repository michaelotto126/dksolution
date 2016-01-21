<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Change Maxium Allowed Usage of a License</h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/licenses/change-usage/$license->license_key"); ?>" method="post">
									<label class="control-label">Allowed Usage</label><br>
									<input type="text" name="allowed_usage" class="form-control" style="width:10%;" value="<?php echo $license->allowed_usage; ?>" /><br>
									<input type="submit" value="Update Allowed Usage" class="btn btn-x btn-danger">
									<a href="<?php echo url("admin/licenses"); ?>" class="btn btn-x btn-primary">Cancel</a>
								</form>
								
								<br>
								<h4>License Details</h4>
								<table class="table">
									<tbody>
										<tr>
											<th>License Key</th>
											<td><?php echo $license->license_key; ?></td>
										</tr>
										<tr>
											<th>Allowed Usage</th>
											<td><?php echo $license->allowed_usage; ?></td>
										</tr>
										<tr>
											<th>Buyer</th>
											<?php $buyer = $license->transaction->purchase->buyer; ?>
											<td><?php echo "$buyer->first_name $buyer->last_name ($buyer->email)"; ?></td>
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