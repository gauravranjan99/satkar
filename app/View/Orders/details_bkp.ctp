<div class="be-content">
        <div class="main-content container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="panel panel-default panel-table">
                <div class="panel-heading">
                <div class="col-md-12">
                    <div class="col-md-6">
                    <div style="font-size:15px;"><b>Customer Name:</b>
                            <?php echo $customerDetails['Customer']['name']; ?></div>
                            <div style="font-size:15px;"><b>Address:  </b><?php echo $customerDetails['Customer']['address']; ?></div>
                            <div style="font-size:15px;"><b>Mb:  </b><?php echo $customerDetails['Customer']['mobile']; ?></div>
                    </div>
                <?php //pr($orderDetails);die;
                    if ($orderDetails['Order']['status'] == 0 ) {
                        $status = 'Draft';
                        $orderStatusClass = 'text-warning';
                    } else if ($orderDetails['Order']['status'] == 1 ) {
                        $status = 'Confirm';
                        $orderStatusClass = 'text-success';
                    } else if ($orderDetails['Order']['status'] == 2) {
                        $status = 'Cancelled';
                        $orderStatusClass = 'text-danger';
                    } else if ($orderDetails['Order']['status'] == 3) {
                        $status = 'Partial Cancelled';
                        $orderStatusClass = 'text-warning';
                    }
                ?>
                        <div class="col-md-6">
                            <div class="tools" style="font-size:18px;">
                                <?php echo "Order ID: " .$orderDetails['Order']['order_number']; ?>
                            </div><br/>
                            <div class="tools" style="font-size:15px;">
                                <span style="font-size:18px;">Date: </span><?php echo date('d-M-Y h:i A', strtotime($orderDetails['Order']['created'])); ?>
                            </div><br/>
                            <div class="tools" style="font-size:15px;">
                                <span style="font-size:18px;">Status: </span><span class="<?php echo $orderStatusClass ?>"><?php echo $status; ?></span>
                            </div>
                        </div>
                    </div><br/><br/>
                    <br/>
                </div>

                <hr>
                
                <div class="panel-body">
                  <table id="" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Category</th>
						<th>Item</th>
                        <th>Rate</th>
                        <th>Weight</th>
                        <th>Making Charge</th>
                        <th>Gems</th>
                        <th>Gems Rate</th>
                        <th>Gems Weight</th>
                        <!-- <th>Gems Amount</th> -->
						<th>Total</th>
						<th>Discount</th>
						<th>Grand Total</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
					
                    <?php
                        $confirmItem = array();
                        // $activeItemTotal = 0;
                        foreach ($orderDetails['OrderItem'] as $orderDetail) {
                            if($orderDetail['status'] == 1) {
                                $statusClass = 'text-danger';
                            } else {
                                // $activeItemTotal+= $orderDetail['grand_total'];
                                array_push($confirmItem,$orderDetail['id']);
                                $statusClass = '';
                            }
                        ?>
                    <tr class="odd gradeX">
                        <td><span class="<?php echo $statusClass ?>"><?php echo $orderDetail['Category']['name']; ?></span></td>
                        <td  data-container="body" data-toggle="popover" data-placement="top" data-content="<?php echo $orderDetail['comments']; ?>" data-original-title="Comments"><span class="<?php echo $statusClass ?>"><?php echo $orderDetail['name']; ?></span></td>
                        <?php if(isset($orderDetail['rate']) && !empty($orderDetail['rate'])) { ?>
                            <td><span class="<?php echo $statusClass ?>">&#8377;<?php echo $orderDetail['rate']; ?></span></td>
                        <?php } else { ?>
                            <td></td>
                        <?php } ?>
                        <?php if(isset($orderDetail['weight']) && !empty($orderDetail['weight'])) { ?>
                            <td><span class="<?php echo $statusClass ?>"><?php echo $orderDetail['weight']; ?> gm</span></td>
                        <?php } else { ?>
                            <td></td>
                        <?php } ?>
                        <?php if(isset($orderDetail['making_charge']) && !empty($orderDetail['making_charge'])) { ?>
                            <td><span class="<?php echo $statusClass ?>">&#8377;<?php echo $orderDetail['making_charge']; ?></span></td>
                        <?php } else { ?>
                            <td></td>
                        <?php } ?>
                        <td><span class="<?php echo $statusClass ?>"><?php echo $orderDetail['gems_name']; ?></span></td>
                        <td><span class="<?php echo $statusClass ?>"><?php echo $orderDetail['gems_rate']; ?></span></td>
                        <td><span class="<?php echo $statusClass ?>"><?php echo $orderDetail['gems_weight']; ?></span></td>
                        <!-- <td><?php //echo $orderDetail['gems_price']; ?></td> -->
                        <td><span class="<?php echo $statusClass ?>">&#8377;<?php echo number_format($orderDetail['total'],2); ?></span></td>
                        <?php if(isset($orderDetail['discount']) && !empty($orderDetail['discount'])) { ?>
                            <td><span class="<?php echo $statusClass ?>">&#8377;<?php echo number_format($orderDetail['discount'],2); ?></span></td>
                        <?php } else { ?>
                            <td></td>
                        <?php } ?>
                        <td><span class="<?php echo $statusClass ?>">&#8377;<?php echo  number_format($orderDetail['grand_total'],2); ?></span></td>
                        <?php if($orderDetail['status'] == 1) { ?>
                            <td><span class="text-danger">Cancel</span></td>
                        <?php } else { ?>
                            <td><span class="text-success"><?php echo $this->Html->link('Confirm', 'javascript:void(0);',  array("class" => "text-success return_item", "escape" => false,'order_item_id'=>$orderDetail['id'],'item_grand_total'=>$orderDetail['grand_total'],'title'=>'Return this item')); ?></span></td>
                        <?php } ?>
                    </tr>
                    <?php }
                    // pr($activeItemTotal);die;
                    $confirmItemCount = count($confirmItem);
                    ?>
                    </tbody>
                  </table>
                  <br/><br/><br/><br/>
                <hr>
                <div class="col-md-12">
                  <div class="row xs-pt-12">
                    <div class="form-group col-sm-10">
                        <?php if (isset($orderDetails['Order']['comments']) && !empty($orderDetails['Order']['comments'])) { ?>
                            <label>Comments:&nbsp;&nbsp;</label>
                            <?php echo $orderDetails['Order']['comments']; ?>
                        <?php } ?>
                    </div>
                    <!-- <div class="form-group col-sm-2">
                        <label>Total:</label>
                        &#8377;<?php //echo number_format($orderDetails['Order']['total'],2); ?>
                    </div> -->
                </div>

                <!-- <div class="row xs-pt-12">
                    <div class="form-group col-sm-10">
                    </div>
                    <div class="form-group col-sm-2">
                        <label>Discount:</label>
                        <?php //if (isset($orderDetails['Order']['discount']) && !empty($orderDetails['Order']['discount'])) { ?>
                        &#8377;<?php //echo number_format($orderDetails['Order']['discount'],2); ?>
                        <?php// } else { ?>
                            &#8377; 0.0
                        <?php //} ?>
                    </div>
                </div> -->

                <div class="row xs-pt-12">
                    <div class="form-group col-sm-10">
                    </div>
                    <div class="form-group col-sm-2">
                        <label>Grand Total:</label>
                        &#8377;<?php echo number_format($orderDetails['Order']['grand_total'],2); ?>
                    </div>
                </div>

                 <div class="row xs-pt-12">
                    <div class="form-group col-sm-10">
                    </div>
                    <div class="form-group col-sm-2">
                        <label>Payment:</label>
                        &#8377;<?php 
                            $payment = 0;
                            foreach ($orderDetails['OrderTransaction'] as $orderTransaction) {
                                $payment+= $orderTransaction['amount_paid'];
                            }
                        echo number_format($payment,2); ?>
                    </div>
                </div>

                <div class="row xs-pt-12">
                    <div class="form-group col-sm-10">
                    </div>
                    <div class="form-group col-sm-2">
                        <label>Dues:</label>
                        <?php
                            if ($payment > $orderDetails['Order']['grand_total']) {
                                $dues = '0.00';
                            } else {
                                $sum = 0;
                                foreach ($orderDetails['OrderTransaction'] as $orderTransaction) {
                                    $sum+= $orderTransaction['amount_paid'];
                                }
                                $dues = ($orderDetails['Order']['grand_total'] - $sum);
                            }
                            
                            // echo (int)($dues);die;
                            
                        ?>
                        <span class="text-danger">&#8377;<?php echo number_format($dues,2); ?></span>
                    </div>
                </div>

                <?php
                    
                    if ($payment > $orderDetails['Order']['grand_total']) { 
                        $advance = ($payment - $orderDetails['Order']['grand_total']);
                        ?>
                        <div class="row xs-pt-12">
                            <div class="form-group col-sm-10">
                            </div>
                            <div class="form-group col-sm-2">
                                <label>Wallet:</label>
                                
                                <span class="text-success">&#8377;<?php echo number_format($advance,2); ?></span>
                            </div>
                        </div>
                <?php } ?>
                        
                <?php //$dues = (int)($dues); ?>
                

                </div>
                <div class="col-md-12">
                    <p class="xs-mt-10 xs-mb-10">
                        <button class="btn btn-rounded btn-space btn-success" id="order_invoice">Generate Order Invoice</button>
                        <?php if ($orderDetails['Order']['payment_status'] == 1) { ?>
                            <button class="btn btn-rounded btn-space btn-primary" id="make_payment">Make Payment</button>
                        <?php }
                        if ($orderDetails['Order']['status'] != 2) { ?>
                        <button class="btn btn-rounded btn-space btn-danger" id="cancel_order">Cancel Order</button>
                        <?php } ?>
                        <button class="btn btn-rounded btn-space btn-warning" id="payment_history">Payment History</button>
                        <button class="btn btn-rounded btn-space btn-default" id="payment_receipt">Generate Payment Receipt</button>
                    </p>
                </div>

                <?php //pr($confirmItemCount);die; ?>
                </div>
              </div>
            </div>
          </div>
      </div>



