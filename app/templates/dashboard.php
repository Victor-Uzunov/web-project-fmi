<?php
// app/templates/dashboard.php

// This file acts as a wrapper for the main content of the logged-in user dashboard.
// Variables like $message_for_form, $courses_to_display, $prerequisite_options are expected to be available from index.php

// Include the add course form template
// Pass $message_for_form and $prerequisite_options down to this partial
$message = $message_for_form;
$prerequisites_options = $prerequisite_options; // Ensure variable name matches
include __DIR__ . '/add_course_form.php';

// Include the course list template
// Pass $courses_to_display and $message_for_form down to this partial
$courses = $courses_to_display;
$message = $message_for_form; // Re-use message variable name expected by list template if needed
include __DIR__ . '/course_list.php';

// Include the edit course modal template
// Pass $prerequisite_options down to this partial
$prerequisites_options = $prerequisite_options; // Ensure variable name matches
include __DIR__ . '/modals/edit_course_modal.php';

?>
