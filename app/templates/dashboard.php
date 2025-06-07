<?php
// app/templates/dashboard.php

// This file acts as a wrapper for the main content of the logged-in user dashboard.
// Variables like $message_for_form and $courses_to_display are expected to be available from index.php

// Include the add course form template
// Pass $message_for_form down to this partial
$message = $message_for_form;
include __DIR__ . '/add_course_form.php';

// Include the course list template
// Pass $courses_to_display and $message_for_form down to this partial
$courses = $courses_to_display;
$message = $message_for_form; // Re-use message variable name expected by list template if needed
include __DIR__ . '/course_list.php';

// No edit course modal in this simplified version

?>