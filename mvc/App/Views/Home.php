<?php

    echo "<h1>Welcome to the Home Page</h1>";

    if(!empty($users)) {
        echo "<ul>";
        foreach($users as $user) {
            echo "<li>" . htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No users found.</p>";
    }
    
?>