# API Documentation

## Base URL

```
https://hbci.ac.ls/api
```

## Authentication

All API requests require authentication via Laravel Sanctum token.

```
Authorization: Bearer {token}
```

### Obtaining a Token

```bash
POST /api/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}

Response:
{
    "token": "1|xxxxxxxxxxxx",
    "user": { ... }
}
```

## Response Format

### Success Response

```json
{
    "data": { ... }
}
```

### Error Response

```json
{
    "message": "Unauthorized",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 204 | No Content |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

## Endpoints

### Students

#### List Students

```http
GET /api/students
```

**Query Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by name, email, or student number |
| `programme_id` | integer | Filter by programme |
| `status` | string | Filter by status (active, graduated, suspended) |
| `page` | integer | Page number (default: 1) |
| `per_page` | integer | Items per page (default: 15) |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "first_name": "John",
            "last_name": "Doe",
            "email": "john@example.com",
            "user_number": "S2025001",
            "programme": {
                "id": 1,
                "name": "Culinary Arts"
            },
            "enrollment_date": "2025-01-15"
        }
    ],
    "links": { ... },
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

#### Get Student

```http
GET /api/students/{id}
```

**Response**

```json
{
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "user_number": "S2025001",
        "phone": "+1234567890",
        "date_of_birth": "2000-01-15",
        "gender": "male",
        "address": "123 Main St",
        "programme": {
            "id": 1,
            "name": "Culinary Arts",
            "code": "CUL-101"
        },
        "enrollments": [
            {
                "id": 1,
                "module": {
                    "id": 5,
                    "name": "Knife Skills",
                    "code": "CUL-101-01"
                },
                "enrolled_at": "2025-01-15"
            }
        ]
    }
}
```

#### Create Student

```http
POST /api/students
Content-Type: application/json

{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "date_of_birth": "2000-01-15",
    "gender": "male",
    "address": "123 Main St",
    "programme_id": 1,
    "department_id": 2
}
```

**Response**

```json
{
    "data": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "message": "Student created successfully"
    }
}
```

#### Update Student

```http
PUT /api/students/{id}
Content-Type: application/json

{
    "phone": "+1987654320",
    "address": "456 Oak Ave"
}
```

#### Delete Student

```http
DELETE /api/students/{id}
```

**Response**: `204 No Content`

### Staff

#### List Staff

```http
GET /api/staff
```

**Query Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search by name, email, or employee number |
| `department_id` | integer | Filter by department |
| `role` | string | Filter by role |
| `page` | integer | Page number |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "first_name": "Jane",
            "last_name": "Smith",
            "email": "jane@hbci.ac.ls",
            "user_number": "E2025001",
            "department": {
                "id": 1,
                "name": "Culinary Arts"
            },
            "position": "Head Instructor",
            "roles": ["instructor", "supervisor"]
        }
    ]
}
```

### Invoices

#### List Invoices

```http
GET /api/invoices
```

**Query Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status |
| `academic_year` | string | Filter by academic year |
| `student_id` | integer | Filter by student |
| `date_from` | date | Filter by due date from |
| `date_to` | date | Filter by due date to |
| `page` | integer | Page number |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "invoice_number": "INV-2025-0001",
            "student": {
                "id": 1,
                "name": "John Doe",
                "user_number": "S2025001"
            },
            "programme": {
                "id": 1,
                "name": "Culinary Arts"
            },
            "amount": 15000.00,
            "amount_paid": 5000.00,
            "balance": 10000.00,
            "status": "pending",
            "due_date": "2025-02-01",
            "academic_year": "2025"
        }
    ]
}
```

#### Create Invoice

```http
POST /api/invoices
Content-Type: application/json

{
    "student_id": 1,
    "programme_id": 1,
    "type": "tuition",
    "amount": 15000.00,
    "due_date": "2025-02-01",
    "academic_year": "2025",
    "notes": "First semester tuition"
}
```

#### Record Payment

```http
POST /api/invoices/{id}/payments
Content-Type: application/json

{
    "amount": 5000.00,
    "payment_method": "bank_transfer",
    "reference": "TXN-123456",
    "paid_at": "2025-01-20"
}
```

### Grades

#### List Grades

```http
GET /api/grades
```

**Query Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `student_id` | integer | Filter by student |
| `module_id` | integer | Filter by module |
| `academic_year` | string | Filter by academic year |
| `page` | integer | Page number |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "student": {
                "id": 1,
                "name": "John Doe"
            },
            "module": {
                "id": 5,
                "name": "Knife Skills",
                "code": "CUL-101-01"
            },
            "grade": "A",
            "score": 92,
            "graded_at": "2025-06-15",
            "graded_by": {
                "id": 2,
                "name": "Jane Smith"
            }
        }
    ]
}
```

### Attendance

#### List Attendance

```http
GET /api/attendance
```

**Query Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `student_id` | integer | Filter by student |
| `module_id` | integer | Filter by module |
| `date_from` | date | Filter by date from |
| `date_to` | date | Filter by date to |
| `status` | string | Filter by status (present, absent, late) |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "student": {
                "id": 1,
                "name": "John Doe"
            },
            "module": {
                "id": 5,
                "name": "Knife Skills"
            },
            "date": "2025-01-15",
            "status": "present",
            "notes": null
        }
    ]
}
```

### Documents

#### List Documents

```http
GET /api/documents
```

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "title": "Student Handbook",
            "type": "policy",
            "version": "2.1",
            "published_at": "2025-01-01",
            "acknowledgement_required": true,
            "download_url": "https://hbci.ac.ls/storage/documents/student-handbook.pdf"
        }
    ]
}
```

### Events

#### List Events

```http
GET /api/events
```

