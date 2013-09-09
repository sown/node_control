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
}	
