<?php
require_once 'helper.php'; // Include the helper file


function sanitizeInput($data) {

    $data = trim($data);

    $data = stripslashes($data);

    $data = htmlspecialchars($data);

    return $data;

}



// Function to check if a value is empty

function isEmpty($value) {

    return empty(trim($value));

}



// Function to display errors

function displayErrors($errors) {

    foreach ($errors as $field => $error) {

        echo "<p>Error in $field: $error</p>";

    }

}

