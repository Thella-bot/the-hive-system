# Database Schema Documentation

This document provides a comprehensive overview of the database schema for The Hive System.

## Core Tables

### users
The central authentication and user management table.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | User's full name |
| email | string | Unique email address |
| email_verified_at | timestamp | When email was verified |
| password | string | Bcrypt hashed password |
| two_factor_secret | text | 2FA secret key |
| two_factor_recovery_codes | text | 2FA recovery codes |
| two_factor_confirmed_at | timestamp | When 2FA was enabled |
| approved_at | timestamp | When user account was approved |
| student_number | string | Unique student number (nullable) |
| current_team_id | bigint | Current team (Jetstream) |
| profile_photo_path | string | Profile photo file path |
| remember_token | string | remember_token for session |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### profiles
Stores user profile data (dual profile system - StaffProfile or StudentProfile linked via polymorphic relation).

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| profileable_id | bigint | Foreign key to user |
| profileable_type | string | User model type |
| date_of_birth | date | Birth date |
| gender | enum | gender, female, other |
| phone | string | Phone number |
| address | text | Physical address |
| emergency_contact | string | Emergency contact name |
| emergency_phone | string | Emergency contact phone |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### departments
Academic departments within the institute.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Department name |
| code | string | Unique department code |
| description | text | Department description |
| head_user_id | bigint | Foreign key to department head user |
| is_active | boolean | Whether active |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### programmes
Academic programmes offered by the institute.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Programme name |
| code | string | Unique programme code |
| department_id | bigint | Foreign key to department |
| description | text | Programme description |
| duration_months | int | Programme duration in months |
| fee | decimal | Programme fee amount |
| status | enum | active, inactive, archived |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### programme_variants
Different variants of programmes (e.g., full-time, part-time).

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| programme_id | bigint | Foreign key to programme |
| name | string | Variant name |
| code | string | Variant code |
| fee | decimal | Variant-specific fee |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### academic_years
Academic year configuration.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Academic year name (e.g., "2026") |
| start_date | date | Academic year start date |
| end_date | date | Academic year end date |
| is_active | boolean | Whether currently active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### cohorts
Student cohort/group within an academic year and programme.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Cohort name |
| code | string | Unique cohort code |
| programme_id | bigint | Foreign key to programme |
| academic_year_id | bigint | Foreign key to academic year |
| start_date | date | Cohort start date |
| end_date | date | Cohort end date |
| capacity | int | Maximum students |
| is_active | boolean | Whether active |
| deleted_at | timestamp | Soft delete timestamp |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### modules
Course modules within a programme.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| programme_id | bigint | Foreign key to programme |
| name | string | Module name |
| code | string | Unique module code |
| description | text | Module description |
| credits | int | Module credits |
| week_number | int | Scheduled week number |
| is_active | boolean | Whether active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### programme_module (pivot)
Many-to-many relationship between programmes and modules.

| Column | Type | Description |
|-------|------|-------------|
| programme_id | bigint | Foreign key to programme |
| module_id | bigint | Foreign key to module |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Student Management

### applications
Student programme applications.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to applicant user |
| programme_id | bigint | Foreign key to programme |
| cohort_id | bigint | Foreign key to cohort (nullable) |
| status | enum | pending, approved, rejected, admitted, enrolled, withdrawn |
| application_date | date | Date of application |
| review_notes | text | Internal review notes |
| attachment_path | string | Path to uploaded documents |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### enrollments
Student module enrollments.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| module_id | bigint | Foreign key to module |
| cohort_id | bigint | Foreign key to cohort |
| status | enum | active, completed, dropped, failed |
| enrolled_at | timestamp | When enrolled |
| completed_at | timestamp | When completed (nullable) |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### student_grades
Student grades for modules.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| module_id | bigint | Foreign key to module |
| cohort_id | bigint | Foreign key to cohort |
| score | decimal | Numeric score (0-100) |
| grade | string | Letter grade (A, B, C, etc.) |
| remarks | text | Instructor remarks |
| graded_by | bigint | Foreign key to grading user |
| graded_at | timestamp | When graded |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### submissions
Student assignment submissions.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| gradable_id | bigint | Foreign key to gradable |
| file_path | string | Submitted file path |
| content | text | Text content (if inline) |
| submitted_at | timestamp | When submitted |
| grade_score | decimal | Awarded grade (nullable) |
| grade_feedback | text | Grade feedback (nullable) |
| graded_by | bigint | Foreign key to grading user |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Assessments (Gradables)

