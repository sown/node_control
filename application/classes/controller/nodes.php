<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Nodes extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Node" => Route::url('create_node'), "Deployable Nodes" => Route::url('deployable_nodes'), "All Nodes" => Route::url('nodes'));
		$title = 'Nodes';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Nodes";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                	'id' => 'ID',
               		'boxNumber' => 'Box Number',
			'currentDeployment' => 'Current Deployment',
			'nodeHardware' => 'Node Hardware',
			'firmwareVersion' => 'Firmware Version',
               		'firmwareImage' => 'Firmware Image',
			'undeployable' => 'Deployable?',
			'certificateWritten' => 'Certificate Written',
			'nodeCA' => 'CA',
			'latestNote' => 'Latest Note',
               		'view' => '',
               		'edit' => '',
			'submit_hash' => '',
               		'delete' => '',
       		);
		$rows = Doctrine::em()->getRepository('Model_Node')->findBy(array(), array('boxNumber' => 'ASC'));
		$objectType = 'node';
		$idField = 'boxNumber';
		$content = View::factory('partial/table')
			->bind('fields', $fields)
			->bind('rows', $rows)
			->bind('objectType', $objectType)
			->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_deployable()
        {
                $this->check_login("systemadmin");
                $subtitle = "Deployable Nodes";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

                $fields = array(
                        'id' => 'ID',
                        'boxNumber' => 'Box Number',
                        'currentDeployment' => 'Current Deployment',
			'nodeHardware' => 'Node Hardware',
			'firmwareVersion' => 'Firmware Version',
                        'firmwareImage' => 'Firmware Image',
                        'certificateWritten' => 'Certificate Written',
                        'nodeCA' => 'CA',
                        'latestNote' => 'Latest Note',
                        'view' => '',
                        'edit' => '',
                        'submit_hash' => '',
                        'delete' => '',
                );
                $rows = Doctrine::em()->getRepository('Model_Node')->findBy(array('undeployable' => '0'), array('boxNumber' => 'ASC'));
                $objectType = 'node';
                $idField = 'boxNumber';
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
		$subtitle = "Create Node";
		View::bind_global('subtitle', $subtitle);
		$errors = array();
		$mac = $this->request->param('mac');
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues)
				->rule('boxNumber', 'digit')
				->rule('boxNumber', 'max_length', array(':value', '4'))
				->rule('boxNumber', 'Model_Node::nonUniqueBoxNumber', array(':value'))
				->rule('wiredMac', 'not_empty', array(':value'))
				->rule('wiredMac', 'SownValid::mac', array(':value'))
				->rule('wirelessMac', 'SownValid::mac', array(':value'))
                                ->rule('wirelessMac', 'not_empty', array(':value'));				
			if ($validation->check())
        		{
				if (!isset($formValues['externalBuild']))
				{
					$formValues['externalBuild'] = 0;
				}
				
				$node = Model_Builder::create_node($formValues['boxNumber'], $formValues['vpnServer'], $formValues['wiredMac'], $formValues['wirelessMac'], $formValues['nodeHardware'], $formValues['firmwareVersion'], $formValues['firmwareImage'], $formValues['externalBuild']);
                        	$success = "Successfully created node with box number: <a href=\"/admin/nodes/$node->boxNumber\">$node->boxNumber</a>.";
 
        		}
			else 
			{
				$errors = $validation->errors();
			} 
                }
		else
		{
			$formValues = array(
				'boxNumber' => '',
				'vpnServer' => '',
				'wiredMac' => $mac,
				'wirelessMac' => $mac,
				'nodeHardware' => '',
				'firmwareVersion' => '',
				'firmwareImage' => Kohana::$config->load('system.default.firmware_image_default'),
				'externalBuild' => 0,
			);		
		}
		$formTemplate = array(
			'boxNumber' => array('title' => 'Box Number', 'type' => 'input', 'size' => 3, 'hint' => "Leave empty to auto-assign box number"),
			'vpnServer' => array('title' => 'VPN Server', 'type' => 'select', 'options' => Model_VpnServer::getVpnServerNames()),
			'wiredMac' => array('title' => 'Wired Mac', 'type' => 'input', 'size' => 15, 'hint' => "e.g. 01:23:45:67:89:AB"),
                        'wirelessMac' => array('title' => 'Wireless Mac', 'type' => 'input', 'size' => 15, 'hint' => "e.g. 01:23:45:67:89:AB"),
			'nodeHardware' => array('title' => 'Hardware', 'type' => 'select', 'options' => Model_NodeHardware::getNodeHardwareOptions()),
			'firmwareVersion' => array('title' => 'Firmware Version', 'type' => 'select', 'options' => Kohana::$config->load('system.default.firmware_versions')),
                        'firmwareImage' => array('title' => 'Firmware Image', 'size' => 50, 'type' => 'input'),
			'externalBuild' => array('title' => 'External Build', 'type' => 'checkbox'),
		);
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('Node', $formTemplate, $formValues, array('createNode' => 'Create Node'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_node', array('boxNumber' => $this->request->param('boxNumber'))));
                }
		$subtitle = "View Node " . $this->request->param('boxNumber');
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('boxNumber'), 'view');
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($this->request->param('boxNumber'));
		$switch = $node->switch;
		$formTemplate = $this->_load_form_template('view', $node, $formValues['externalBuild'], $switch, true);
		$notesFormValues = Controller_Notes::load_from_database('Node', $formValues['id'], 'view');
                $notesFormTemplate = Controller_Notes::load_form_template('view');
		$this->template->content = FormUtils::drawForm('Node', $formTemplate, $formValues, array('editNode' => 'Edit Node')) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null);
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Node " . $this->request->param('boxNumber');
		View::bind_global('subtitle', $subtitle);
		$jsFiles = array('jquery.js');
                View::bind_global('jsFiles', $jsFiles);
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$errors = array();
                $success = "";
		if ($this->request->method() == 'POST')
                {
                        $formValues = FormUtils::parseForm($this->request->post());
			if (!empty($formValues['updateNode']))
                        {
				$errors = $this->_validate($formValues);
				if (sizeof($errors) == 0)
				{
					$this->_update($this->request->param('boxNumber'), $formValues);
					$success = "Successfully updated node";
					$formValues = $this->_load_from_database($this->request->param('boxNumber'), 'edit');
				}
			}
			elseif (!empty($formValues['addSwitch']))
                        {
                                $node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($this->request->param('boxNumber'));
                                $switch = Model_Switch::build("eth1");
                                $node->switch = $switch;
                                $node->save();
                                $success = "Successfully created switch for node";

                        }
                        elseif (!empty($formValues['removeSwitch']))
                        {
                                $node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($this->request->param('boxNumber'));
                                $switch = $node->switch;
                                $node->switch = null;
                                $switch->delete();
                                $node->save();
                                $success = "Successfully deleted switch for node";
                        }

		}
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($this->request->param('boxNumber'));
		$formValues = $this->_load_from_database($this->request->param('boxNumber'), 'edit');
		$switch = $node->switch;
		$formTemplate = $this->_load_form_template('edit', $node, isset($formValues['externalBuild']), $switch, true);
		$formButtons = array('updateNode' => 'Update Node');
		if (isset($switch) && $switch->id > 0)
                {
                        $formButtons['removeSwitch'] = "Remove Switch";
                }
                else
                {
                        $formButtons['addSwitch'] = "Add Switch";
                }
		$notesFormValues = Controller_Notes::load_from_database('Node', $formValues['id'], 'edit');
                $notesFormTemplate = Controller_Notes::load_form_template('edit');
                $this->template->content = FormUtils::drawForm('Node', $formTemplate, $formValues, $formButtons, $errors, $success) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null) . Controller_Notes::generate_form_javascript();
        }

	public function action_submit_hash()
        {
                $this->check_login("systemadmin");
                $subtitle = "Submit New Password Hash for Node " . $this->request->param('boxNumber');
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$success = "";
		$errors = array();
		if ($this->request->method() == 'POST')
                {
			$formValues = FormUtils::parseForm($this->request->post());
			if (!empty($formValues['passwordHash'])) 
			{
				$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($this->request->param('boxNumber'));
				$node->passwordHash = $formValues['passwordHash'];
				$node->save();
				$success = "Successfully updated password hash";
			}
			else 
			{
				$errors = array("Node Password Hash" => "No password hash submitted.");
			}
		}
		else {
			$formValues = array("passwordHash" => "");
		}
		$formTemplate = array("passwordHash" => array('title' => 'Password Hash', 'type' => 'input', 'size' => 50));
		$this->template->content = FormUtils::drawForm('NodePasswordHash', $formTemplate, $formValues, array('submitPasswordHash' => 'Submit Password Hash'), $errors, $success);
	}

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($this->request->param('boxNumber'));
                if (!is_object($node)) 
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Node " . $this->request->param('boxNumber') ;
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
	                        if (Model_Builder::destroy_node($formValues['boxNumber']))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted node with box number " . $formValues['boxNumber'] .".</p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete node with box number " . $formValues['boxNumber'] .".</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Node with box number " . $formValues['boxNumber'] . " was not deleted.</p>";
                        }
			
		}
		else
		{
			$formTemplate = array(
				'boxNumber' =>	array('type' => 'hidden'),
				'message' => array('type' => 'message'),
			);
			$formValues = array(
				'boxNumber' => $this->request->param('boxNumber'),
				'message' => "Are you sure you want to delete node with box number ".$this->request->param('boxNumber') . "?",
			);
			$this->template->content = FormUtils::drawForm('Node', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
		}
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
	}

	
	private function _validate($formValues) 
	{
		$errors = array();

		$validation = Validation::factory($formValues)
			->rule('primaryDNSIPv4Addr', 'SownValid::ipv4', array(':value', true))
			->rule('secondaryDNSIPv4Addr', 'SownValid::ipv4', array(':value', true))
			->rule('primaryDNSIPv6Addr', 'SownValid::ipv6', array(':value', true))
                        ->rule('secondaryDNSIPv6Addr', 'SownValid::ipv6', array(':value', true));

		if (!$validation->check())
                {
                        foreach ($validation->errors() as $e => $error)
                        {
                                $errors["Node $e"] = $error;
                        }
                }
		
		if (empty($formValues['vpnEndpoint']['disabled']))
		{
			$validation = Validation::factory($formValues['vpnEndpoint'])
		                ->rule('port','not_empty', array(':value'))
                	        ->rule('port', 'Model_VpnServer::validPort', array(':value', $formValues['vpnEndpoint']['vpnServer']))
                        	->rule('port', 'Model_VpnEndpoint::freePort', array(':value', $formValues['vpnEndpoint']['id']))
				->rule('IPv4Addr', 'not_empty', array(':value'))
        	                ->rule('IPv4Addr', 'SownValid::ipv4', array(':value'))
                	        ->rule('IPv4AddrCidr', 'not_empty', array(':value'))
                        	->rule('IPv4AddrCidr', 'SownValid::ipv4cidr', array(':value'))
	                        ->rule('IPv4Addr', 'Model_VpnServer::validIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv4AddrCidr'], 4, $formValues['vpnEndpoint']['vpnServer']))
        	                ->rule('IPv4Addr', 'Model_VpnEndpoint::freeIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv4AddrCidr'], 4, $formValues['vpnEndpoint']['id']))
                	        ->rule('IPv6Addr', 'not_empty', array(':value'))
                        	->rule('IPv6Addr', 'SownValid::ipv6', array(':value'))
	             	        ->rule('IPv6AddrCidr', 'not_empty', array(':value'))
                	        ->rule('IPv6AddrCidr', 'SownValid::ipv6cidr', array(':value'))
                        	->rule('IPv6Addr', 'Model_VpnServer::validIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv6AddrCidr'], 6, $formValues['vpnEndpoint']['vpnServer']))
                        	->rule('IPv6Addr', 'Model_VpnEndpoint::freeIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv6AddrCidr'], 6, $formValues['vpnEndpoint']['id']));
                	if (!$validation->check())
	                {
                		foreach ($validation->errors() as $e => $error)
        	                {
                        	        $errors["VPN Endpoint $e"] = $error;
	                        }
        	        }
		}
                foreach ($formValues['interfaces']['currentInterfaces'] as $i => $interface)
                {
                	if(!empty($interface['name'])) 
			{
                        	$validation = Validation::factory($interface)
                                        ->rule('name', 'SownValid::interfaceName', array(':value'))
                                        ->rule('ssid', 'SownValid::ssid', array(':value'))
                                        ->rule('networkAdapterMac', 'SownValid::mac', array(':value', $interface['name']));

                                if ($interface['type'] == "static")
                                {
                                	$validation->rule('IPv4Addr', 'not_empty', array(':value'))
                                        	->rule('IPv4Addr', 'SownValid::ipv4', array(':value'))
                                                ->rule('IPv4AddrCidr', 'SownValid::ipv4cidr', array(':value', false, $interface['name']))
                                                ->rule('IPv4Addr', 'Model_Interface::freeIPSubnet', array(':value', $interface['IPv4AddrCidr'], 4, $interface['id']))
						->rule('IPv4GatewayAddr', 'SownValid::ipv4', array(':value', true))
                                                ->rule('IPv6Addr', 'SownValid::ipv6', array(':value', true))
                                                ->rule('IPv6AddrCidr', 'SownValid::ipv6cidr', array(':value', true))
                                                ->rule('IPv6Addr', 'Model_Interface::freeIPSubnet', array(':value', $interface['IPv6AddrCidr'], 6, $interface['id']))
						->rule('IPv6GatewayAddr', 'SownValid::ipv6', array(':value', true));
                                }
                                else
                                {
                                	$validation->rule('IPv4Addr', 'SownValid::emptyField', array(':value'))
                                                ->rule('IPv4AddrCidr', 'SownValid::emptyField', array(':value'))
						->rule('IPv4GatewayAddr', 'SownValid::emptyField', array(':value'))
                                                ->rule('IPv6Addr', 'SownValid::emptyField', array(':value'))
                                                ->rule('IPv6AddrCidr', 'SownValid::emptyField', array(':value'))
						->rule('IPv6GatewayAddr', 'SownValid::emptyField', array(':value'));
                                }
                                if (in_array($interface['networkAdapterType'], array('100M', '1G')))
                                {
                                        $validation->rule('networkAdapterWirelessChannel', 'SownValid::emptyField', array(':value'));
                                }
                                else
                                {
                                        $validation->rule('networkAdapterWirelessChannel', 'SownValid::wirelessChannel', array(':value', $interface['networkAdapterType']));
                                }
                                if (!$validation->check())
                                {
                                        foreach ($validation->errors() as $e => $error)
                                        {
                                                $errors["Interface " . $interface['name'] . " $e"] = $error;
                                        }
                                }
                       }
		}
                if(!empty($formValues['cnames']['newCname']))
                {
			$validation = Validation::factory($formValues['cnames'])
                                ->rule('newCname', 'SownValid::localCname', array(':value'))
				->rule('newCname', 'SownValid::notNodeCname', array(':value'))
				->rule('newCname', 'Sown::unusedHostname', array(':value'));
			if (!$validation->check())
	                {
       		                foreach ($validation->errors() as $e => $error)
               		        {
                       		        $errors["New CNAME"] = $error;
                        	}
			}
		}	
		return $errors;
	}

	private function _load_from_database($boxNumber, $action = 'edit')
	{
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($boxNumber);
                if (!is_object($node)) 
		{
			throw new HTTP_Exception_404();
		}
		Doctrine::em()->refresh($node);

                $formValues = array(
		       	'id' => $node->id,
                       	'boxNumber' => $node->boxNumber,
			'nodeHardware' => $node->nodeHardware->id,
			'firmwareVersion' => $node->firmwareVersion,
                       	'firmwareImage' => $node->firmwareImage,
			'undeployable' => $node->undeployable,
			'externalBuild' => $node->externalBuild,
			'primaryDNSIPv4Addr' => $node->primaryDNSIPv4Addr,
			'secondaryDNSIPv4Addr' => $node->secondaryDNSIPv4Addr,
			'primaryDNSIPv6Addr' => $node->primaryDNSIPv6Addr,
                        'secondaryDNSIPv6Addr' => $node->secondaryDNSIPv6Addr,
			'certificateWritten' => ( (strlen($node->certificate->privateKey) > 0) ? 'Yes' : 'No' ),
			'vpnEndpoint' => array(	
                       		'disabled' => 1,
			),
			'interfaces' => array(
				'dnsInterface' => "",
				'currentInterfaces' => array(),
			),
			'cnames' => array(
				'currentCnames' => array(),
			),
                );
		$vpnEndpoint = $node->vpnEndpoint;
		if (!empty($vpnEndpoint))
		{
			$formValues['vpnEndpoint'] = array(
				'id' => $vpnEndpoint->id,
                                'port' => $vpnEndpoint->port,
                                'protocol' => $vpnEndpoint->protocol,
                                'IPv4Addr' => $vpnEndpoint->IPv4Addr,
                                'IPv4AddrCidr' => $vpnEndpoint->IPv4AddrCidr,
                                'IPv6Addr' => $vpnEndpoint->IPv6Addr,
                                'IPv6AddrCidr' => $vpnEndpoint->IPv6AddrCidr,
                                'vpnServer' => $vpnEndpoint->vpnServer->id,
			);
		}
		
		$dnsInterface = $node->dnsInterface;
		if (!empty($dnsInterface))
		{
			$formValues['interfaces']['dnsInterface'] = $dnsInterface->id;
		}

		$formValuesMap = array(
			'id' => 'id', 
			'name' => 'name', 
			'IPv4Addr' => 'IPv4Addr', 
			'IPv4AddrCidr' => 'IPv4AddrCidr', 
			'IPv4GatewayAddr' => 'IPv4GatewayAddr',
			'IPv6Addr' => 'IPv6Addr', 
			'IPv6AddrCidr' => 'IPv6AddrCidr', 
			'IPv6GatewayAddr' => 'IPv6GatewayAddr',
			'ssid' => 'ssid', 
			'type' => 'type', 
			'offerDhcp' => 'offerDhcp', 
			'offerDhcpV6' => 'offerDhcpV6', 
			'is1x' => 'is1x', 
			'radiusConfig' => 'radiusConfig:id',
			'networkAdapterMac' => 'networkAdapter:mac', 
			'networkAdapterWirelessChannel' => 'networkAdapter:wirelessChannel', 
			'networkAdapterType' => 'networkAdapter:type'
		);
		$i = 0;
                foreach ($node->interfaces as $i => $interface)
                {
			foreach ($formValuesMap as $fmv_key => $fmv_value)
			{
				$fmv_value_bits = explode(":", $fmv_value);
				if (sizeof($fmv_value_bits) == 1)
				{
					$formValues['interfaces']['currentInterfaces'][$i][$fmv_key] = $interface->$fmv_value;
				}
				else 
				{	
					$object = $interface->$fmv_value_bits[0];
					if (isset($object))
					{
						$formValues['interfaces']['currentInterfaces'][$i][$fmv_key] = $object->$fmv_value_bits[1];
					}
				}
			}
			if ($action == 'view')
			{
				$formValues['interfaces']['currentInterfaces'][$i]['offerDhcp'] = ( $formValues['interfaces']['currentInterfaces'][$i]['offerDhcp'] ? 'Yes' : 'No') ;
			 	$formValues['interfaces']['currentInterfaces'][$i]['offerDhcpV6'] = ( $formValues['interfaces']['currentInterfaces'][$i]['offerDhcpV6'] ? 'Yes' : 'No') ;
				$formValues['interfaces']['currentInterfaces'][$i]['is1x'] = ( $formValues['interfaces']['currentInterfaces'][$i]['is1x'] ? 'Yes' : 'No') ;
			}	
                }
		$c = 0;
		foreach ($node->cnames as $c => $cname)
		{
			$formValues['cnames']['currentCnames'][$c]['id'] = $cname->id;
			$formValues['cnames']['currentCnames'][$c]['cname'] = $cname->cname;
		}
		$switch = $node->switch;
                if (!empty($switch) && $switch->id > 0)
                {
                        $formValues['switch'] = Model_Switch::getValuesForForm($switch, $action);
                }
		if ($action == 'edit')
		{
			foreach ($formValuesMap as $f => $field)
			{
				$formValues['interfaces']['currentInterfaces'][$i+1][$f] = '';
			}
		}
		if ($action == 'view')
		{
			$formValues['undeployable'] = (!empty($formValues['undeployable']) ? 'Yes' : 'No');
			$formValues['externalBuild'] = (!empty($formValues['externalBuild']) ? 'Yes' : 'No');
			$firmware_versions = Kohana::$config->load('system.default.firmware_versions'); 
			$formValues['firmwareVersion'] = $firmware_versions[$formValues['firmwareVersion']];
			if (!empty($formValues['dnsInterface']))
			{
				$dnsInterface = Doctrine::em()->getRepository('Model_Interface')->findOneById($formValues['dnsInterface']);
				$formValues['dnsInterface'] = $dnsInterface->name;
			}
			else	
			{
				$formValues['dnsInterface'] = 'VPN';
			}
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit', $node = null, $externalBuild = 0, $switch = null, $or_no_vpnserver = false)
	{
		$formTemplate = array(
                        'id' => array('type' => 'hidden'),
                        'boxNumber' => array('title' => 'Box Number', 'type' => 'statichidden'),
			'nodeHardware' => array('title' => 'Node Hardware', 'type' => 'select', 'options' => Model_NodeHardware::getNodeHardwareOptions()),
			'firmwareVersion' => array('title' => 'Firmware Version', 'type' => 'select', 'options' => Kohana::$config->load('system.default.firmware_versions')),
                        'firmwareImage' => array('title' => 'Firmware Image', 'type' => 'input', 'size' => 50),
			'undeployable' => array('title' => 'Undeployable', 'type' => 'checkbox'),
			'externalBuild' => array('title' => 'External Build', 'type' => 'checkbox'),
			'primaryDNSIPv4Addr' => array('title' => 'Primary DNS Server (IPv4)', 'type' => 'input', 'size' => 15, 'hint' => "8.8.8.8"),
			'secondaryDNSIPv4Addr' => array('title' => 'Secondary DNS Server (IPv4)', 'type' => 'input', 'size' => 15, 'hint' => "8.8.4.4"),
			'primaryDNSIPv6Addr' => array('title' => 'Primary DNS Server (IPv6)', 'type' => 'input', 'size' => 39, 'hint' => "2001:4860:4860::8888"),
                        'secondaryDNSIPv6Addr' => array('title' => 'Secondary DNS Server (IPv6)', 'type' => 'input', 'size' => 39, 'hint' => " 2001:4860:4860::8844"),
			'certificateWritten' => array('title' => 'Certificate written', 'type' => 'statichidden'),
                        'vpnEndpoint' => array(
                                'title' => 'VPN Endpoint',
                                'type' => 'fieldset',
                                'fields' => array(
					'disabled' => array('title' => 'Disabled?', 'type' => 'checkbox'),
                                        'id' => array('type' => 'hidden'),
                                        'port' => array('title' => 'Port', 'type' => 'input', 'size' => 4),
                                        'protocol' => array('title' => 'Protocol', 'type' => 'select', 'options' => array(0 => "", "udp" => "UDP", "tcp" => "TCP")),
                                        'IPv4Addr' => array('title' => 'IPv4 Address', 'type' => 'input', 'size' => 12),
                                        'IPv4AddrCidr' => array('title' => 'IPv4 CIDR', 'type' => 'input', 'size' => 2),
                                        'IPv6Addr' => array('title' => 'IPv6 Address', 'type' => 'input', 'size' => 20),
                                        'IPv6AddrCidr' => array('title' => 'IPv6 CIDR', 'type' => 'input', 'size' => 2),
                                        'vpnServer' => array('title' => 'VPN Server', 'type' => 'select', 'options' => Model_VpnServer::getVpnServerNames($or_no_vpnserver)),
                                ),
                        ),
                        'interfaces' => array(
                                'title' => 'Interfaces',
				'type' => 'fieldset',
				'fields' => array(
					'dnsInterface' => array( 'title' => 'DNS Interface', 'type' => 'select', 'options' => SOWN::getStaticInterfaceOptionsForNode($node)),
					'currentInterfaces' => array(
						'title' => '',
                                		'type' => 'table',
                                		'fields' => array(
              			                        'id' => array('type' => 'hidden'),
                                        		'name' => array('title' => 'Name', 'type' => 'input', 'size' => 7),
	                	                        'IPv4Addr' => array('title' => 'IPv4', 'type' => 'input', 'size' => 12),
        	                	                'IPv4AddrCidr' => array('title' => '', 'type' => 'input', 'size' => 2),
							'IPv4GatewayAddr' => array('title' => 'IPv4 GW', 'type' => 'input', 'size' => 12),
                	                	        'IPv6Addr' => array('title' => 'IPv6', 'type' => 'input', 'size' => 20),
		                                        'IPv6AddrCidr' => array('title' => '', 'type' => 'input', 'size' => 2),	
							'IPv6GatewayAddr' => array('title' => 'IPv6 GW', 'type' => 'input', 'size' => 20),
                		                        'ssid' => array('title' => 'SSID', 'type' => 'input', 'size' => 10),
                                		        'type' => array('title' => 'Type', 'type' => 'select', 'options' => array("dhcp" => "DHCP", "static" => "Static")),
		                                        'offerDhcp' => array('title' => 'Offer DHCP', 'type' => 'checkbox'),
		                                        'offerDhcpV6' => array('title' => 'Offer DHCPv6', 'type' => 'checkbox'),
                		                        'is1x' => array('title' => 'Is 1x', 'type' => 'checkbox'),
							'radiusConfig' => array('title' => 'RADIUS Config', 'type' => 'select', 'options' => Model_RadiusConfig::getRadiusConfigNames(true)),
                                		        'networkAdapterMac' => array('title' => 'Mac', 'type' => 'input', 'size' => 15),
		                                        'networkAdapterWirelessChannel' => array('title' => 'Channel', 'type' => 'input', 'size' => 2),
                		                        'networkAdapterType' => array('title' => 'Adapter Type', 'type' => 'select', 'options' => Kohana::$config->load('system.default.adapter_types')),
                                		),
					),
				),
                        ),
			'cnames' => array(
                                'title' => 'CNAMEs',
                                'type' => 'fieldset',
                                'fields' => array(
					'currentCnames' => array(
                                                'title' => '',
                                                'type' => 'table',
						'fields' => array(
		                               		'id' => array('type' => 'hidden'),
	                	                        'cname' => array('title' => 'CNAME', 'type' => 'static', 'size' => 20),
							'delete' => array('title' => 'Delete?', 'type' => 'checkbox'),
						),
					),
				),
			),
		);

                if ($action == 'edit')
                {
                        $formTemplate['cnames']['fields']['newCname'] = array('title' => 'New CNAME', 'type' => 'input', 'size' => 20);
                }
		if (!empty($switch))
                {
                        $formTemplate['switch'] = Model_Switch::getFormTemplate($switch, $action);
                }
		if ($externalBuild && $externalBuild != "No") 
                {
                        $formTemplate['interfaces']['title'] .= " **Changes here will have no affect**";
                }
		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}
		return $formTemplate;
	}

	private function _update($boxNumber, $formValues)
	{
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($boxNumber);
		$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->find($formValues['nodeHardware']);
		$node->nodeHardware = $nodeHardware;
		$node->firmwareVersion = $formValues['firmwareVersion'];
		$node->firmwareImage = $formValues['firmwareImage'];
		$node->undeployable = (empty($formValues['undeployable']) ? 0 : $formValues['undeployable']);
		$node->externalBuild = (empty($formValues['externalBuild']) ? 0 : $formValues['externalBuild']);
		$node->primaryDNSIPv4Addr = $formValues['primaryDNSIPv4Addr'];
		$node->secondaryDNSIPv4Addr = $formValues['secondaryDNSIPv4Addr'];
		$node->primaryDNSIPv6Addr = $formValues['primaryDNSIPv6Addr'];
                $node->secondaryDNSIPv6Addr = $formValues['secondaryDNSIPv6Addr'];
		$node->dnsInterface = null;
		if (!empty($formValues['interfaces']['dnsInterface']))
		{
			$node->dnsInterface = Doctrine::em()->getRepository('Model_Interface')->findOneById($formValues['interfaces']['dnsInterface']);
		}
		if (empty($formValues['vpnEndpoint']['disabled']))
		{
			if (!empty($node->vpnEndpoint))
			{
				$vpnEndpoint = $node->vpnEndpoint;
        	        	$vpnEndpoint->port = $formValues['vpnEndpoint']['port'];
				$vpnEndpoint->protocol = $formValues['vpnEndpoint']['protocol'];
				$vpnEndpoint->IPv4Addr = $formValues['vpnEndpoint']['IPv4Addr'];
				$vpnEndpoint->IPv4AddrCidr = $formValues['vpnEndpoint']['IPv4AddrCidr'];
				$vpnEndpoint->IPv6Addr = $formValues['vpnEndpoint']['IPv6Addr'];
	                	$vpnEndpoint->IPv6AddrCidr = $formValues['vpnEndpoint']['IPv6AddrCidr'];
				$vpnEndpoint->vpnServer = Doctrine::em()->getRepository('Model_VpnServer')->find($formValues['vpnEndpoint']['vpnServer']);
				$vpnEndpoint->save();
			}
			else
			{
				$ipv4 = IPv4_Network_Address::factory($formValues['vpnEndpoint']['IPv4Addr'], $formValues['vpnEndpoint']['IPv4AddrCidr']);
				$ipv6 = IPv6_Network_Address::factory($formValues['vpnEndpoint']['IPv6Addr'], $formValues['vpnEndpoint']['IPv6AddrCidr']); 
				$vpnServer = Doctrine::em()->getRepository('Model_VpnServer')->find($formValues['vpnEndpoint']['vpnServer']);
				$vpnEndpoint = Model_VpnEndpoint::build($formValues['vpnEndpoint']['port'], $formValues['vpnEndpoint']['protocol'], $ipv4, $ipv6, $vpnServer);
			}
			$vpnEndpoint->save();
			$node->vpnEndpoint = $vpnEndpoint;
		}
		else
                {
			$vpnEndpoint = $node->vpnEndpoint;
			if (!empty($vpnEndpoint))
			{
				$node->vpnEndpoint = null;
				$vpnEndpoint->delete();
			}
		}		
                foreach ($formValues['interfaces']['currentInterfaces'] as $i => $interfaceValues)
                {	
			if (empty($interfaceValues['name']))
			{
				if (!empty($interfaceValues['id']))
				{
					$interface = Doctrine::em()->getRepository('Model_Interface')->findOneById($interfaceValues['id']);
					$interface->delete();
				}
			}
			else
			{
			 	if (!isset($interfaceValues['offerDhcp']))
                                {
                                        $interfaceValues['offerDhcp'] = 0;
                                }
			 	if (!isset($interfaceValues['offerDhcpV6']))
                                {
                                        $interfaceValues['offerDhcpV6'] = 0;
                                }
                                if (!isset($interfaceValues['is1x']))
                                {
                                        $interfaceValues['is1x'] = 0;
                                }
				if (!isset($interfaceValues['radiusConfig']))
                                {
                                        $interfaceValues['radiusConfig'] = 0;
                                }
				if (empty($interfaceValues['id'])) {
					$ipv4 = null;
					if (!empty($interfaceValues['IPv4Addr']))
					{
						$ipv4 = IP_Network_Address::factory($interfaceValues['IPv4Addr'], $interfaceValues['IPv4AddrCidr']);
					}
					$ipv6 = null;
					$interfaceValues['ipv4GatewayAddr'] = (empty($interfaceValues['ipv4GatewayAddr']) ? null : $interfaceValues['ipv4GatewayAddr']);
					if (!empty($interfaceValues['IPv6Addr']))
                                        {
						$ipv6 = IP_Network_Address::factory($interfaceValues['IPv6Addr'], $interfaceValues['IPv6AddrCidr']);
					}
					$interfaceValues['ipv6GatewayAddr'] = (empty($interfaceValues['ipv6GatewayAddr']) ? null : $interfaceValues['ipv6GatewayAddr']);
					$networkAdapter = Model_NetworkAdapter::build(
						$interfaceValues['networkAdapterMac'], 
						$interfaceValues['networkAdapterWirelessChannel'], 
						$interfaceValues['networkAdapterType'], 
						$node
					);
					$node->interfaces->add(Model_Interface::build(
						$ipv4, 
						$interfaceValues['ipv4GatewayAddr'],
						$ipv6, 
						$interfaceValues['ipv6GatewayAddr'],
						$interfaceValues['name'],
						$interfaceValues['ssid'], 
						$interfaceValues['type'], 
						$interfaceValues['offerDhcp'], 
						$interfaceValues['offerDhcpV6'], 
						$interfaceValues['is1x'], 
						$interfaceValues['radiusConfig'],
						$networkAdapter, 
						$node
					));
				}
				else
				{
					$interface = Doctrine::em()->getRepository('Model_Interface')->find($interfaceValues['id']);
					$interface->name = $interfaceValues['name'];
					$ipv4 = null;
                                        if (!empty($interfaceValues['IPv4Addr']))
                                        {
                                                $ipv4 = IP_Network_Address::factory($interfaceValues['IPv4Addr'], $interfaceValues['IPv4AddrCidr']);
                                        }
					$interfaceValues['ipv4GatewayAddr'] = (empty($interfaceValues['ipv4GatewayAddr']) ? null : $interfaceValues['ipv4GatewayAddr']);
                                        $ipv6 = null;
                                        if (!empty($interfaceValues['IPv6Addr']))
                                        {
                                                $ipv6 = IP_Network_Address::factory($interfaceValues['IPv6Addr'], $interfaceValues['IPv6AddrCidr']);
                                        }
					$interfaceValues['ipv6GatewayAddr'] = (empty($interfaceValues['ipv6GatewayAddr']) ? null : $interfaceValues['ipv6GatewayAddr']);
					$interface->IPv4 = $ipv4;
					$interface->IPv4GatewayAddr = $interfaceValues['IPv4GatewayAddr'];
					$interface->IPv6 = $ipv6;
					$interface->IPv6GatewayAddr = $interfaceValues['IPv6GatewayAddr'];
 					$interface->ssid = $interfaceValues['ssid'];
 					$interface->type = $interfaceValues['type'];
					$interface->offerDhcp = $interfaceValues['offerDhcp']; 
					$interface->offerDhcpV6 = $interfaceValues['offerDhcpV6']; 
					$interface->is1x = $interfaceValues['is1x'];
					$radiusConfig = null;
					if (!empty($interfaceValues['radiusConfig']))
					{
						$radiusConfig = Doctrine::em()->getRepository('Model_RadiusConfig')->find($interfaceValues['radiusConfig']);
					}
					$interface->radiusConfig = $radiusConfig;
					$networkAdapter = $interface->networkAdapter;
					$networkAdapter->mac = $interfaceValues['networkAdapterMac'];
                                        $networkAdapter->wirelessChannel = $interfaceValues['networkAdapterWirelessChannel']; 
                                        $networkAdapter->type = $interfaceValues['networkAdapterType'];
					$networkAdapter->save();
					$interface->save();
				}	
                        }
                };
		$node->updateCnames($formValues['cnames']);
		if (isset($formValues['switch']))
                {
			$switch = $node->switch;
                        Model_Switch::update($switch, $formValues['switch']);
                }
		$node->save();
	}
}
	
