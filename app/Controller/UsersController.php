<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 * @property PaginatorComponent $Paginator
 */
class UsersController extends AppController {

/**
 * Components
 *
 * @var array
 */
	//public $components = array('Paginator');
	public $components = array(
		'Auth' => array(
			'loginRedirect' => array('controller' => 'users', 'action' => 'index'),
			'logoutRedirect' => array('controller' => 'users', 'action' => 'login'),
		)
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('login','register');
	}

	public function register() {
	    if(!empty($this->request->data)) {
			$this->request->data['User']['status'] = 1;
			$this->request->data['User']['role'] = 1;
			$username = $this->data['User']['username'];
			$password = $this->data['User']['password'];
			$confirmPassword = $this->data['User']['confirm_passowrd'];
			$isUserExist = $this->User->find('first',array('conditions'=>array('User.username'=>$username)));
			if(!empty($isUserExist)) {
				$this->Session->SetFlash('User already exists!!', 'error');
			}
			else if($password != $confirmPassword){
				$this->Session->SetFlash('Password does not match', 'error');
			} else {
				unset($this->request->data['User']['confirm_passowrd']);
				$this->request->data['User']['password'] = AuthComponent::password($password);
				if($this->User->save($this->request->data)) {
					$this->redirect(array('controller'=>'users','action'=>'login'));
				}
			}
		}
	}
	
	public function login() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				$this->redirect($this->Auth->redirect());
			} else {
				$this->Session->SetFlash('Invalid username or password, please try again!!', 'error');
				$this->request->data = array();
			}
		}
	}
	  
	public function logout() {
		$this->redirect($this->Auth->logout());
	}

	public function index() {
		$this->layout = "my_layout";
		// $this->User->recursive = 0;
		// $this->set('users', $this->Paginator->paginate());
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
		$this->set('user', $this->User->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->Flash->success(__('The user has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The user could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->User->save($this->request->data)) {
				$this->Flash->success(__('The user has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Flash->error(__('The user could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
			$this->request->data = $this->User->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->User->delete()) {
			$this->Flash->success(__('The user has been deleted.'));
		} else {
			$this->Flash->error(__('The user could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
