<?php defined('SYSPATH') or die('No direct script access.');

class SOWN
{
	public static function send_irc_message($message)
	{
		$host = 'bot.sown.org.uk';
		$port = 4444;
		
		if ($_SERVER['HTTP_HOST'] == 'www.sown.org.uk')
			$host = 'sown-monitor.ecs.soton.ac.uk';
		
		$fp = fsockopen($host, $port);
		fwrite($fp, $message);
		fclose($fp);
	}

	public static function notify_icinga($hostname, $service, $status, $message)
	{
		$host = 'monitor.sown.org.uk';
		$port = 8080;

		$post = '{"host": "'.$hostname.'", "service": "'.$service.'", "status": '.$status.', "output": "'.$message.'"}';
		$url = "http://$host:$port/submit_result";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		return curl_exec($ch);
	}

	public static function send_nsca($host, $service, $status, $message)
	{
	        $data="$host\t$service\t$status\t$message\n";

	        $descriptorspec = array(
	           0 => array("pipe", "r"),
	           1 => array("file", "/dev/null", "a"),
	           2 => array("file", "/dev/null", "a")
	        );

	        $process = proc_open('/usr/sbin/send_nsca -H monitor.sown.org.uk', $descriptorspec, $pipes, "/tmp", NULL);

	        if (is_resource($process))
	        {
	                fwrite($pipes[0], $data);
	                fclose($pipes[0]);
	
	                $return_value = proc_close($process);
	        }
	}

	public static function pluralise($string) 
	{
		if (substr($string, -1) == 'y') 
		{
			return substr($string, 0, -1) . "ies";
		}
		else 
		{
			return $string . "s";
		}
	}

	public static function find_host($hostString)
	{
		$ipAddress = filter_var($hostString, FILTER_VALIDATE_IP);
		if (!empty($ipAddress))
		{
			return Sown::find_host_by_ip($hostString);
		}
		return Sown::find_host_by_name($hostString);
	}

	public static function find_host_by_ip($ipString) 
	{
		if (in_array($ipString, array("127.0.0.1", "127.0.1.1", "::1")))
		{
			$hostname = trim(`hostname`);
			$ipString = trim(`host -t A $hostname | awk 'BEGIN{FS=" "}{print \$NF}'`);

		}
		$server = Model_Server::getByIPAddress($ipString);
                if (is_object($server))
		{
			return $server;
		}
		$nodes = Doctrine::em()->getRepository('Model_Node')->findAll();
		try 
		{
			$ip = IPv4_Address::factory($ipString);
			$ipv4 = true;
		}
		catch(InvalidArgumentException $e)
		{
			try 
			{
				$ip = IPv6_Address::factory($ipString);
				$ipv4 = false;
			}
			catch(InvalidArgumentException $e2)
			{
				return NULL;
			}
		}
		foreach ($nodes as $node) 
		{
			if ($ipv4)
			{
				$vpnEndpointNetAddr = IPv4_Network_Address::factory($node->vpnEndpoint->IPv4Addr, $node->vpnEndpoint->IPv4AddrCidr);
			}
			else
			{
				$vpnEndpointNetAddr = IPv6_Network_Address::factory($node->vpnEndpoint->IPv6Addr, $node->vpnEndpoint->IPv6AddrCidr);
			}
			if ($vpnEndpointNetAddr->encloses_address($ip))
			{
				return $node;
			}
		}
		return NULL;	
	}

	public static function find_host_by_name($nameString) 
	{
		$server = Doctrine::em()->getRepository('Model_Server')->findByName($nameString);
                if (is_object($server))
                {
                        return $server;
                }
		$server = Model_Server::getByHostname($nameString);
		if (is_object($server))
                {
                        return $server;
                }
		elseif (substr($nameString,0,4) == "node" || substr($nameString,0,4) == "Node") 
		{
			$boxNumber = str_replace("node", "", strtolower($nameString));
			$node = Doctrine::em()->getRepository('Model_Node')->findByBoxNumber($boxNumber);
			if (is_object($node)) 
			{
				return $node;
			}
		}
		return NULL;
	}

