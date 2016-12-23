<?php defined('SYSPATH') or die('No direct script access.');

class Controller_NodeHardwares extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Node Hardware" => Route::url('create_node_hardware'), "All Node Hardwares" => Route::url('node_hardwares'));
		$title = "Node Hardwares";
		View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Node Hardwares";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$searchOn = "";
		if ($this->request->method() == 'POST')
                {
                        $searchFormValues = $this->request->post();
			if (!empty($searchFormValues['reset'])) {
				$searchFormValues['searchOn'] = "";
			}
			else {
				$searchOn = $searchFormValues['searchOn'];
			}
		}
		$content = View::factory('partial/search')->bind('searchOn', $searchOn);
		$fields = array(
                        'id' => 'ID',
			'manufacturer' => 'Manufacturer',
			'model' => 'Model',
			'revision' => 'Revision',
			'systemOnChip' => 'System-on-Chip',
			'developmentStatus' => 'Development Status',
                        'view' => '',
                        'edit' => '',
                        'delete' => '',
                );
		if (empty($searchOn)) 
		{
			$rows = Doctrine::em()->getRepository('Model_NodeHardware')->findAll();
		}
		else {
			$qb = Doctrine::em()->getRepository('Model_NodeHardware')->createQueryBuilder('i');
			$qb->where('i.model LIKE :searchString');
			$qb->orWhere('i.manufacturer LIKE :searchString');
			$qb->orWhere('i.developmentStatus LIKE :searchString');
			$qb->orWhere('i.systemOnChip LIKE :searchString');
        	        $qb->orderBy('i.id', 'ASC');
                	$qb->setParameter(':searchString', "%$searchOn%");
                	$rows = $qb->getQuery()->getResult();
		}
		$objectType = 'node_hardware';
                $idField = 'id';
		$content .= View::factory('partial/table')
			->bind('fields', $fields)
                        ->bind('rows', $rows)	
			->bind('objectType', $objectType)
                        ->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_create()
	{
		$this->check_login("systemadmin");
		$subtitle = "Create Node Hardware";
		View::bind_global('subtitle', $subtitle);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues);
			if ($validation->check())
        		{
				$nodeHardware = Model_NodeHardware::build($formValues['manufacturer'], $formValues['model'], $formValues['revision'], $formValues['systemOnChip'], $formValues['ram'], $formValues['flash'], $formValues['wirelessProtocols'], $formValues['ethernetPorts'], $formValues['power'], $formValues['fccid'], $formValues['openwrtPage'], $formValues['developmentStatus']);
				$nodeHardware->save();
				$url = Route::url('view_node_hardware', array('id' => $nodeHardware->id));
                        	$success = "Successfully created node hardware with ID: <a href=\"$url\">" . $nodeHardware->id . "</a>.";
 
        		}
			else 
			{
				$errors = $validation->errors();
			} 
                }
		else
		{
			$formValues = array(
				'manufacturer' => '',
				'model' => '',
				'revision' => '1',
				'systemOnChip' => '',
				'ram' => '',
				'flash' => '',
				'wirelessProtocols' => '',
				'ethernetPorts' => '',
				'power' => '',
				'fccid' => '',
				'openwrtPage' => '',
				'developmentStatus' => '',
			);
			
		}
		$formTemplate = array(
			'manufacturer' => array('title' => 'Manufacturer', 'type' => 'input', 'size' => 30),
                        'model' => array('title' => 'Model', 'type' => 'input', 'size' => 30),
                        'revision' => array('title' => 'Revision', 'type' => 'input', 'size' => 10),
                        'systemOnChip' => array('title' => 'System-on-Chip', 'type' => 'input', 'size' => 40),
                        'ram' => array('title' => 'RAM', 'type' => 'input', 'size' => 5, 'hint' => 'MB'),
                        'flash' => array('title' => 'Flash', 'type' => 'input', 'size' => 5, 'hint' => 'MB'),
                        'wirelessProtocols' => array('title' => 'Wireless Protocols', 'type' => 'input', 'size' => 40),
                        'ethernetPorts' => array('title' => 'Ethernet Ports', 'type' => 'input', 'size' => 40),
                        'power' => array('title' => 'Power', 'type' => 'input', 'size' => 20),
			'fccid' => array('title' => 'FCC ID', 'type' => 'input', 'size' => 20),
			'openwrtPage' => array('title' => 'OpenWRT Page', 'type' => 'input', 'size' => 100),
			'developmentStatus' => array('title' => 'Development Status', 'type' => 'select', 'options' => array('supported' => 'supported', 'under development' => 'under development', 'planned' => 'planned', 'deprecated' => 'deprecated', 'partially deprecated' => 'partially deprecated')),
		);
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('create_node_hardware', $formTemplate, $formValues, array('createObject' => 'Create Node Hardware'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_node_hardware', array('id' => $this->request->param('id'))));
                }
		$subtitle = "View Node Hardware " . $this->request->param('id');
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($this->request->param('id'));
		$switch = $nodeHardware->switch;
		$formTemplate = $this->_load_form_template('view', $switch);
		$this->template->content = FormUtils::drawForm('view_node_hardware', $formTemplate, $formValues, array('editNodeHardware' => 'Edit Node Hardware'));
	}

	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Node Hardware " . $this->request->param('id');
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
			if (!empty($formValues['updateNodeHardware']))
			{
				$errors = $this->_validate($formValues);
				if (sizeof($errors) == 0)
				{
					$this->_update($this->request->param('id'), $formValues);
					$success = "Successfully updated node hardware";
				}
			}
			elseif (!empty($formValues['addSwitch']))
			{
				$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($this->request->param('id'));
				$switch = Model_Switch::build("eth1");
                		$nodeHardware->switch = $switch;
				$nodeHardware->save();
				$success = "Successfully created switch for node hardware";

			}
			elseif (!empty($formValues['removeSwitch']))
			{
				$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($this->request->param('id'));
				$switch = $nodeHardware->switch;
				$nodeHardware->switch = null;
				$switch->delete();
				$nodeHardware->save();
				$success = "Successfully deleted switch for node hardware";	
			}
			
		}
		$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($this->request->param('id'));
		$formValues = $this->_load_from_database($nodeHardware->id, 'edit');
		$switch = $nodeHardware->switch;
		$formTemplate = $this->_load_form_template('edit', $switch);
		$formButtons = array('updateNodeHardware' => 'Update Node Hardware');
		if (isset($switch) && $switch->id > 0)
		{
			$formButtons['removeSwitch'] = "Remove Switch";
		}
		else
		{
			$formButtons['addSwitch'] = "Add Switch";
		}
                $this->template->content = FormUtils::drawForm('NodeHardware', $formTemplate, $formValues, $formButtons, $errors, $success, array('multipart' => true));
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$object = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($this->request->param('id'));
                if (!is_object($object))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Node Hardware " . $this->request->param('id');
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'NodeHardware';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted node hardware with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('node_hardwares')."\">Node Hardwares</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete node hardware with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('node_hardwares')."\">Node Hardwares</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Node hardware with ID " . $formValues['id'] . " was not deleted.  Go back to <a href=\"".Route::url('node_hardwares')."\">Node Hardwares</a>.</p>";
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
				'message' => "Are you sure you want to delete node hardware with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_node_hardware', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
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
		$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($id);
                if (!is_object($nodeHardware))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
		 	'id' => $nodeHardware->id,
                        'manufacturer' => $nodeHardware->manufacturer,
			'model' => $nodeHardware->model,
			'revision' => $nodeHardware->revision,
			'systemOnChip' => $nodeHardware->systemOnChip,
        		'ram' => $nodeHardware->ram,
			'flash' => $nodeHardware->flash,
			'wirelessProtocols' => $nodeHardware->wirelessProtocols,
			'ethernetPorts' => $nodeHardware->ethernetPorts,
			'power' => $nodeHardware->power,	
			'fccid' => $nodeHardware->fccid,	
			'openwrtPage' => $nodeHardware->openwrtPage,
			'developmentStatus' => $nodeHardware->developmentStatus,
		);

		$switch = $nodeHardware->switch;
		if (!empty($switch) && $switch->id > 0)
		{
			$formValues['switch'] = Model_Switch::getValuesForForm($switch, $action);
		}

		if ($action == 'view')
		{
			$formValues['ram'] .= "MB";
			$formValues['flash'] .= "MB";
			$formValues['openwrtPage'] = "<a href=\"{$formValues['openwrtPage']}\">{$formValues['openwrtPage']}</a>";
			$formValues['fccid'] = "<a href=\"https://fccid.io/{$formValues['fccid']}\">{$formValues['fccid']}</a>";
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit', $switch = null)
	{
		$formTemplate = array(
                        'manufacturer' => array('title' => 'Manufacturer', 'type' => 'input', 'size' => 30),
                        'model' => array('title' => 'Model', 'type' => 'input', 'size' => 30),
                        'revision' => array('title' => 'Revision', 'type' => 'input', 'size' => 10),
                        'systemOnChip' => array('title' => 'System-on-Chip', 'type' => 'input', 'size' => 40),
                        'ram' => array('title' => 'RAM', 'type' => 'input', 'size' => 5, 'hint' => 'MB'),
                        'flash' => array('title' => 'Flash', 'type' => 'input', 'size' => 5, 'hint' => 'MB'),
                        'wirelessProtocols' => array('title' => 'Wireless Protocols', 'type' => 'input', 'size' => 40),
                        'ethernetPorts' => array('title' => 'Ethernet Ports', 'type' => 'input', 'size' => 40),
                        'power' => array('title' => 'Power', 'type' => 'input', 'size' => 20),
                        'fccid' => array('title' => 'FCC ID', 'type' => 'input', 'size' => 20),
                        'openwrtPage' => array('title' => 'OpenWRT Page', 'type' => 'input', 'size' => 100),
                        'developmentStatus' => array('title' => 'Development Status', 'type' => 'select', 'options' => array('supported' => 'supported', 'under development' => 'under development', 'planned' => 'planned', 'deprecated' => 'deprecated', 'partially deprecated' => 'partially deprecated')),
                );
		if (!empty($switch))
		{
			$formTemplate['switch'] = Model_Switch::getFormTemplate($switch);
		}
		if ($action == 'view') 
		{
			$formTemplate = FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$nodeHardware = Doctrine::em()->getRepository('Model_NodeHardware')->findOneById($id);
		$nodeHardware->manufacturer = $formValues['manufacturer'];
		$nodeHardware->model = $formValues['model'];
		$nodeHardware->revision = $formValues['revision'];
		$nodeHardware->systemOnChip = $formValues['systemOnChip'];
		$nodeHardware->ram = $formValues['ram'];
		$nodeHardware->flash = $formValues['flash'];
		$nodeHardware->wirelessProtocols = $formValues['wirelessProtocols'];
		$nodeHardware->ethernetPorts = $formValues['ethernetPorts'];
		$nodeHardware->power = $formValues['power'];
		$nodeHardware->fccid = $formValues['fccid'];
		$nodeHardware->openwrtPage = $formValues['openwrtPage'];
		$nodeHardware->developmentStatus = $formValues['developmentStatus'];
		if (isset($formValues['switch']))
		{
			$switch = $nodeHardware->switch;
			Model_Switch::update($switch, $formValues['switch']);
                }
		$nodeHardware->save();
	}
}
	
