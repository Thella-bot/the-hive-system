# Controllers Documentation

This document provides a comprehensive overview of all controllers in The Hive System.

## Public Controllers

### PublicController
Handles public-facing pages.

**Location:** `app/Http/Controllers/PublicController.php`

| Method | Route | Description |
|--------|------|-------------|
| home | GET / | Landing page |
| about | GET /about | About page |
| programmes | GET /programmes | Programme listing |
| contact | GET /contact | Contact page |
| apply | GET /apply | Apply page |
| sendContact | POST /contact | Submit contact form |

### ApplicationController
Handles public programme applications.

**Location:** `app/Http/Controllers/ApplicationController.php`

| Method | Route | Description |
|--------|------|-------------|
| store | POST /apply | Submit new application |

### PublicShortCourseController
Handles public short course pages.

**Location:** `app/Http/Controllers/PublicShortCourseController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /short-courses | List short courses |
| apply | GET /short-courses/{short_course}/apply | Short course apply form |
| store | POST /short-courses/{short_course}/apply | Submit short course application |

### ContactController
Handles contact form submissions.

**Location:** `app/Http/Controllers/ContactController.php`

---

## Hive Dashboard

### DashboardController
Main dashboard page.

**Location:** `app/Http/Controllers/Hive/DashboardController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/dashboard | Main dashboard |

---

## People Management

### UserController
User management (admin).

**Location:** `app/Http/Controllers/Hive/UserController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/users | User listing |
| create | GET /hive/users/create | Create user form |
| store | POST /hive/users | Create user |
| show | GET /hive/users/{user} | View user |
| edit | GET /hive/users/{user}/edit | Edit user form |
| update | PUT/PATCH /hive/users/{user} | Update user |
| destroy | DELETE /hive/users/{user} | Delete user |

### StudentController
Student management.

**Location:** `app/Http/Controllers/Hive/StudentController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/students | Student listing |
| create | GET /hive/students/create | Create student form |
| store | POST /hive/students | Create student |
| show | GET /hive/students/{student} | View student |
| edit | GET /hive/students/{student}/edit | Edit student form |
| update | PUT/PATCH /hive/students/{student} | Update student |
| destroy | DELETE /hive/students/{student} | Delete student |

### StaffController
Staff management.

**Location:** `app/Http/Controllers/Hive/StaffController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/staff | Staff listing |
| create | GET /hive/staff/create | Create staff form |
| store | POST /hive/staff | Create staff |
| show | GET /hive/staff/{staff} | View staff |
| edit | GET /hive/staff/{staff}/edit | Edit staff form |
| update | PUT/PATCH /hive/staff/{staff} | Update staff |
| destroy | DELETE /hive/staff/{staff} | Delete staff |

### UserApprovalController
User approval workflow.

**Location:** `app/Http/Controllers/Hive/Admin/UserApprovalController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/admin/approve-users | Pending approvals |
| approve | POST /hive/admin/approve-users/{user} | Approve user |

### ImportUsersController
Bulk user import.

**Location:** `app/Http/Controllers/Hive/Admin/ImportUsersController.php`

| Method | Route | Description |
|--------|------|-------------|
| show | GET /hive/admin/import-users | Import form |
| import | POST /hive/admin/import-users | Process import |

---

## Academic Structure

### DepartmentController
Department management.

**Location:** `app/Http/Controllers/Hive/DepartmentController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/departments | Department listing |
| create | GET /hive/departments/create | Create form |
| store | POST /hive/departments | Create department |
| show | GET /hive/departments/{department} | View department |
| edit | GET /hive/departments/{department}/edit | Edit form |
| update | PUT/PATCH /hive/departments/{department} | Update department |
| destroy | DELETE /hive/departments/{department} | Delete department |

### ProgrammeController
Programme management.

**Location:** `app/Http/Controllers/Hive/ProgrammeController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/programmes | Programme listing |
| create | GET /hive/programmes/create | Create form |
| store | POST /hive/programmes | Create programme |
| show | GET /hive/programmes/{programme} | View programme |
| edit | GET /hive/programmes/{programme}/edit | Edit form |
| update | PUT/PATCH /hive/programmes/{programme} | Update programme |
| destroy | DELETE /hive/programmes/{programme} | Delete programme |

### ModuleController
Module/course management.