	public static function get_name_for_host($host)
        {
		if (in_array(get_class($host), array("Model_Server", "Model_VpnServer")))
		{
			return $host->name;
		}
		elseif (get_class($host) == "Model_Node")
		{
			return "node" . $host->boxNumber;
		}
 	}

	public static function get_all_locations()
	{
		$locations = array(
			0 => 'UNSPECIFIED',
		);
                $results = Doctrine::em()->getRepository('Model_Location')->findBy(array(), array('name' => 'ASC'));
                foreach ($results as $result)
                {
                        $locations[$result->id] = "{$result->longName} ({$result->name})";
                }
		return $locations;
	}
	
	public static function get_all_vlans()
        {
                $locations = array(
                        0 => '',
                );
                $results = Doctrine::em()->getRepository('Model_Vlan')->findBy(array(), array('id' => 'ASC'));
                foreach ($results as $result)
                {
                        $locations[$result->id] = $result->name;
                }
                return $locations;
        }

	public static function get_all_cron_job_hosts()
	{
		$hosts = array();
		$servers = Doctrine::em()->getRepository('Model_Server')->findBy(array("retired" => 0), array('name' => 'ASC'));
		foreach ($servers as $server)
		{
			$hosts['server:'.$server->id] = $server->name;
		}
		$hosts['aggregate:all nodes'] = 'all nodes';
                $hosts['aggregate:bandwidth nodes'] = 'bandwidth nodes';
                $hosts['aggregate:openwrt nodes'] = 'openwrt nodes';
                $hosts['aggregate:tunneled nodes'] = 'tunneled nodes';
		$nodes = Doctrine::em()->getRepository('Model_Node')->findBy(array(), array('boxNumber' => 'ASC'));
                foreach ($nodes as $node)
                {
                        $hosts['node:'.$node->id] = "node" . $node->boxNumber;
                }
		return $hosts;
	}

	public static function get_all_host_services()
        {
		$host_services = array();
		$services = Doctrine::em()->getRepository('Model_Service')->findAll();
                foreach ($services as $service)
                {
                        $host_services[$service->id] = $service->label;
                }
		return $host_services;
	}

	public static function process_server_attributes()
	{
		$logging = true;
                $log="";
                if (empty($_POST))
                {
                        die("Lists of server attributes can only be posted to this URL");
                }
		if (empty($_POST['attributes'])) 
		{
                        die("No list of server attributes sent");
                }
                $reportedServerAttributesString = $_POST['attributes']; // $in_string
                $hostAddress = $_SERVER["REMOTE_ADDR"];
                $host = Sown::find_host_by_ip($hostAddress);
                $name = Sown::get_name_for_host($host);
                $log .= "Name: $name\n";
		$curSrvAttrs = array();
		foreach (Kohana::$config->load('system.default.reported_server_attributes') as $srvAttrName) 
		{
			$curSrvAttrs[$srvAttrName] = $host->$srvAttrName;
		}
	 	$log.="=== fromDB ===\n".var_export($curSrvAttrs,true)."\n\n";
                $reportedServerAttributes = explode("<FS>", $reportedServerAttributesString);
                $repSrvAttrs = array();
		foreach ($reportedServerAttributes as $repSrvAttr)
		{
			$repSrvAttr = trim($repSrvAttr);
			if (!empty($repSrvAttr))
			{
				$repSrvAttrBits = explode(":", $repSrvAttr);
				$repSrvAttrs[$repSrvAttrBits[0]] = $repSrvAttrBits[1];
			}
		}
                $log.="=== fromHost ===\n".var_export($repSrvAttrs,true)."\n\n";
                $changed = array();
		$save = 1;
		$errors = "";
		foreach ($curSrvAttrs as $srvAttrName => $curSrvAttrValue)
		{	
			if (!empty($repSrvAttrs[$srvAttrName]) && $curSrvAttrValue != $repSrvAttrs[$srvAttrName])
			{
				$host->$srvAttrName = $repSrvAttrs[$srvAttrName];
				$changed[$srvAttrName] = 1;
				$save = 1;
			}
			elseif (empty($repSrvAttrs[$srvAttrName]))
			{
				$errors .= " No value for $srvAttrName. ";
			}
			else {
				$changed[$srvAttrName] = 0;
			}
		}
                $log.="=== changed ===\n".var_export($changed, true)."\n\n";
		if ($save)
		{
			$host->save();
		}
		# Send to icinga
                if (!isset($errors) || $errors == "")
                {
                        Sown::notify_icinga($name, "SERVER-ATTRS", 0, "SERVER-ATTRS OK: All server attributes reported");
                }
                else
                {
                        Sown::notify_icinga($name, "SERVER-ATTRS", 1, "SERVER-ATTRS WARNING: $errors");
                }
                if (!empty($logging))
                {
			error_log("Logging to /tmp/server_attributes_incoming_${hostAddress}.log");
                        $handle = fopen("/tmp/server_attributes_incoming_${hostAddress}.log","w");
                        fwrite($handle,$log);
                        fclose($handle);
                }
	}

