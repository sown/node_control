<?php

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\JoinColumns;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\DiscriminatorMap;
/**
 * Model_UserAccount
 *
 * @Table(name="user_accounts")
 * @Entity
 */
class Model_UserAccount extends Model_Entity
{
	/**
         * @var Model_User
         *
         * @ManyToOne(targetEntity="Model_User")
         * @JoinColumns({
         *   @JoinColumn(name="user_id", referencedColumnName="id")
         * })
         */
        protected $user;

	/**
         * @var Model_Site
         *
         * @ManyToOne(targetEntity="Model_Site")
         * @JoinColumns({
         *   @JoinColumn(name="site_id", referencedColumnName="id")
         * })
         */
        protected $site;

	/**
         * @var string $username
         *
         * @Column(name="username", type="string", length=255, nullable=true)
         */
        protected $username;

	/**
         * @var string $permissions
         *
         * @Column(name="permissions", type="string", length=255, nullable=true)
         */
        protected $permissions;

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

	public function __toString()
	{
		$this->logUse();
		return "UserAccount: {$this->id}, username={$this->username}, permissions={$this->permissions}, user={$this->user->name}, site={$this->site->name}, location={$this->location}, createdAt={$this->createdAt->format('Y-m-d H:i:s')}";
		return $str;
	}

	public function toHTML()
	{
		$this->logUse();
		$str  = "<div class='useraccount' id='useraccount_{$this->id}'>";
		$str .= "<table>";
		$str .= "<tr class='ID'><th>UserAccount/th><td>{$this->id}</td></tr>";
		foreach(array('username', 'permissions') as $field)
		{
			$str .= $this->fieldHTML($field);
		}
		$str .= $this->fieldHTML('site', $this->site);
		$str .= $this->fieldHTML('createdAt', $this->createdAt->format('Y-m-d H:i:s'));
		$str .= "</table>";
		$str .= "</div>";
		return $str;
	}

	public static function build($user, $site, $username = "", $permissions = "")
        {
                $user_account = new Model_UserAccount();
                $user_account->user = $user;
		$user_account->site = $site;
		$user_account->username = $username;
		$user_account->permissions = $permissions;
		$user_account->save();
                return $user_account;
        }

}
