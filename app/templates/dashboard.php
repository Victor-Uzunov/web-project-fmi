<?php
// app/templates/dashboard.php

// This file acts as a wrapper for the main content of the logged-in user dashboard.
// Variables like $message and $courses are expected to be available from index.php
include __DIR__ . '/add_course_form.php';
include __DIR__ . '/course_list.php';

?>
