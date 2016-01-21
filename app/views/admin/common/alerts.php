<?php if(Session::has('alert_message')): ?>
<div class="alert alert-success alert-dismissable">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="icon-remove-sign"></i></button>
	<?php echo Session::get('alert_message'); ?>
</div>
<?php endif; ?>

<?php if(Session::has('alert_error')): ?>
<div class="alert alert-danger alert-dismissable">
	<button type="button" class="close" data-dismiss="alert" aria-hidden="true"><i class="icon-remove-sign"></i></button>
	<?php echo Session::get('alert_error'); ?>
</div>
<?php endif; ?>