        public static function process_cron_jobs()
        {
                $logging = true;
                $log="";
                if (empty($_POST))
                {
                        die("Lists of cron jobs can only be posted to this URL");
                }
                if (empty($_POST['jobs']))
                {
                        die("No list of cronjobs sent");
                }
                $hostCronJobsString = $_POST['jobs']; // $in_string
                $hostAddress = $_SERVER["REMOTE_ADDR"];
                $host = Sown::find_host_by_ip($hostAddress);
                $name = Sown::get_name_for_host($host);
                $log .= "Name: $name\n";
                $dbCronJobs = $host->getEnabledCronJobs();
                $fromDb = array();
                foreach ($dbCronJobs as $dbCronJob)
                {
                        if (!isset($fromDb[$dbCronJob->username][$dbCronJob->command]))
                        {
                                $fromDb[$dbCronJob->username][$dbCronJob->command] = 1;
                        }
                        else
                        {
                                $fromDb[$dbCronJob->username][$dbCronJob->command]++;
                        }
                }
                $log.="=== fromDb ===\n".var_export($fromDb,true)."\n\n";
                $hostCronJobs = explode("<FS>", $hostCronJobsString);
                for ($i=0; $i<count($hostCronJobs); $i++)
                {
                        $user = substr($hostCronJobs[$i],0,strpos($hostCronJobs[$i],":"));
                        $user = trim($user);
                        $hostCronJob = substr($hostCronJobs[$i],strpos($hostCronJobs[$i],":")+1,strlen($hostCronJobs[$i]));
                        $hostCronJob = trim($hostCronJob);
                        if ($user != "" && $hostCronJob != "" && trim($user) !="cron.update" )
                        {
                                if(! isset($from_node[$user][$hostCronJob]))
                                        $fromHost[$user][$hostCronJob] = 1;
                                else
                                        $fromHost[$user][$hostCronJob]++;
                        }
                }
                $log.="=== fromHost ===\n".var_export($fromHost,true)."\n\n";
                $compare = array();
                foreach ($fromHost as $user => $jobs)
                {
                        if (!isset($compare[$user]))
                                $compare[$user] = array();
                        foreach ($jobs as $job => $value)
                        {
                                if (!isset($compare[$user][$job]))
                                $compare[$user][$job] = 0;
                                $compare[$user][$job] -= $value;
                        }
                }
                foreach ($fromDb as $user => $jobs)
                {
                        if (!isset($compare[$user]))
                                $compare[$user] = array();
                        foreach ($jobs as $job => $value)
                        {
                                if (!isset($compare[$user][$job]))
                                        $compare[$user][$job] = 0;
                                $compare[$user][$job] += $value;
                        }
                }
                $log.="=== compared ===\n".var_export($compare, true)."\n\n";
                $errors = '';
                foreach ($compare as $user => $jobs)
                {
                        foreach ($jobs as $job => $value)
                        {
                                if ($value < 0)
                                {
                                        $errors .= " Node has unregistered job: ($user : $job) ";
                                }
                                elseif ($value > 0)
                                {
                                        $errors .= " Node is missing job: ($user : $job) ";
                                }
                        }
                }
                # Send to icinga
                if (!isset($errors) || $errors == "")
                {
                        Sown::notify_icinga($name, "CRONJOBS", 0, "CRONJOBS OK: Cronjobs as expected");
                }
                else
                {
                        Sown::notify_icinga($name, "CRONJOBS", 1, "CRONJOBS WARNING: $errors");
                }
                if (!empty($logging))
                {
                        $handle = fopen("/tmp/crons_incoming_${hostAddress}.log","w");
                        fwrite($handle,$log);
                        fclose($handle);
                }
        }

