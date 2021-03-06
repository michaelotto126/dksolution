<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Delete - <?php echo $product->name; ?></h4></div>
							<div class="module-content">

								<h3>Are you sure to delete <?php echo $product->name; ?>?</h3>
								<p>All associated plans and transactions will also be deleted. If you proceed with delete, this action cannot be undone.</p>

								<form action="<?php echo url("admin/products/delete-product/$product->id"); ?>" method="post">
									<input type="submit" value="Yes, Delete product" class="btn btn-x btn-danger">
									<a href="<?php echo url("admin/products"); ?>" class="btn btn-x btn-primary">Cancel</a>
								</form>

							</div>
						</div>
                    </div>
					<!-- /Full width table -->
                    
					
                    
				</div>
				
				<div class="row"></div>

			</section>
			<!-- /Main Content -->
<?php echo $footer; ?>