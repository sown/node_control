<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Nodes extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Node" => Route::url('create_node'), "All Nodes" => Route::url('nodes'));
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
               		'firmwareImage' => 'Firmware Image',
			'certificateWritten' => 'Certificate Written',
			'latestNote' => 'Latest Note',
               		'view' => '',
               		'edit' => '',
               		'delete' => '',
       		);
		$rows = Doctrine::em()->getRepository('Model_Node')->findAll();
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
				$node = Model_Builder::create_node($formValues['boxNumber'], $formValues['vpnServer'], $formValues['wiredMac'], $formValues['wirelessMac'], $formValues['firmwareImage']);
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
				'wiredMac' => '',
				'wirelessMac' => '',
				'firmwareImage' => 'Attitude Adjusment (Bleeding Edge, r31360)',
			);
			
		}
		$formTemplate = array(
			'boxNumber' => array('title' => 'Box Number', 'type' => 'input', 'size' => 3, 'hint' => "Leave empty to auto-assign box number"),
			'vpnServer' => array('title' => 'VPN Server', 'type' => 'select', 'options' => Model_VpnServer::getVpnServerNames()),
			'wiredMac' => array('title' => 'Wired Mac', 'type' => 'input', 'size' => 15, 'hint' => "e.g. 01:23:45:67:89:AB"),
                        'wirelessMac' => array('title' => 'Wireless Mac', 'type' => 'input', 'size' => 15, 'hint' => "e.g. 01:23:45:67:89:AB"),
                        'firmwareImage' => array('title' => 'Firmware Image', 'size' => 50, 'type' => 'input'),
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
		$formTemplate = $this->_load_form_template('view');
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
			$errors = $this->_validate($formValues);
			if (sizeof($errors) == 0)
			{
				$this->_update($this->request->param('boxNumber'), $formValues);
				$success = "Successfully updated node";
				$formValues = $this->_load_from_database($this->request->param('boxNumber'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('boxNumber'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
		$notesFormValues = Controller_Notes::load_from_database('Node', $formValues['id'], 'edit');
                $notesFormTemplate = Controller_Notes::load_form_template('edit');
                $this->template->content = FormUtils::drawForm('Node', $formTemplate, $formValues, array('updateNode' => 'Update Node'), $errors, $success) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null) . Controller_Notes::generate_form_javascript();
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
		$validation = Validation::factory($formValues['vpnEndpoint'])
	               ->rule('port','not_empty', array(':value'))
                       ->rule('port', 'Model_VpnServer::validPort', array(':value', $formValues['vpnEndpoint']['vpnServer']))
                       ->rule('port', 'Model_VpnEndpoint::unusedPort', array(':value', $formValues['vpnEndpoint']['id']))
                       ->rule('IPv4Addr', 'not_empty', array(':value'))
                       ->rule('IPv4Addr', 'SownValid::ipv4', array(':value'))
                       ->rule('IPv4AddrCidr', 'not_empty', array(':value'))
                       ->rule('IPv4AddrCidr', 'SownValid::ipv4cidr', array(':value'))
                       ->rule('IPv4Addr', 'Model_VpnServer::validIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv4AddrCidr'], 4, $formValues['vpnEndpoint']['vpnServer']))
                       ->rule('IPv4Addr', 'Model_VpnEndpoint::unusedIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv4AddrCidr'], 4, $formValues['vpnEndpoint']['id']))
                       ->rule('IPv6Addr', 'not_empty', array(':value'))
                       ->rule('IPv6Addr', 'SownValid::ipv6', array(':value'))
                       ->rule('IPv6AddrCidr', 'not_empty', array(':value'))
                       ->rule('IPv6AddrCidr', 'SownValid::ipv6cidr', array(':value'))
                       ->rule('IPv6Addr', 'Model_VpnServer::validIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv6AddrCidr'], 6, $formValues['vpnEndpoint']['vpnServer']))
                       ->rule('IPv6Addr', 'Model_VpnEndpoint::unusedIPSubnet', array(':value', $formValues['vpnEndpoint']['IPv6AddrCidr'], 6, $formValues['vpnEndpoint']['id']));

                if (!$validation->check())
                {
                	foreach ($validation->errors() as $e => $error)
                        {
                                $errors["VPN Endpoint $e"] = $error;
                        }
                }
                foreach ($formValues['interfaces'] as $i => $interface)
                {
                	if(!empty($interface['name'])) 
			{
                        	$validation = Validation::factory($interface)
                                        ->rule('name', 'alpha_numeric', array(':value'))
                                        ->rule('ssid', 'SownValid::ssid', array(':value'))
                                        ->rule('networkAdapterMac', 'not_empty', array(':value'))
                                        ->rule('networkAdapterMac', 'SownValid::mac', array(':value'));

                                if ($interface['type'] == "static")
                                {
                                	$validation->rule('IPv4Addr', 'not_empty', array(':value'))
                                        	->rule('IPv4Addr', 'SownValid::ipv4', array(':value'))
                                                ->rule('IPv4AddrCidr', 'SownValid::ipv4cidr', array(':value'))
                                                ->rule('IPv4Addr', 'Model_Interface::unusedIPSubnet', array(':value', $interface['IPv4AddrCidr'], 4, $interface['id']))
                                                ->rule('IPv6Addr', 'not_empty', array(':value'))
                                                ->rule('IPv6Addr', 'SownValid::ipv6', array(':value'))
                                                ->rule('IPv6AddrCidr', 'SownValid::ipv6cidr', array(':value'))
                                                ->rule('IPv6Addr', 'Model_Interface::unusedIPSubnet', array(':value', $interface['IPv6AddrCidr'], 6, $interface['id']));
                                }
                                else
                                {
                                	$validation->rule('IPv4Addr', 'SownValid::emptyField', array(':value'))
                                                ->rule('IPv4AddrCidr', 'SownValid::emptyField', array(':value'))
                                                ->rule('IPv6Addr', 'SownValid::emptyField', array(':value'))
                                                ->rule('IPv6AddrCidr', 'SownValid::emptyField', array(':value'));
                                }
                                if (in_array($interface['networkAdapterType'], array('100M', '1G')))
                                {
                                        $validation->rule('networkAdapterWirelessChannel', 'SownValid::emptyField', array(':value'));
                                }
                                else
                                {
                                        $validation->rule('networkAdapterWirelessChannel', 'SownValid::wirelessChannel', array(':value'));
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
		return $errors;
	}

	private function _load_from_database($boxNumber, $action = 'edit')
	{
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($boxNumber);
                if (!is_object($node)) 
		{
			throw new HTTP_Exception_404();
		}
                $formValues = array(
		       	'id' => $node->id,
                       	'boxNumber' => $node->boxNumber,
                       	'firmwareImage' => $node->firmwareImage,
			'certificateWritten' => ( (strlen($node->certificate->privateKey) > 0) ? 'Yes' : 'No' ),
			'vpnEndpoint' => array(	
	               		'id' => $node->vpnEndpoint->id,
                       		'port' => $node->vpnEndpoint->port,
                       		'protocol' => $node->vpnEndpoint->protocol,
                       		'IPv4Addr' => $node->vpnEndpoint->IPv4Addr,
                       		'IPv4AddrCidr' => $node->vpnEndpoint->IPv4AddrCidr,
                       		'IPv6Addr' => $node->vpnEndpoint->IPv6Addr,
                       		'IPv6AddrCidr' => $node->vpnEndpoint->IPv6AddrCidr,
                       		'vpnServer' => $node->vpnEndpoint->vpnServer->name,
			),
			'interfaces' => array(
				'currentInterfaces' => array(),
			),
                );
                foreach ($node->interfaces as $i => $interface)
                {
                       	$formValues['interfaces']['currentInterfaces'][$i] = array (
	                	'id' => $interface->id,
        	               	'name' => $interface->name,
                	        'IPv4Addr' => $interface->IPv4Addr,
                           	'IPv4AddrCidr' => $interface->IPv4AddrCidr,
                        	'IPv6Addr' => $interface->IPv6Addr,
	                       	'IPv6AddrCidr' => $interface->IPv6AddrCidr,
        	               	'ssid' => $interface->ssid,
                	       	'type' => $interface->type,
                           	'offerDhcp' => $interface->offerDhcp,
	                       	'is1x' => $interface->is1x,
        	               	'networkAdapterMac' => $interface->networkAdapter->mac,
                            	'networkAdapterWirelessChannel' => $interface->networkAdapter->wirelessChannel,
                      	     	'networkAdapterType' => $interface->networkAdapter->type,
                      	);
			if ($action == 'view')
			{
				$formValues['interfaces']['currentInterfaces'][$i]['offerDhcp'] = ( $formValues['interfaces']['currentInterfaces'][$i]['offerDhcp'] ? 'Yes' : 'No') ;
				$formValues['interfaces']['currentInterfaces'][$i]['is1x'] = ( $formValues['interfaces']['currentInterfaces'][$i]['is1x'] ? 'Yes' : 'No') ;
			}	
                }
		if ($action == 'edit')
		{
			foreach ($formValues['interfaces']['currentInterfaces'][$i] as $f => $field)
			{
				$formValues['interfaces']['currentInterfaces'][$i+1][$f] = '';
			}
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
                        'id' => array('type' => 'hidden'),
                        'boxNumber' => array('title' => 'Box Number', 'type' => 'statichidden'),
                        'firmwareImage' => array('title' => 'Firmware Image', 'type' => 'input', 'size' => 50),
			'certificateWritten' => array('title' => 'Certificate written', 'type' => 'statichidden'),
                        'vpnEndpoint' => array(
                                'title' => 'VPN Endpoint',
                                'type' => 'fieldset',
                                'fields' => array(
                                        'id' => array('type' => 'hidden'),
                                        'port' => array('title' => 'Port', 'type' => 'input', 'size' => 4),
                                        'protocol' => array('title' => 'Protocol', 'type' => 'select', 'options' => array("udp" => "UDP", "tcp" => "TCP")),
                                        'IPv4Addr' => array('title' => 'IPv4 Address', 'type' => 'input', 'size' => 12),
                                        'IPv4AddrCidr' => array('title' => 'IPv4 CIDR', 'type' => 'input', 'size' => 2),
                                        'IPv6Addr' => array('title' => 'IPv6 Address', 'type' => 'input', 'size' => 20),
                                        'IPv6AddrCidr' => array('title' => 'IPv6 CIDR', 'type' => 'input', 'size' => 2),
                                        'vpnServer' => array('title' => 'VPN Server', 'type' => 'select', 'options' => Model_VpnServer::getVpnServerNames()),
                                ),
                        ),
                        'interfaces' => array(
                                'title' => 'Interfaces',
				'type' => 'fieldset',
				'fields' => array(
					'currentInterfaces' => array(
						'title' => '',
                                		'type' => 'table',
                                		'fields' => array(
              			                        'id' => array('type' => 'hidden'),
                                        		'name' => array('title' => 'Name', 'type' => 'input', 'size' => 10),
	                	                        'IPv4Addr' => array('title' => 'IPv4', 'type' => 'input', 'size' => 12),
        	                	                'IPv4AddrCidr' => array('title' => '', 'type' => 'input', 'size' => 2),
                	                	        'IPv6Addr' => array('title' => 'IPv6', 'type' => 'input', 'size' => 20),
		                                        'IPv6AddrCidr' => array('title' => '', 'type' => 'input', 'size' => 2),
                		                        'ssid' => array('title' => 'SSID', 'type' => 'input', 'size' => 15),
                                		        'type' => array('title' => 'Type', 'type' => 'select', 'options' => array("dhcp" => "DHCP", "static" => "Static")),
		                                        'offerDhcp' => array('title' => 'Offer DHCP', 'type' => 'checkbox'),
                		                        'is1x' => array('title' => 'Is 1x', 'type' => 'checkbox'),
                                		        'networkAdapterMac' => array('title' => 'Mac', 'type' => 'input', 'size' => 15),
		                                        'networkAdapterWirelessChannel' => array('title' => 'Channel', 'type' => 'input', 'size' => 3),
                		                        'networkAdapterType' => array('title' => 'Adapter Type', 'type' => 'select', 'options' => Kohana::$config->load('system.default.adapter_types')),
                                		),
					),
				),
                        ),
                );
		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($boxNumber, $formValues)
	{
		$node = Doctrine::em()->getRepository('Model_Node')->findOneByBoxNumber($boxNumber);
		$node->firmwareImage = $formValues['firmwareImage'];
		$vpnEndpoint = $node->vpnEndpoint;
                $vpnEndpoint->port = $formValues['vpnEndpoint']['port'];
		$vpnEndpoint->protocol = $formValues['vpnEndpoint']['protocol'];
		$vpnEndpoint->IPv4Addr = $formValues['vpnEndpoint']['IPv4Addr'];
		$vpnEndpoint->IPv4AddrCidr = $formValues['vpnEndpoint']['IPv4AddrCidr'];
		$vpnEndpoint->IPv6Addr = $formValues['vpnEndpoint']['IPv6Addr'];
                $vpnEndpoint->IPv6AddrCidr = $formValues['vpnEndpoint']['IPv6AddrCidr'];
		$vpnEndpoint->vpnServer = Doctrine::em()->getRepository('Model_VpnServer')->findOneByName($formValues['vpnEndpoint']['vpnServer']);
		$vpnEndpoint->save();
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
                                if (!isset($interfaceValues['is1x']))
                                {
                                        $interfaceValues['is1x'] = 0;
                                }
				if (empty($interfaceValues['id'])) {
					$ipv4 = IP_Network_Address::factory($interfaceValues['IPv4Addr'], $interfaceValues['IPv4AddrCidr']);
					$ipv6 = IP_Network_Address::factory($interfaceValues['IPv6Addr'], $interfaceValues['IPv6AddrCidr']);
					$networkAdapter = Doctrine::em()->getRepository('Model_NetworkAdapter')->findOneBy(array('mac' => $interfaceValues['networkAdapterMac'], 'type' => $interfaceValues['networkAdapterType']));
					if (empty($networkAdapter))
					{
						$networkAdapter = Model_NetworkAdapter::build(
							$interfaceValues['networkAdapterMac'], 
							$interfaceValues['networkAdapterWirelessChannel'], 
							$interfaceValues['networkAdapterType'], 
							$node
						);
					}
					$node->interfaces->add(Model_Interface::build(
						$ipv4, 
						$ipv6, 
						$interfaceValues['name'],
						$interfaceValues['ssid'], 
						$interfaceValues['type'], 
						$interfaceValues['offerDhcp'], 
						$interfaceValues['is1x'], 
						$networkAdapter, 
						$node
					));
				}
				else
				{
					$interface = Doctrine::em()->getRepository('Model_Interface')->findOneById($interfaceValues['id']);
					$interface->name = $interfaceValues['name'];
					$interface->IPv4Addr = $interfaceValues['IPv4Addr'];
					$interface->IPv4AddrCidr = $interfaceValues['IPv4AddrCidr'];
					$interface->IPv6Addr = $interfaceValues['IPv6Addr'];
	        	                $interface->IPv6AddrCidr = $interfaceValues['IPv6AddrCidr'];
 					$interface->ssid = $interfaceValues['ssid'];
					$interface->offerDhcp = $interfaceValues['offerDhcp']; 
					$interface->is1x = $interfaceValues['is1x'];
					$networkAdapter = $interface->networkAdapter;
					$networkAdapter->mac = $interfaceValues['networkAdapterMac'];
                                        $networkAdapter->wirelessChannel = $interfaceValues['networkAdapterWirelessChannel']; 
                                        $networkAdapter->type = $interfaceValues['networkAdapterType'];
					$networkAdapter->save();
					$interface->save();
				}	
                        }
                }
		$node->save();
	}
}
	
