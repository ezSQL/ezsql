/**
 * Tear up script for generating database and user for tests
 *
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @name    ezSQL_mysql_tear_up
 * @package ezSQL
 * @subpackage unitTests
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 */

-- Create the database
CREATE DATABASE ez_test charset='utf8';

-- Create the user
GRANT ALL PRIVILEGES ON ez_test.* TO ezTest@localhost IDENTIFIED BY 'ezTest';
