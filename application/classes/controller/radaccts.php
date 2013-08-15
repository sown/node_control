<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Radaccts extends Controller_AbstractAdmin
{
	public function before()
        {
		$this->bannerItems = array();
		parent::before();
	}

	public function action_default()
	{
		$this->check_login("systemadmin");
                $title = "Radius Accounting Logs";
                View::bind_global('title', $title);
		$cssFiles =  array('jquery-ui.css');
                View::bind_global('cssFiles', $cssFiles);
                $jsFiles = array('jquery.js', 'jquery-ui.js');
                View::bind_global('jsFiles', $jsFiles);
		$limit = 100;
		$page = 1;
		$formValues = $this->request->post();
		if ($this->request->param('page') && is_numeric($this->request->param('page')) && $this->request->param('page') > 0)
		{
			$page = $this->request->param('page');
		}
		elseif(!empty($formValues['page']) && is_numeric($formValues['page']) && $formValues['page'] > 0)
		{
			$page = $formValues['page'];
		}
		$offset = ($page-1) * $limit;
		$date = '';
		$node = '';
		$formValues = $this->request->post();
	
		if (!empty($formValues['date']))
		{
			$date = $formValues['date'];
			$dateymd = date("Y-m-d", strtotime($date));
		}
		if (!empty($formValues['node']))
                {
                        $node = $formValues['node'];
                }
		$this->template->sidebar = View::factory('partial/sidebar');
		$this->template->banner = View::factory('partial/banner')->bind('bannerItems', $this->bannerItems);
		$content = $this->template->datenode = View::factory('partial/datenode')->bind('date', $date)->bind('node', $node);
		$fields = array(
                        'radacctid' => 'ID',
			'username' => 'Username',
			'framedipaddress' => 'Client IP Address',
			'calledstationid' => 'Node (SSID)',
			'nasipaddress' => 'Node IP Address',
			'acctstarttime' => 'Start Time',
			'acctstoptime' => 'Stop Time',
			'acctsessiontime' => 'Session Time (Seconds)',
			'acctinputoctets' => 'Upstream (MB)',
			'acctoutputoctets' => 'Downstream (MB)',
                        'view' => '',
                );
		$qb = Doctrine::em('radius')->getRepository('Model_Radacct')->createQueryBuilder('ra');
		$countdql = "SELECT COUNT(ra.radacctid) FROM Model_Radacct ra WHERE 1=1 ";
		$qb->where('1=1');
		if (!empty($dateymd)) {
			$countdql .= "AND ra.acctstarttime >= '$dateymd 00:00:00' AND ra.acctstarttime <= '$dateymd 23:59:59' ";
			$qb->andWhere("ra.acctstarttime >= '$dateymd 00:00:00'")->andWhere("ra.acctstarttime <= '$dateymd 23:59:59'");
			
		}
		if (!empty($node)) {
			$query = Doctrine::em()->createQuery("SELECT na.mac FROM Model_Node n JOIN n.interfaces i JOIN i.networkAdapter na WHERE n.boxNumber = '{$node}'")->setMaxResults(1);
                	$macAddress = $query->getSingleScalarResult();
			$macAddress = str_replace(":", "-", $macAddress);
			$qb->andWhere("ra.calledstationid LIKE '$macAddress:%'");
			$countdql .= "AND ra.calledstationid LIKE '$macAddress:%'";
		}
                $qb->orderBy('ra.radacctid', 'DESC');
                $qb->setFirstResult($offset);
   		$qb->setMaxResults($limit);
		$query = $qb->getQuery();
		$rows = $query->getResult();
		$count = Doctrine::em()->createQuery($countdql)->getSingleScalarResult();
                $maxpages = ceil($count/$limit);
		$hiddenfields = array(
			'date' => $date,
			'node' => $node,
		);
		$objectType = 'radacct';
                $idField = 'radacctid';
		$content .= $this->template->pages = View::factory('partial/pages')->bind('page', $page)->bind('maxpages', $maxpages)->bind('hiddenfields', $hiddenfields);
		$content .= View::factory('partial/table')
			->bind('fields', $fields)
                        ->bind('rows', $rows)	
			->bind('objectType', $objectType)
                        ->bind('idField', $idField);
		$this->template->content = $content;	
	}

	public function action_view()
	{
		$this->check_login("systemadmin");
		$title = "View Radius Accounting Record";
		View::bind_global('title', $title);
		$this->template->sidebar = View::factory('partial/sidebar');
		$formValues = $this->_load_from_database($this->request->param('radacctid'), 'view');
		$formTemplate = $this->_load_form_template('view');
//		\Doctrine\Common\Util\Debug::dump($formValues);
		$this->template->content = FormUtils::drawForm('radacct', $formTemplate, $formValues, NULL);
	}

	private function _load_from_database($id, $action = 'edit')
	{
		$radacct = Doctrine::em('radius')->getRepository('Model_Radacct')->findOneByRadacctid($id);
                if (!is_object($radacct))
                {
                        throw new HTTP_Exception_404();
                }
		$csbits=explode(':', $radacct->calledstationid);
                $nodename = str_replace('-', ':', $csbits[0]);
                $query = Doctrine::em()->createQuery("SELECT n FROM Model_Node n JOIN n.interfaces i JOIN i.networkAdapter na WHERE na.mac like '{$nodename}'");
                $query->setMaxResults(1);
                $nodes = $query->getResult();
                if (!empty($nodes[0]))
                {
                        $nodename = "node" . $nodes[0]->boxNumber . " ({$csbits[1]})";
                }
		else 
		{
			$nodename = "{$csbits[0]} ({$csbits[1]})";
		}
		$formValues = array(
                        'radacctid' => $radacct->radacctid,
                        'acctsessionid' => $radacct->acctsessionid,
                        'acctuniqueid' => $radacct->acctuniqueid,
                        'username' => $radacct->username,
                        'groupname' => $radacct->groupname,
                        'realm' => $radacct->realm,
                        'nasipaddress' => $radacct->nasipaddress,
                        'nasportid' => $radacct->nasportid,
                        'nasporttype' => $radacct->nasporttype, 
                        'acctstarttime' => $radacct->acctstarttime->format('Y-m-d H:i:s'),
                        'acctstoptime' => $radacct->acctstoptime->format('Y-m-d H:i:s'),
                        'acctsessiontime' => $radacct->acctsessiontime,
                        'connectinfo_start' => $radacct->connectinfo_start,
                        'connectinfo_stop' => $radacct->connectinfo_stop,
                        'acctinputoctets' => round($radacct->acctinputoctets/1024/1024,3),
                        'acctoutputoctets' => round($radacct->acctoutputoctets/1024/1024,3),
                        'calledstationid' => $nodename,
                        'callingstationid' => $radacct->callingstationid,
                        'acctterminatecause' => $radacct->acctterminatecause,
                        'servicetype' => $radacct->servicetype,
                        'framedprotocol' => $radacct->framedprotocol,
                        'framedipaddress' => $radacct->framedipaddress,
                        'acctstartdelay' => $radacct->acctstartdelay,
                        'acctstopdelay' => $radacct->acctstopdelay,
                        'xascendsessionsvrkey' => $radacct->xascendsessionsvrkey,
                );

		return $formValues;
	}

	private function _load_form_template($action = 'edit')
	{
		$formTemplate = array(
			'radacctid' => array('title' => 'ID', 'type' => 'static'),
                        'acctsessionid' => array('title' => 'Account Session ID', 'type' => 'static'),
			'acctuniqueid' => array('title' => 'Account Unique ID', 'type' => 'static'),
			'username' => array('title' => 'Username', 'type' => 'static'),
			'groupname' => array('title' => 'Group Name', 'type' => 'static'),
			'realm' => array('title' => 'Realm', 'type' => 'static'),
			'nasipaddress' => array('title' => 'Node IP Address', 'type' => 'static'),
			'nasportid' => array('title' => 'Node Port ID', 'type' => 'static'),
			'nasporttype' => array('title' => 'Node Port Type', 'type' => 'static'),
			'acctstarttime' => array('title' => 'Start Time', 'type' => 'static'),
			'acctstoptime' => array('title' => 'Stop Time', 'type' => 'static'),
			'acctsessiontime' => array('title' => 'Session Time', 'type' => 'static'),
			'connectinfo_start' => array('title' => 'Connection Information - Start', 'type' => 'static'),
			'connectinfo_stop' => array('title' => 'Connection Information - Stop', 'type' => 'static'),
			'acctinputoctets' => array('title' => 'Upstream (MB)', 'type' => 'static'),
			'acctoutputoctets' => array('title' => 'Downstream (MB)', 'type' => 'static'),
			'calledstationid' => array('title' => 'Node (SSID)', 'type' => 'static'),
			'callingstationid' => array('title' => 'Client MAC Address', 'type' => 'static'),
			'acctterminatecause' => array('title' => 'Termination Cause', 'type' => 'static'),
			'servicetype' => array('title' => 'Service Type', 'type' => 'static'),
			'framedprotocol' => array('title' => 'Client Protocol', 'type' => 'static'),
			'framedipaddress' => array('title' => 'Client IP Address', 'type' => 'static'),
			'acctstartdelay' => array('title' => 'Start Delay', 'type' => 'static'),
			'acctstopdelay' => array('title' => 'Stop Delay', 'type' => 'static'),
			'xascendsessionsvrkey' => array('title' => 'X Ascend Session Server Key', 'type' => 'static'),
		);
		
		if ($action == 'view') 
		{
			return FormUtils::makeStaticForm($formTemplate);
		}	
		return $formTemplate;
	}

}
	