**Query Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `date_from` | date | Filter by date from |
| `date_to` | date | Filter by date to |
| `type` | string | Filter by type |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "title": "Open Day",
            "type": "open_day",
            "start_date": "2025-02-01T10:00:00Z",
            "end_date": "2025-02-01T16:00:00Z",
            "location": "Main Hall",
            "description": "Annual open day for prospective students",
            "rsvp_required": true,
            "rsvp_count": 45
        }
    ]
}
```

### Chat Messages

#### List Messages

```http
GET /api/chat/messages
```

**Query Parameters**

| Parameter | Type | Description |
|-----------|------|-------------|
| `channel_id` | integer | Filter by channel |
| `since` | timestamp | Messages since this timestamp |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "channel_id": 1,
            "user": {
                "id": 1,
                "name": "John Doe"
            },
            "message": "Hello everyone!",
            "created_at": "2025-01-15T10:30:00Z"
        }
    ]
}
```

#### Send Message

```http
POST /api/chat/messages
Content-Type: application/json

{
    "channel_id": 1,
    "message": "Hello everyone!"
}
```

## Rate Limiting

API requests are limited to:

- **Authenticated users**: 1000 requests per minute
- **Unauthenticated users**: 60 requests per minute

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
```

## Pagination

All list endpoints support pagination:

```
GET /api/students?page=1&per_page=15
```

Response includes pagination metadata:

```json
{
    "data": [...],
    "links": {
        "first": "http://...",
        "last": "http://...",
        "prev": null,
        "next": "http://..."
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 5,
        "path": "http://...",
        "per_page": 15,
        "to": 15,
        "total": 75
    }
}
```

## Filtering

List endpoints support filtering via query parameters:

```
GET /api/invoices?status=pending&academic_year=2025&date_from=2025-01-01
```

## Sorting

```
GET /api/students?sort=first_name&direction=asc
GET /api/students?sort=-created_at  (descending)
```

## Includes

Some endpoints support eager loading via `include` parameter:

```
GET /api/students/1?include=programme,enrollments.module
```

## OpenAPI Specification

```yaml
openapi: 3.0.3
info:
  title: The Hive System API
  description: API for The Hive Educational Management System
  version: 1.0.0
  contact:
    name: API Support
    email: api@hbci.ac.ls

servers:
  - url: https://hbci.ac.ls/api
    description: Production server

components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: Sanctum Token

  schemas:
    Student:
      type: object
      required:
        - first_name
        - last_name
        - email
      properties:
        id:
          type: integer
        first_name:
          type: string
        last_name:
          type: string
        email:
          type: string
          format: email
        user_number:
          type: string
        phone:
          type: string
        date_of_birth:
          type: string
          format: date
        gender:
          type: string
          enum: [male, female, other]
        address:
          type: string
        programme_id:
          type: integer
        created_at:
          type: string
          format: datetime

    Invoice:
      type: object
      required:
        - student_id
        - amount
        - due_date
      properties:
        id:
          type: integer
        invoice_number:
          type: string
        student_id:
          type: integer
        programme_id:
          type: integer
        type:
          type: string
          enum: [registration, tuition, uniform, tools, resource, examination, other]
        amount:
          type: number
          format: float
        amount_paid:
          type: number
          format: float
        balance:
          type: number
          format: float
        status:
          type: string
          enum: [pending, partial, paid, overdue, cancelled]
        due_date:
          type: string
          format: date
        academic_year:
          type: string
        notes:
          type: string

    Grade:
      type: object
      properties:
        id:
          type: integer
        student_id:
          type: integer
        module_id:
          type: integer
        grade:
          type: string
        score:
          type: number
          format: float
        graded_at:
          type: string
          format: datetime

paths:
  /students:
    get:
      tags:
        - Students
      summary: List students
      security:
        - BearerAuth: []
      parameters:
        - name: search
          in: query
          schema:
            type: string
        - name: programme_id
          in: query
          schema:
            type: integer
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          schema:
            type: integer
            default: 15
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Student'
                  meta:
                    $ref: '#/components/schemas/PaginationMeta'

    post:
      tags:
        - Students
      summary: Create student
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Student'
      responses:
        '201':
          description: Student created
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    $ref: '#/components/schemas/Student'

  /students/{id}:
    get:
      tags:
        - Students
      summary: Get student
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  data:
                    $ref: '#/components/schemas/Student'
        '404':
          description: Student not found

    put:
      tags:
        - Students
      summary: Update student
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              properties:
                first_name:
                  type: string
                last_name:
                  type: string
                phone:
                  type: string
                address:
                  type: string
      responses:
        '200':
          description: Student updated

    delete:
      tags:
        - Students
      summary: Delete student
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      responses:
        '204':
          description: Student deleted

  /invoices:
    get:
      tags:
        - Finance
      summary: List invoices
      security:
        - BearerAuth: []
      parameters:
        - name: status
          in: query
          schema:
            type: string
        - name: student_id
          in: query
          schema:
            type: integer
        - name: date_from
          in: query
          schema:
            type: string
            format: date
        - name: date_to
          in: query
          schema:
            type: string
            format: date
      responses:
        '200':
          description: Successful response

    post:
      tags:
        - Finance
      summary: Create invoice
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/Invoice'
      responses:
        '201':
          description: Invoice created

  /invoices/{id}/payments:
    post:
      tags:
        - Finance
      summary: Record payment
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema:
            type: integer
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - amount
              properties:
                amount:
                  type: number
                  format: float
                payment_method:
                  type: string
                  enum: [cash, bank_transfer, mobile_money, cheque]
                reference:
                  type: string
                paid_at:
                  type: string
                  format: date
      responses:
        '201':
          description: Payment recorded
```
