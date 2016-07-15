<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * Model_Site
 *
 * @Table(name="sites")
 * @Entity
 */
class Model_Site extends Model_Entity
{
	 /**
         * @var string $name
         *
         * @Column(name="name", type="string", length=255, nullable=true)
         */
        protected $name;

	/**
         * @var string $url
         *
         * @Column(name="url", type="string", length=255, nullable=true)
         */
        protected $url;

	 /**
         * @var text $ipAddrs
         *
         * @Column(name="ip_addrs", type="text", nullable=true)
         */
        protected $ipAddrs;

        /**
         * @var string $defaultPermissions
         *
         * @Column(name="default_permissions", type="string", length=255, nullable=true)
         */
        protected $defaultPermissions;

	/**
	 * @var datetime $createdAt
	 *
	 * @Column(name="created_at", type="datetime", nullable=true)
	 */
	protected $createdAt;


	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
			default:
				if (property_exists($this, $name))
				{
					return $this->$name;
				}
				else
				{
					return parent::__get($name);
				}
		}
	}
	
	public function __set($name, $value)
	{
		switch($name)
		{
			default:
				if (property_exists($this, $name))
				{
					$this->$name = $value;
				}
				else
				{
					parent::__set($name, $value);
				}
		}
	}

	public function __toString()
	{
		$this->logUse();
		$str  = "Site: {$this->id}, name={$this->name}, url={$this->url}, ipAddrs={$this->ipAddrs}, defaultPermissions={$this->defaultPermissions}, createdAt={$this->createdAt->format('Y-m-d H:i:s')}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='site' id='site_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>Site</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('name', $this->name);
		$str .= $this->fieldHTML('url', $this->url);
		$str .= $this->fieldHTML('ipAddrs', $this->ipAddrs);
                $str .= $this->fieldHTML('defaultPermissions', $this->defaultPermissions);
		$str .= $this->fieldHTML('createdAt', $this->createdAt->format('Y-m-d H:i:s'));
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	 public static function getSiteNames()
        {
                $sites = Doctrine::em()->getRepository('Model_Site')->findAll();
                $siteNames = array();
                foreach ($sites as $site)
                {
                        $siteNames[$site->id] = $site->name;
                }
                return $siteNames;
        }

	public static function build($name, $url, $ipAddrs, $defaultPermissions = "")
        {
                $site = new Model_Site();
		
                $site->name = $name;
		$site->url = $url;
		$site->ipAddrs = $ipAddrs;
		$site->defaultPermissions = $defaultPermissions;		
		$site->createdAt = new \DateTime();
		$site->save;
                return $site;
        }
}
