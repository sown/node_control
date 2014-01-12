<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_AbstractAdmin extends Controller_Template
{
	public $template = 'template';
	public $cssFiles = array();
	public $jsFiles = array();
	public $bannerItems;
	protected $userRole = "";

        protected function check_login($role = NULL, $page_type = NUll, $page_id = NULL)
        {
                if (!Auth::instance()->logged_in($role))
                {
                        if (!Auth::instance()->logged_in())
			{
                                $this->request->redirect(Route::url('login').URL::query(array('url' => $this->request->url())));
				return;
			}
                        else
			{
				$user = Doctrine::em()->getRepository('Model_User')->findOneByUsername(Auth::instance()->get_user());
				if ($page_type == "Deployment")
				{
					$deployment = Doctrine::em()->getRepository('Model_Deployment')->find($page_id);
					if ($deployment->hasCurrentDeploymentAdmin($user->id))
					{
						$this->userRole = "deploymentadmin";
						return;
					}
				}
                                throw new HTTP_Exception_403('You do not have permission to access this page.');
			}
                }
		$this->userRole = $role;
        }

	protected function test_login($role = NULL)
        {
                if (!Auth::instance()->logged_in($role))
			return FALSE; 
		return TRUE;
        }

}
