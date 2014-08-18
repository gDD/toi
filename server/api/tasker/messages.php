<?php

// @todo: Tasker cannot handle '|' char correctly, translates to "++"

define( 'TOI_ROOT', dirname( dirname( dirname( __FILE__ ) ) ) );
require( TOI_ROOT . '/lib/database.php' );
require( TOI_ROOT . '/lib/config.php' );

if ( ( $db = init_db( TOI_SQLITE_FILE ) ) === false ) {
    die( 'Database initialization...failed.');
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if ( !isset( $_POST['from_no'], $_POST['to_no'], $_POST['date'], $_POST['time'], $_POST['content'] ) ) {
        die();
    }

    $log = TOI_ROOT . '/data/received.log';

    $sender_no = $_POST['from_no'];
    $receiver_no = $_POST['to_no'];
    // Tasker cannot correctly handle SMS body (%SMSRB) with \n, so we encode it by JavaScript equivalent of encodeURIComponent
    $content = rawurldecode( $_POST['content' ] );
    // @todo make it configurable
    $timezone = new DateTimeZone( 'Asia/Chongqing' );
    $datetime = DateTime::createFromFormat( 'm-d-Y H.i', "{$_POST['date']} {$_POST['time']}", $timezone );
    $date = $datetime->format( DateTime::ISO8601 );
    $processed = compact( 'sender_no', 'receiver_no', 'date', 'content' );

    $stmt = $db->prepare( "INSERT INTO messages VALUES ( :sender_no, :receiver_no, :date, :content, :status )" );
    $stmt->bindValue( ':sender_no', $sender_no, SQLITE3_TEXT );
    $stmt->bindValue( ':receiver_no', $receiver_no, SQLITE3_TEXT );
    $stmt->bindValue( ':date', $date, SQLITE3_TEXT );
    $stmt->bindValue( ':content', $content, SQLITE3_TEXT );
    $stmt->bindValue( ':status', STATUS_RECEIVED, SQLITE3_INTEGER );
    $result = $stmt->execute();

    file_put_contents( $log, "\n", FILE_APPEND );
    file_put_contents( $log, "\n", FILE_APPEND );
    file_put_contents( $log, $_SERVER['HTTP_CONTENT_TYPE'], FILE_APPEND );
    file_put_contents( $log, "\n", FILE_APPEND );
    file_put_contents( $log, var_export( $_POST, true ), FILE_APPEND );
    file_put_contents( $log, "\n", FILE_APPEND );
    file_put_contents( $log, var_export( $processed, true ), FILE_APPEND );
    file_put_contents( $log, "\n", FILE_APPEND );

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
}