**Location:** `app/Http/Controllers/Hive/ModuleController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/modules | Module listing |
| create | GET /hive/modules/create | Create form |
| store | POST /hive/modules | Create module |
| edit | GET /hive/modules/{module}/edit | Edit form |
| update | PUT/PATCH /hive/modules/{module} | Update module |
| destroy | DELETE /hive/modules/{module} | Delete module |
| storeProgramme | POST /hive/programmes | Quick programme create |

### CohortController
Cohort management.

**Location:** `app/Http/Controllers/Hive/CohortController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/cohorts | Cohort listing |
| create | GET /hive/cohorts/create | Create form |
| store | POST /hive/cohorts | Create cohort |
| show | GET /hive/cohorts/{cohort} | View cohort |
| edit | GET /hive/cohorts/{cohort}/edit | Edit form |
| update | PUT/PATCH /hive/cohorts/{cohort} | Update cohort |
| destroy | DELETE /hive/cohorts/{cohort} | Delete cohort |

### AcademicYearController
Academic year management.

**Location:** `app/Http/Controllers/Hive/AcademicYearController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/academic-years | Listing |
| create | GET /hive/academic-years/create | Create form |
| store | POST /hive/academic-years | Create |
| edit | GET /hive/academic-years/{academic_year}/edit | Edit form |
| update | PUT/PATCH /hive/academic-years/{academic_year} | Update |
| destroy | DELETE /hive/academic-years/{academic_year} | Delete |

### ShortCourseController
Short course management.

**Location:** `app/Http/Controllers/Hive/ShortCourseController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/short-courses | Listing |
| create | GET /hive/short-courses/create | Create form |
| store | POST /hive/short-courses | Create |
| edit | GET /hive/short-courses/{short_course}/edit | Edit form |
| update | PUT/PATCH /hive/short-courses/{short_course} | Update |
| destroy | DELETE /hive/short-courses/{short_course} | Delete |

### ShortCourseApplicationController
Short course application management.

**Location:** `app/Http/Controllers/Hive/ShortCourseApplicationController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/short-course-applications | Applications |
| review | POST /hive/short-course-applications/{application}/review | Review application |

---

## Student Management

### ApplicationController (Hive)
Student application management.

**Location:** `app/Http/Controllers/Hive/ApplicationController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/applications | Application listing |
| create | GET /hive/applications/create | Create form |
| store | POST /hive/applications | Create application |
| show | GET /hive/applications/{application} | View application |
| edit | GET /hive/applications/{application}/edit | Edit form |
| update | PUT/PATCH /hive/applications/{application} | Update application |
| completeRegistration | POST /hive/applications/{application}/complete-registration | Complete registration |

### RegistrationController
Student registration.

**Location:** `app/Http/Controllers/Hive/RegistrationController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/registration | Registration form |
| store | POST /hive/registration | Submit registration |
| update | PATCH /hive/registration/{application} | Update registration |
| downloadProof | GET /hive/registration/proof | Download proof PDF |

### EnrollmentController (Hive)
Module enrollment.

**Location:** `app/Http/Controllers/Hive/EnrollmentController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/enrollment | My enrollments |
| store | POST /hive/enrollment | Enroll in module |
| destroy | DELETE /hive/enrollment/{module} | Drop module |

### TranscriptController
Transcript generation.

**Location:** `app/Http/Controllers/Hive/TranscriptController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/transcript | Transcript view |
| show | GET /hive/transcript/{student}/download | Download transcript PDF |

### GradeController
Student grading.

**Location:** `app/Http/Controllers/Hive/GradeController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/grades | Grade listing |
| store | POST /hive/grades | Create grade |
| update | PUT/PATCH /hive/grades/{grade} | Update grade |
| destroy | DELETE /hive/grades/{grade} | Delete grade |

### WaitlistController
Programme waitlist.

**Location:** `app/Http/Controllers/Hive/WaitlistController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/waitlist | View waitlist |
| join | POST /hive/waitlist | Join waitlist |
| leave | DELETE /hive/waitlist/{waitlist} | Leave waitlist |

---

## Assessments

### SubmissionController
Assignment submissions.

**Location:** `app/Http/Controllers/Hive/SubmissionController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/submissions | Submission listing |
| store | POST /hive/submissions | Submit assignment |
| update | PUT/PATCH /hive/submissions/{submission} | Update submission |
| destroy | DELETE /hive/submissions/{submission} | Delete submission |

### GradableController
Assessment management.

