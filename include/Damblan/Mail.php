<?php
    
    /**
     * Generic mailer class
     * This class enables you to create, fill and send templates of emails.
     */
    require_once 'Mail.php';
    
    class Damblan_Mail {

        /**
         * The template data.
         * A template consists of an array with several keys. The main key is "Body",
         * which contains the text of the body. The rest of the keys represent email
         * headers like "To", "From", "Reply-To" or "X-Mailer". Except the body, every
         * key may contain an array of strings, the body itself may only be a string.
         * If one of the header keys is an array, the elements of this array will be
         * concated using the string ", ". For example templates, see the 
         * include/Damblan/Mail/ directory, where all templates are stored.
         *  
         * @var array
         * @since  
         */
        private $_template = '';

        /**
         * The data to replace in the template.
         * This is an array of keys which represent variables (without the %-sign
         * used in the template), associated with the data to replace the variables.
         *  
         * @var array
         * @since
         */
        private $_data = array(); 

        /**
         * Default headers.
         * These default headers will be used, if one of those is neither set through the
         * template nor through the additional headers submitted to the send() method.
         *  
         * @var array
         * @since
         */
        private $_defaultHeaders = array(
            'To'        => 'pear-webmaster@lists.php.net',
            'From'      => 'pear-sys@php.net'
            'Reply-To'  => 'pear-webmaster@lists.php.net',
            'X-Mailer'  => 'PEARWeb - http://pear.php.net'
        ); 

        /**
         * Private constructor, use factory methods!
         *
         *  
         * @since
         * @access private
         * @return void
         */
        private function __construct ()
        {
        }

        /**
         * Create a new mail from a template.
         * This static method will create a new Damblan_Mail instance for you. The
         * given parameters represent the $_template and $_data variables of this class.
         * See their documentation for further information.
         *
         * @since  
         * @access public
         *
         * @param array $template The template. This can either be a name of a template or the
         *                        the template itself (the array). If you choose to use the template's
         *                        name, the template file will automatically be included. The template
         *                        file has to reside in the include/Damblan/Mail/ directory and follow
         *                        the following naming conventions: <name_of_the_template>.tpl.php.
         *
         * @param array $data     The data to set in the template (in the formate 'variable' => 'value').
         *
         * @see Damblan_Mail::$_data
         * @see Damblan_Mail::$_template
         *
         * @static
         *
         * @return Damblan_Mailer The created mailer object.
         * @throws Exception If the template file chosen does not exist.
         * @throws Exception If the data submited is not an array.
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
         * Send the mail.
         * This method is used to send a generated email. When calling this method,
         * the template itself is compiled. Afterwards, the existing headers are merged
         * with the additional header submited to this method.
         *  
         * @since
         * @access public
         * @param array $headers Additional headers to use when sending an email. Note, that
                                 none of these headers will overwrite existing headers, set in
                                 the template. If a header already exists in the template
         * @return void
         */
        public function send ( $headers )
        {
            // Compile the template
            $data = $this->_compile();
            // Merge additional header information to the generated data
            $data = $this->_mergeHeaders($data, $headers);
            // Check sanity of the email headers
            $data = $this->_sanitize($data);
            // Restructure data for use with PEAR::Mail (To-header and body are submitted directly)
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
            if (PEAR::isError($mail)) {
                throw new Exception('Could not create Mail instance. '.$mail->getMessage());
            }
            $res = $mail->send($to, $data, $body);
            if (PEAR::isError($res)) {
                throw new Exception('Unable to send mail. '.$res->getMessage());
            }
            return true;
        }

        /**
         * Sanitize headers. 
         * Sanity checks for the headers of the mail. If a header is missing, it's set to a
         * default value, provided from Damblan_Mail::$_defaultHeaders.
         *  
         * @since  
         * @access private
         * @return array $data The sanitized template data.
         */
        private function _sanitize ($data)
        {
            foreach ($this->_defaultHeaders as $headerName => $header) {
                if (!isset($data[$headerName])) {
                    $data[$headerName] = $header;
                }
            }
            return $data;
        }

        /**
         * Merge new headers into the template.
         * This method merges the newly submited headers into those set by the template.
         * The merge is done in the following ways:
         *  - If no old header was set, the new one is added.
         *  - If the new header is an array and the old one a string, the old one is
         *    converted to an array and then merged with the new (without overwriting).
         *  - If both (old and new header) are an array, they are merged (without
         *    overwriting).
         *  - If the old header is an array and the new one is a string, the new header
         *    is added to the array.
         *  - If both (old and new header) are strings, the new header _overwrites_ the
         *    old one!!
         *  
         * @since  
         * @access private
         * @param array $data The compiled template data.
         * @param array $headers The new header data to merge.
         * @return array $data The merged template data.
         */
        private function _mergeHeaders ( $data, $headers )
        {
            foreach ($headers as $headerName => $header) {
                // The new header is an array
                if (is_array($header)) {
                    // If the header set in the template is not an array, convert it.
                    if (isset($data[$headerName]) && (!is_array($data[$headerName]))) {
                        $data[$headerName] = array($data[$headerName]);
                    }
                    // Add the new headers
                    foreach ($header as $element) {
                        $data[$headerName][] = $element;
                    }
                // The new header is a string
                } else {
                    // The old header is an array, merge the new one
                    if (isset($data[$headerName]) && is_array($data[$headerName])) {
                        $data[$headerName][] = $header;
                    // The old header is a string, overwrite it
                    } else {
                        $data[$headerName] = $header;
                    }
                }
            }
            return $data;
        }

        /**
         * Compile the data into the template.
         * The compilation is processed through all data elements (headers and body).
         *  
         * @since  
         * @access private
         * @return array $data The compiled data.
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
