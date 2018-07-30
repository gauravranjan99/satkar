<div class="be-content">
    <div class="main-content container-fluid">
    <?php echo $this->Session->flash(); ?>
        <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
            <div class="panel-body">
            <?php echo $this->Form->create('Customer',array('url'=> array('controller' => 'Customers', 'action' => 'add'),'method'=>'POST')); ?>
                <div class="form-group xs-pt-10">
                    <label>Name</label>
                    <?php echo $this->Form->input("Customer.name",array('placeholder'=>'Enter Name','required'=>'required','class'=>'form-control','label'=>false));?>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <?php echo $this->Form->input("Customer.address",array('type'=>'text','placeholder'=>'Enter Address','required'=>'required','class'=>'form-control','label'=>false));?>
                </div>
                <div class="form-group">
                    <label>Mobile</label>
                    <?php echo $this->Form->input("Customer.mobile",array('type'=>'text','max'=>10,'placeholder'=>'Enter Mobile Number','required'=>'required','class'=>'form-control','label'=>false));?>
                    <span style="color:red;" id="customerMobileAjaxMsg"></span>
                </div>
				<div class="form-group">
                    <label>Email</label>
                    <?php echo $this->Form->input("Customer.email",array('type'=>'email','placeholder'=>'Enter Email','class'=>'form-control','label'=>false));?>
					<span style="color:red;" id="customerEmailAjaxMsg"></span>
				</div>
                <div class="row xs-pt-15">
                    <div class="col-xs-6">
                    <?php echo $this->Form->button('Submit',array('class'=>'btn btn-space btn-primary','id'=>'registerCustomer','type'=>'submit'));?>
					<?php echo $this->Html->link('cancel', array('controller' => 'customers','action' => 'index'),array('class'=>'btn btn-space btn-default',));?>
                    </div>
                </div>
                <?php echo $this->Form->end();?>
            </div>
            </div>
        </div>
        </div>
    </div>
</div>
<script type="text/javascript">
      $(document).ready(function() {
        $('#CustomerEmail').blur(function() {
            var customerEmail = $(this).val();
            var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
            if (customerEmail != '') {
                if(pattern.test(customerEmail)) {
                    $.ajax({
                        type: "POST",
                        url:"<?php echo Router::url(array('controller'=>'customers','action'=>'check_email_unique'));?>",
                        data:({data:customerEmail}),
                        success: function(data) {
                            if(data==0) {
                                $("#customerEmailAjaxMsg").text(customerEmail+" already exists");
                                $("#customerEmailAjaxMsg").show();
                                $("#registerCustomer").attr('disabled','disabled');
                            } else {
                                $("#customerEmailAjaxMsg").hide();
                                $("#registerCustomer").removeAttr('disabled');
                            }
                        }
                    });
                } else {
                    $("#customerEmailAjaxMsg").show();
                    $("#customerEmailAjaxMsg").text("Please enter valid email address!");
                    $("#registerCustomer").removeAttr('disabled');
                }
            } else {
                $("#customerEmailAjaxMsg").hide();
                $("#registerCustomer").removeAttr('disabled');
            }
        });

        $('#CustomerMobile').keydown(function(e) {
            if (e.which>=96 && e.which<=105) {
                return true;
            } else if (e.shiftKey || e.ctrlKey || e.altKey) {
                e.preventDefault();
            } else {
                var regex = /^[0-9._]+|[\b]+$/;
                var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
                if (regex.test(str)) {
                    return true;
                }
            }
            e.preventDefault();
            return false;
        });

        $('#CustomerMobile').blur(function(e) {
            if($(this).val().length < 10) {
                $("#customerMobileAjaxMsg").show();
                $("#customerMobileAjaxMsg").text("Mobile Number must be of 10 digit");
                $("#registerCustomer").attr('disabled','disabled');
            } else {
				var customerMobile = $(this).val();
				$.ajax({
                type: "POST",
                url:"<?php echo Router::url(array('controller'=>'customers','action'=>'check_unique_mobile'));?>",
                data:({data:customerMobile}),
                success: function(data) {
                    if(data==0) {
                        $("#customerMobileAjaxMsg").text("This number is already registerd!");
                        $("#customerMobileAjaxMsg").show();
                        $("#registerCustomer").attr('disabled','disabled');
                    } else {
                        $("#customerMobileAjaxMsg").hide();
                        $("#registerCustomer").removeAttr('disabled');
                    }
                }
            });
            }
        });
        
      });
      
</script>