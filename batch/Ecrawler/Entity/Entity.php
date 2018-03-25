<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ecrawler\Entity;

/**
 * Description of Entity
 *
 * @author murata_sho
 */
abstract class Entity {
    private $default;
    
    function __construct (Array $props = null, $default = null){
        if (!is_null($props)) {
            foreach ($props as $key => $val) {
                $this->set($key, $val);
            }            
        }
        if (!is_null($default)) {
            $this->default = $default;
        } else {
            $this->default = null;
        }
    }
    
    public function set($key, $val) {
        if(property_exists ($this->myName(), $key)) {
            $this->$key = $val;
        }
    }
    
    public function get($key) {
        if(property_exists ($this->myName(), $key) && !is_null($this->$key)){
            return $this->$key;
        }
        return $this->default;
    }
    
    public function getAllProperties() {
        return get_object_vars($this);
    }
    
    private function myName() {
        return get_class($this);
    }
}
