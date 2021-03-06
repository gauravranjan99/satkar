<?php
App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');
App::import('Vendor', 'PDF', array('file' => 'mpdf/vendor/autoload.php'));
App::import('Vendor', 'barcode', array('file' => 'barcode/vendor/autoload.php'));

class OrdersController extends AppController {

    public $helpers = array('Barcode','QrCode');
    public $components = array('Paginator','Encryption','Common');

    private function redirectToIndexPage($criteria) {
        if (isset($criteria['Order']['customer_id']) && !empty($criteria['Order']['customer_id'])) {
            $enocedCustomerId = $criteria['Order']['customer_id'];
            $customerId=$this->Encryption->decode($enocedCustomerId);
            $criteria['Order']['customer_id'] = $customerId;
            $this->Session->write('criteria', $criteria);
            $this->redirect(array('action' => 'index',$enocedCustomerId));
        } else {
            $this->Session->write('criteria', $criteria);
            $this->redirect(array('action' => 'index'));
        }
    }

    private function isClickedOnSearch($criteria) {
        /* Verifying search button is clicked and redirecting to first page. */
        if (isset($this->request->data['Order'])) {
            $this->redirectToIndexPage($criteria);
        }
        
        /* Doing this to show the search results when search happened in other than page 1. */
        if (!empty($this->Session->read('criteria'))) {
            $criteria = $this->Session->read('criteria');
            $this->Session->delete('criteria');
        }
        return $criteria;
    }

    public function index($customerId=null) {
        $this->layout = "my_layout";
        $Encryption=$this->Encryption;
        $customerId=$this->Encryption->decode($customerId);
        $this->loadModel('Order');
        $this->Order->unbindModel(array('hasMany' => array('OrderItem')),true);

        $criteria = "";
        
        if ($this->request->is(array('post','put'))) {
            $criteria = $this->request->data;
        }
        $criteria = $this->isClickedOnSearch($criteria);

        if (!empty($this->params->params['named']['criteria'])) {
            $criteria = $this->params->params['named']['criteria'];
        } else if (!empty($this->request->data['criteria'])) {
            $criteria = $this->request->data['criteria'];
        }

        if (isset($customerId) && !empty($customerId)) {
            $conditions = array('Order.customer_id' => $customerId);
        } else {
            $conditions = array();
        }

        if (isset($criteria['Order']['customer_id']) && !empty($criteria['Order']['customer_id'])) {
            $conditions = array('Order.customer_id' => $customerId);
        }

        if(!empty($criteria['Order']['order_number'])) {
            $conditions = array_merge($conditions,array('Order.order_number LIKE'=>trim("%".$criteria['Order']['order_number']."%")));
        }

        if(!empty($criteria['Order']['status'])) {
            if ($criteria['Order']['status'] == 'draft') {
                $conditions = array_merge($conditions,array('Order.status'=>'0'));
            } else {
                $conditions = array_merge($conditions,array('Order.status'=>$criteria['Order']['status']));
            }
        }
        
        if(!empty($criteria['Order']['payment_status'])) {
            if ($criteria['Order']['payment_status'] == 'completed') {
                $conditions = array_merge($conditions,array('Order.payment_status'=>'0'));
            } else {
                $conditions = array_merge($conditions,array('Order.payment_status'=>$criteria['Order']['payment_status']));
            }
        }

        if(!empty($criteria['Order']['start_date']) && !empty($criteria['Order']['end_date'])) {
            $dateTo = $criteria['Order']['start_date'];
            $dateFrom = $criteria['Order']['end_date'].' 23:59:59';
            $conditions = array_merge($conditions,array('Order.created BETWEEN ? AND ?'=>array($dateTo,$dateFrom)));  
        }
        
        $userType = $this->Auth->User('type');
        
        if ($userType == 'user') {
            $conditions = array_merge($conditions,array('Order.is_show'=>'1'));
        }
        $this->paginate = array('conditions' =>  $conditions,'order'=>'Order.id DESC','limit'=>20);
        $orderLists = $this->Paginator->paginate();
        $this->set('criteria', $criteria);
        $this->set(compact('orderLists','Encryption','customerId'));
    }