	public static function draw_bar_graph($title, $xlabel, $ylabel, $xdata, $ydata, $width = 600, $height = 400, $margins = array(70, 10, 30, 60), $angle = 50, $orientate = "vertical")
	{
		require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph', 'php');
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph_bar', 'php');
		$graph = SOWN::setup_graph("Graph", array("width" => $width, "height" => $height, "scale" => "textint"));
		$angle = SOWN::setup_graph_orientation($graph, $orientate, $margins, $angle);
		SOWN::setup_graph_title($graph->title, $title);
		SOWN::setup_graph_axis($graph->xaxis, $xlabel, $xdata, $angle);
		SOWN::setup_graph_axis($graph->yaxis, $ylabel);
		SOWN::add_graph_barplot($graph, $ydata);
                $graph->Stroke();

	}

	public static function draw_accbar_graph($title, $xlabel, $ylabel, $xdata, $ydata, $legend, $width = 600, $height = 400, $margins = array(70, 10, 30, 60), $angle = 50, $orientate = "vertical", $angle2 = 90, $log = false)
	{
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph', 'php');
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph_bar', 'php');
		$scale = "textint";
		if ($log) 
		{
			require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph_log', 'php');
			$scale = "textlog";
		}
		$graph = SOWN::setup_graph("Graph", array("height" => $height, "width" => $width, "scale" => $scale));

		$angle = SOWN::setup_graph_orientation($graph, $orientate, $margins, $angle, $angle);
		SOWN::setup_graph_title($graph->title, $title);
		SOWN::setup_graph_axis($graph->xaxis, $xlabel, $xdata, $angle);
		SOWN::setup_graph_axis($graph->yaxis, $ylabel, null, $angle2);
		SOWN::add_graph_barplot($graph, $ydata, $legend, 0.8, array("#000033", "#003399", "#0066FF"), array("#000033", "#003399", "#0066FF"));
		$graph->graph_theme = null;
                $graph->Stroke();

        }

	public static function draw_line_graph($title, $xlabel, $ylabel, $xdata, $ydata, $width = 600, $height = 400, $margins = array(70, 10, 30, 60), $angle = 50, $orientate = "vertical", $show_fraction = 12)
	{
		require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph', 'php');
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph_line', 'php');
                $graph = SOWN::setup_graph("Graph", array("width" => $width, "height" => $height, "scale" => "textint", "max" => max($ydata)));
		$angle = SOWN::setup_graph_orientation($graph, $orientate, $margins, $angle);
                SOWN::setup_graph_title($graph->title, $title);
		$xdata_new = array();
		foreach ($xdata as $x => $val)
		{
			$xdata_new[] = ($x % $show_fraction == 0 ? $val : " ");
		}
		SOWN::setup_graph_axis($graph->xaxis, $xlabel, $xdata_new, $angle+90);
                SOWN::setup_graph_axis($graph->yaxis, $ylabel);
                SOWN::add_graph_lineplot($graph, $ydata);
                $graph->Stroke();
	}

