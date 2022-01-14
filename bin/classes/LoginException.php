<?php

use spitfire\exceptions\PublicException;

      class LoginException extends PublicException
      {
          private $userID = 0;
          private $reason = '';
          private $expiry = 0;

          public function __construct($message = '', $code = 0, Throwable $previous = null){
              if (empty($message)){ //Set Default Message
                  $message = 'There was a problem while logging in.';
              }
              parent::__construct($message, $code, $previous);
          }

          public function setUserID($userID){
              $this->userID = $userID;
          }

          public function getUserID(){
              return $this->userID;
          }

          public function setReason($reason){
              $this->reason = $reason;
          }

          public function getReason(){
              return $this->reason;
          }

          public function setExpiry($expiry){
              $this->expiry = $expiry;
          }

          public function getExpiry(){
              return $this->expiry;
          }
      }
