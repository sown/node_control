<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
/**
 * Model_User
 *
 * @Table(name="users")
 * @Entity
 */
class Model_User extends Model_Entity
{
	/**
	 * @var text $username
	 *
	 * @Column(name="username", type="text", nullable=false)
	 */
	protected $username;

	/**
         * @var text $password
         *
         * @Column(name="password", type="text", nullable=false)
         */
	protected $password;

	/**
         * @var text $name
         *
         * @Column(name="name", type="text", nullable=false)
         */
        protected $name;

	/**
	 * @var text $email
	 *
	 * @Column(name="email", type="text", nullable=false)
	 */
	protected $email;

	/**
	 * @var boolean $isSystemAdmin
	 *
	 * @Column(name="is_system_admin", type="boolean", nullable=false)
	 */
	protected $isSystemAdmin;

	/**
         * @var boolean $canAccessWiki
         *
         * @Column(name="can_access_wiki", type="boolean", nullable=false)
         */
        protected $canAccessWiki;

	/**
         * @var text $wikiUsername
         *
         * @Column(name="wiki_username", type="text", nullable=false)
         */
        protected $wikiUsername;

	/**
	 * @var text $resetPasswordHash
	 *
	 * @Column(name="reset_password_hash", type="text", nullable=false)
	 */
	protected $resetPasswordHash;

        /**
         * @var text $resetPasswordTime
         *
         * @Column(name="reset_password_time", type="datetime", nullable=true)
         */
        protected $resetPasswordTime;
	
	/**
	 * @OneToMany(targetEntity="Model_DeploymentAdmin", mappedBy="user")
	 */
	protected $admins;

	/**
	 * @OneToMany(targetEntity="Model_Device", mappedBy="user")
	 */
	protected $devices;

	 /**
         * @OneToMany(targetEntity="Model_Note", mappedBy="user", cascade={"persist", "remove"})
         */
        protected $notes;

	public function __get($name)
	{
		$this->logUse();
		switch($name)
		{
//			case "bandwidth":
//				return $this->getBandwidth();
			case "deploymentsAsCurrentAdmin":
				return $this->getDeploymentsAsCurrentAdmin();
			case "password":
				return $this->hiddenPassword();
			case "passwordHash":
				return $this->password;
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
//			case "bandwidth":
//				parent::__throwReadOnlyException($name);
			case "password":
				$this->password = SOWN::mysql_password($value);
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

	public static function build($username, $email, $isSystemAdmin = FALSE)
        {
                $obj = new Model_User();
                $obj->username = $username;
                $obj->email = $email;
                $obj->isSystemAdmin = $isSystemAdmin;
		$obj->resetPasswordHash = "";
                return $obj;
        }

	public function __toString()
	{
		$this->logUse();
		$str  = "User: {$this->id}, username={$this->username}, password={$this->hiddenPassword()}, name={$this->name}, email={$this->email}, isSystemAdmin={$this->isSystemAdmin}, wikiUsername={$this->wikiUsername}, resetPasswordHash={$this->resetPasswordHash}, resetPasswordTime={$this->resetPasswordTimestamp()}";
		foreach($this->admins as $admin)
		{
			$str .= "<br/>";
			$str .= "admin={$admin}";
		}
		foreach($this->devices as $device)
		{
			$str .= "<br/>";
			$str .= "device={$device}";
		}
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='user' id='user_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>User</th><td>{$this->id}</td></tr>";
		$str .= $this->fieldHTML('username');
		$str .= $this->fieldHTML('password', $this->hiddenPassword());	
		foreach(array('name', 'email', 'isSystemAdmin', 'wikiUsername', 'resetPasswordHash') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= $this->fieldHTML('resetPasswordTime', $this->resetPasswordTimestamp());
		foreach($this->admins as $admin)
		{
			$str .= $this->fieldHTML('admin', $admin->toHTML());
		}
		foreach($this->devices as $device)
		{
			$str .= $this->fieldHTML('device', $device->toHTML());
		}
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public function getDeploymentsAsCurrentAdmin()
        {
                $deployments = array();
                foreach($this->admins as $admin)
                {
                        $deployments[] = $admin->deployment;
                }
                return $deployments;
        }

	public static function uniqueUsername($username, $id = 0)
        {
		return Model_User::uniqueUsernameWithDomain($username . '@' . Kohana::$config->load('system.default.domain'), $id);
        }

	public static function uniqueUsernameWithDomain($username, $id = 0)	
	{
		if (empty($username))
                {
                        return FALSE;
                }
                $result = Doctrine::em()->getRepository('Model_User')->findByUsername($username);
                if (!empty($result->id) && $result->id == $id)
                {
                        return TRUE;
                }
                return empty($result->id);
	}
		
	public static function uniqueEmail($email, $id = 0)
        {
                if (empty($email))
                {
                        return FALSE;
                }
                $result = Doctrine::em()->getRepository('Model_User')->findOneByEmail($email);
                if (!empty($result->id) && $result->id == $id)
                {
                        return TRUE;
                }
		return empty($result->id);
        }

	public static function validExternalDomain($username)
	{
		if (empty($username))
		{
			return FALSE;
		}
		$user_and_domain = explode("@", $username);
		return in_array($user_and_domain[1], Kohana::$config->load('system.default.admin_system.valid_external_domains'));
	}

	private function hiddenPassword()
	{
		if (strlen($this->password)) 
		{
			return "[HIDDEN]";
		}
		return "";
	}

	private function resetPasswordTimestamp()
	{
		if (is_object($this->resetPasswordTime))
		{
			return $this->resetPasswordTime->getTimestamp();
		}
		return "";
	}


}
