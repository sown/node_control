<?php defined('SYSPATH') or die('No direct script access.');

class Controller_NodeSetupRequests extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("All Node Setup Requests" => Route::url('node_setup_requests'), "Pending Node Setup Requests" => Route::url('pending_node_setup_requests'), "Create Approved Node Setup Request" => Route::url('create_node_setup_request'),);
                $title = 'Node Setup Requests';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Node Setup Requests";
		View::bind_global('subtitle', $subtitle);
		$cssFiles =  array('jquery-ui.css');
                View::bind_global('cssFiles', $cssFiles);
                $jsFiles = array('jquery.js', 'jquery-ui.js');
                View::bind_global('jsFiles', $jsFiles);

                $limit = 20;
                $page = 1;
                $formValues = $this->request->post();
                if ($this->request->param('page') && is_numeric($this->request->param('page')) && $this->request->param('page') > 0)
                {
                        $page = $this->request->param('page');
                }
                elseif(!empty($formValues['page']) && is_numeric($formValues['page']) && $formValues['page'] > 0)
                {
                        $page = $formValues['page'];
                }
                $offset = ($page-1) * $limit;
                $date = '';
		$node = '';
                $formValues = $this->request->post();

                if (!empty($formValues['date']))
                {
                        $date = $formValues['date'];
                        $dateymd = date("Y-m-d", strtotime($date));
                }
		if (!empty($formValues['node']))
                {
                        $node = $formValues['node'];
                }
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
                $content = $this->template->datenode = View::factory('partial/datenode')->bind('date', $date)->bind('node', $node);
		$fields = array(
                        'id' => 'ID',
			'mac' => 'MAC Address',
			'ipAddr' => 'Requesting IP Address',
			'requestedDate' => 'Requested Date',
			'status' => 'Status',
			'nodeBoxNumber' => 'Node',
			'lastModified' => 'Last Modified',
                        'view' => '',
                        'delete' => '',
                );


		$qb = Doctrine::em()->getRepository('Model_NodeSetupRequest')->createQueryBuilder('nsr');
                $countdql = "SELECT COUNT(nsr.id) FROM Model_NodeSetupRequest nsr WHERE 1=1 ";
                $qb->where('1=1');
                if (!empty($dateymd)) 
		{
                        $countdql .= "AND nsr.lastModified >= '$dateymd 00:00:00' AND nsr.lastModified <= '$dateymd 23:59:59' ";
                        $qb->andWhere("nsr.lastModified >= '$dateymd 00:00:00'")->andWhere("nsr.lastModified <= '$dateymd 23:59:59'");

                }
		if (!empty($node)) 
		{
			$nodeObject = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($node);
			$countdql .= "AND nsr.node = '{$nodeObject->id}' ";
                        $qb->andWhere("nsr.node = '{$nodeObject->id}'");
                }
                $qb->orderBy('nsr.lastModified', 'DESC');
                $qb->setFirstResult($offset);
                $qb->setMaxResults($limit);
                $query = $qb->getQuery();
                $rows = $query->getResult();
                $count = Doctrine::em()->createQuery($countdql)->getSingleScalarResult();
                $maxpages = ceil($count/$limit);
                $hiddenfields = array(
                        'date' => $date,
			'node' => $node,
                );
                $content .= $this->template->pages = View::factory('partial/pages')->bind('page', $page)->bind('maxpages', $maxpages)->bind('hiddenfields', $hiddenfields);
		$objectType = 'node_setup_request';
                $idField = 'id';
		$content .= View::factory('partial/table')
			->bind('fields', $fields)
                        ->bind('rows', $rows)	
			->bind('objectType', $objectType)
                        ->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_pending()
        {
                $this->check_login("systemadmin");
                $subtitle = "Pending Node Setup Requests";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

                $fields = array(
                        'id' => 'ID',
                        'mac' => 'MAC Address',
                        'ipAddr' => 'Requesting IP Address',
                        'requestedDate' => 'Requested Date',
			'nodeBoxNumber' => 'Node',
                        'lastModified' => 'Last Modified',
                        'view' => '',
                        'delete' => '',
                );
         	$rows = Doctrine::em()->getRepository('Model_NodeSetupRequest')->findByStatus('pending');
                $objectType = 'node_setup_request';
                $idField = 'id';
                $content = View::factory('partial/table')
                        ->bind('fields', $fields)
                        ->bind('rows', $rows)
                        ->bind('objectType', $objectType)
                        ->bind('idField', $idField);
                $this->template->content = $content;
        }

	public function action_create()
        {
                $this->check_login("systemadmin");
                $subtitle = "Create Approved Node Setup Request";
                View::bind_global('subtitle', $subtitle);
                $errors = array();
                $success = "";
                if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
                        $validation = Validation::factory($formValues)
                                ->rule('ipAddr', 'SownValid::ipv4', array(':value'))
                                ->rule('ipAddr', 'not_empty', array(':value'))
				->rule('nodeId', 'not_empty', array(':value'));

                        if ($validation->check())
                        {
				$node = Doctrine::em()->getRepository('Model_Node')->find($formValues['nodeId']);
				$nodeSetupRequest = new Model_NodeSetupRequest();
		                $nodeSetupRequest->node = $node;
				$nodeSetupRequest->mac = strtolower($node->getWiredMac());
                		$nodeSetupRequest->ipAddr = $formValues['ipAddr'];
				$nodeSetupRequest->status = 'approved';
				$nodeSetupRequest->requestedDate = new \DateTime();
                		$nodeSetupRequest->approvedDate = new \DateTime();
				$nodeSetupRequest->approvedBy = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user()); 
                		$nodeSetupRequest->expiryDate = new \DateTime('+1 day');
                                $nodeSetupRequest->password = RadAcctUtils::generateRandomString(20);
				$nodeSetupRequest->save();
                                $success = "Successfully created approved node setup request for box number: $node->boxNumber";
                        }
                        else
                        {
                                $errors = $validation->errors();
                        }
                }
                else
                {
                        $formValues = array(
                                'nodeId' => '',
                                'ipAddr' => '',
                        );

                }
		$deployableNodes = Model_Node::getDeployableNodes();
		asort($deployableNodes);
                $formTemplate = array(
                        'nodeId' => array('title' => 'Box Number', 'type' => 'select', 'size' => 3, 'options' => $deployableNodes),
                        'ipAddr' => array('title' => 'IP Address', 'type' => 'input', 'size' => 15, 'hint' => 'E.g. 152,78.65.123, 152.78.70.0'),
                );

                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
                $this->template->content = FormUtils::drawForm('NodeSetupRequest', $formTemplate, $formValues, array('createNodeSetupRequest' => 'Create Node Setup Request'), $errors, $success);
        }


	public function action_view()
	{
		$this->check_login("systemadmin");
		$errors = array();
                $success = "";

		$nodeSetupRequest = Doctrine::em()->getRepository('Model_NodeSetupRequest')->findOneById($this->request->param('id'));
		$node = $nodeSetupRequest->node;
		if (!is_object($node))
		{
			$node = Model_Node::getByMac($nodeSetupRequest->mac);
			if (is_object($node))
			{
				$nodeSetupRequest->node = $node;
				$nodeSetupRequest->save();
				$nodeSetupRequest = Doctrine::em()->getRepository('Model_NodeSetupRequest')->findOneById($this->request->param('id'));
			}
		}

		if ($this->request->method() == 'POST')
               	{
			$formValues = FormUtils::parseForm($this->request->post());
			error_log(var_export($formValues,1));
			if (!empty($formValues['createnode']))
			{
				$this->request->redirect(Route::url('create_node_mac', array('mac' => $nodeSetupRequest->mac)));	
			}
			elseif (!empty($formValues['approve']))
			{
				$nodeSetupRequest->status = "approved";
				$nodeSetupRequest->approvedBy = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
				$nodeSetupRequest->approvedDate = new \DateTime();
				$nodeSetupRequest->expiryDate = new \DateTime('+1 day');
				$nodeSetupRequest->password = RadAcctUtils::generateRandomString(20); 
				$nodeSetupRequest->save();
				$success = "Node Setup Request Approved";
			}
			else if (!empty($formValues['reject']))
			{
				$nodeSetupRequest->status = "rejected";
                                $nodeSetupRequest->approvedBy = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
                                $nodeSetupRequest->approvedDate = new \DateTime();
				$nodeSetupRequest->password = "";
				$nodeSetupRequest->save();
				$success = "Node Setup Request Rejected";
			}
                }
		$subtitle = "View Node Setup Request";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		if (is_object($node))
		{
			if (strlen($node->certificate->privateKey) > 0)
			{
				$this->template->content = FormUtils::drawForm('view_node_setup_request', $formTemplate, $formValues, array('approve' => 'Approve', 'reject' => 'Reject'), $errors, $success);
			}
			else 
			{
				$errors['Certificate'] = array("created for the node associated with this setup request.");
				$this->template->content = FormUtils::drawForm('view_node_setup_request', $formTemplate, $formValues, array('reject' => 'Reject'), $errors, $success);
			}
		}
		else
		{
			$this->template->content = "<p class=\"error\">There is currently no node in the system that has the MAC address used in this setup request.</p>";
			$this->template->content .= FormUtils::drawForm('view_node_setup_request', $formTemplate, $formValues, array('createnode' => 'Create Node', 'reject' => 'Reject'), $errors, $success);
		}
	}

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$nodeSetupRequest = Doctrine::em()->getRepository('Model_NodeSetupRequest')->findOneById($this->request->param('id'));
                if (!is_object($nodeSetupRequest))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Node Setup Request";
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'NodeSetupRequest';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted Node Setup Request with ID " . $formValues['id'] .".</p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete Node Setup Request with ID " . $formValues['id'] .".</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Node Setup Request with ID " . $formValues['id'] . " was not deleted.</p>";
                        }
			
		}
		else
		{
			$formTemplate = array(
				'id' =>	array('type' => 'hidden'),
				'message' => array('type' => 'message'),
			);
			$formValues = array(
				'id' => $this->request->param('id'),
				'message' => "Are you sure you want to delete Node Setup Request with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_node_setup_request', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->sidebar = View::factory('partial/sidebar');
	}

	
	private function _load_from_database($id, $action = 'view')
	{
		$nodeSetupRequest = Doctrine::em()->getRepository('Model_NodeSetupRequest')->findOneById($id);
                if (!is_object($nodeSetupRequest))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
			'id' => $nodeSetupRequest->id,
			'nonce' => preg_replace('/(\w{64})/', '\1<br/>', $nodeSetupRequest->nonce),
			'mac' => $nodeSetupRequest->mac,
			'ipAddr' => $nodeSetupRequest->ipAddr,
                        'requestedDate' => $nodeSetupRequest->requestedDate->format('Y-m-d H:i:s'),
			'status' => $nodeSetupRequest->status,
		);
		
		$approvedBy = $nodeSetupRequest->approvedBy;
		$formValues['approvedBy'] = (empty($approvedBy) ? '' : $approvedBy->username);
		$approvedDate = $nodeSetupRequest->approvedDate;
                $formValues['approvedDate'] = (empty($approvedDate) ? '' : $approvedDate->format('Y-m-d H:i:s'));
		$password = $nodeSetupRequest->password;
		$formValues['password'] = (empty($password) ? '[UNSET]' : '[SET]');
		$expiryDate = $nodeSetupRequest->expiryDate;
                $formValues['expiryDate'] = (empty($expiryDate) ? '' : $expiryDate->format('Y-m-d H:i:s'));
		$lastModified = $nodeSetupRequest->lastModified;
                $formValues['lastModified'] = (empty($lastModified) ? '' : $lastModified->format('Y-m-d H:i:s'));
		$node = $nodeSetupRequest->node;
		$formValues['node'] = (empty($node) ? '' : $node->boxNumber);
		return $formValues;
	}

        private function _load_form_template($action = 'view')
        {
		$formTemplate = array(
                        'nonce' => array('title' => 'Nonce', 'type' => 'static'),
                        'mac' => array('title' => 'MAC Address', 'type' => 'static'),
			'ipAddr' => array('title' => 'Requesting IP Address', 'type' => 'static'),
                        'requestedDate' => array('title' => 'Requested Date', 'type' => 'static'),
                        'status' => array('title' => 'Status', 'type' => 'static'),
			'approvedBy' => array('title' => 'Approved By', 'type' => 'static'),
			'approvedDate' => array('title' => 'Approved Date', 'type' => 'static'),
			'password' => array('title' => 'Password', 'type' => 'static'),
			'expiryDate' => array('title' => 'Expiry Date', 'type' => 'static'),
			'lastModified' => array('title' => 'Last Modified', 'type' => 'static'),
			'node' => array('title' => 'Node', 'type' => 'static'),
                );

                if ($action == 'view' )
                {
                        return FormUtils::makeStaticForm($formTemplate);
                }
                return $formTemplate;
        }

}
	
