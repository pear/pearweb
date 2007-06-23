<?php
class MockSession
{
   private $_session;

   function init($name, array $values)
   {
       session_set_save_handler(
           array($this, 'open'),
           array($this, 'close'),
           array($this, 'read'),
           array($this, 'write'),
           array($this, 'destroy'),
           array($this, 'gc'));
       $this->_session[$name] = $values;
   }

   function _serialize($id)
   {
       $ret = '';
       foreach ($id as $var => $value) {
           $ret .= $var . '|' . serialize($value);
       }
       return $ret;
   }

   function _unserialize($id)
   {
       $ret = array();
       preg_match_all('/([a-zA-Z0-9]+)\\|/', $id, $matches);
       foreach ($matches[1] as $i => $varname) {
           $b = isset($matches[1][$i + 1]) ? strpos($id, $matches[1][$i + 1]) : strlen($id);
           $ser = substr($id, strlen($varname) + 1, $b - 2);
           $ret[$varname] = unserialize($ser);
           $id = substr($id, strlen($ser) + strlen($varname) + 1);
       }
       return $ret;
   }

   function getSession($name)
   {
       return isset($this->_session[$name]) ? $this->_session[$name] : array();
   }

   function open($savepath, $name)
   {
       if (!isset($this->_session[$name])) $this->_session[$name] = array();
       return true;
   }

   function close()
   {
       return true;
   }

   function read($id)
   {
       $i = $this->getSession($id);
       return $i ? $this->_serialize($i) : '';
   }

   function write($id, $data)
   {
       $this->_session[$id] = $data? $this->_unserialize($data) : null;
   }

   function destroy($id)
   {
       if (isset($this->_session[$id])) unset($this->_session[$id]);
   }

   function gc($maxlifetime)
   {
   }
}