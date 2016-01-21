<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
						
						<div class="module no-padding">
							<div class="module-header"><h4>Products</h4></div>
                            <a href="<?php echo url("admin/products/new-product"); ?>" class="btn btn-primary pull-right up-mrg">Add Product</a>
                            <div style="clear:both;"></div>
							<div class="module-content table-responsive">
								<?php if($products): ?>
								<table class="table table-striped">
									<thead>
										<tr>
                                            <th>Name</th>
                                            <th>Unique Code</th>
                                            <th>Type</th>
											<th class="text-right">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($products as $product): ?>
										<tr>
                                            <td><?php echo $product->name; ?></td>
                                            <td><?php echo $product->code; ?></td>
                                            <td><?php echo $product->type == 1 ? "One Time" : "Subscription"; ?></td>
											<td class="text-right">
												<a href="<?php echo url("admin/products/plans/$product->id"); ?>" class="btn btn-xs btn-primary">Manage Plans</a>
												<a href="<?php echo url("admin/products/customize/$product->id"); ?>" class="btn btn-xs btn-primary">Customize</a>
												<a href="<?php echo url("admin/products/edit-product/$product->id"); ?>" class="btn btn-xs btn-info">Edit</a>
												<a href="<?php echo url("admin/products/delete-product/$product->id"); ?>" class="btn btn-xs btn-danger">Delete</a>
											</td>
										</tr>
										<?php endforeach; ?>
                                        
									</tbody>
								</table>
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