<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Subnets_Reserved extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Reserved Subnet" => Route::url('create_reserved_subnet'), "All Reserved Subnets" => Route::url('reserved_subnets'));
		$title = 'Reserved Subnets';
		View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Reserved Subnets";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'name' => 'Name',
			'IPv4' => 'IPv4 Subnet',
			'IPv6' => 'IPv6 Subnet',
                        'edit' => '',
                        'delete' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_Subnet_Reserved')->findAll();
		$objectType = 'reserved_subnet';
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
		$subtitle = "Create Reserved Subnet";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues)
				->rule('name', 'not_empty', array(':value'))
				->rule('IPv4Addr', 'not_empty', array(':value'))
	                      	->rule('IPv4Addr', 'SownValid::ipv4', array(':value'))
                       		->rule('IPv4AddrCidr', 'not_empty', array(':value'))
                       		->rule('IPv4AddrCidr', 'SownValid::ipv4cidr', array(':value'))
                       		->rule('IPv6Addr', 'not_empty', array(':value'))
                       		->rule('IPv6Addr', 'SownValid::ipv6', array(':value'))
                       		->rule('IPv6AddrCidr', 'not_empty', array(':value'))
                       		->rule('IPv6AddrCidr', 'SownValid::ipv6cidr', array(':value'));	
			if ($validation->check())
        		{
				$ipv4 = IP_Network_Address::factory($formValues['IPv4Addr'], $formValues['IPv4AddrCidr']);
                                $ipv6 = IP_Network_Address::factory($formValues['IPv6Addr'], $formValues['IPv6AddrCidr']);
				$reservedSubnet = Model_Subnet_Reserved::build($formValues['name'], $ipv4, $ipv6);
				$reservedSubnet->save();
				$url = Route::url('edit_reserved_subnet', array('id' => $reservedSubnet->id));
                        	$success = "Successfully created Reserved Subnet with ID: <a href=\"$url\">" . $reservedSubnet->id . "</a>.";
        		}
			else 
			{
				$errors = $validation->errors();
			} 
                }
		else
		{
			$formValues = array(
				'name' => '',
	                        'IPv4Addr' => '',
                	        'IPv4AddrCidr' => '',
                        	'IPv6Addr' => '',
                	        'IPv6AddrCidr' => '',
			);
		}
		$hosts = Sown::get_all_cron_job_hosts();
		$formTemplate = array(
                        'name' => array('title' => 'Name', 'type' => 'input', 'size' => 80),
                        'IPv4Addr' => array('title' => 'IPv4 Address', 'type' => 'input', 'size' => 15),
                        'IPv4AddrCidr' => array('title' => 'IPv4 Subnet', 'type' => 'input', 'size' => 2),
			'IPv6Addr' => array('title' => 'IPv6 Address', 'type' => 'input', 'size' => 31 ),
                        'IPv6AddrCidr' => array('title' => 'IPv6 Subnet', 'type' => 'input', 'size' => 3),
		);
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('create_subnet_reserved', $formTemplate, $formValues, array('createSubnet_Reserved' => 'Create Reserved Subnet'), $errors, $success);
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
		$subtitle = "Edit Reserved Subnet: " . $formValues['name'];
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$errors = array();
                $success = "";
		if ($this->request->method() == 'POST')
                {
                        $formValues = FormUtils::parseForm($this->request->post());
			$errors = $this->_validate($formValues);
			if (sizeof($errors) == 0)
			{
				$this->_update($this->request->param('id'), $formValues);
				$success = "Successfully updated Reserved Subnet";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
                $this->template->content = FormUtils::drawForm('update_subnet_reserved', $formTemplate, $formValues, array('updateSubnet_Reserved' => 'Update Reserved Subnet'), $errors, $success);
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$object = Doctrine::em()->getRepository('Model_Subnet_Reserved')->findOneById($this->request->param('id'));
                if (!is_object($object))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Reserved Subnet " . $this->request->param('id');
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'Subnet_Reserved';
		                if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted Reserved Subnet with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('reserved_subnets')."\">All reserved Subnets</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete Reserved Subnet with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('reserved_subnets')."\">All Reserved Subnets</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Reserved Subnet with ID " . $formValues['id'] . " was not deleted.  Go back to <a href=\"".Route::url('reserved_subnets')."\">All Reserved Subnets</a>.</p>";
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
				'message' => "Are you sure you want to delete Reserved Subnet with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_reserved_subnet', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
	}

	
	private function _validate($formValues) 
	{
		$errors = array();
		$validation = Validation::factory($formValues);

                if (!$validation->check())
                {
			$errors = $validation->errors();
                }
		return $errors;
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$reservedSubnet = Doctrine::em()->getRepository('Model_Subnet_Reserved')->findOneById($id);
                if (!is_object($reservedSubnet))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
			'id' => $reservedSubnet->id,
                        'name' => $reservedSubnet->name,
			'IPv4Addr' => $reservedSubnet->IPv4Addr,
                        'IPv4AddrCidr' => $reservedSubnet->IPv4AddrCidr,
                        'IPv6Addr' => $reservedSubnet->IPv6Addr,
                        'IPv6AddrCidr' => $reservedSubnet->IPv6AddrCidr,
		);
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$hosts = Sown::get_all_cron_job_hosts();
		$formTemplate = array(
                        'id' =>  array('type' => 'hidden'),
			'name' => array('title' => 'Name', 'type' => 'input', 'size' => 80),
                        'IPv4Addr' => array('title' => 'IPv4 Address', 'type' => 'input', 'size' => 15),
                        'IPv4AddrCidr' => array('title' => 'IPv4 Subnet', 'type' => 'input', 'size' => 2),
                        'IPv6Addr' => array('title' => 'IPv6 Address', 'type' => 'input', 'size' => 31 ),
                        'IPv6AddrCidr' => array('title' => 'IPv6 Subnet', 'type' => 'input', 'size' => 3),
                );
                return $formTemplate;

	}

	private function _update($id, $formValues)
	{
		$reservedSubnet = Doctrine::em()->getRepository('Model_Subnet_Reserved')->findOneById($id);
		$ipv4 = IP_Network_Address::factory($formValues['IPv4Addr'], $formValues['IPv4AddrCidr']);
                $ipv6 = IP_Network_Address::factory($formValues['IPv6Addr'], $formValues['IPv6AddrCidr']);	
                $reservedSubnet->name = $formValues['name'];
		$reservedSubnet->IPv4 = $ipv4;
		$reservedSubnet->IPv6 = $ipv6;
                $reservedSubnet->save();		
	}
}
