{capture name='data_content' assign='data_content'}
<div class="pp-info" data-pp-info>
	<div class="row">
		<div class="col-md-6 item bt__mb-3">
			<img src="{$path|escape:'html':'UTF-8'}views/img/protected.png" style="height: 20px;" alt="">
			<div class="header bt__pt-2">Title</div>
			<div class="desc bt__pt-1">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum quis enim nihil quae</div>
		</div>
		<div class="col-md-6 item bt__mb-3">
			<img src="{$path|escape:'html':'UTF-8'}views/img/protected.png" style="height: 20px;" alt="">
			<div class="header bt__pt-2">Title</div>
			<div class="desc bt__pt-1">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum quis enim nihil quae</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6 item bt__mb-3">
			<img src="{$path|escape:'html':'UTF-8'}views/img/protected.png" style="height: 20px;" alt="">
			<div class="header bt__pt-2">Title</div>
			<div class="desc bt__pt-1">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum quis enim nihil quae</div>
		</div>
		<div class="col-md-6 item bt__mb-3">
			<img src="{$path|escape:'html':'UTF-8'}views/img/protected.png" style="height: 20px;" alt="">
			<div class="header bt__pt-2">Title</div>
			<div class="desc bt__pt-1">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum quis enim nihil quae</div>
		</div>
	</div>
</div>
{/capture}
<div data-bt-paypal-info class="bt__d-table-cell">
	<a href="#"
		 class="bt__text-primary"
		 data-bt-paypal-info-popover 
		 data-html="true" 
		 data-trigger="hover" 
		 data-container="body"
		 data-content="{$data_content}"
	>
		<i class="material-icons">info</i>
	</a>
</div>