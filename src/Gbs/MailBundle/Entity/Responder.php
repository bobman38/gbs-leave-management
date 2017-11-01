<?php

namespace Gbs\MailBundle\Entity;

class Responder {
    private $text;

 /**
 * Get the value of Text
 *
 * @return mixed
 */
 public function getText() {
     return $this->text;
}  /**
  * Set the value of Text
  *
  * @param mixed text
  *
  * @return self
  */
  public function setText($text) {
      $this->text = $text;
      return $this;
}
}
