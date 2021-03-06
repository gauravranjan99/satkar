<style>
.hide_order{
    color:#CD451D;
}
.show_order {
    color:#15AA2E;
}
</style>
<?php echo $this->Html->css('bootstrap-datetimepicker.min');?>
<?php echo $this->Html->script('bootstrap-datetimepicker.min');?>

<div class="modal animated fadeIn" id="myModalForCustomer" tabindex="-1" role="dialog" aria-labelledby="smallModalHead" aria-hidden="true">
    <div class="modal-dialog modal-lg" style=" margin: 0  auto;top:10%;width: 40%;">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header" style="text-align: center;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title" style="line-height:1;">Customer Details</h3><hr>
            </div>
            
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="col-md-3"><b>Name:</b></div>
                    <div class="col-md-9 customer-name"></div>
                </div>
                <br/><br/>
                <div class="col-md-12">
                    <div class="col-md-3"><b>Address:</b></div>
                    <div class="col-md-9 customer-address"></div>
                </div><br/><br/>
                <div class="col-md-12">
                    <div class="col-md-3"><b>Mobile:</b></div>
                    <div class="col-md-9 customer-mobile"></div>
                </div><br/><br/>
                <div class="col-md-12">
                    <div class="col-md-3"><b>Email:</b></div>
                    <div class="col-md-9 customer-email"></div>
                </div>
                <div class="">
                    <div class="">
                        <div class="col-md-12">
                            <?php echo $this->Form->button('Cancel',array('type'=>'button','data-dismiss'=>'modal','class'=>'btn btn-rounded btn-default','style'=>'margin-top: 26px;margin-bottom: 18px;','escape'=>false));?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="be-content">
        <div class="main-content container-fluid">
            <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                <div class="panel-heading">Orders
                <?php $encodedCustomerId = $Encryption->encode($customerId); 
                    
                ?>
                  <div class="tools"><span class="icon mdi mdi-download"></span><span class="icon mdi mdi-more-vert"></span></div>
                </div>

                <div class="form-group col-md-12"></div>
                <div class="form-group col-md-12">
                    <?php
                    if (!empty($encodedCustomerId)) {
                        echo $this->Form->create('Order',array('url'=> array('controller' => 'Orders', 'action' => 'admin_index',$encodedCustomerId),'method'=>'POST')); 
                        echo $this->Form->input('Order.customer_id',array('type'=>'hidden','value'=>$encodedCustomerId));
                    } else {
                        echo $this->Form->create('Order',array('url'=> array('controller' => 'Orders', 'action' => 'admin_index'),'method'=>'POST')); 
                    }
                    
                    ?>
                    
                    <div class="form-group col-md-12">
                        <div class="form-group col-md-3">
                            <label>Order Number</label>
                            <?php echo $this->Form->input("Order.order_number",array('placeholder'=>'Enter order number','class'=>'form-control input-sm allowOnlyNumber','autocomplete'=>'off','label'=>false,'value'=>isset($criteria['Order']['order_number'])? $criteria['Order']['order_number']:''));?>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Order Status</label>
                            <?php echo $this->Form->input("Order.status",array('class'=>'form-control input-sm','options'=>array('draft'=>'Draft','1'=>'Confirm','2'=>'Cancelled','3'=>'Partial Cancelled'),'empty'=>'--Select--','label'=>false,'default' =>isset($criteria['Order']['status'])? $criteria['Order']['status']:''));?>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Payment Status</label>
                            <?php echo $this->Form->input("Order.payment_status",array('class'=>'form-control input-sm','options'=>array('completed'=>'Completed','1'=>'Pending'),'empty'=>'--Select--','label'=>false,'default' =>isset($criteria['Order']['payment_status'])? $criteria['Order']['payment_status']:''));?>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Order Show Status</label>
                            <?php echo $this->Form->input("Order.is_show",array('class'=>'form-control input-sm','options'=>array('hide'=>'Hide','1'=>'show'),'empty'=>'--Select--','label'=>false,'default' =>isset($criteria['Order']['is_show'])? $criteria['Order']['is_show']:''));?>
                        </div>

                    </div>

                    <div class="form-group col-md-12">
                        <div class="form-group col-md-4">
                            <label>Order start date</label>
                            <?php echo $this->Form->input("Order.start_date",array('placeholder'=>'Enter start date','id'=>'start_date','class'=>'form-control input-sm date datetimepicker','data-min-view' =>'2','data-date-format'=>'yyyy-mm-dd','autocomplete'=>'off','label'=>false,'value'=>isset($criteria['Order']['start_date'])? $criteria['Order']['start_date']:''));?>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Order end date</label>
                            <?php echo $this->Form->input("Order.end_date",array('placeholder'=>'Enter end date','id'=>'end_date','class'=>'form-control input-sm date datetimepicker','data-min-view' =>'2','data-date-format'=>'yyyy-mm-dd','autocomplete'=>'off','label'=>false,'value'=>isset($criteria['Order']['end_date'])? $criteria['Order']['end_date']:''));?>
                        </div>

                        <div class="form-group col-md-4">
                            <?php echo $this->Form->button('Search',array('type'=>'submit','id'=>'search_orders','class'=>'btn btn-rounded btn-primary','escape'=>false,'style'=>'width:45%;margin: 26px;'));?>
                        </div>
                    </div>
                    
                    <?php echo $this->Form->end();?>
                </div>

                <div class="panel-body">
                  <table class="table table-striped table-hover">
                    <thead>
                      <tr>
                        
                        <th style="width:19%;">Order ID</th>
                        <th style="width:17%;">Customer</th>
                        <th style="width:11%;">Total</th>
                        <th style="width:10%;">Paid</th>
                        <th style="width:10%;">Dues</th>
                        <th style="width:13%;">Status</th>
                        <th style="width:12%;">Date</th>
                        <th style="width:10%;">Payment</th>
                        <th style="width:10%;">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php 
                        foreach ($orderLists as $orderList) {
                            if ($orderList['Order']['status'] == 0 ) {
                                $status = 'Draft';
                                $orderStatusClass = 'text-warning';
                            } else if ($orderList['Order']['status'] == 1 ) {
                                $status = 'Confirm';
                                $orderStatusClass = 'text-success';
                            } else if ($orderList['Order']['status'] == 2) {
                                $status = 'Cancelled';
                                $orderStatusClass = 'text-danger';
                            } else if ($orderList['Order']['status'] == 3) {
                                $status = 'Partial Cancelled';
                                $orderStatusClass = 'text-warning';
                            }
                    ?>
                      <tr>
                        <?php if ($orderList['Order']['is_show'] == 1) { ?>
                            <td ><span class="order_show_status show_order" title="Hide Order" style="cursor:pointer;" order-id="<?php echo $orderList['Order']['id']; ?>" order-show-status="hide_order"><?php echo 'OD' . $orderList['Order']['order_number']; ?></span></td>
                        <?php } else { ?>
                            <td ><span class="order_show_status hide_order" title="Show Order" style="cursor:pointer;" order-id="<?php echo $orderList['Order']['id']; ?>" order-show-status="show_order"><?php echo 'OD' . $orderList['Order']['order_number']; ?></span></td>
                        <?php } ?>
                        <td class="cell-detail"> <span><?php echo $this->Html->link($orderList['Customer']['name'], 'javascript:void(0);',  array("class" => "customer_details", "escape" => false,"mobile"=>$orderList['Customer']['mobile'],"email"=>$orderList['Customer']['email'],"address"=>$orderList['Customer']['address'])); ?></span></td>
                        <?php if ($orderList['Order']['status'] == 2) { ?>
                            <td class="milestone">&#8377;<?php echo number_format($orderList['Order']['total'],2); ?></td>
                        <?php } else { ?>
                            <td class="milestone">&#8377;<?php echo number_format($orderList['Order']['grand_total'],2); ?></td>
                        <?php } ?>
                        <?php 
                            $sum = 0;
                            foreach ($orderList['OrderTransaction'] as $orderTransaction) {
                                $sum+= $orderTransaction['amount_paid'];
                            }
                            $dues = ($orderList['Order']['grand_total'] - $sum);
                        ?>
                        <td class="cell-detail"><span>&#8377;<?php echo number_format($sum,2); ?></span></td>
                        <?php if ($orderList['Order']['status'] == 2 || $orderList['Order']['payment_status'] == 0) { ?>
                            <td></td>
                        <?php } else { ?>
                            <td class="cell-detail"><span>&#8377;<?php echo number_format($dues,2); ?></span></td>
                        <?php } ?>
                        <td class="<?php echo $orderStatusClass ?>"><?php echo $status; ?></td>
                        <td><?php echo date('d-M-Y', strtotime($orderList['Order']['created'])); ?></td>
                        <?php if ($orderList['Order']['payment_status'] == 1) { ?>
                            <td><?php echo $this->Html->link('Pending', 'javascript:void(0);',  array("class" => "text-danger payment_pending", "escape" => false,'order_id'=>$orderList['Order']['id'],'title'=>'Change to Completed')); ?></td>
                        <?php } else { ?>
                            <td class="text-success">Completed</td>
                        <?php } ?>

                        <td class="text-right">
                            <div class="btn-group btn-hspace">
                                <button type="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle" aria-expanded="false">Open <span class="icon-dropdown mdi mdi-chevron-down"></span></button>
                                <ul role="menu" class="dropdown-menu pull-right">
                                    <?php $encodedOrderId = $Encryption->encode($orderList['Order']['id']);?>
                                    <li><?php echo $this->Html->link('Order Details', array('controller' => 'Orders','action' => 'admin_details',$encodedOrderId),array('class'=>'','target'=>'_blank'));?></li>
                                </ul>
                            </div>                        
                        </td>
                      </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            </div>
            
    </div>
        <?php

            if (!empty($encodedCustomerId)) {
                $this->Paginator->options(array('url' => array($encodedCustomerId .'/'. 'criteria' => $criteria)));
            } else {
                if (!empty($criteria)) {
                    $this->Paginator->options(array('url' => array('criteria' => $criteria)));
                }
            }

            
        ?>	
        <nav>
            <center>
                <ul class="pagination">
                    <li>
                    <?php
                        echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
                        echo $this->Paginator->numbers(array('separator' => ''));
                        echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
                    ?>
                    </li>
                </ul>
            </center>
        </nav>
</div>

<script type="text/javascript">
	$(document).ready(function() {

        var start = new Date();
        // set end date to max one year period:
        var end = new Date(new Date().setYear(start.getFullYear()+1));

        $('#start_date').datetimepicker({
            autoclose: true,
            componentIcon: '.mdi.mdi-calendar',
            navIcons:{
                rightIcon: 'mdi mdi-chevron-right',
                leftIcon: 'mdi mdi-chevron-left'
            },
            //startDate : start,
            endDate   : start
            // update "toDate" defaults whenever "fromDate" changes
            }).on('changeDate', function(){
                // set the "toDate" start to not be later than "fromDate" ends:
                $('#end_date').datetimepicker('setStartDate', new Date($(this).val()));
        }); 

        $('#end_date').datetimepicker({
            autoclose: true,
            componentIcon: '.mdi.mdi-calendar',
            navIcons:{
                rightIcon: 'mdi mdi-chevron-right',
                leftIcon: 'mdi mdi-chevron-left'
            },
            // startDate : start,
            endDate   : start
        // update "fromDate" defaults whenever "toDate" changes
        }).on('changeDate', function(){
            // set the "fromDate" end to not be later than "toDate" starts:
            $('#start_date').datetimepicker('setEndDate', new Date($(this).val()));
        });

        $('.datetimepicker').keypress(function(){
            return false;
        });

        $(".customer_details").click(function(){
            var customerName = $(this).text();
            var customerAddress = $(this).attr('address');
            var customerMobile = $(this).attr('mobile');
            var customerEmail = $(this).attr('email');
            $('#myModalForCustomer').modal('show');
            $('#myModalForCustomer').find('.customer-name').text(customerName);
            $('#myModalForCustomer').find('.customer-address').text(customerAddress);
            $('#myModalForCustomer').find('.customer-mobile').text(customerMobile);
            $('#myModalForCustomer').find('.customer-email').text(customerEmail);
		});

        $('.payment_pending').click(function(){
            var orderID = $(this).attr('order_id');
            var ref = $(this);
            if (confirm('Are you sure to change the payment status ?')) {
                $.ajax({
                    url:"<?php echo Router::url(array('controller'=>'Orders','action'=>'change_payment_status'));?>/"+orderID,
                    success:function(data){
                        if (data == 1) {
                            ref.parent().addClass('text-success');
                            ref.parent().html('Completed');
                        } else {
                            alert('Error Occured!!');
                        }
                    }
			    });
            }
        });

        $('.order_show_status').click(function(){
            var orderId = $(this).attr('order-id');
            var orderShowStatus = $(this).attr('order-show-status');
            var ref = $(this);
            if (confirm('Are you sure to continue ?')) {
                $.ajax({
                    url:"<?php echo Router::url(array('controller'=>'Orders','action'=>'admin_changeShowStatus'));?>/"+orderId + '/' + orderShowStatus,
                    dataType: 'json',
                    success:function(data){
                        console.log(data);
                        if (data.success) {
                            if (data.msg == 'show_order') {
                                ref.removeClass('hide_order');
                                ref.addClass('show_order');
                                ref.attr('order-show-status','hide_order');
                                ref.attr('title','Hide Order');
                            } else if (data.msg == 'hide_order') {
                                ref.removeClass('show_order');
                                ref.addClass('hide_order');
                                ref.attr('order-show-status','show_order');
                                ref.attr('title','Show Order');
                            }
                        } else {
                            alert('Error Occured!!');
                        }
                    }
			    });
            }
        });

	});	
      
</script>