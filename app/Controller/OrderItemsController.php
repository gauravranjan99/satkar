<?php
App::uses('AppController', 'Controller');
/**
 * OrderItems Controller
 */
class OrderItemsController extends AppController {

	public function add($customerId=null) {
		$this->layout = "my_layout";
		if (empty($customerId)) {
			$this->redirect(array('controller'=>'customers','action'=>'index'));
		}
		$this->set('customerId',$customerId);
		$this->loadModel('Category');
		$categoryLists = $this->Category->find('list',array('conditions'=>array('Category.parent_id'=>0)));
		$this->set('categoryLists',$categoryLists);
		
		if ($this->request->is('post')) {
			pr($this->request->data);
			pr($this->request->data['OrderItem']);die;
			// foreach ()
		}
		
		
	}



}
