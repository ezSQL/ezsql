/**
 * Tear up script for generating database and user for tests
 *
 * @author  Stefanie Janine Stoelting (mail@stefanie-stoelting.de)
 * @name    ezSQL_postgresql_tear_up
 * @package ezSQL
 * @subpackage unitTests
 * @license FREE / Donation (LGPL - You may do what you like with ezSQL - no exceptions.)
 */

-- Create the user
CREATE USER ez_test WITH PASSWORD 'ezTest';

-- Create the database
CREATE DATABASE ez_test OWNER ez_test;
