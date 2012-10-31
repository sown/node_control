<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Error extends Controller_AbstractAdmin
{
	public $template = 'template';

	public function before()
	{
		// Don't answer external requests for this controller
		if (Request::$initial === Request::$current)
		{
			$this->request->action(404);
		}

		$status = (int) $this->request->action();

		if (500 == $status)
		{
			$this->template = 'kohana/error';
		}

		parent::before();

		if ($message = rawurldecode($this->request->param('message')))
		{
			$this->template->message = $message;
		}

		$this->response->status($status);
	}

	public function action_403()
        {
                $this->template->title = '403 Forbidden';
		$this->template->content = '<p style="text-align: center; font-weight: bold; font-size: 1em;">You do not have permission to access this page!</p>';
		if ($this->test_login())
			$this->template->sidebar =  View::factory('partial/sidebar');
        }

	public function action_404()
	{
		$this->template->title = '404 Not Found';
		$this->template->content = '<p style="text-align: center; font-weight: bold; font-size: 1em;">This page could not be found!</p>';

		if ($this->test_login())
                        $this->template->sidebar =  View::factory('partial/sidebar');
		else
			$this->template->content = '<p style="text-align: center; font-weight: bold; font-size: 1em;">This page could not be found! Click <a href="/">here</a> to login</p>';			

		// Here we check to see if a 404 came from our website. This allows the
		// webmaster to find broken links and update them in a shorter amount of time.
		if (isset ($_SERVER['HTTP_REFERER']) AND strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) !== FALSE)
		{
			// Set a local flag so we can display different messages in our template.
			$this->template->local = TRUE;
		}
	}

	public function action_503()
	{
		$this->template->title = 'Maintenance Mode';
		$this->template->content = '<p style="text-align: center; font-weight: bold; font-size: 1em;">The SOWN Admin site is currently down for maintenance but should be available again shortly!</p>';
	}

	public function action_500()
	{
		$this->template->title = 'Internal Server Error';
	}

}