	public function add($customerId=null) {
        $this->layout = "my_layout";
        //$customerId=$this->Encryption->decode($customerId);
		if (empty($customerId)) {
			$this->redirect(array('controller'=>'customers','action'=>'index'));
        }
		$this->set('customerId',$customerId);
		$this->loadModel('Category');
		$categoryLists = $this->Category->find('list',array('conditions'=>array('Category.parent_id'=>0)));
		$this->set('categoryLists',$categoryLists);
		
		if ($this->request->is('post')) {
            // pr($this->request->data);die;
            // $transactionsObj = $this->Order->getDataSource();
            // $transactionsObj->begin();
            // $saveFlag = true;
            
            $customerId=$this->Encryption->decode($customerId);
            $orderNumber = $customerId. rand() . time();
            $this->loadModel('Order');
            $this->loadModel('OrderItem');
            $this->loadModel('OrderTransaction');
            $this->Order->create();
            $this->request->data['Order']['customer_id'] = $customerId;
            $this->request->data['Order']['order_number'] = $orderNumber;
            $this->request->data['Order']['total'] = $this->request->data['Order']['grand_total'];
            $grandTotal = floatval($this->request->data['Order']['grand_total']);
            if ($this->Order->save($this->request->data['Order'])) {
                $orderId = $this->Order->getLastInsertID();
                $orderItems = $this->request->data['OrderItem'];
                $orderItemFieldList = array(
                    array('name' => 'order_id','type' => 'int'),
                    array('name' => 'category_id','type' => 'int'),
                    array('name' => 'name','type' => 'varchar'),
                    array('name' => 'weight','type' => 'varchar'),
                    array('name' => 'rate','type' => 'varchar'),
                    array('name' => 'making_charge','type' => 'varchar'),
                    array('name' => 'purity','type' => 'varchar'),
                    array('name' => 'gems_name','type' => 'varchar'),
                    array('name' => 'gems_rate','type' => 'varchar'),
                    array('name' => 'gems_weight','type' => 'varchar'),
                    array('name' => 'gems_price','type' => 'decimal'),
                    array('name' => 'total','type' => 'decimal'),
                    array('name' => 'discount','type' => 'decimal'),
                    array('name' => 'grand_total','type' => 'decimal'),
                    array('name' => 'comments','type' => 'text')
                );

                $orderItemData = array();
                // prepare $taskHistoryData array to save record into vi_task_history table
                foreach ($orderItems as $orderItem) {
                    $orderItemData[] = array($orderId,$orderItem['category_id'],$orderItem['name'],isset($orderItem['weight'])?$orderItem['weight']:NULL,isset($orderItem['rate'])?$orderItem['rate']:NULL,isset($orderItem['making_charge'])?$orderItem['making_charge']:NULL,isset($orderItem['purity'])?$orderItem['purity']:NULL,isset($orderItem['gems_name'])?$orderItem['gems_name']:NULL,isset($orderItem['gems_rate'])?$orderItem['gems_rate']:NULL,isset($orderItem['gems_weight'])?$orderItem['gems_weight']:NULL,isset($orderItem['gems_price'])?$orderItem['gems_price']:NULL,$orderItem['total'],isset($orderItem['discount'])?$orderItem['discount']:NULL,$orderItem['grand_total'],isset($orderItem['comments'])?$orderItem['comments']:NULL);
                }

                $orderItemQuery = $this->Common->bulkInsert($orderItemFieldList,$orderItemData,"order_items");
                $this->OrderItem->query($orderItemQuery);

                // foreach ($orderItems as $orderItem) {
                //     $orderItem['order_id'] = $orderId;
                //     $this->OrderItem->create();
                //     $this->OrderItem->save($orderItem);
                // }
            }
            
            $encodedOrderId=$this->Encryption->encode($orderId);
            $this->redirect(array('controller'=>'Orders','action'=>'summary',$encodedOrderId));
		}
    }
    