	public static function setup_graph($class = null, $attributes = array())
	{
		if (is_null($class))
            		$class = "Graph";
		if (!isset($attributes['max']))
			$attributes['max'] = 0;
      		$width = 600;
      		$height = 400;
		if (isset($attributes['width']))
                        $width = $attributes['width'];
	      	if (isset($attributes['height']))
			$height = $attributes['height'];
		$graph = new $class($width, $height, "auto");
		if (isset($attributes['scale']))
			$graph->setScale($attributes['scale'], 0, $attributes['max']);
		else
			$graph->setScale("textint");
		$graph->SetMarginColor('lightblue'); 
		$graph->SetFrame(true,'black',1);
		$graph->SetBox(true,'black',1);
      		$graph->SetShadow("lightblue");
		$graph->legend->Pos(0.5, 0.97, "center", "bottom");
		$graph->legend->SetFillColor('lightblue');
		$graph->legend->SetLayout(LEGEND_HOR);
      		return $graph;
	}
		
	public static function setup_graph_orientation($graph, $orientate, $margins, $angle = 0)
	{
		if ($orientate == "horizontal")
                {
                        $graph->Set90AndMargin($margins[3], $margins[1], $margins[0], $margins[2]);
                        $angle = 90 - $angle;
                }
                else
		{
                        $graph->SetMargin($margins[0], $margins[1], $margins[2], $margins[3]);
		}
		return $angle;
	}

	public static function setup_graph_title($graphtitle, $title)
	{
		$graphtitle->Set($title);
      		$graphtitle->SetFont(FF_GEORGIA,FS_NORMAL,14);
		$graphtitle->SetColor('black');
	}

	public static function setup_graph_axis($axis, $title, $data = null, $angle = null)
	{
                $axis->SetFont(FF_GEORGIA,FS_NORMAL,10);
		$axis->SetColor('black');
                $axis->SetTitle($title);
                $axis->SetTitleMargin(30);
		if (!empty($angle))
                	$axis->SetLabelAngle($angle);
		if (!empty($data))
                	$axis->SetTickLabels($data);
	}

	public static function add_graph_barplot($graph, $data, $legend = array(), $barwidth = 0.8, $colors = array('#000033'), $fillcolors = array('#000033'))
	{
		$single = false;
		if (!is_array($data[0]))
		{
			$temp = $data;
			$data = array($temp);
			$single = true;
		}
		$b = 0;
		$barplots = array();
		foreach ($data as $series) 
		{
			$barplot = new BarPlot($series);
			$barplot->SetWidth($barwidth);
			if (!$single && isset($legend[$b]))
				$barplot->SetLegend($legend[$b]);
			$barplots[$b++] = $barplot;
		}
		if ($single)
		{
			$graph->Add($barplots[0]);
		}
		else 
		{
			$accbarplot = new AccBarPlot($barplots);
			$accbarplot->SetWidth($barwidth);
                	$graph->Add($accbarplot);
		}
		foreach ($barplots as $b => $barplot)
		{
		 	$barplot->SetColor($colors[$b]);
			$barplot->SetFillGradient($fillcolors[$b], "#DDDDFF", GRAD_LEFT_REFLECTION);
                        $barplot->SetFillColor($fillcolors[$b]);
		}
	}

