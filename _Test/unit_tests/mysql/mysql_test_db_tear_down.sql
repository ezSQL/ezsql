/**
 * Tear down script to remove all test objects after the test
 *
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * 
 * @name    ezSQL_mysql_tear_down
 * @package ezSQL
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)

*/

-- Drop the database
DROP DATABASE ez_test;

-- Drop the user
DROP USER ezTest;
