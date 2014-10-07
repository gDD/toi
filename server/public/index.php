<?php

define( 'TOI_ROOT', dirname( dirname( __FILE__ ) ) );
require( TOI_ROOT . '/lib/database.php' );

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
        .option {
            float: right;
            margin-right: 14px;
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
        .message > div {
            display: block;;
            margin-left: -28px;
            padding: 15px 14px 15px 28px;
            text-decoration: none;
        }
        .message > div:hover {
            text-decoration: none;
        }
        .message p {
            line-height: 20px;
            margin: 0;
            color: #8e8e93;
            font-size: 15px;
        }
        .message .caller-id {
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
    <button class="option option--password">🔑</button>
    <h3 class="nav-title">Messages</h3>

</div>
<ul class="messages">
    <?php while ( $row = $res->fetchArray( SQLITE3_ASSOC ) ): ?>
        <li class="message" data-encrypted="<?php echo htmlspecialchars($row['encrypted']); ?>">
            <div class="place-holder">
                <h4 class="caller-id">SMS_CALLER_ID</h4>
                <time datetime="DATETIME" class="date">
                    SMS_DATETIME
                </time>
                <p class="content">SMS_CONTENT</p>
            </div>
        </li>
    <?php endwhile; ?>
</ul>
<script src="assets/js/jquery-2.1.1.min.js"></script>
<script src="assets/js/gibberish-aes-1.0.0.min.js"></script>
<script>
    (function() {
        var password = localStorage.getItem("password") || "";

        $(function() {
            // Save new password
            $(".option--password").on("click", function() {
                var new_password = window.prompt("Decryption password: (will reload current page after submit)", password);
                if (new_password === null) {
                    return;
                }
                // OK with empty content, ask permission to clear password
                if (new_password === "") {
                    var clear = confirm("Do you want to clear the password? (will reload current page after clear)");
                    if (clear) {
                        localStorage.removeItem("password");
                        password = null;
                        location.reload();
                    }
                    return;
                }
                // OK with new password, set it up
                if (new_password !== null) {
                    localStorage.setItem("password", new_password);
                    password = localStorage.getItem("password");
                    location.reload();
                }
            });

            if (password) {
                // Decrypt and display messages
                $(".message").each(function() {
                    var $message = $(this);
                    var data = JSON.parse(GibberishAES.dec($message.data("encrypted"), password));

                    $message.find(".caller-id").text(data.caller_id);
                    $message.find(".date").text(data.date_time);
                    $message.find(".content").text(data.content);
                });
            }
        });
    })();
</script>
</body>
