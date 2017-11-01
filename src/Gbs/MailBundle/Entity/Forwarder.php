<?php

namespace Gbs\MailBundle\Entity;

class Forwarder {
    private $to;

 /**
 * Get the value of To
 *
 * @return mixed
 */
 public function getTo() {
     return $this->to;
}  /**
  * Set the value of To
  *
  * @param mixed to
  *
  * @return self
  */
  public function setTo($to) {
      $this->to = $to;
      return $this;
}
}
