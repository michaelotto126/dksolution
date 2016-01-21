<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Confirm Deletion of License Usage</h4></div>
							<div class="module-content">

								<h3>Are you sure to delete?</h3>
								<p>Warning: If you proceed with deletion, this action cannot be undone.</p>

								<form action="<?php echo url("admin/licenses/delete-usage/$use->id"); ?>" method="post">
									<input type="submit" value="Yes, Delete Usage" class="btn btn-x btn-danger">
									<a href="<?php echo url("admin/licenses/usage/$use->license_key"); ?>" class="btn btn-x btn-primary">Cancel</a>
								</form>
								
								<br>
								<h4>Usage Details</h4>
								<table class="table">
									<tbody>
										<tr>
											<th>GUID/Domain</th>
											<td><?php echo $use->guid; ?></td>
										</tr>
										<tr>
											<th>License Key</th>
											<td><?php echo $use->license_key; ?></td>
										</tr>
										<tr>
											<th>Activated At</th>
											<td><?php echo date("m/d/Y", $use->activated_at); ?></td>
										</tr>
										<tr>
											<th>Last Checked</th>
											<td><?php echo date("m/d/Y", $use->last_checked); ?></td>
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