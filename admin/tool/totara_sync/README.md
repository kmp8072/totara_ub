HR Sync / Totara Sync
=====================

External database source testing
--------------------------------

To run tests for HR Sync using an external database as the source, the external database needs to be defined in the sites config.

The following snippet illustrates the addition needed to you your sites config.php file.

    define('TEST_SYNC_DB_TYPE', 'mysqli');
    define('TEST_SYNC_DB_HOST', 'localhost');
    define('TEST_SYNC_DB_NAME', '');
    define('TEST_SYNC_DB_USER', '');
    define('TEST_SYNC_DB_PASS', '');
    define('TEST_SYNC_DB_PORT', '');
    define('TEST_SYNC_DB_TABLE', '');

All tests that require an external database will be skipped if the above configuration is not defined.