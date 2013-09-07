<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Inventory extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("Create Inventory Item" => Route::url('create_inventory_item'), "All Inventory Items" => Route::url('inventory'));
		$title = "Inventory";
		View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Inventory Items";
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
			'uniqueIdentifier' => 'Unique Identifier',
			'type' => 'Type',
			'model' => 'Model',
			'price' => 'Price',
			'location' => 'Location',
			'latestNote' => 'Latest Note',
                        'view' => '',
			'photo' => '',
			'wikiLink' => '',
                        'edit' => '',
                        'delete' => '',
                );
		if (empty($searchOn)) 
		{
			$rows = Doctrine::em()->getRepository('Model_InventoryItem')->findAll();
		}
		else {
			$qb = Doctrine::em()->getRepository('Model_InventoryItem')->createQueryBuilder('i');
	                $qb->where('i.uniqueIdentifier LIKE :searchString');
			$qb->orWhere('i.model LIKE :searchString');
			$qb->orWhere('i.type LIKE :searchString');
			$qb->orWhere('i.state LIKE :searchString');
			$qb->orWhere('i.description LIKE :searchString');
			$qb->orWhere('i.location LIKE :searchString');
        	        $qb->orderBy('i.id', 'ASC');
                	$qb->setParameter(':searchString', "%$searchOn%");
                	$rows = $qb->getQuery()->getResult();
		}
		$objectType = 'inventory_item';
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
		$subtitle = "Create Inventory Item";
		View::bind_global('subtitle', $subtitle);
		$errors = array();
		$success = "";
		if ($this->request->method() == 'POST')
                {
			$formValues = $this->request->post();
			$validation = Validation::factory($formValues);
			if ($validation->check())
        		{
				$inventoryItem = Model_InventoryItem::build($formValues['uniqueIdentifier'], $formValues['type'], $formValues['model'], $formValues['description'], $formValues['location'], $formValues['price'], $formValues['wikiLink'], $formValues['addedBy'], $formValues['state'], $formValues['architecture']);
				$inventoryItem->save();
				$url = Route::url('view_inventory_item', array('id' => $inventoryItem->id));
                        	$success = "Successfully created inventory item with ID: <a href=\"$url\">" . $inventoryItem->id . "</a>.";
 
        		}
			else 
			{
				$errors = $validation->errors();
			} 
                }
		else
		{
			$formValues = array(
				'uniqueIdentifier' => '',
				'type' => '',
				'model' => '',
				'description' => '',
				'location' => '',
				'price' => '',
				'wikiLink' => 'http://www.sown.org.uk/wiki/',
				'addedBy' => Auth::instance()->get_user(),
				'state' => '',
				'architecture' => '',
			);
			
		}
		$formTemplate = array(
			'uniqueIdentifier' => array('title' => 'Unique Identifier', 'type' => 'input', 'size' => 20),
                        'type' => array('title' => 'Type', 'type' => 'input', 'size' => 20),
                        'model' => array('title' => 'Model', 'type' => 'input', 'size' => 40),
                        'description' => array('title' => 'Descritpion', 'type' => 'textarea'),
                        'location' => array('title' => 'Location', 'type' => 'input', 'size' => 40),
                        'price' => array('title' => 'Price', 'type' => 'input', 'size' => 20),
                        'wikiLink' => array('title' => 'Wiki Link', 'type' => 'input', 'size' => 100),
                        'addedBy' => array('title' => 'Added By', 'type' => 'input', 'size' => 40),
                        'state' => array('title' => 'State', 'type' => 'input', 'size' => 70),
                        'architecture' => array('title' => 'Architecture', 'type' => 'input', 'size' => 70)
		);
	
                $this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$this->template->content = FormUtils::drawForm('create_inventory_item', $formTemplate, $formValues, array('createObject' => 'Create Inventory Item'), $errors, $success);
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		if ($this->request->method() == 'POST')
                {
                        $this->request->redirect(Route::url('edit_inventory_item', array('id' => $this->request->param('id'))));
                }
		$subtitle = "View Inventory Item " . $this->request->param('id');
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$notesFormValues = Controller_Notes::load_from_database('InventoryItem', $formValues['id'], 'view');
                $notesFormTemplate = Controller_Notes::load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_inventory_item', $formTemplate, $formValues, array('editInventoryItem' => 'Edit Inventory Item')) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null);
	}

 	public function action_view_photo() 
        {
		$this->auto_render = false;
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$titlealt=$formValues['model'];
		if (!empty($formValues['uniqueIdentifier'])) 
		{
			$titlealt .= " ({$formValues['uniqueIdentifier']})";
		}
		$fullsizeurl = Route::url("view_photo_size_inventory_item",  array('id' => $this->request->param('id'), 'size' => 'full'));
		$fittedurl = Route::url("view_photo_size_inventory_item",  array('id' => $this->request->param('id'), 'size' => 'fitted'));
		$inventoryurl = Route::url("inventory");
		$width="";
		if ($this->request->param('size') != 'full') 
		{
			$width = " width=\"100%\"";
		}
                echo "<html><head><title>$titlealt | " . Kohana::$config->load('system.default.admin_system.site_name') . "</title><head><body><p><a href=\"$fullsizeurl\">Full Size</a> | <a href=\"$fittedurl\">Fit to Page Width</a> | <a href=\"$inventoryurl\">Back to Inventory</a></p><img$width src=\"data:image/jpg;base64,".$formValues['photo']."\" title=\"$titlealt\" alt=\"$titlealt\" /></body></html>";
        }


	public function action_edit()
        {
                $this->check_login("systemadmin");
		$subtitle = "Edit Inventory Item " . $this->request->param('id');
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
				$this->_update($this->request->param('id'), $formValues);
				$success = "Successfully updated inventory item";
				$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
			}
		}
		else
		{
			$formValues = $this->_load_from_database($this->request->param('id'), 'edit');
                }
		$formTemplate = $this->_load_form_template('edit');
		$notesFormValues = Controller_Notes::load_from_database('InventoryItem', $formValues['id'], 'edit');
                $notesFormTemplate = Controller_Notes::load_form_template('edit');
                $this->template->content = FormUtils::drawForm('InventoryItem', $formTemplate, $formValues, array('updateObject' => 'Update Inventory Item'), $errors, $success, array('multipart' => true)) . FormUtils::drawForm('Notes', $notesFormTemplate, $notesFormValues, null) . Controller_Notes::generate_form_javascript();
        }

	public function action_delete()
        {
                $this->check_login("systemadmin");
		$object = Doctrine::em()->getRepository('Model_InventoryItem')->findOneById($this->request->param('id'));
                if (!is_object($object))
                {
                        throw new HTTP_Exception_404();
                }
                $success = "";
		$subtitle = "Delete Inventory Item " . $this->request->param('id');
		View::bind_global('subtitle', $subtitle);
		if ($this->request->method() == 'POST')
                {
                        $formValues = $this->request->post();
			
                        if (!empty($formValues['yes']))
                        {
				$type = 'InventoryItem';
	                        if (Model_Builder::destroy_simple_object($formValues['id'], $type))
				{
                                	$this->template->content = "      <p class=\"success\">Successfully deleted inventory item with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('objects')."\">Inventory</a>.</p></p>";
				}
				else
				{
					$this->template->content = "      <p class=\"error\">Could not delete inventory item with ID " . $formValues['id'] .".  Go back to <a href=\"".Route::url('objects')."\">Inventory</a>.</p>";
				}
                        }
                        elseif (!empty($formValues['no']))
                        {
                              	$this->template->content = "      <p class=\"success\">Inventory item with ID " . $formValues['id'] . " was not deleted.  Go back to <a href=\"".Route::url('objects')."\">Inventory</a>.</p>";
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
				'message' => "Are you sure you want to delete inventory item with ID ".$this->request->param('id') . "?",
			);
			$this->template->content = FormUtils::drawForm('delete_inventory_item', $formTemplate, $formValues, array('yes' => 'Yes', 'no' => 'No'));
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
		$inventoryItem = Doctrine::em()->getRepository('Model_InventoryItem')->findOneById($id);
                if (!is_object($inventoryItem))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
		 	'id' => $inventoryItem->id,
                        'uniqueIdentifier' => $inventoryItem->uniqueIdentifier,
                        'type' => $inventoryItem->type,
			'model' => $inventoryItem->model,
			'writtenOffDate' => $inventoryItem->writtenOffDate->format('Y-m-d H:i:s'),
			'description' => $inventoryItem->description,
			'location' => $inventoryItem->location,
        		'price' => $inventoryItem->price,
			'photo' => "",
			'wikiLink' => $inventoryItem->wikiLink,
			'addedBy' => $inventoryItem->addedBy,
			'acquiredDate' => $inventoryItem->acquiredDate->format('Y-m-d H:i:s'),
			'state' => $inventoryItem->state,	
			'architecture' => $inventoryItem->architecture,	
		);
		if ($inventoryItem->photo !== NULL)
		{
			if (gettype($inventoryItem->photo) == "resource")
			{
				$formValues['photo'] = stream_get_contents($inventoryItem->photo);
			}
			else
			{
				$formValues['photo'] = $inventoryItem->photo;
			}
		}
		if ($action == 'view')
		{
			$formValues['wikiLink'] = "<a href=\"{$formValues['wikiLink']}\">{$formValues['wikiLink']}</a>";
		}
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
                        'id' =>  array('type' => 'hidden'),
                        'uniqueIdentifier' => array('title' => 'Unique Identifier', 'type' => 'input', 'size' => 20),
                        'type' => array('title' => 'Type', 'type' => 'input', 'size' => 20),
                        'model' => array('title' => 'Model', 'type' => 'input', 'size' => 40),
                        'writtenOffDate' => array('title' => 'Written Off Date', 'type' => 'static'),
                        'description' => array('title' => 'Descritpion', 'type' => 'textarea'),
			'location' => array('title' => 'Location', 'type' => 'input', 'size' => 40),
                        'price' => array('title' => 'Price', 'type' => 'input', 'size' => 20),
                        'photo' => array('title' => 'Photo', 'type' => 'imageupload'),
                        'wikiLink' => array('title' => 'Wiki Link', 'type' => 'input', 'size' => 100),
                        'addedBy' => array('title' => 'Added By', 'type' => 'input', 'size' => 40),
                        'acquiredDate' => array('title' => 'Acquired Date', 'type' => 'static'),
                        'state' => array('title' => 'State', 'type' => 'input', 'size' => 70),
                        'architecture' => array('title' => 'Architecture', 'type' => 'input', 'size' => 70),
                );
		if ($action == 'view') 
		{
			$formTemplate = FormUtils::makeStaticForm($formTemplate);
			$formTemplate['photo']['type'] = 'image';
		}	
		return $formTemplate;
	}

	private function _update($id, $formValues)
	{
		$inventoryItem = Doctrine::em()->getRepository('Model_InventoryItem')->findOneById($id);
		$inventoryItem->uniqueIdentifier = $formValues['uniqueIdentifier'];
		$inventoryItem->type = $formValues['type'];
		$inventoryItem->model = $formValues['model'];
		$inventoryItem->description = $formValues['description'];
		$inventoryItem->location = $formValues['location'];
		$inventoryItem->price = $formValues['price'];
		$inventoryItem->wikiLink = $formValues['wikiLink'];
		$inventoryItem->addedBy = $formValues['addedBy'];
		$inventoryItem->state = $formValues['state'];
		$inventoryItem->architecture = $formValues['architecture'];
		if (!empty($_FILES['photo']['tmp_name']))
		{
			$photo = file_get_contents($_FILES['photo']['tmp_name']);
			$inventoryItem->photo = base64_encode($photo);
		}
		$inventoryItem->save();
	}
}
	
