<?php defined('SYSPATH') or die('No direct script access.');

class Controller_NodeSetupRequests extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("All Node Setup Request" => Route::url('node_setup_requests'), "Pending Node Setup Request" => Route::url('pending_node_setup_requests'),);
                $title = 'Node Setup Requests';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Node Setup Requests";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

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
		$rows = Doctrine::em()->getRepository('Model_NodeSetupRequest')->findAll();
		$objectType = 'node_setup_request';
                $idField = 'id';
		$content = View::factory('partial/table')
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
			$this->template->content = FormUtils::drawForm('view_node_setup_request', $formTemplate, $formValues, array('approve' => 'Approve', 'reject' => 'Reject'), $errors, $success);
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
	
