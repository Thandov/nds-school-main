# Course Enrollments Component

A reusable component for displaying and managing course enrollments for students/learners.

## Usage

```php
// Basic usage - show all enrollments for a student
echo nds_course_enrollments_component($student_id);

// With custom options
echo nds_course_enrollments_component($student_id, [
    'show_title' => true,                    // Show "Course Enrollments" title
    'show_enroll_button' => true,            // Show enroll button
    'show_actions' => true,                  // Show View/Unenroll actions
    'limit' => 5,                            // Limit to 5 recent enrollments
    'status_filter' => 'enrolled',           // Filter by status
    'empty_message' => 'Custom message',     // Custom empty state message
    'enroll_button_text' => 'Add Course',    // Custom button text
    'enroll_button_url' => '#',              // Custom button URL
]);
```

## Parameters

- **$student_id** (int, required): The student/learner ID
- **$args** (array, optional): Configuration options

### Available Options

- `show_title` (bool): Display the "Course Enrollments" heading (default: true)
- `show_enroll_button` (bool): Show the enroll button (default: true)
- `show_actions` (bool): Show View/Unenroll action buttons (default: true)
- `empty_message` (string): Custom message when no enrollments (default: null)
- `limit` (int): Limit number of enrollments shown (default: null = all)
- `status_filter` (string): Filter by enrollment status (default: null = all)
- `enroll_button_text` (string): Custom button text (default: "Enroll in Course")
- `enroll_button_url` (string): Custom button URL (default: "#")
- `enroll_button_class` (string): Custom button CSS classes
- `wrapper_class` (string): Custom wrapper CSS classes
- `table_class` (string): Custom table CSS classes

## Examples

### Show recent 5 enrollments only
```php
echo nds_course_enrollments_component($student_id, [
    'limit' => 5,
    'show_title' => false,  // Hide title for embedded use
]);
```

### Show only completed courses
```php
echo nds_course_enrollments_component($student_id, [
    'status_filter' => 'completed',
    'show_enroll_button' => false,  // Hide enroll button for completed courses
]);
```

### Minimal view (no actions, no title)
```php
echo nds_course_enrollments_component($student_id, [
    'show_title' => false,
    'show_enroll_button' => false,
    'show_actions' => false,
]);
```

## Features

- Displays course enrollments in a clean table format
- Shows course name, code, program, term, status, and grade
- Empty state with customizable message
- Unenroll functionality with confirmation
- Success/error message handling
- Fully customizable via parameters
- Responsive design

## Dependencies

- WordPress database tables: `wp_nds_student_enrollments`, `wp_nds_courses`, `wp_nds_programs`, `wp_nds_faculties`, `wp_nds_academic_years`, `wp_nds_semesters`
- Font Awesome icons (should be enqueued separately)