	public static function add_graph_lineplot($graph, $data, $legend = array(), $linewidth = 0.8, $colors = array('#000033'), $fillcolors = array('#000033'))
        {
                $single = false;
                if (!is_array($data[0]))
                {
                        $temp = $data;
                        $data = array($temp);
                        $single = true;
                }
                $l = 0;
                $lineplots = array();
                foreach ($data as $series)
                {
                        $lineplot = new LinePlot($series);
                        #$lineplot->SetWidth($linewidth);
                        if (!$single && isset($legend[$l]))
                                $lineplot->SetLegend($legend[$l]);
                        $lineplots[$l++] = $lineplot;
                }
                if ($single)
                {
                        $graph->Add($lineplots[0]);
                }
                else
                {
                        $acclineplot = new AccLinePlot($lineplots);
                        #$acclineplot->SetWidth($linewidth);
                        $graph->Add($acclineplot);
		}
                foreach ($lineplots as $l => $lineplot)
                {
                        $lineplot->SetColor($colors[$l]);
                        #$lineplot->SetFillGradient($fillcolors[$l], "#DDDDFF", GRAD_LEFT_REFLECTION);
                        #$lineplot->SetFillColor($fillcolors[$l]);
			
                }
        }

	public static function decimal_to_minute_second_degrees($decdeg, $type, $nodecimal = FALSE)
        {
                $direction = '';
                if ($type == 'longitude')
                {
                        if ($decdeg >= 0) $direction = "E";
                        else $direction = "W";
                }
                else
                {
                        if ($decdeg >= 0) $direction = "N";
                        else $direction = "S";
                }
                $sec = abs($decdeg * 3600);
                $deg = floor($sec / 3600);
                $sec = $sec % 3600;
                $min = floor($sec / 60);
                $sec = $sec % 60;
		if ($nodecimal)
		{
			return array($deg, $min, $sec, $direction);
		}
                return array($deg, $min, $sec.".000", $direction);
        }

	public static function formatted_decimal_to_minute_second_degrees($decdeg, $type, $nodecimal = FALSE)
	{
		$dmsd = SOWN::decimal_to_minute_second_degrees($decdeg, $type, $nodecimal);
		return $dmsd[0]."&deg;".$dmsd[1]."'".$dmsd[2]."&quot;".$dmsd[3];
	}

	public static function sql_password($plaintext) {
    		$pass = strtoupper(sha1(sha1($plaintext, true)));
    		return '*' . $pass;
	}

	public static function get_certificates_for_set($setid)
        {
		$query = Doctrine::em()->createQueryBuilder();
		$query->select('c.id')->from('Model_CertificateSet', 'cs')->join('cs.certificate', 'c')->where("cs.setid = $setid");
		$results = $query->getQuery()->getArrayResult();
                $certs = array();
                foreach ($results as $result)
                {
                        $certs[] =  Doctrine::em()->getRepository('Model_Certificate')->find($result['id']);
                }
                return $certs;
        }

	public static function jsonpp($json, $istr='  ')
	{	
    		$q = FALSE;
    		$result = '';
    		for($p=$i=0; isset($json[$p]); $p++)
    		{
        		if($json[$p] == '"' && ($p>0?$json[$p-1]:'') != '\\')
        		{
            			$q=!$q;
        		}
		        else if(in_array($json[$p], array('}', ']')) && !$q)
		        {
            			$result .= "\n".str_repeat($istr, --$i);
        		}
        		$result .= $json[$p];
        		if(in_array($json[$p], array(',', '{', '[')) && !$q)
        		{
        	    		$i += in_array($json[$p], array('{', '['));
            			$result .= "\n".str_repeat($istr, $i);
      	  		}
    		}
    		return $result;
	}

	public static function tabs($string, $tabs, $tablength = 8)
	{
		$ret = "";
		for ($t = 0; $t < floor(($tabs * $tablength - strlen($string) - 1) / $tablength); $t++)
		{
			$ret .= "\t";
		}
		return $ret;
	}

	public static function array_keys_and_values($array) 
	{
		$hash = array();
		foreach ($array as $value)
		{
			$hash[$value] = $value;
		}
		return $hash;
	}

}	

