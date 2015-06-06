<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Certificates extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array("All Certificates" => Route::url('certificates'), "Current Certificates" => Route::url('current_certificates'));
                $title = 'Certificates';
                View::bind_global('title', $title);
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
		$subtitle = "All Certificates";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

		$fields = array(
                        'id' => 'ID',
			'cn' => 'Common Name',
			'ca' => 'Certificate Authortity',
			'publicKeyMD5' => 'Certificate MD5 Sum',
			'privateKeyMD5' => 'Key MD5 Sum',
			'current' => '',
                        'view' => '',
                );
		$rows = Doctrine::em()->getRepository('Model_Certificate')->findAll();
		$objectType = 'certificate';
                $idField = 'id';
		$content = View::factory('partial/table')
			->bind('fields', $fields)
                        ->bind('rows', $rows)	
			->bind('objectType', $objectType)
                        ->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_current()
        {
                $this->check_login("systemadmin");
                $subtitle = "Current Certificates";
                View::bind_global('subtitle', $subtitle);
                $this->template->sidebar = View::factory('partial/sidebar');
                $this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);

                $fields = array(
                        'id' => 'ID',
                        'cn' => 'Common Name',
                        'ca' => 'Certificate Authortity',
                        'publicKeyMD5' => 'Certificate MD5 Sum',
                        'privateKeyMD5' => 'Key MD5 Sum',
                        'view' => '',
                );
                $rows = Doctrine::em()->getRepository('Model_Certificate')->findByCurrent(1);
                $objectType = 'certificate';
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
		$subtitle = "View Certificate";
		View::bind_global('subtitle', $subtitle);
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$formValues = $this->_load_from_database($this->request->param('id'), 'view');
		$formTemplate = $this->_load_form_template('view');
		$this->template->content = FormUtils::drawForm('view_certificate', $formTemplate, $formValues);
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$certificate = Doctrine::em()->getRepository('Model_Certificate')->findOneById($id);
                if (!is_object($certificate))
                {
                        throw new HTTP_Exception_404();
                }
                $formValues = array(
			'id' => $certificate->id,
                        'cn' => $certificate->cn,
			'ca' => $certificate->ca,
			'publicKeyMD5' => $certificate->publicKeyMD5,
                        'privateKeyMD5' => $certificate->privateKeyMD5,
			'publicKeyFingerprint' => $certificate->publicKeyFingerprint,
                        'privateKeyFingerprint' => $certificate->privateKeyFingerprint,
                        'current' => ( $certificate->current ? 'Yes' : 'No' ),
                        'lastModified' => $certificate->lastModified->format('Y-m-d H:i:s'),
		);
		return $formValues;
	}

	private function _load_form_template($action = 'edit')
        {
                $formTemplate = array(
                        'id' =>  array('type' => 'hidden'),
                        'cn' => array('title' => 'Common Name', 'type' => 'static'),
                        'ca' => array('title' => 'Certificate Aurthority', 'type' => 'static'),
                        'publicKeyMD5' => array('title' => 'Certitficate MD5 Sum', 'type' => 'static'),
                        'privateKeyMD5' => array('title' => 'Key MD5 Sum', 'type' => 'static'),
                        'publicKeyFingerprint' => array('title' => 'Certificate Fingerprint', 'type' => 'static'),
                        'privateKeyFingerprint' => array('title' => 'Key Fingerprint', 'type' => 'static'),
			'current' => array('title' => 'Current', 'type' => 'static'),
                        'lastModified' => array('title' => 'Last Modified', 'type' => 'static'),
                );
                return $formTemplate;

        }


}	
