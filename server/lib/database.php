<?php


// status:
define( 'STATUS_RECEIVED', 0 ); // received message
define( 'STATUS_WAITING',  2 ); // waiting for relay
define( 'STATUS_SENDING',  3 ); // relayed, waiting for confirmation
define( 'STATUS_SENT',     4 ); // sent

define( 'TOI_SQLITE_FILE', TOI_ROOT . '/data/db.sqlite' );

/**
 * @param $filename
 * @param bool $scratch Build database from scratch
 * @return bool|SQLite3
 */
function init_db( $filename, $scratch = false ) {
    if ( $scratch ) {
        unlink( $filename );
    }

    if ( !file_exists( $filename ) ) {
        $scratch = true;
    }

    if ( ( $db = new SQLite3( $filename ) ) === false ) {
        return false;
    }

    if ( $scratch ) {
        // Actually every OpenSSL AES encryption is unique. UNIQUE here to avoid retry requests.
        $db->exec( 'CREATE TABLE messages (encrypted TEXT UNIQUE, status INTEGER)' );
    }

    return $db;
}
