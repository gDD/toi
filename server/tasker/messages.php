<?php

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    error_log( json_encode( $_POST ) );
} else {
    exit();
    # Exit with this and wait for the Tasker app's timer to get this SMS draft
    exit( '+8618600186000|Mengdi Gao brings you this awesome stuff!' );
}
