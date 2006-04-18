<?php
// Todo: Move me to a shared place!
$items = array(
               'Overview' => array('url'   => 'index.php',
                                   'title' => 'Support Overview'
                                   ),
               'Mailing Lists' => array('url'   => 'lists.php',
                                        'title' => 'PEAR Mailing Lists'
                                        ),
               'Tutorials' => array('url'   => 'tutorials.php',
                                    'title' => 'Tutorials about PEAR (packages)'
                                    ),
               'Presentation Slides' => array('url'   => 'slides.php',
                                              'title' => 'Slides of presentations about PEAR'
                                              ),
               'Icons' => array('url'   => 'icons.php',
                                'title' => 'PEAR icons'
                                ),
               'Forums' => array('url'   => 'forums.php',
                                'title' => 'Forums'
                                )
               );

print_tabbed_navigation($items);