**Location:** `app/Http/Controllers/Hive/GradableController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/gradables | Assessment listing |
| create | GET /hive/gradables/create | Create form |
| store | POST /hive/gradables | Create assessment |
| show | GET /hive/gradables/{gradable} | View assessment |
| edit | GET /hive/gradables/{gradable}/edit | Edit form |
| update | PUT/PATCH /hive/gradables/{gradable} | Update assessment |
| destroy | DELETE /hive/gradables/{gradable} | Delete assessment |

---

## Attendance

### AttendanceController
Attendance tracking.

**Location:** `app/Http/Controllers/Hive/AttendanceController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/attendance | Attendance listing |
| scan | GET /hive/attendance/scan | QR scan page |
| checkin | POST /hive/attendance/checkin | QR check-in |

---

## User Profile

### ProfileController
User profile management.

**Location:** `app/Http/Controllers/Hive/ProfileController.php`

| Method | Route | Description |
|--------|------|-------------|
| edit | GET /hive/profile | Edit profile form |
| update | POST /hive/profile | Update profile |

### StudentIdController
Student ID card generation.

**Location:** `app/Http/Controllers/Hive/StudentIdController.php`

| Method | Route | Description |
|--------|------|-------------|
| show | GET /hive/student-id | View ID card |

### SearchController
Global search.

**Location:** `app/Http/Controllers/Hive/SearchController.php`

| Method | Route | Description |
|--------|------|-------------|
| __invoke | GET /hive/search | Search results |

---

## Announcements

### AnnouncementController
Announcement management.

**Location:** `app/Http/Controllers/Hive/AnnouncementController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/announcements | Listing |
| create | GET /hive/announcements/create | Create form |
| store | POST /hive/announcements | Create |
| show | GET /hive/announcements/{announcement} | View |
| edit | GET /hive/announcements/{announcement}/edit | Edit form |
| update | PUT/PATCH /hive/announcements/{announcement} | Update |
| destroy | DELETE /hive/announcements/{announcement} | Delete |
| downloadAttachment | GET /hive/announcements/{announcement}/attachments/{attachment}/download | Download file |

---

## Documents

### DocumentController
Document management.

**Location:** `app/Http/Controllers/Hive/DocumentController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/documents | Document listing |
| create | GET /hive/documents/create | Upload form |
| store | POST /hive/documents | Upload document |
| show | GET /hive/documents/{document} | View document |
| edit | GET /hive/documents/{document}/edit | Edit form |
| update | PUT/PATCH /hive/documents/{document} | Update |
| destroy | DELETE /hive/documents/{document} | Delete |
| moduleSelect | GET /hive/documents/modules | Module selector |
| addVersion | POST /hive/documents/{document}/versions | Add version |
| download | GET /hive/document-versions/{version}/download | Download version |
| acknowledge | POST /hive/documents/{document}/acknowledge | Acknowledge document |
| acknowledgements | GET /hive/documents/{document}/acknowledgements | View acknowledgements |

---

## Notifications

### NotificationController
In-app notifications.

**Location:** `app/Http/Controllers/Hive/NotificationController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/notifications | Notification listing |
| markRead | POST /hive/notifications/{notification}/read | Mark as read |
| markAllRead | POST /hive/notifications/read-all | Mark all as read |

---

## Events & Calendar

### EventController
Event management.

**Location:** `app/Http/Controllers/Hive/EventController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/events | Event listing |
| create | GET /hive/events/create | Create form |
| store | POST /hive/events | Create event |
| show | GET /hive/events/{event} | View event |
| edit | GET /hive/events/{event}/edit | Edit form |
| update | PUT/PATCH /hive/events/{event} | Update event |
| destroy | DELETE /hive/events/{event} | Delete event |
| rsvp | POST /hive/events/{event}/rsvp | RSVP to event |
| exportICal | GET /hive/events/{event}/ical | Export calendar |
| qrCode | GET /hive/events/{event}/qrcode | Generate QR code |

---

## Chat & Messaging

### ChatController
Chat/messaging system.

**Location:** `app/Http/Controllers/Hive/ChatController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/chat | Chat listing |
| showChannel | GET /hive/chat/channel/{channel} | View channel chat |
| showModule | GET /hive/chat/module/{module} | View module chat |

---

## Polls & Surveys

### PollController
Poll management.

**Location:** `app/Http/Controllers/Hive/PollController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/polls | Poll listing |
| store | POST /hive/polls | Create poll |
| destroy | DELETE /hive/polls/{poll} | Delete poll |
| vote | POST /hive/polls/{poll}/vote | Vote in poll |

