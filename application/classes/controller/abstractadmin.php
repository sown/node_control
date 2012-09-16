<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_AbstractAdmin extends Controller
{
        protected function check_login($role = NULL)
        {
                if (!Auth::instance()->logged_in($role))
                {
                        if (!Auth::instance()->logged_in())
                                $this->request->redirect(Route::url('login').URL::query(array('url' => $this->request->url())));
                        else
                                throw new HTTP_Exception_403('You do not have permission to access this page.');
                }
        }
}