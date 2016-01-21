<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>
				
				<div class="row clear">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>List of License Usage - <?php echo $license_key; ?></h4></div>
							<div class="module-content">

								<?php if($uses): ?>
								<h4>Usage Details</h4>
								<table class="table">
									<thead>
										<tr>
											<th>GUID/Domain</th>
											<th>Activated At</th>
											<th>Last Checked</th>
											<th class="text-center">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($uses as $use): ?>
										<tr>
											<td><?php echo $use->guid; ?></td>
											<td><?php echo date("m/d/Y", $use->activated_at); ?></td>
											<td><?php echo date("m/d/Y", $use->last_checked); ?></td>
											<td class="text-center">
												<a href="<?php echo url("admin/licenses/delete-usage/$use->id"); ?>" class="btn btn-xs btn-danger">Delete</a>
											</td>
										</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php else: ?>
								<h3>No usage was found</h3>
								<p>It seems this license key has not been used yet.</p>
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