---

## Achievements

### AchievementController
Achievement/leaderboard management.

**Location:** `app/Http/Controllers/Hive/AchievementController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/achievements | Listing |
| store | POST /hive/achievements | Create |
| destroy | DELETE /hive/achievements/{achievement} | Delete |

---

## Leave Management

### LeaveRequestController
Staff leave requests.

**Location:** `app/Http/Controllers/Hive/LeaveRequestController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/leave-requests | Leave listing |
| create | GET /hive/leave-requests/create | Request form |
| store | POST /hive/leave-requests | Submit request |
| update | PUT/PATCH /hive/leave-requests/{leave} | Update request |
| destroy | DELETE /hive/leave-requests/{leave} | Cancel request |

---

## Payslips

### PayslipController
Staff payslip management.

**Location:** `app/Http/Controllers/Hive/PayslipController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/payslips | Payslip listing |
| create | GET /hive/payslips/create | Create form |
| store | POST /hive/payslips | Create payslip |
| edit | GET /hive/payslips/{payslip}/edit | Edit form |
| update | PUT/PATCH /hive/payslips/{payslip} | Update payslip |
| destroy | DELETE /hive/payslips/{payslip} | Delete payslip |
| generate | POST /hive/payslips/generate | Generate single payslip |
| generateBatch | POST /hive/payslips/generate-batch | Generate batch |
| download | GET /hive/payslips/{payslip}/download | Download PDF |

---

## Library

### LibraryController
Library book management.

**Location:** `app/Http/Controllers/Hive/LibraryController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/library | Book listing |
| create | GET /hive/library/create | Add book form |
| store | POST /hive/library | Add book |
| show | GET /hive/library/{book} | View book |
| edit | GET /hive/library/{book}/edit | Edit form |
| update | PUT/PATCH /hive/library/{book} | Update book |
| destroy | DELETE /hive/library/{book} | Delete book |
| loan | POST /hive/library/{book}/loan | Issue book |
| return | POST /hive/library/{book}/return | Return book |
| reserve | POST /hive/library/{book}/reserve | Reserve book |

---

## Bursar / Finance

### InvoiceController
Invoice management.

**Location:** `app/Http/Controllers/Hive/Bursar/InvoiceController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/bursar/invoices | Invoice listing |
| store | POST /hive/bursar/invoices | Create invoice |
| generate | GET /hive/bursar/invoices/generate | Generate invoice form |
| show | GET /hive/bursar/invoices/{invoice} | View invoice |
| update | PUT/PATCH /hive/bursar/invoices/{invoice} | Update invoice |
| destroy | DELETE /hive/bursar/invoices/{invoice} | Delete invoice |

### PaymentController
Payment management.

**Location:** `app/Http/Controllers/Hive/Bursar/PaymentController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/bursar/payments | Payment listing |
| store | POST /hive/bursar/payments | Create payment |
| show | GET /hive/bursar/payments/{payment} | View payment |
| update | PUT/PATCH /hive/bursar/payments/{payment} | Update payment |
| destroy | DELETE /hive/bursar/payments/{payment} | Delete payment |
| verify | PATCH /hive/bursar/payments/{payment}/verify | Verify payment |

### ExpenseController
Expense management.

**Location:** `app/Http/Controllers/Hive/Bursar/ExpenseController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/bursar/expenses | Expense listing |
| create | GET /hive/bursar/expenses/create | Create form |
| store | POST /hive/bursar/expenses | Create expense |
| show | GET /hive/bursar/expenses/{expense} | View expense |
| update | PUT/PATCH /hive/bursar/expenses/{expense} | Update expense |
| destroy | DELETE /hive/bursar/expenses/{expense} | Delete expense |
| approve | PATCH /hive/bursar/expenses/{expense}/approve | Approve |
| reject | PATCH /hive/bursar/expenses/{expense}/reject | Reject |
| markPaid | PATCH /hive/bursar/expenses/{expense}/mark-paid | Mark as paid |
| categories | GET /hive/bursar/expenses/categories | Category listing |
| storeCategory | POST /hive/bursar/expenses/categories | Create category |
| updateCategory | PATCH /hive/bursar/expenses/categories/{category} | Update category |
| destroyCategory | DELETE /hive/bursar/expenses/categories/{category} | Delete category |

### BudgetController
Budget management.

**Location:** `app/Http/Controllers/Hive/Bursar/BudgetController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/bursar/budgets | Budget listing |
| store | POST /hive/bursar/budgets | Create budget |
| show | GET /hive/bursar/budgets/{budget} | View budget |
| update | PUT/PATCH /hive/bursar/budgets/{budget} | Update budget |
| destroy | DELETE /hive/bursar/budgets/{budget} | Delete budget |
| activate | PATCH /hive/bursar/budgets/{budget}/activate | Activate |
| close | PATCH /hive/bursar/budgets/{budget}/close | Close |

