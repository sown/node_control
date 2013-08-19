<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Radpostauths extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array();
		parent::before();
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		$title = "View Radius Post Auth Record";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('radpostauth', $formTemplate, $formValues, NULL);
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$radpostauth = Doctrine::em('radius')->getRepository('Model_Radpostauth')->findOneById($id);
                if (!is_object($radpostauth))
                {
                        throw new HTTP_Exception_404();
                }
		$formValues = array(
                        'id' => $radpostauth->id,
                        'username' => $radpostauth->username,
                        'pass' => $radpostauth->pass,
                        'reply' => $radpostauth->reply,
                        'authdate' => $radpostauth->authdate->format('Y-m-d H:i:s'),
                );

		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
			'id' => array('title' => 'ID', 'type' => 'static'),
			'username' => array('title' => 'Username', 'type' => 'static'),
			'pass' => array('title' => 'Pass', 'type' => 'static'),
			'reply' => array('title' => 'Reply', 'type' => 'static'),
			'authdate' => array('title' => 'Authdate', 'type' => 'static'),
		);
		
		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

}
	