### gradables
Assessment tasks (assignments, quizzes, exams).

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| module_id | bigint | Foreign key to module |
| title | string | Assessment title |
| description | text | Assessment description |
| type | enum | assignment, quiz, exam |
| max_score | decimal | Maximum possible score |
| passing_score | decimal | Passing score threshold |
| due_date | timestamp | Submission deadline |
| allow_late_submission | boolean | Allow late submissions |
| is_published | boolean | Visible to students |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### gradable_questions
Questions within gradable assessments.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| gradable_id | bigint | Foreign key to gradable |
| question | text | Question text |
| type | enum | multiple_choice, short_answer, essay |
| points | int | Points worth |
| order | int | Display order |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### gradable_question_options
Answer options for multiple choice questions.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| gradable_question_id | bigint | Foreign key to question |
| option_text | string | Option text |
| is_correct | boolean | Whether correct answer |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### gradable_answers
Student answers to gradable questions.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| submission_id | bigint | Foreign key to submission |
| gradable_question_id | bigint | Foreign key to question |
| answer | text | Student's answer |
| is_correct | boolean | Whether correct (nullable, set on grading) |
| points_earned | int | Points earned (nullable) |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### gradable_attachments
Files attached to gradables.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| gradable_id | bigint | Foreign key to gradable |
| file_name | string | Original file name |
| file_path | string | Storage path |
| mime_type | string | File MIME type |
| file_size | int | File size in bytes |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Attendance

### attendances
Student attendance records.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| module_id | bigint | Foreign key to module |
| date | date | Attendance date |
| status | enum | present, absent, late, excused |
| notes | text | Attendance notes |
| recorded_by | bigint | Foreign key to recording user |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Bursar / Finance

### invoices
Student tuition invoices.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| invoice_number | string | Unique invoice number |
| due_date | date | Payment due date |
| status | enum | draft, sent, paid, partial, overdue, cancelled |
| notes | text | Invoice notes |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### invoice_items
Line items on invoices.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| invoice_id | bigint | Foreign key to invoice |
| description | string | Item description |
| quantity | int | Quantity |
| unit_price | decimal | Price per unit |
| subtotal | decimal | Line item total |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### payments
Student payments received.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| invoice_id | bigint | Foreign key to invoice (nullable) |
| payment_number | string | Unique payment reference |
| amount | decimal | Payment amount |
| payment_method | enum | cash, bank_transfer, cheque, card, other |
| payment_date | date | Date of payment |
| reference_number | string | Bank/reference number |
| status | enum | pending, verified, rejected |
| verified_by | bigint | Foreign key to verifier user |
| verified_at | timestamp | When verified |
| notes | text | Payment notes |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### budgets
Annual budgets.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Budget name |
| academic_year_id | bigint | Foreign key to academic year |
| amount | decimal | Budgeted amount |
| spent | decimal | Amount spent |
| status | enum | draft, active, closed |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### expenses
Operational expenses.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| budget_id | bigint | Foreign key to budget (nullable) |
| category_id | bigint | Foreign key to expense category |
| supplier_id | bigint | Foreign key to supplier (nullable) |
| description | string | Expense description |
| amount | decimal | Expense amount |
| date | date | Expense date |
| status | enum | pending, approved, rejected, paid |
| receipt_path | string | Receipt file path |
| approved_by | bigint | Foreign key to approver user |
| approved_at | timestamp | When approved |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### expense_categories
Categories for expenses.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Category name |
| description | text | Category description |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### suppliers
External suppliers and vendors.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Supplier name |
| category | enum | food, equipment, supplies, maintenance, services, other |
| contact_name | string | Contact person name |
| phone | string | Contact phone |
| email | string | Contact email |
| contract_expiry | date | Contract expiry date |
| notes | text | Supplier notes |
| is_active | boolean | Whether active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### payslips
Staff salary slips.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to staff user |
| period_start | date | Pay period start |
| period_end | date | Pay period end |
| basic_salary | decimal | Basic salary amount |
| allowances | decimal | Total allowances |
| deductions | decimal | Total deductions |
| net_salary | decimal | Net salary |
| status | enum | draft, generated, paid |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Library

