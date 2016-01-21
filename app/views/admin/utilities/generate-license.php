<?php echo $header; ?>
			<!-- Main Content -->
			<section class="main-content padding">
			
			<?php echo View::make("admin/common/alerts"); ?>

				<div class="row">
					<!-- Full width table -->
					<div class="col-lg-12">
                    <div class="module">
							<div class="module-header"><h4>Generate License</h4></div>
							<div class="module-content">

								<form action="<?php echo url("admin/utilities/generate-license"); ?>" method="post" class="form-horizontal">

									<div class="form-group col-lg-8 m-l"><h4>Transaction</h4></div>

									<div class="form-group col-lg-8 <?php echo $errors->first('transaction_id') ? 'has-error' : NULL; ?>">
									  <label for="transaction_id" class="col-lg-4 control-label">Transaction ID <br><small>(Search Transaction ID by User Email)</small></label>
										<div class="col-lg-4" style="margin-top:10px;">
										  <input id="transaction_id" type="text" name="transaction_id" value="<?php echo Input::old('transaction_id'); ?>" class="form-control">
										</div>
									</div>

									<div class="form-group">
										<div class="col-lg-offset-3a col-lg-9">
											<input type="submit" value="Submit" class="btn btn-primary" />
										</div>
									</div>

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