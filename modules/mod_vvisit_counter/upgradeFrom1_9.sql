-- Mod_VVisit_Counter Upgrade Script
-- 
-- Upgrade the Counter Table from Version 1_9_xx to Version >= 2_0_0
--
-- Majunke Michael
-- http://www.mmajunke.de
--
-- !! if your joomla prefix is not jos you must replace !
--    : replace jos_ with your prefix
-- !! if you change the counter default table, you must change the names here !
--    : replace vvisitcounter with your table name
--

--
-- drop memo table
-- : this table can dropped always without impact on the counter
-- : no backup needed
DROP TABLE IF EXISTS jos_mod_vvisit_counter_memo;

--
-- backup counter data
-- : the original table is renamed to _tmp
RENAME TABLE jos_vvisitcounter TO jos_vvisitcounter_tmp ;

--
-- create the new counter data table
-- : the new destination table 
CREATE TABLE IF NOT EXISTS jos_vvisitcounter (
  id int(11) unsigned NOT NULL auto_increment,
  tm int(11) NOT NULL,
  ip binary(20) NOT NULL,
  ipraw varchar(40) default NULL,
  userAgent varchar(1024) default NULL,
  data longtext,
  PRIMARY KEY  (id),
  KEY tm (tm),
  KEY ip (ip),
  KEY iptm (ip,tm)
) ENGINE=MyISAM;

--
-- restore date an convert to v2 data structure
-- copy Data from tmp to new 
INSERT INTO jos_vvisitcounter ( id, tm , ip , ipraw , userAgent , data )
  SELECT id, tm , UNHEX(SHA1(jos_vvisitcounter_tmp.ip)) , ip , userAgent , NULL
     FROM jos_vvisitcounter_tmp ;
  
--           
-- CleanUp old data 
-- : if all works fine, please run this line without the commentmarkers (--)
-- DROP TABLE IF EXISTS jos_vvisitcounter_tmp;


-- Done
-- Now you can Install the new Counter Version in Joomla..


-- Errors occured ?
-- Please do not install the Counter in Joomla
-- rename the new table
---  RENAME TABLE jos_vvisitcounter TO jos_vvisitcounter_toCheckErrs ;
-- and then restore the original datatable
---  RENAME TABLE jos_vvisitcounter_tmp TO jos_vvisitcounter ;
-- send me the Errors..     