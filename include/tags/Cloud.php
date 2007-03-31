<?php
require_once 'HTML/TagCloud.php';
class Pearweb_TagCloud extends HTML_TagCloud
{

    /**
     * @var    array
     * @access protected
     */
    protected $epocLevel = array(
        array(
            'earliest' => array(
                'link'    => '339900',
                'visited' => '339900',
                'hover'   => '339900',
                'active'  => '339900',
            ),
        ),
        array(
            'earlier' => array(
                'link'    => '9999cc',
                'visited' => '9999cc',
                'hover'   => '9999cc',
                'active'  => '9999cc',
            ), 
        ),
        array(
            'later' => array(
                'link'    => '9999ff',
                'visited' => '9999ff',
                'hover'   => '9999ff',
                'active'  => '9999ff',
            ),
        ),
        array(
            'latest' => array(
                'link'    => '0000ff',
                'visited' => '0000ff',
                'hover'   => '0000ff',
                'active'  => '0000ff',
            ),
        ),
    );
    private $_titles = array();
    protected function _createHTMLTag($tag, $type, $fontSize)
    {
        return '<a href="'. $tag['url'] . '" style="font-size: '. 
               $fontSize . $this->sizeSuffix . ';" class="'.  $type . '" title="' .
               $this->_titles[$tag['name']] . '">' .
               htmlspecialchars($tag['name']) . '</a>&nbsp;'. "\n";
    }

    /**
    *
    * add a Tag Element to build Tag Cloud
    *
    * @return  void
    * @param   string  $tag
    * @param   string  $url
    * @param   int     $count
    * @param   int     $timestamp unixtimestamp 
    * @param   string  $title HTML <a> title
    * @access  public
    */
    public function addElement($name = '', $url ='', $count = 0, $timestamp = null,
                               $title = '')
    {
        parent::addElement($name, $url, $count, $timestamp);
        $this->_titles[$name] = $title;
    }
}