<?php
    
    /**
     * Generic mailer class
     * This class enables you to create, fill and send templates of emails.
     */
    require_once 'Mail.php';
    
    class Damblan_Mail {

        /**
         * The template data 
         *  
         * @var string
         * @since  
         */
        private $_template = '';

        /**
         * The data to replace in the template 
         *  
         * @var array
         * @since  
         */
        private $_data = array(); 

        /**
         * Private constructor, use factory methods
         *
         *  
         * @since  
         * @access private
         * @param  
         * @return void
         */
        private function __construct ()
        {
        }

        /**
         * Create a new mail from a template 
         *  
         *  
         * @since  
         * @access public
         * @param array $template The template to load (see example templates for further info)
         * @param array $data The data to set in the template (in the formate 'variable' => 'value')
         * @static
         * @return Damblan_Mailer The created mailer object
         */
        public static function create ($template, $data)
        {
            require 'Damblan/Mail/'.$template.'.tpl.php';
            if (!isset($tpl)) {
                throw new Exception('Template '.$template.' does not exist.');
            }
            if (!is_array($data)) {
                throw new Exception('Data not in correct format, has to be array.');
            }
            $mailer = new Damblan_Mail();
            $mailer->_template = $tpl;
            $mailer->_data = $data;
            return $mailer;
        }
        
        /**
         * Send the mail
         *  
         *  
         * @since
         * @access public
         * @return void
         */
        public function send ( $headers )
        {
            $data = $this->_compile();
            // Merge additional header information to the generated data
            foreach ($headers as $headerName => $header) {
                if (is_array($header)) {
                    foreach ($header as $element) {
                        $data[$headerName][] = $element;
                    }
                } else {
                    $data[$headerName][] = $header;
                }
            }
            // Restructure data for use with PEAR::Mail
            foreach ($data as $field => $content) {
                switch (strtolower($field)) {
                case 'to':
                    $to = $content;
                    unset($data[$field]);
                    break;
                case 'body':
                    $body = $content;
                    unset($data[$field]);
                    break;
                default:
                    if (is_array($content)) {
                        $data[$field] = implode(', ', $content);
                    }
                    break;
                }
            }
            // Attempt to send mail:
            $mail = Mail::factory('mail');
            $res = $mail->send($to, $data, $body);
            if (PEAR::isError($res)) {
                throw new Exception('Unable to send mail. '.$res->getMessage());
            }
            return true;
        }

        /**
         * Compile the data into the template 
         *  
         *  
         * @since  
         * @access private
         * @return void
         */
        private function _compile ()
        {
            // Prepare preg_replace() arrays
            $data['patterns'] = array();
            $data['replacements'] = array();
            foreach ($this->_data as $var => $rep) {
                $data['patterns'][] = "/%$var%/";
                $data['replacements'][] = $rep;
            }
            // Compile template to $res
            $res = array();
            foreach ($this->_template as $key => $val) {
                // If we deal with an array (e.g. the "to"-header may be one)
                if (is_array($val)) {
                    // Walk throuh all array elements
                    foreach ($val as $skey => $sval) {
                        $val[$skey] = preg_replace($data['patterns'], $data['replacements'], $sval);
                    }
                } else {
                    $val = preg_replace($data['patterns'], $data['replacements'], $val);
                }
                // Save the compiled data
                $res[$key] = $val;
            }
            return $res;
        }
    }

?>
