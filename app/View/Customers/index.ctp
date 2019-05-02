<div class="be-content">
	<div class="main-content container-fluid">
		<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-default panel-table">
			<div class="panel-heading">Customers
				<div class="tools">
					<!-- <span class="icon mdi mdi-download"></span>
					<span class="icon mdi mdi-more-vert"></span> -->
					<?php echo $this->Html->link('<div class="icon"><span class="mdi mdi-account-add"></span></div>',array('controller'=>'Customers','action'=>'add'),array('escape'=>false)); ?>
				</div>
			</div>
			<div class="panel-body">
				<table id="table1" class="table table-striped table-hover table-fw-widget">
					<thead>
						<tr>
						<th>Name</th>
						<th>Address</th>
						<th>Email</th>
						<th>Mobile</th>
						<th>Reference</th>
						<th>Status</th>
						<th>Created</th>
						<th>Action</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($customerLists as $customerList) { ?>
						<tr class="odd gradeX">
						<td><?php echo $customerList['c1']['name']; ?></td>
						<td><?php echo $customerList['c1']['address']; ?></td>
						<td><?php echo $customerList['c1']['email']; ?></td>
						<td><?php echo $customerList['c1']['mobile']; ?></td>
						<?php if(isset($customerList['c1']['reference_id']) && !empty($customerList['c1']['reference_id'])) { ?>
							<td><?php echo $customerList['c2']['name']; ?></td>
						<?php } else { ?>
							<td>NA</td>
						<?php } ?>
						<td class="center"><?php if($customerList['c1']['status'] == 1) {
							echo $this->Html->link($this->Html->image('circle_green.png',array('alt'=>'active', 'class'=>'status','value'=>$customerList['c1']['id'] )),'javascript:void(0)', array('escape' => false));
						} else {
							echo $this->Html->link($this->Html->image('circle_red.png',array('alt'=>'deactive','class'=>'status','value'=>$customerList['c1']['id'])),'javascript:void(0)', array('escape' => false));
						} ?></td>
						<td class="center"><?php echo $customerList['c1']['created']; ?></td>
						<td class="center"><?php echo $this->Html->link('<span class="mdi mdi-edit"></span>',array('controller'=>'Customers','action'=>'edit',$customerList['c1']['id']),array('escape'=>false)); ?>
						<?php echo $this->Html->link('<span class="mdi mdi-hospital"></span>',array('controller'=>'Orders','action'=>'add',$customerList['c1']['id']),array('escape'=>false)); ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			</div>
		</div>
		</div>
	</div>
<script type="text/javascript">
	$(document).ready(function() {
		$(".status").click(function(){
			var val = $(this).attr('value');
			var ref = $(this);
			$.ajax({
				url:"<?php echo Router::url(array('controller'=>'Suppliers','action'=>'change_status'));?>/"+val,
				success:function(data){
					if(data == 0){
						ref.attr({
							src: '/satkar/img/circle_red.png',
							value: val,
							alt:'inactive',
							title:'Inactive'
							});
					}else{
						ref.attr({
							src: '/satkar/img/circle_green.png',
							value: val,
							alt:'active',
							title:'Active'
						});
					}
				}
			});
		});
	});	
      
</script>