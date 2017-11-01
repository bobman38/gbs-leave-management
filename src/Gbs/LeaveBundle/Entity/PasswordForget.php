<?php

namespace Gbs\LeaveBundle\Entity;


/**
 * PasswordForget
 *
 */
class PasswordForget {
    private $email;
    private $actualPw;
    private $newPw1;
    private $newPw2;

    /**
     * Gets the value of email.
     *
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Sets the value of email.
     *
     * @param mixed $email the email 
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets the value of actualPw.
     *
     * @return mixed
     */
    public function getActualPw()
    {
        return $this->actualPw;
    }
    
    /**
     * Sets the value of actualPw.
     *
     * @param mixed $actualPw the actual pw 
     *
     * @return self
     */
    public function setActualPw($actualPw)
    {
        $this->actualPw = $actualPw;

        return $this;
    }

    /**
     * Gets the value of newPw1.
     *
     * @return mixed
     */
    public function getNewPw1()
    {
        return $this->newPw1;
    }
    
    /**
     * Sets the value of newPw1.
     *
     * @param mixed $newPw1 the new pw1 
     *
     * @return self
     */
    public function setNewPw1($newPw1)
    {
        $this->newPw1 = $newPw1;

        return $this;
    }

    /**
     * Gets the value of newPw2.
     *
     * @return mixed
     */
    public function getNewPw2()
    {
        return $this->newPw2;
    }
    
    /**
     * Sets the value of newPw2.
     *
     * @param mixed $newPw2 the new pw2 
     *
     * @return self
     */
    public function setNewPw2($newPw2)
    {
        $this->newPw2 = $newPw2;

        return $this;
    }
}

?>