<div class="modal animated fadeIn" id="orderPayment" tabindex="-1" role="dialog" aria-labelledby="smallModalHead" aria-hidden="true">
    <div class="modal-dialog modal-lg" style=" margin: 0  auto;top:10%;">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header" style="text-align: center;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title" style="line-height:1;">Payment</h3><hr>
            </div>
            
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="col-md-3"><b>Order ID:</b></div>
                    <div class="col-md-9"><?php echo $orderDetails['Order']['order_number']; ?></div>
                </div>
                <br/><br/>

                
                <div class="col-md-12">
                    <div class="col-md-3"><b>Grand Total:</b></div>
                    <div class="col-md-9">&#8377;<?php echo number_format($orderDetails['Order']['grand_total'],2); ?></div>
                </div><br/><br/>

                <div class="col-md-12">
                    <div class="col-md-3"><b>Paid:</b></div>
                    <div class="col-md-9">&#8377;<?php echo number_format($payment,2); ?></div>
                </div><br/><br/>

                <div class="col-md-12">
                    <div class="col-md-3"><b>Dues:</b></div>
                    <div class="col-md-9"><span class="text-danger">&#8377;<?php echo number_format($dues,2); ?></span></div>
                </div><br/><br/>

                <div class="col-md-12">
                    <div class="col-md-4"><b>Payment Mode:</b></div>
                    <div class="col-md-8">
                        <?php echo $this->Form->input("OrderTransaction.type",array('type'=>'select','options'=>array('cash'=>'Cash','metal'=>'Metal','wallet'=>'Wallet','cheque'=>'Cheque','net-banking'=>'Net-Bannking','credit-card'=>'Credit Card','debit-card'=>'Debit Card'),'placeholder'=>'Enter category','required'=>'required','class'=>'form-control input-sm','label'=>false));?>
                        <?php //echo $this->Form->input("OrderTransaction.amount_paid",array('id'=>'dues_payment','placeholder'=>'Enter amount','type'=>'text','autocomplete'=>'off','required'=>'required','class'=>'form-control input-sm allowOnlyNumber','maxlength'=>'7','label'=>false));?>
                    </div>
                </div><br/><br/><br/><br/>

                <div class="col-md-12">
                    <div class="col-md-4"><?php echo $this->Form->input("OrderTransaction.amount_paid",array('id'=>'dues_payment','placeholder'=>'Enter amount','type'=>'text','autocomplete'=>'off','required'=>'required','class'=>'form-control input-sm allowOnlyNumber','maxlength'=>'7','label'=>false));?></div>
                    <div class="col-md-8"><?php echo $this->Form->input("OrderTransaction.comments",array('placeholder'=>'Enter comments','type'=>'text','autocomplete'=>'off','class'=>'form-control input-sm','label'=>false));?></div>
                </div>
                
                
                <div class="">
                    <div class="">
                        <div class="col-md-12">
                            <?php echo $this->Form->button('Make Payment',array('type'=>'button','id'=>'pay_dues','class'=>'btn btn-rounded btn-primary','style'=>'margin-top: 26px;margin-bottom: 18px;','escape'=>false));?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal animated fadeIn" id="paymentHistory" tabindex="-1" role="dialog" aria-labelledby="smallModalHead" aria-hidden="true">
    <div class="modal-dialog modal-lg" style=" margin: 0  auto;top:10%;width: 40%;">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header" style="text-align: center;">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h3 class="modal-title" style="line-height:1;">Payment History</h3><hr>
            </div>
            <div class="modal-body">
            <!-- <div class="panel-body table-responsive"> -->
                <table class="table">
                    <thead>
                        <tr>
                            <th >Invoice ID</th>
                            <th >Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody class="no-border-x">
                        <?php foreach ($orderDetails['OrderTransaction'] as $orderTransaction) {?>
                        <tr>
                            <td><?=$orderTransaction['invoice_number']?></td>
                            
                            <td>&#8377;<?php echo number_format($orderTransaction['amount_paid'],2); ?></td>
                            <td><?php echo date('d-M-Y h:i A', strtotime($orderTransaction['created'])); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <!-- </div> -->
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

<script type="text/javascript">
	$(document).ready(function() {
		$("#make_payment").click(function(){
            $('#dues_payment').val('');
            $('#orderPayment').modal('show');
            $('#dues_payment').focus();
		});

        $('#dues_payment').keyup(function(){
			var payment = $(this).val();
            var dues = '<?php echo $dues ?>';
            if (parseFloat(payment) > parseFloat(dues)) {
                alert('Amount should be less than dues.');
                $(this).val('');
            }
		});

        $("#pay_dues").click(function(){
            var orderId = '<?php echo $orderDetails['Order']['id']; ?>';
            var dues = '<?php echo $dues ?>';
            var payment = $('#dues_payment').val();
            if (payment == '') {
                alert('Please enter amount');
                return false;
            } else {
                $('#orderPayment').modal('hide');
                $.ajax({
                    url:"<?php echo Router::url(array('controller'=>'Orders','action'=>'pay_dues'));?>/"+ orderId + '/' + payment + '/' + dues,
                    success:function(data){
                        if (data == 1) {
                            location.reload();
                        } else {
                            alert('Error Occured!!');
                        }
                    }
			    });
            }
		});

        $('#payment_history').click(function(){
            $('#paymentHistory').modal('show');
        });

        $('#payment_receipt').click(function(){
            var orderId = '<?php echo $orderDetails['Order']['id']; ?>';
            var orderNumber = '<?php echo $orderDetails['Order']['order_number']; ?>';
            var customerId = '<?php echo $orderDetails['Order']['customer_id']; ?>';
            var grandTotal = '<?php echo $orderDetails['Order']['grand_total']; ?>';
            var base_url = "<?php echo Router::url(array('controller'=>'Orders','action'=>'generatePaymentHistory'));?>/" + orderId + '/' + customerId + '/' + grandTotal + '/' + orderNumber;
            //window.location.href=base_url;
            window.open(base_url,'_blank');
        });

        $('#cancel_order').click(function(){
            var orderId = '<?php echo $orderDetails['Order']['id']; ?>';
            if (confirm('Are you sure to cancel this order ?')) {
                $.ajax({
                    url:"<?php echo Router::url(array('controller'=>'Orders','action'=>'cancel_order'));?>/" + orderId,
                    success:function(data){
                        if (data == 1) {
                            location.reload();
                        } else {
                            alert('Error Occured!!');
                        }
                    }
			    });
            }
        });

        $('.return_item').click(function(){
            var orderId = '<?php echo $orderDetails['Order']['id']; ?>';
            var itemId = $(this).attr('order_item_id');
            var confirmItemCount = '<?php echo $confirmItemCount; ?>';
            var customerId = '<?php echo $orderDetails['Order']['customer_id']; ?>';
            var itemGrandTotal = $(this).attr('item_grand_total');
            var orderGrandTotal = '<?php echo $orderDetails['Order']['grand_total']; ?>';
            var orderPayment = '<?php echo $payment; ?>';
            var dues = '<?php echo $dues; ?>';
            // alert(dues);return false;
            if (confirm('Are you sure to cancel this item ?')) {
                $.ajax({
                    url:"<?php echo Router::url(array('controller'=>'Orders','action'=>'cancel_order_item'));?>/" + orderId + '/' + itemId + '/' + confirmItemCount + '/' + customerId + '/' + itemGrandTotal + '/' + orderGrandTotal + '/' + orderPayment + '/' + dues,
                    success:function(data){
                        if (data == 1) {
                            location.reload();
                        } else {
                            alert('Error Occured!!');
                        }
                    }
			    });
            }
        });

	});	
      
</script>
