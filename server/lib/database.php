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
        $db->exec( 'CREATE TABLE messages (sender_no TEXT, receiver_no TEXT, date TEXT, content TEXT, status INTEGER)' );
    }

    return $db;
}
