<?php

namespace Gbs\LeaveBundle\Security;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException,
    Symfony\Component\Security\Core\Exception\UnsupportedUserException,
    Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

use IMAG\LdapBundle\Manager\LdapManagerUserInterface,
    IMAG\LdapBundle\User\LdapUserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class LdapUserProvider implements UserProviderInterface
{
    /**
     * @var \IMAG\LdapBundle\Manager\LdapManagerUserInterface
     */
    private $ldapManager;

    /**
     * @var \FOS\UserBundle\Model\UserManagerInterface
     */
    protected $userManager;

    /**
    * @var string
    */
    private $bindUsernameBefore;

    /**
    * The class name of the User model
    * @var string
    */
    private $userClass;

    /**
     * Constructor
     *
     * @param LdapManagerUserInterface $ldapManager
     * @param UserManagerInterface     $userManager
     * @param bool|string              $bindUsernameBefore
     * @param string                   $userClass
     */
    public function __construct(LdapManagerUserInterface $ldapManager, 
                                UserManagerInterface $userManager,
                                $bindUsernameBefore = false,
                                $userClass)
    {
        $this->ldapManager = $ldapManager;
        $this->bindUsernameBefore = $bindUsernameBefore;
        $this->userManager = $userManager;
        $this->userClass = $userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        // Throw the exception if the username is not provided.
        if (empty($username)) {
            throw new UsernameNotFoundException('The username is not provided.');
        }

        // check if the user is already know to us
        $user = $this->userManager->findUserBy(array("username" => $username));

        if (true === $this->bindUsernameBefore) {
            $ldapUser = $this->simpleUser($username, $user);
        } else {
            $ldapUser = $this->anonymousSearch($username, $user);
        }

        return $ldapUser;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof LdapUserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        if (false === $this->bindUsernameBefore) {
            return $this->loadUserByUsername($user->getUsername());
        } else {
            return $this->bindedSearch($user->getUsername());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    private function simpleUser($username, $user)
    {
        $ldapUser = new $this->userClass;
        $ldapUser->setUsername($username);

        return $ldapUser;
    }

    private function anonymousSearch($username, $user)
    {
        $this->ldapManager->exists($username);

        $lm = $this->ldapManager
            ->setUsername($username)
            ->doPass();

        if (empty($user)) {
            $user = $this->userManager->createUser();
            $user->setRoles($lm->getRoles());
            $user
                ->setEnabled(true)
                ->setUsername($lm->getUsername())
                ->setPassword("")
                ->setDn($lm->getDn())
                ->setFullName($lm->getDisplayName())
                ->setLastName($lm->getSurname())
                ->setEmail($lm->getEmail())
                ->setManager($this->userManager->findUserByUsername('sebastien'))
                ;

            $this->userManager->updateUser($user);
        }
        else {
            $user->setRoles($lm->getRoles())
                ->setFullName($lm->getDisplayName())
                ->setLastName($lm->getSurname())
                ->setEmail($lm->getEmail());
            $this->userManager->updateUser($user);
        }

        return $user;
    }

    private function bindedSearch($username)
    {
        return $this->anonymousSearch($username);
    }
}
