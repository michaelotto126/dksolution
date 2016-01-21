<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
						
						<div class="module no-padding">
							<div class="module-header"><h4>Plans - <?php echo $product->name; ?></h4></div>
							<a href="<?php echo url("admin/products/new-plan/$product->id"); ?>" class="btn btn-primary pull-right up-mrg">New Plan</a>
							<div style="clear:both;"></div>
							<div class="module-content table-responsive">
								<?php if($plans): ?>
								<table class="table table-striped">
									<thead>
										<tr>
											<th>Name</th>
											<th>Code</th>
                                            <th>Price</th>
                                            <th>Type</th>
                                            <th>Stripe ID</th>
                                            <th>InfusionSoft ID</th>
											<th class="text-right">Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($plans as $plan): ?>
										<tr>
											<td><?php echo $plan->name; ?> <?php if(!$plan->status): ?><span class="label label-danger">Disabled</span><?php endif; ?></td>
											<td><?php echo $plan->code; ?></td>
                                            <td>$<?php echo $plan->price; ?></td>
                                            <td><?php echo $plan->is_oto ? "OTO" : "Front End"; ?></td>
                                            <td><?php echo $plan->stripe_id; ?></td>
                                            <td><?php echo $plan->infusion_id; ?></td>
											<td class="text-right">
												
												<?php if($plan->is_oto): ?>
												<button data-clipboard-text="<?php echo url("checkout/oto/$product->code/$plan->code"); ?>" class="d_clip_button btn btn-xs btn-info">Copy Link</button>
												<?php else : ?>
												<button data-clipboard-text="<?php echo url("checkout/$product->code/$plan->code"); ?>" class="d_clip_button btn btn-xs btn-info">Copy Link</button>
												<?php endif; ?>

												<?php if($plan->status): ?>
													<a href="<?php echo url("admin/products/change-plan-status/$product->id/$plan->id"); ?>" class="btn btn-xs btn-orange">Disable</a>
												<?php else: ?>
													<a href="<?php echo url("admin/products/change-plan-status/$product->id/$plan->id"); ?>" class="btn btn-xs btn-warning">Enable</a>
												<?php endif; ?>
												
												<a href="<?php echo url("admin/products/edit-plan/$product->id/$plan->id"); ?>" class="btn btn-xs btn-success">Edit</a>
												<a href="<?php echo url("admin/products/delete-plan/$product->id/$plan->id"); ?>" class="btn btn-xs btn-danger">Delete</a>
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