### book_categories
Categories for library books.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Category name |
| code | string | Category code |
| description | text | Category description |
| is_active | boolean | Whether active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### library_books
Library book inventory.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| isbn | string | ISBN number |
| title | string | Book title |
| author | string | Author name |
| publisher | string | Publisher name |
| year_published | year | Publication year |
| category_id | bigint | Foreign key to category |
| edition | string | Book edition |
| copies_total | int | Total copies |
| copies_available | int | Available copies |
| location | string | Shelf location |
| status | enum | available, lost, damaged |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### book_loans
Book loans to students/staff.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| book_id | bigint | Foreign key to book |
| user_id | bigint | Foreign key to borrower |
| loan_date | date | Date borrowed |
| due_date | date | Due date |
| return_date | date | Date returned (nullable) |
| status | enum | active, returned, overdue |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### book_reservations
Book reservations.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| book_id | bigint | Foreign key to book |
| user_id | bigint | Foreign key to reservator |
| reserved_at | timestamp | When reserved |
| status | enum | pending, fulfilled, cancelled, expired |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Announcements & Events

### announcements
System announcements.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to author |
| title | string | Announcement title |
| content | text | Announcement content |
| is_pinned | boolean | Pinned to top |
| is_public | boolean | Visible to public |
| expires_at | timestamp | Expiration (nullable) |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### announcement_attachments
Files attached to announcements.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| announcement_id | bigint | Foreign key to announcement |
| file_name | string | Original file name |
| file_path | string | Storage path |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### events
Calendar events.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to organizer |
| title | string | Event title |
| description | text | Event description |
| start_at | timestamp | Event start |
| end_at | timestamp | Event end |
| location | string | Event location |
| is_all_day | boolean | All-day event |
| is_public | boolean | Public event |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### event_rsvps
Event RSVPs.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| event_id | bigint | Foreign key to event |
| user_id | bigint | Foreign key to user |
| status | enum | attending, maybe, not_attending |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Document Management

### documents
Policy documents and procedures.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| module_id | bigint | Foreign key to module (nullable) |
| title | string | Document title |
| description | text | Document description |
| version | string | Document version |
| file_path | string | File storage path |
| status | enum | draft, published, archived |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### document_versions
Version history for documents.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| document_id | bigint | Foreign key to document |
| version | string | Version number |
| file_path | string | File storage path |
| created_by | bigint | Foreign key to creator user |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### document_acknowledgements
Document acknowledgement tracking.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| document_id | bigint | Foreign key to document |
| user_id | bigint | Foreign key to user |
| acknowledged_at | timestamp | When acknowledged |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Staff Management

### leave_requests
Staff leave requests.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to staff user |
| type | enum | annual, sick, study, unpaid, other |
| start_date | date | Leave start date |
| end_date | date | Leave end date |
| days | int | Number of leave days |
| reason | text | Leave reason |
| status | enum | pending, approved, rejected |
| approved_by | bigint | Foreign key to approver |
| approved_at | timestamp | When approved |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### salary_profiles
Staff salary configuration.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to staff user |
| basic_salary | decimal | Basic monthly salary |
| hourly_rate | decimal | Hourly rate |
| bank_name | string | Bank name |
| account_number | string | Bank account number |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Engagement & Social

### chat_channels
Chat channels for communication.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Channel name |
| description | text | Channel description |
| is_public | boolean | Public channel |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### messages
Chat messages.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to sender |
| channel_id | bigint | Foreign key to channel (nullable) |
| module_id | bigint | Foreign key to module (nullable) |
| content | text | Message content |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### polls
Polls and surveys.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to creator |
| question | string | Poll question |
| options | json | Poll options array |
| expires_at | timestamp | Expiration (nullable) |
| is_active | boolean | Whether active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### poll_votes
Poll votes.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| poll_id | bigint | Foreign key to poll |
| user_id | bigint | Foreign key to user |
| option_index | int | Selected option index |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### achievements
Student achievements and badges.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| title | string | Achievement title |
| description | text | Achievement description |
| icon | string | Icon identifier |
| points | int | Points value |
| criteria | text | How to earn |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Short Courses