    public function summary($orderId=null) {
        $this->layout = "my_layout";
        $this->loadModel('Order');
        $this->loadModel('OrderItem');
        $this->loadModel('Customer');
        $this->loadModel('OrderTransaction');
        $this->loadModel('Category');
        $orderId = $this->Encryption->decode($orderId);
        $Encryption=$this->Encryption;
        $this->Order->recursive = 2;
        $this->Order->unbindModel(array('belongsTo' => array('Customer')),true);
        $this->OrderTransaction->unbindModel(array('belongsTo' => array('Order')),true);
        $this->OrderItem->unbindModel(array('belongsTo' => array('Order')),true);
        $orderDetails = $this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId)));
        $customerId = $orderDetails['Order']['customer_id'];
        $customerDetails = $this->Customer->find('first',array('conditions'=>array('Customer.id'=>$customerId),'fields'=>array('name','address','mobile')));
        $categoryLists = $this->Category->find('list',array('conditions'=>array('Category.parent_id'=>0)));
        $this->set(compact('orderDetails','customerDetails','categoryLists','Encryption'));
    }


    public function delete_order($orderId=null) {
        $this->layout = false;
        $this->autoRender = false;
        $this->loadModel('Order');
        $this->Order->deleteAll(array('Order.id'=>$orderId));
        echo '1';
    }

    public function change_payment_status($orderId=null) {
        $this->layout = false;
        $this->autoRender = false;
        $this->loadModel('Order');
        $this->Order->updateAll(array('Order.payment_status' =>0),array('Order.id'=>$orderId));
        echo '1';
    }

    public function details($orderId=null) {
        $this->layout = "my_layout";
        $this->loadModel('Order');
        $this->loadModel('OrderItem');
        $this->loadModel('Customer');
        $this->loadModel('OrderTransaction');
        $this->loadModel('Category');
        $orderId = $this->Encryption->decode($orderId);
        $Encryption=$this->Encryption;
        $this->Order->recursive = 2;
        $this->Customer->recursive = -1;
        $this->Order->unbindModel(array('belongsTo' => array('Customer'),'hasMany'=>array('Wallet')),true);
        $this->OrderTransaction->unbindModel(array('belongsTo' => array('Order')),true);
        $this->OrderItem->unbindModel(array('belongsTo' => array('Order'),'hasMany'=>array('Wallet')),true);
        $orderDetails = $this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId)));
        $customerId = $orderDetails['Order']['customer_id'];
        $customerDetails = $this->Customer->find('first',array('conditions'=>array('Customer.id'=>$customerId),'fields'=>array('name','address','mobile')));
        $categoryLists = $this->Category->find('list',array('conditions'=>array('Category.parent_id'=>0)));
        $this->set(compact('orderDetails','customerDetails','categoryLists','paymentAmtToWords','Encryption'));
    }

    
    public function pay_dues() {
        $this->layout = false;
        $this->autoRender = false;
        $this->loadModel('OrderTransaction');
        $this->loadModel('Order');
        if ($this->request->is(array('post','put'))) {
            $this->request->data['OrderTransaction']['total_amount'] = $this->request->data['OrderTransaction']['amount_paid'];
            $dues = $this->request->data['OrderTransaction']['dues'];
            $amountPaid = $this->request->data['OrderTransaction']['amount_paid'];
            if ($this->request->data['OrderTransaction']['type'] == 'wallet') {
                $walletBalance = $this->request->data['OrderTransaction']['wallet_balance'];
                $remainingBalance = ($walletBalance - $amountPaid);
                $this->loadModel('Wallet');
                $this->Wallet->create();
                $walletData['Wallet']['customer_id'] = $this->request->data['OrderTransaction']['customer_id'];
                $walletData['Wallet']['order_id'] = $this->request->data['OrderTransaction']['order_id'];
                $walletData['Wallet']['order_number'] = $this->request->data['OrderTransaction']['order_number'];
                $walletData['Wallet']['debit'] = $amountPaid;
                $walletData['Wallet']['type'] = 'pay-dues';
                $walletData['Wallet']['balance'] = $remainingBalance;
                if ($this->Wallet->save($walletData)) {
                    unset($this->request->data['OrderTransaction']['dues']);
                    unset($this->request->data['OrderTransaction']['customer_id']);
                    unset($this->request->data['OrderTransaction']['wallet_balance']);
                    unset($this->request->data['OrderTransaction']['order_number']);
                    $orderId = $this->request->data['OrderTransaction']['order_id'];
                    $invoiceNumber =  rand() .$orderId . time();
                    $this->request->data['OrderTransaction']['invoice_number'] = $invoiceNumber;
                    $this->request->data['OrderTransaction']['transaction_date'] = date('Y-m-d H:i:s');
                    $this->OrderTransaction->create();
                    if ($this->OrderTransaction->save($this->request->data)) {
                        if ($amountPaid == $dues) {
                            $this->Order->updateAll(array('Order.payment_status' =>0),array('Order.id'=>$orderId));
                        }
                        echo '1';
                    }
                }
            } else {
                if ($amountPaid > $dues) {
                    $remainingBal = ($amountPaid - $dues); 
                    $customerId = $this->request->data['OrderTransaction']['customer_id'];
                    $this->loadModel('Wallet');
                    $this->Wallet->recursive = -1;
                    $walletBal = $this->Wallet->find('first',array('conditions' => array('Wallet.customer_id' =>$customerId),'fields'=>array('balance'),'order' => array('Wallet.id' => 'DESC')));
                    if (!empty($walletBal)) {
                        $walletMoney = $walletBal['Wallet']['balance'];
                    } else {
                        $walletMoney = '0.00';
                    }
                    $orderId = $this->request->data['OrderTransaction']['order_id'];
                    $invoiceNumber =  rand() .$orderId . time();
                    $this->request->data['OrderTransaction']['invoice_number'] = $invoiceNumber;
                    $this->request->data['OrderTransaction']['amount_paid'] = $dues;
                    if (!empty($this->request->data['OrderTransaction']['cheque_bank_name'])) {
                        $this->request->data['OrderTransaction']['bank_name'] = $this->request->data['OrderTransaction']['cheque_bank_name'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['bank_name'])) {
                        $this->request->data['OrderTransaction']['bank_name'] = $this->request->data['OrderTransaction']['bank_name'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['cheque_transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = $this->request->data['OrderTransaction']['cheque_transaction_date'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = $this->request->data['OrderTransaction']['transaction_date'];
                    }
                    if (empty($this->request->data['OrderTransaction']['transaction_date']) && empty($this->request->data['OrderTransaction']['cheque_transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = date('Y-m-d H:i:s');
                    }
                    $this->OrderTransaction->create();
                    if ($this->OrderTransaction->save($this->request->data)) {
                        $this->Order->updateAll(array('Order.payment_status' =>0),array('Order.id'=>$orderId));
                        
                        $this->Wallet->create();
                        $walletData['Wallet']['customer_id'] = $customerId;
                        $walletData['Wallet']['order_id'] = $this->request->data['OrderTransaction']['order_id'];
                        $walletData['Wallet']['order_number'] = $this->request->data['OrderTransaction']['order_number'];
                        if (!empty($this->request->data['OrderTransaction']['item'])) {
                            $walletData['Wallet']['item'] = $this->request->data['OrderTransaction']['item'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['metal_type'])) {
                            $walletData['Wallet']['metal_type'] = $this->request->data['OrderTransaction']['metal_type'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['weight'])) {
                            $walletData['Wallet']['weight'] = $this->request->data['OrderTransaction']['weight'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['return_percentage'])) {
                            $walletData['Wallet']['return_percentage'] = $this->request->data['OrderTransaction']['return_percentage'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['rate'])) {
                            $walletData['Wallet']['rate'] = $this->request->data['OrderTransaction']['rate'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['cheque_number'])) {
                            $walletData['Wallet']['cheque_number'] = $this->request->data['OrderTransaction']['cheque_number'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['cheque_bank_name'])) {
                            $walletData['Wallet']['bank_name'] = $this->request->data['OrderTransaction']['cheque_bank_name'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['bank_name'])) {
                            $walletData['Wallet']['bank_name'] = $this->request->data['OrderTransaction']['bank_name'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['cheque_transaction_date'])) {
                            $walletData['Wallet']['transaction_date'] = $this->request->data['OrderTransaction']['cheque_transaction_date'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['transaction_date'])) {
                            $walletData['Wallet']['transaction_date'] = $this->request->data['OrderTransaction']['transaction_date'];
                        }
                        if (!empty($this->request->data['OrderTransaction']['payment_transaction_id'])) {
                            $walletData['Wallet']['payment_transaction_id'] = $this->request->data['OrderTransaction']['payment_transaction_id'];
                        }
                        $walletData['Wallet']['type'] = $this->request->data['OrderTransaction']['type'];
                        $walletData['Wallet']['credit'] = $remainingBal;
                        $walletData['Wallet']['comments'] = $this->request->data['OrderTransaction']['comments'];
                        $walletData['Wallet']['balance'] = ($walletMoney + $remainingBal);
                        $this->Wallet->save($walletData);
                        echo '1';
                    }

                } else if ($amountPaid < $dues) {
                    $orderId = $this->request->data['OrderTransaction']['order_id'];
                    $invoiceNumber =  rand() .$orderId . time();
                    $this->request->data['OrderTransaction']['invoice_number'] = $invoiceNumber;
                    $this->request->data['OrderTransaction']['amount_paid'] = $amountPaid;
                    if (!empty($this->request->data['OrderTransaction']['cheque_bank_name'])) {
                        $this->request->data['OrderTransaction']['bank_name'] = $this->request->data['OrderTransaction']['cheque_bank_name'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['bank_name'])) {
                        $this->request->data['OrderTransaction']['bank_name'] = $this->request->data['OrderTransaction']['bank_name'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['cheque_transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = $this->request->data['OrderTransaction']['cheque_transaction_date'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = $this->request->data['OrderTransaction']['transaction_date'];
                    }
                    if (empty($this->request->data['OrderTransaction']['transaction_date']) && empty($this->request->data['OrderTransaction']['cheque_transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = date('Y-m-d H:i:s');
                    }
                    unset($this->request->data['OrderTransaction']['dues']);
                    unset($this->request->data['OrderTransaction']['customer_id']);
                    unset($this->request->data['OrderTransaction']['wallet_balance']);
                    unset($this->request->data['OrderTransaction']['order_number']);
                    $this->OrderTransaction->create();
                    $this->OrderTransaction->save($this->request->data);
                    $this->Order->updateAll(array('Order.payment_status' =>1),array('Order.id'=>$orderId));
                    echo '1';
                } else if ($amountPaid == $dues) {
                    $orderId = $this->request->data['OrderTransaction']['order_id'];
                    $invoiceNumber =  rand() .$orderId . time();
                    $this->request->data['OrderTransaction']['invoice_number'] = $invoiceNumber;
                    $this->request->data['OrderTransaction']['amount_paid'] = $amountPaid;
                    if (!empty($this->request->data['OrderTransaction']['cheque_bank_name'])) {
                        $this->request->data['OrderTransaction']['bank_name'] = $this->request->data['OrderTransaction']['cheque_bank_name'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['bank_name'])) {
                        $this->request->data['OrderTransaction']['bank_name'] = $this->request->data['OrderTransaction']['bank_name'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['cheque_transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = $this->request->data['OrderTransaction']['cheque_transaction_date'];
                    }
                    if (!empty($this->request->data['OrderTransaction']['transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = $this->request->data['OrderTransaction']['transaction_date'];
                    }
                    if (empty($this->request->data['OrderTransaction']['transaction_date']) && empty($this->request->data['OrderTransaction']['cheque_transaction_date'])) {
                        $this->request->data['OrderTransaction']['transaction_date'] = date('Y-m-d H:i:s');
                    }
                    unset($this->request->data['OrderTransaction']['dues']);
                    unset($this->request->data['OrderTransaction']['customer_id']);
                    unset($this->request->data['OrderTransaction']['wallet_balance']);
                    unset($this->request->data['OrderTransaction']['order_number']);
                    $this->OrderTransaction->create();
                    $this->OrderTransaction->save($this->request->data);
                    $this->Order->updateAll(array('Order.payment_status' =>0),array('Order.id'=>$orderId));
                    echo '1';
                }
            }
        }
    }

    public function generatePaymentHistory($orderId=null,$customerId=null,$grandTotal=null,$orderNumber=null) {
        $this->layout = "ajax";
        $this->autoRender = false;
        $this->set('title_for_layout','payment History');
        error_reporting(0);
        $this->loadModel('OrderTransaction');
        $this->loadModel('Customer');
        $view = new View($this, false);
        $this->Customer->unbindModel(array('hasMany' => array('Order')),true);
        $customerDetails = $this->Customer->find('first',array('conditions'=>array('Customer.id'=>$customerId),'fields'=>array('name','address','mobile','email')));
        $this->OrderTransaction->unbindModel(array('belongsTo' => array('Order')),true);
        $paymentLists = $this->OrderTransaction->find('all',array('conditions'=>array('OrderTransaction.order_id'=>$orderId)) ,array('order'=>array('OrderTransaction.id'=>'desc')));
        $filename =  "order". '-'. date("m-d-y");
        $view->set(compact('paymentLists','customerDetails','grandTotal','orderNumber'));
        $html = $view->render('payment_history_pdf');
        // $pdf= new mPDF('utf-8', 'A4-L');
        //A4-P is for portrait view
        $pdf= new mPDF('utf-8', 'A4-P');
        // Define a Landscape page size/format by name
        //$mpdf=new mPDF('utf-8', 'A4-L');
        $pdf->WriteHTML($html);
        // $pdf->Output($filename.".pdf", "D");
        $pdf->Output($filename.".pdf", "I");
    }

    public function generateOrderInvoice($orderId=null,$orderNumber=null,$grandTotal=null,$payment=null,$dues=null,$paymentForWords=null){
        $this->layout = "ajax";
        $this->autoRender = false;
        ini_set('memory_limit', '-1');
        error_reporting(0);
        $view = new View($this, false);
        $orderId=$this->Encryption->decode($orderId);
        $this->Order->unbindModel(array('hasMany' => array('OrderTransaction','Wallet')),true);
        $orderDetails = $this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId)));
        $paymentAmtToWords =  $this->getIndianCurrency($paymentForWords);
        // pr($paymentAmtToWords);die;
        $barcodeGenerator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $view->set(compact('orderDetails','orderNumber','grandTotal','payment','dues','barcodeGenerator','paymentAmtToWords'));
        $filename = $orderNumber;
        $html = $view->render('invoice_pdf');
        $pdf= new mPDF('utf-8', 'A4-L');
        $pdf->WriteHTML($html);
        $pdf->Output($filename.".pdf", "I");
    }

    public function cancel_order($orderId=null,$dues=null,$payment=null,$customerId=null,$orderNumber=null) {
        $this->autoRender = false;
        $this->layout = false;
        $this->loadModel('Order');
        $this->loadModel('OrderItem');
        $this->loadModel('Wallet');
        $this->OrderItem->recursive = -1;
        $orderItemDetails = $this->OrderItem->find('list',array('conditions'=>array('OrderItem.order_id'=>$orderId,'OrderItem.status'=>0),'fields'=>array('id','grand_total')));
        
        $this->Wallet->recursive = -1;
        $Latest = $this->Wallet->find('first',array('conditions' => array('Wallet.customer_id' => $customerId),'fields'=>array('Wallet.balance'),'order' => array('Wallet.id' => 'DESC')));
        if (empty($Latest)) {
            $Latest['Wallet']['balance'] = '0.00';
        }
        //comma seperated order items id
        $orderItemId = array_keys($orderItemDetails);
        $orderItemIds = implode(",",$orderItemId);
        //item grand total
        $itemTotal = 0;
        foreach ($orderItemDetails as $orderItemDetail) {
            $itemTotal+= $orderItemDetail;
        }
        $itemsGrandTotal = round($itemTotal);
        // $cancelDate = date('Y-m-d H:i:s');
        $this->OrderItem->updateAll(array('OrderItem.status' =>1,'Order.status' =>2,'Order.payment_status' =>0,'Order.grand_total'=>'0.00'),array('OrderItem.order_id'=>$orderId));
        //Need to check
        $this->Order->id=$orderId;
		$this->Order->saveField("cancel_date",date('Y-m-d H:i:s'));
        // $this->OrderItem->updateAll(array('OrderItem.status' =>1),array('OrderItem.order_id'=>$orderId));
        // $this->Order->updateAll(array('Order.cancel_date'=>date('Y-m-d H:i:s')),array('Order.id'=>$orderId));
        
        if (empty($dues)) {
            //credit item total amt in wallet
            $walletData['Wallet']['customer_id'] = $customerId;
            $walletData['Wallet']['order_id'] = $orderId;
            $walletData['Wallet']['order_item_id'] = $orderItemIds;
            $walletData['Wallet']['order_number'] = $orderNumber;
            $walletData['Wallet']['type'] = 'cancel-order';
            $walletData['Wallet']['credit'] = $itemsGrandTotal;
            $walletData['Wallet']['balance'] = $Latest['Wallet']['balance'] + $itemsGrandTotal;
            $this->Wallet->create();
            $this->Wallet->save($walletData);
            echo "1";
        } else {
            $walletData['Wallet']['customer_id'] = $customerId;
            $walletData['Wallet']['order_id'] = $orderId;
            $walletData['Wallet']['order_item_id'] = $orderItemIds;
            $walletData['Wallet']['order_number'] = $orderNumber;
            $walletData['Wallet']['type'] = 'cancel-order';
            $walletData['Wallet']['credit'] = $payment;
            $walletData['Wallet']['balance'] = $Latest['Wallet']['balance'] + $payment;
            $this->Wallet->create();
            $this->Wallet->save($walletData);
            echo "1";
        }
    }

    public function cancel_order_item($orderId=null,$orderItemId=null,$confirmItemCount=null,$customerId=null,$itemGrandTotal=null,$orderGrandTotal=null,$orderPayment=null,$dues=null,$orderNumber=null) {
        $this->autoRender = false;
        $this->layout = false;
        $this->loadModel('Wallet');
        $this->Wallet->unbindModel(array('belongsTo' => array('Order','OrderItem','Customer')),true);
        $Latest = $this->Wallet->find('first',array('conditions' => array('Wallet.customer_id' => $customerId),'order' => array('Wallet.id' => 'DESC')));
        $newGrandTotal = ($orderGrandTotal - $itemGrandTotal);
        $newGrandTotal = round($newGrandTotal);
        $this->loadModel('OrderItem');
        $this->OrderItem->updateAll(array('OrderItem.status' =>1,'Order.grand_total'=>$newGrandTotal),array('OrderItem.id'=>$orderItemId));
        //Need to check again
        $this->OrderItem->id=$orderItemId;
        $this->OrderItem->saveField("cancel_date",date('Y-m-d H:i:s'));
        
        if ($confirmItemCount == 1) {
            $this->loadModel('Order');
            $this->Order->updateAll(array('Order.status' =>2),array('Order.id'=>$orderId));
        } else {
            $this->loadModel('Order');
            $this->Order->updateAll(array('Order.status' =>3),array('Order.id'=>$orderId));
        }

        if ($orderPayment > $newGrandTotal) { 
            $this->Order->updateAll(array('Order.payment_status' =>0),array('Order.id'=>$orderId));
        }

        $dues = round($dues);
        $dues = (int)($dues);

        if (empty($dues)) {
            $this->Wallet->create();
            $walletData['Wallet']['customer_id'] = $customerId;
            $walletData['Wallet']['order_id'] = $orderId;
            $walletData['Wallet']['order_number'] = $orderNumber;
            $walletData['Wallet']['order_item_id'] = $orderItemId;
            $walletData['Wallet']['credit'] = $itemGrandTotal;
            $walletData['Wallet']['type'] = 'return-item';
            if (empty($Latest)) {
                $walletData['Wallet']['balance'] = $itemGrandTotal;
            } else {
                $walletData['Wallet']['balance'] = $Latest['Wallet']['balance'] + $itemGrandTotal;
            }
            $this->Wallet->save($walletData);
        } else {
            if ($orderPayment > $newGrandTotal) {
                $customerAdvance = ($orderPayment - $newGrandTotal);
                $this->Wallet->create();
                $walletData['Wallet']['customer_id'] = $customerId;
                $walletData['Wallet']['order_id'] = $orderId;
                $walletData['Wallet']['order_number'] = $orderNumber;
                $walletData['Wallet']['order_item_id'] = $orderItemId;
                $walletData['Wallet']['credit'] = $customerAdvance;
                $walletData['Wallet']['type'] = 'return-item';
                if (empty($Latest)) {
                    $walletData['Wallet']['balance'] = $customerAdvance;
                } else {
                    $walletData['Wallet']['balance'] = $Latest['Wallet']['balance'] + $customerAdvance;
                }
                $this->Wallet->save($walletData);
            }
        }
        echo "1";
    }

    public function confirm_order($orderId=null) {
        $this->autoRender = false;
        $this->layout = false;
        $this->loadModel('Order');
        $this->Order->updateAll(array('Order.status' =>1),array('Order.id'=>$orderId));
        echo "1";
    }

    public function extra_discount() {
        $this->autoRender = false;
        $this->layout = false;
        $this->loadModel('Order');
        $this->loadModel('OrderItem');
        if ($this->request->is(array('post','put'))) {
            $itemId = $this->request->data['discount_details']['item_id'];
            $orderId = $this->request->data['discount_details']['order_id'];
            
            $itemTotalDiscount = (float)$this->request->data['discount_details']['item_discount'] + (float)$this->request->data['discount_details']['item_extra_discount']; 
            $itemNewGrandTotal = ((float)$this->request->data['discount_details']['item_grand_total'] - (float)$this->request->data['discount_details']['item_extra_discount']);
            
            $this->OrderItem->updateAll(array('OrderItem.grand_total' =>$itemNewGrandTotal,'OrderItem.discount' =>$itemTotalDiscount),array('OrderItem.id'=>$itemId));
            $newOrderGrandTotal = ((float)$this->request->data['discount_details']['order_grand_total'] - (float)$this->request->data['discount_details']['item_extra_discount']);
            
            if ((float)$this->request->data['discount_details']['item_discount'] == (float)$this->request->data['discount_details']['dues']) {
                $this->Order->updateAll(array('Order.grand_total' =>$newOrderGrandTotal,'Order.payment_status' =>0),array('Order.id'=>$orderId));
            } else {
                $this->Order->updateAll(array('Order.grand_total' =>$newOrderGrandTotal),array('Order.id'=>$orderId));
            }
            echo '1';
        }
    }

    public function add_more_item() {
        $this->autoRender = false;
        $this->layout = false;
        $this->loadModel('Order');
        $this->loadModel('OrderItem');
        if ($this->request->is(array('post','put'))) {
            $orderId = $this->request->data['Order']['order_id'];
            $this->Order->recursive = -1;
            $orderDetails = $this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId),'fields'=>array('Order.grand_total','total')));
            $orderGrandTotal = $orderDetails['Order']['grand_total'];
            $orderTotal = $orderDetails['Order']['total'];
            $newItemsGrandTotal = (float)$this->request->data['Order']['grand_total'];
            $newOrderGrandTotal = (float)($orderGrandTotal + $newItemsGrandTotal);
            
            foreach ($this->request->data['OrderItem'] as $orderItem) {
                $orderTotal+= (float)$orderItem['total'];
                $orderItem['order_id'] = $orderId;
                $this->OrderItem->create();
                $this->OrderItem->save($orderItem);
            }
            $this->Order->updateAll(array('Order.total' =>$orderTotal,'Order.grand_total' =>$newOrderGrandTotal),array('Order.id'=>$orderId));
            echo '1';
        }
    }

    public function delete_order_item() {
        $this->autoRender = false;
        $this->layout = false;
        $this->loadModel('Order');
        $this->loadModel('OrderItem');
        if ($this->request->is(array('post','put'))) {
            $orderId = $this->request->data['delete_details']['order_id'];
            $orderGrandTotal = $this->request->data['delete_details']['order_grand_total'];
            $orderTotal = $this->request->data['delete_details']['order_total'];
            $itemTotal = $this->request->data['delete_details']['item_total'];
            $itemGrandTotal = $this->request->data['delete_details']['item_grand_total'];
            $newOrderTotal = (float)($orderTotal - $itemTotal);
            $newOrderGrandTotal = (float)($orderGrandTotal - $itemGrandTotal);
            $this->OrderItem->deleteAll(array('OrderItem.id'=>$this->request->data['delete_details']['item_id']));
            $this->Order->updateAll(array('Order.total' =>$newOrderTotal,'Order.grand_total' =>$newOrderGrandTotal),array('Order.id'=>$orderId));
            echo '1';
        }
    }

    public function admin_index($customerId=null) {
        $this->layout = "admin_layout";
        $Encryption=$this->Encryption;
        $customerId=$this->Encryption->decode($customerId);
        $this->loadModel('Order');
        $this->Order->unbindModel(array('hasMany' => array('OrderItem')),true);

        $criteria = "";
        
        if ($this->request->is(array('post','put'))) {
            $criteria = $this->request->data;
        }
        $criteria = $this->isClickedOnSearch($criteria);

        if (!empty($this->params->params['named']['criteria'])) {
            $criteria = $this->params->params['named']['criteria'];
        } else if (!empty($this->request->data['criteria'])) {
            $criteria = $this->request->data['criteria'];
        }

        if (isset($customerId) && !empty($customerId)) {
            $conditions = array('Order.customer_id' => $customerId);
        } else {
            $conditions = array();
        }

        if (isset($criteria['Order']['customer_id']) && !empty($criteria['Order']['customer_id'])) {
            $conditions = array('Order.customer_id' => $customerId);
        }

        if(!empty($criteria['Order']['order_number'])) {
            $conditions = array_merge($conditions,array('Order.order_number LIKE'=>trim("%".$criteria['Order']['order_number']."%")));
        }

        if(!empty($criteria['Order']['status'])) {
            if ($criteria['Order']['status'] == 'draft') {
                $conditions = array_merge($conditions,array('Order.status'=>'0'));
            } else {
                $conditions = array_merge($conditions,array('Order.status'=>$criteria['Order']['status']));
            }
        }
        
        if(!empty($criteria['Order']['payment_status'])) {
            if ($criteria['Order']['payment_status'] == 'completed') {
                $conditions = array_merge($conditions,array('Order.payment_status'=>'0'));
            } else {
                $conditions = array_merge($conditions,array('Order.payment_status'=>$criteria['Order']['payment_status']));
            }
        }

        if(!empty($criteria['Order']['is_show'])) {
            if ($criteria['Order']['is_show'] == 'hide') {
                $conditions = array_merge($conditions,array('Order.is_show'=>'0'));
            } else {
                $conditions = array_merge($conditions,array('Order.is_show'=>$criteria['Order']['is_show']));
            }
        }

        if(!empty($criteria['Order']['start_date']) && !empty($criteria['Order']['end_date'])) {
            $dateTo = $criteria['Order']['start_date'];
            $dateFrom = $criteria['Order']['end_date'].' 23:59:59';
            $conditions = array_merge($conditions,array('Order.created BETWEEN ? AND ?'=>array($dateTo,$dateFrom)));  
        }

        $this->paginate = array('conditions' =>  $conditions,'order'=>'Order.id DESC','limit'=>20);
        $orderLists = $this->Paginator->paginate();
        $this->set('criteria', $criteria);
        $this->set(compact('orderLists','Encryption','customerId'));
    }

    public function admin_details($orderId=null) {
        $this->layout = "admin_layout";
        $this->loadModel('Order');
        $this->loadModel('OrderItem');
        $this->loadModel('Customer');
        $this->loadModel('OrderTransaction');
        $this->loadModel('Category');
        $orderId = $this->Encryption->decode($orderId);
        $Encryption=$this->Encryption;
        $this->Order->recursive = 2;
        $this->Customer->recursive = -1;
        $this->Order->unbindModel(array('belongsTo' => array('Customer'),'hasMany'=>array('Wallet')),true);
        $this->OrderTransaction->unbindModel(array('belongsTo' => array('Order')),true);
        $this->OrderItem->unbindModel(array('belongsTo' => array('Order'),'hasMany'=>array('Wallet')),true);
        $orderDetails = $this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId)));
        $customerId = $orderDetails['Order']['customer_id'];
        $customerDetails = $this->Customer->find('first',array('conditions'=>array('Customer.id'=>$customerId),'fields'=>array('name','address','mobile')));
        $categoryLists = $this->Category->find('list',array('conditions'=>array('Category.parent_id'=>0)));
		$this->set(compact('orderDetails','customerDetails','categoryLists','Encryption'));
    }

    public function admin_generatePaymentHistory($orderId=null,$customerId=null,$grandTotal=null,$orderNumber=null) {
        $this->layout = "ajax";
        $this->autoRender = false;
        $this->set('title_for_layout','payment History');
        error_reporting(0);
        $this->loadModel('OrderTransaction');
        $this->loadModel('Customer');
        $view = new View($this, false);
        $this->Customer->unbindModel(array('hasMany' => array('Order')),true);
        $customerDetails = $this->Customer->find('first',array('conditions'=>array('Customer.id'=>$customerId),'fields'=>array('name','address','mobile','email')));
        $this->OrderTransaction->unbindModel(array('belongsTo' => array('Order')),true);
        $paymentLists = $this->OrderTransaction->find('all',array('conditions'=>array('OrderTransaction.order_id'=>$orderId)) ,array('order'=>array('OrderTransaction.id'=>'desc')));
        $filename =  "order". '-'. date("m-d-y");
        $view->set(compact('paymentLists','customerDetails','grandTotal','orderNumber'));
        $html = $view->render('payment_history_pdf');
        $pdf= new mPDF('utf-8', 'A4-P');
        $pdf->WriteHTML($html);
        $pdf->Output($filename.".pdf", "I");
    }

    public function admin_generateOrderInvoice($orderId=null,$orderNumber=null,$grandTotal=null,$payment=null,$dues=null){
        $this->layout = "ajax";
        $this->autoRender = false;
        ini_set('memory_limit', '-1');
        error_reporting(0);
        $view = new View($this, false);
        $orderId=$this->Encryption->decode($orderId);
        $this->Order->unbindModel(array('hasMany' => array('OrderTransaction','Wallet')),true);
        $orderDetails = $this->Order->find('first',array('conditions'=>array('Order.id'=>$orderId)));
        $barcodeGenerator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $view->set(compact('orderDetails','orderNumber','grandTotal','payment','dues','barcodeGenerator'));
        $filename = $orderNumber;
        $html = $view->render('invoice_pdf');
        $pdf= new mPDF('utf-8', 'A4-L');
        $pdf->WriteHTML($html);
        $pdf->Output($filename.".pdf", "I");
    }

    public function admin_changeShowStatus($orderId,$orderShowStatus) {
        $this->layout = false;
        $this->autoRender = false;
        if ($orderShowStatus == 'show_order') {
            $this->Order->updateAll(array('Order.is_show' =>1),array('Order.id'=>$orderId));
            echo json_encode(array('success' => true, 'msg' =>'show_order'));
            // echo '1';
        } else if ($orderShowStatus == 'hide_order') {
            $this->Order->updateAll(array('Order.is_show' =>0),array('Order.id'=>$orderId));
            echo json_encode(array('success' => true, 'msg' =>'hide_order'));
            // echo '1';
        }
    }

    public function getIndianCurrency($number) {
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'one', 2 => 'two',
        3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
        7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve',
        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
    $digits = array('', 'hundred','thousand','lakh', 'crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
}



}
