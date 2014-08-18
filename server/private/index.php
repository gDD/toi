<?php

define( 'TOI_ROOT', dirname( dirname( __FILE__ ) ) );
require( TOI_ROOT . '/lib/database.php' );

date_default_timezone_set( 'Asia/Chongqing' );

if ( ( $db = init_db( TOI_SQLITE_FILE ) ) === false ) {
    die( 'Database initialization...failed.');
}

$res = $db->query( 'SELECT * FROM messages ORDER BY ROWID DESC' );

?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <style>
        body {
            font-family: "Helvetica Neue", sans-serif;
            margin: 0;
        }
        .nav-bar {
            padding: 15px 0 12px 0;
            border-bottom: 1px solid #b2b2b2;
        }
        .nav-title {
            margin: 0;
            text-align: center;
        }
        .messages {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .message {
            margin-left: 28px;
            border-bottom: 1px solid #c8c7cc;
        }
        .message a {
            display: block;;
            margin-left: -28px;
            padding: 15px 14px 15px 28px;
            text-decoration: none;
        }
        .message a:hover {
            text-decoration: none;
        }
        .message p {
            line-height: 20px;
            margin: 0;
            color: #8e8e93;
            font-size: 15px;
        }
        .message .sender {
            float: left;
            line-height: 20px;
            margin: 0;
            color: #000;
        }
        .message .date {
            display: block;
            color: #8e8e93;
            line-height: 20px;
            text-align: right;
            font-size: 15px;
        }
    </style>
</head>
<body>
<div class="nav-bar">
    <h3 class="nav-title">Messages</h3>
</div>
<ul class="messages">
    <?php while ( $row = $res->fetchArray( SQLITE3_ASSOC ) ): ?>
        <li class="message">
            <a href="###">
                <h4 class="sender"><?php echo htmlspecialchars( $row['sender_no'] ); ?></h4>
                <time datetime="<?php echo htmlspecialchars( $row['date'] ); ?>" class="date">
                    <?php
                    $date = new DateTime( $row['date'] );
                    echo htmlspecialchars( $date->format( 'Y-m-d' ) );
                    ?>
                </time>
                <p class="content"><?php echo nl2br( htmlspecialchars( $row['content'] ) ); ?></p>
            </a>
        </li>
    <?php endwhile; ?>
</ul>
</body>
