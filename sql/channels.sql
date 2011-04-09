CREATE TABLE channels (
  name varchar(255) NOT NULL default '', 
  is_active tinyint(1),
  project_label varchar(255) NOT NULL default '',
  project_link varchar(255) NOT NULL default '',
  contact_name varchar(255) NOT NULL default '',
  contact_email varchar(255) NOT NULL default '',
  PRIMARY KEY  (name)
);

INSERT INTO channels VALUES ('http://pear.php.net', 1, 'PEAR', 'http://pear.php.net/', 'PEAR Webmaster', 'pear-webmaster@lists.php.net');

INSERT INTO channels VALUES ('http://pecl.php.net', 1, 'PECL', 'http://pecl.php.net/', 'PEAR Webmaster', 'pear-webmaster@lists.php.net');


INSERT INTO channels VALUES ('http://pear.11abacus.com', 1, '11abacus', 'http://pear.11abacus.com/', '', '');
INSERT INTO channels VALUES ('http://pear.agavi.org', 1, 'Agavi', 'http://pear.agavi.org/', '', '');
INSERT INTO channels VALUES ('http://pear.crisscott.com', 1, 'Crisscott', 'http://pear.crisscott.com/', '', '');
INSERT INTO channels VALUES ('http://pear.domain51.com', 1, 'Domain51', 'http://pear.domain51.com/', '', '');
INSERT INTO channels VALUES ('http://components.ez.no', 1, 'eZ components', 'http://components.ez.no/', '', '');
INSERT INTO channels VALUES ('http://pear.horde.org', 1, 'Horde', 'http://pear.horde.org/', '', '');
INSERT INTO channels VALUES ('http://ragnaroek.pear.midgard-project.org', 1, 'Midgard Project', 'http://www.midguard-project.org/', '', '');
INSERT INTO channels VALUES ('http://pear.phing.info', 1, 'Phing', 'http://pear.phing.info/', '', '');
INSERT INTO channels VALUES ('http://pear.php-tools.net', 1, 'PHP Application Tools', 'http://pear.php-tools.net/', '', '');
INSERT INTO channels VALUES ('http://pear.phpunit.de', 1, 'PHPUnit', 'http://pear.phpunit.de/', '', '');
INSERT INTO channels VALUES ('http://pear.phpspec.org', 1, 'PHPSpec', 'http://pear.phpspec.org/', '', '');
INSERT INTO channels VALUES ('http://pear.piece-framework.com', 1, 'Piece Framework', 'http://pear.piece-framework.com/', '', '');
INSERT INTO channels VALUES ('http://pear-smarty.googlecode.com', 1, 'Inofficial Smarty channel', 'http://pear-smarty.googlecode.com/', '', '');
INSERT INTO channels VALUES ('http://pear.si.kz', 1, 'si.kz', 'http://pear.si.kz', '', '');
INSERT INTO channels VALUES ('http://pear.symfony-project.com', 1, 'Symfony', 'http://pear.symfony-project.com/', '', '');
INSERT INTO channels VALUES ('http://solarphp.com', 1, 'Solar', 'http://solarphp.com/home/index.php?area=Main&amp;page=DownloadInstall#toc1', '', '');
INSERT INTO channels VALUES ('http://pear.funkatron.com', 1, 'Edward Finkler', 'http://pear.funkatron.com/', '', '');
INSERT INTO channels VALUES ('http://zend.googlecode.com', 1, 'Unofficial Zend Framework channel', 'http://zend.googlecode.com/', '', '');
INSERT INTO channels VALUES ('http://pear.firephp.org', 1, 'FirePHP', 'http://pear.firephp.org/', '', '');
INSERT INTO channels VALUES ('http://pear.timj.co.uk', 1, 'Tim Jackson\'s PHP tools', 'http://pear.timj.co.uk/', '', '');
INSERT INTO channels VALUES ('http://pear.phpundercontrol.org', 1, 'phpUnderControl', 'http://pear.phpundercontrol.org/', '', '');
INSERT INTO channels VALUES ('http://pear.pdepend.org', 1, 'PHP Depend', 'http://pear.pdepend.org/', '', '');
INSERT INTO channels VALUES ('http://pear.phpmd.org', 1, 'PHP Mess Detector','http://pear.phpmd.org/', '', '');
INSERT INTO channels VALUES ('http://pear.pearfarm.org', 1, 'PEARFarm', 'http://pear.pearfarm.org/', '', '');
INSERT INTO channels VALUES ('http://pear.pirum-project.org', 1, 'Pirum', 'http://pear.pirum-project.org/', '', '');
INSERT INTO channels VALUES ('http://pearhub.org', 1, 'PEARHub', 'http://pearhub.org/', '', '');
INSERT INTO channels VALUES ('http://pear.fluentdom.org', 1, 'FluentDOM', 'http://pear.fluentdom.org/', '', '');
INSERT INTO channels VALUES ('http://www.faett.net', 1, 'Faett', 'http://www.faett.net/', '', '');
INSERT INTO channels VALUES ('http://phpseclib.sourceforge.net', 1, 'phpseclib', 'http://phpseclib.sourceforge.net/pear.htm', '', '');
INSERT INTO channels VALUES ('http://pear.indeyets.pp.ru', 1, 'Alexey Zakhlestin\'s PEAR channel', 'http://pear.indeyets.pp.ru', '', '');
