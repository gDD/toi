<?php

// @todo: Tasker cannot handle '|' char correctly, translates to " "

define( 'TOI_ROOT', dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) );
require( TOI_ROOT . '/lib/database.php' );
require( TOI_ROOT . '/lib/config.php' );

if ( ( $db = init_db( TOI_SQLITE_FILE ) ) === false ) {
    die( 'Database initialization...failed.');
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( empty( $_POST['encrypted'] ) ) {
        die();
    }

    $log_file = TOI_ROOT . '/data/received.log';

    $encrypted = $_POST['encrypted'];

    $stmt = $db->prepare( "INSERT INTO messages VALUES ( :encrypted, :status )" );
    $stmt->bindValue( ':encrypted', $encrypted, SQLITE3_TEXT );
    $stmt->bindValue( ':status', STATUS_RECEIVED, SQLITE3_INTEGER );

    $result = @$stmt->execute();
    if ($result === false) {
        $code = $db->lastErrorCode();
        $message = $db->lastErrorMsg();
        // Duplicate submits of :encrypted column causes this error code
        if ($code === 13) {
            exit();
        }
    }

    file_put_contents( $log_file, "\n\n" . $encrypted . "\n", FILE_APPEND );

    $url = 'https://api.pushover.net/1/messages.json';
    $data = array(
        'token' => PUSHOVER_TOKEN,
        'user' => PUSHOVER_USER_KEY,
        'title' => 'New SMS Message!',
        'message' => 'You\'ve got a new SMS message.',
        'url_title' => 'View',
        'url' => TOI_FRONT_END_URL,
    );

    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
} else {
    exit();
    # Exit with this and wait for the Tasker app's timer to get this SMS draft
    exit( '+8618600186000|Mengdi Gao brings you this awesome stuff!' );

    // Pop sending pending SMSes from SQLite
    // Still work in progress
    $sql = 'SELECT * FROM messages WHERE status = :status ORDER BY ROWID ASC LIMIT 1';
    $stmt = $db->prepare( $sql );
    $stmt->bindValue( ':status', STATUS_QUEUED, SQLITE3_INTEGER );
    $result = $stmt->execute();

    if (!( $row = $result->fetchArray() )) {
        exit();
    }

    echo( $row['receiver_no'] . '|' . $row['content'] );

    $sql = 'UPDATE messages SET status = :status WHERE ROWID = :message_id';

    $stmt->bindValue( ':status', STATUS_SENDING, SQLITE3_INTEGER );
    $stmt->bindValue( ':message_id', $row['ROWID'], SQLITE3_INTEGER );
    $stmt->execute();
}