### FinancialReportController
Financial reporting.

**Location:** `app/Http/Controllers/Hive/Bursar/FinancialReportController.php`

| Method | Route | Description |
|--------|------|-------------|
| dashboard | GET /hive/bursar/reports/dashboard | Finance dashboard |
| income | GET /hive/bursar/reports/income | Income report |
| expenses | GET /hive/bursar/reports/expenses | Expense report |
| ageAnalysis | GET /hive/bursar/reports/age-analysis | Age analysis |
| studentLedger | GET /hive/bursar/reports/student/{user} | Student ledger |

---

## Facility Management

### KeyController
Physical key management.

**Location:** `app/Http/Controllers/Hive/KeyController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/keys | Key listing |
| store | POST /hive/keys | Add key |
| issue | POST /hive/keys/{key}/issue | Issue key |
| return | POST /hive/keys/{key}/return | Return key |
| reportLost | POST /hive/keys/{key}/report-lost | Report lost |

### VisitorLogController
Visitor sign-in management.

**Location:** `app/Http/Controllers/Hive/VisitorLogController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/visitor-logs | Visitor log listing |
| store | POST /hive/visitor-logs | Log visitor |
| checkOut | POST /hive/visitor-logs/{log}/checkout | Check out visitor |

### SupplierController
Supplier management.

**Location:** `app/Http/Controllers/Hive/SupplierController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/suppliers | Supplier listing |
| store | POST /hive/suppliers | Create supplier |
| update | PUT/PATCH /hive/suppliers/{supplier} | Update supplier |
| destroy | DELETE /hive/suppliers/{supplier} | Delete supplier |

### UniformRequestController
Uniform requests.

**Location:** `app/Http/Controllers/Hive/UniformRequestController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/uniform-requests | Request listing |
| store | POST /hive/uniform-requests | Submit request |
| review | POST /hive/uniform-requests/{request}/review | Review request |

### ProgrammeSoughtController
Programme interest management.

**Location:** `app/Http/Controllers/Hive/ProgrammeSoughtController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /hive/programme-soughts | Interest listing |
| store | POST /hive/programme-soughts | Record interest |
| update | PUT/PATCH /hive/programme-soughts/{sought} | Update |
| destroy | DELETE /hive/programme-soughts/{sought} | Delete |

---

## API Controllers

### ChatMessageController
API chat messages.

**Location:** `app/Http/Controllers/Api/ChatMessageController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /api/modules/{module}/messages | Module messages |
| store | POST /api/modules/{module}/messages | Send message |
| indexChannel | GET /api/channels/{channel}/messages | Channel messages |
| storeChannel | POST /api/channels/{channel}/messages | Send channel message |

### TaskController
API student tasks.

**Location:** `app/Http/Controllers/Api/TaskController.php`

| Method | Route | Description |
|--------|------|-------------|
| index | GET /api/tasks | Task listing |
| store | POST /api/tasks | Create task |
| update | PATCH /api/tasks/{task} | Update task |
| destroy | DELETE /api/tasks/{task} | Delete task |

---

## Controller Patterns

All Hive controllers follow these conventions:

1. **Resourceful routing** where applicable
2. **Authorization** via middleware (role-based)
3. **Form requests** for validation
4. **ertia responses** with shared data
5. **Flash messages** for feedback

### Middleware Used

| Middleware | Purpose |
|-----------|---------|
| auth:sanctum | API authentication |
| role:role1\|role2 | Role-based access |
| registered |Student registration check |

### Common Methods

- `index()` - List all resources
- `create()` - Show create form
- `store()` - Store new resource
- `show()` - Show single resource
- `edit()` - Show edit form
- `update()` - Update resource
- `destroy()` - Delete resource