<?php defined('SYSPATH') or die('No direct script access.');

class SOWN
{
	public static function send_irc_message($message)
	{
		$host = 'bot.sown.org.uk';
		$port = 4444;
		
		if ($_SERVER['HTTP_HOST'] == 'www.sown.org.uk')
			$host = 'sown-vpn.ecs.soton.ac.uk';
		
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
		$qb = Doctrine::em()->getRepository('Model_Server')->createQueryBuilder('s');
		$qb->where('s.internalIPv4 LIKE :ip');
		$qb->orWhere('s.externalIPv4 LIKE :ip');
		$qb->orWhere('s.internalIPv6 LIKE :ip');
		$qb->orWhere('s.externalIPv6 LIKE :ip');
		$qb->setParameter('ip', $ipString);
		$query = $qb->getQuery();
		$hosts = $query->getResult();
                if (!empty($hosts[0]))
		{
			return $hosts[0];
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
		$qb = Doctrine::em()->getRepository('Model_Server')->createQueryBuilder('s');
                $qb->where('s.name LIKE :name');
                $qb->orWhere('s.internalName LIKE :name');
                $qb->orWhere('s.internalCname LIKE :name');
                $qb->orWhere('s.icingaName LIKE :name');
                $qb->setParameter('name', $nameString);
                $query = $qb->getQuery();
                $hosts = $query->getResult();
                if (!empty($hosts[0]))
                {
                        return $hosts[0];
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

	public static function get_icinga_name_for_host($host)
        {
		if (in_array(get_class($host), array("Model_Server", "Model_VpnServer")))
		{
			return $host->icingaName;
		}
		elseif (get_class($host) == "Model_Node")
		{
			return "node" . $host->boxNumber;
		}
 	}

	public static function get_all_cron_job_hosts()
	{
		$hosts = array();
		$servers = Doctrine::em()->getRepository('Model_Server')->findBy(array(), array('icingaName' => 'ASC'));
		foreach ($servers as $server)
		{
			$hosts['server:'.$server->id] = $server->icingaName;
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

	public static function draw_bar_graph($title, $xlabel, $ylabel, $xdata, $ydata, $width = 600, $height = 400, $margins = array(70, 10, 30, 60), $angle = 50, $orientate = "vertical")
	{
		require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph', 'php');
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph_bar', 'php');
		$graph = SOWN::setup_graph("Graph", array("width" => $width, "height" => $height, "scale" => "textint"));
		$anagle = SOWN::setup_graph_orientation($graph, $orientate, $margins);
		SOWN::setup_graph_title($graph->title, $title);
		SOWN::setup_graph_axis($graph->xaxis, $xlabel, $xdata, $angle);
		SOWN::setup_graph_axis($graph->yaxis, $ylabel);
		SOWN::add_graph_barplot($graph, $ydata);
                $graph->Stroke();

	}

	public static function draw_accbar_graph($title, $xlabel, $ylabel, $xdata, $ydata, $legend, $width = 600, $height = 400, $margins = array(70, 10, 30, 60), $angle = 50, $orientate = "vertical")
	{
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph', 'php');
                require_once Kohana::find_file('vendor', 'jpgraph/src/jpgraph_bar', 'php');
		$graph = SOWN::setup_graph("Graph", array("height" => $height, "width" => $width, "scale" => "textint"));

		$angle = SOWN::setup_graph_orientation($graph, $orientate, $margins, $angle);
		SOWN::setup_graph_title($graph->title, $title);
		SOWN::setup_graph_axis($graph->xaxis, $xlabel, $xdata, $angle);
		SOWN::setup_graph_axis($graph->yaxis, $ylabel);
		SOWN::add_graph_barplot($graph, $ydata, $legend, 0.8, array("#000033", "#003399", "#0066FF"), array("#000033", "#003399", "#0066FF"));
		$graph->graph_theme = null;
                $graph->Stroke();

        }

	public static function setup_graph($class = null, $attributes = array())
	{
		if (is_null($class))
            		$class = "Graph";
      		$width = 600;
      		$height = 400;
		if (isset($attributes['width']))
                        $width = $attributes['width'];
	      	if (isset($attributes['height']))
			$height = $attributes['height'];
		$graph = new $class($width, $height, "auto");
		if (isset($attributes['scale']))
			$graph->setScale($attributes['scale']);
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
}	