### short_courses
Non-credit short courses.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| title | string | Course title |
| description | text | Course description |
| start_date | date | Course start date |
| end_date | date | Course end date |
| fee | decimal | Course fee |
| capacity | int | Maximum participants |
| is_active | boolean | Whether active |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### short_course_applications
Short course applications.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| short_course_id | bigint | Foreign key to short course |
| name | string | Applicant name |
| email | string | Applicant email |
| phone | string | Applicant phone |
| status | enum | pending, approved, rejected |
| notes | text | Application notes |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Other Tables

### contacts (staff/student profiles)
Polymorphic profile system.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to user |
| type | string | staff, student |
| data | json | Type-specific data |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### notifications
In-app notifications.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to recipient |
| type | string | Notification type |
| data | json | Notification data |
| read_at | timestamp | When read |
| created_at | timestamp | Creation timestamp |

### bookmarks
User bookmarks/favorites.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to user |
| bookmarkable_id | bigint | Foreign key to bookmarked item |
| bookmarkable_type | string | Bookmarked model type |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### keys
Physical key management.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| label | string | Key label |
| location | string | Key location |
| status | enum | available, issued, lost |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### key_assignments
Key issue/return tracking.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| key_id | bigint | Foreign key to key |
| user_id | bigint | Foreign key to user |
| issued_at | timestamp | When issued |
| returned_at | timestamp | When returned |
| status | enum | active, returned |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### visitor_logs
Visitor sign-in/sign-out log.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| visitor_name | string | Visitor name |
| id_number | string | ID document number |
| host_user_id | bigint | Foreign key to host user |
| purpose | string | Visit purpose |
| arrived_at | timestamp | Arrival time |
| departed_at | timestamp | Departure time |
| status | enum | checked_in, checked_out |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### uniform_requests
Student uniform requests.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| items | json | Requested items |
| status | enum | pending, approved, rejected |
| notes | text | Request notes |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### programme_waitlist
Programme waitlist for full cohorts.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to user |
| programme_id | bigint | Foreign key to programme |
| position | int | Waitlist position |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### programme_soughts
Programme interests (prospective students).

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Person's name |
| email | string | Person's email |
| phone | string | Person's phone |
| programme_id | bigint | Foreign key to programme |
| status | enum | interested, applied, enrolled |
| notes | text | Interest notes |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### contact_messages
Public contact form submissions.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| name | string | Sender name |
| email | string | Sender email |
| phone | string | Sender phone |
| subject | string | Message subject |
| message | text | Message content |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### revision_notes
Document revision notes.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| document_id | bigint | Foreign key to document |
| user_id | bigint | Foreign key to author |
| note | text | Revision note |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

### student_tasks
Student todo tasks.

| Column | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to student user |
| title | string | Task title |
| description | text | Task description |
| due_date | date | Due date |
| is_completed | boolean | Completion status |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

---

## Indexes

Key indexes for performance:

- `users.email` - Unique index for authentication
- `users.student_number` - Unique index for student lookup
- `departments.code` - Unique index
- `programmes.code` - Unique index
- `cohorts.code` - Unique index
- `modules.code` - Unique index
- `library_books.isbn` - Unique index
- `attendances(user_id, date)` - Composite index for attendance queries
- `enrollments(user_id, module_id)` - Composite index for student enrollments
- `book_loans(user_id, status)` - Composite index for loan lookups
- `messages(channel_id, created_at)` - Composite index for chat history
- `notifications(user_id, read_at)` - Composite index for notification queries

## Foreign Keys

All foreign keys use:
- `ON DELETE RESTRICT` or `CASCADE` where appropriate
- Timestamps are automatically managed via `$table->timestamps()`

## Soft Deletes

Tables with soft delete support:
- `departments`
- `programmes`
- `cohorts`
- `student_profiles`
- `documents`

These preserve historical data while allowing removal from active lists.