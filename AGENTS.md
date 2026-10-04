# AGENTS.md — KosFly Project Engineering Guide

> **Purpose:** Define how an AI coding agent must understand, plan, modify, and extend the KosFly project.
>
> **Core rule:** **Understand first → analyze sources → plan → implement one small scope → test → review → continue.**
>
> The agent MUST NOT treat the repository as a blank project and MUST NOT begin by implementing the entire system at once.

---

# 1. PROJECT IDENTITY

## Project

**KosFly** — web application for managing a boarding-house / kos operation.

## Current technology

The project is expected to use:

- Laravel 13.x
- PHP 8.3.x
- MySQL
- Laragon for local development
- Laravel Breeze for authentication
- Spatie Laravel Permission for roles/permissions
- Blade for server-rendered views
- Existing frontend HTML/CSS/JavaScript design supplied by the project owner

The exact versions and dependencies present in the repository are always authoritative.

**Do not assume a package, framework, library, or version exists merely because it is listed here. Inspect the repository first.**

---

# 2. PRIMARY OPERATING PRINCIPLE

The agent must work as a **senior engineer joining an existing project**, not as an autonomous greenfield developer.

Before modifying code, the agent MUST understand:

1. Repository structure
2. Existing Laravel architecture
3. Existing authentication
4. Existing authorization / Spatie setup
5. Existing database migrations
6. Existing models
7. Existing relationships
8. Existing seeders / factories
9. Existing controllers
10. Existing routes
11. Existing Blade views
12. Existing CSS and JavaScript
13. Existing UI/design system
14. Existing implementation conventions
15. Current application state
16. Known incomplete or prototype features

The agent must preserve working functionality unless a requested change explicitly requires modifying it.

---

# 3. SOURCE-OF-TRUTH PRIORITY

When multiple sources provide information, use this priority:

1. **Actual repository code**
2. **Current database migrations/schema**
3. **Provided ERD**
4. **Existing working application behavior**
5. **Existing Blade/UI implementation**
6. **Project documentation**
7. **Task/request from the project owner**
8. General Laravel conventions

Do NOT silently replace project-specific decisions with generic Laravel conventions.

If the ERD and code disagree, **do not silently "fix" the project**.

Instead:

- identify the mismatch,
- explain the consequences,
- state the safest interpretation,
- ask only when a decision is genuinely ambiguous.

If a task is safe to continue without clarification, prefer the smallest change that preserves existing behavior.

---

# 4. REQUIRED FIRST PHASE — UNDERSTAND THE PROJECT

When first entering the project, the agent MUST NOT immediately code.

First inspect the repository.

At minimum inspect:

```text
composer.json
package.json
.env.example
routes/
app/
database/
resources/
public/
config/
```

Also inspect project-specific documentation and any supplied design files.

The agent should identify:

```text
Application architecture
Authentication
Authorization
Database structure
Models
Relationships
Routes
Controllers
Views
Assets
JavaScript
CSS
Existing CRUD
Incomplete modules
Prototype modules
```

The initial goal is to create a mental model of the project.

---

# 5. ERD ANALYSIS REQUIREMENT

The project owner may provide an ERD inside the project.

The agent MUST analyze the ERD before implementing database-dependent features.

Extract at least:

- Tables
- Primary keys
- Foreign keys
- Unique constraints
- Nullable fields
- Data types
- Many-to-one relationships
- One-to-many relationships
- Many-to-many relationships
- Pivot tables
- Cascading behavior
- Domain boundaries

The agent must translate the ERD into a relationship map.

Example:

```text
Room
 ├── hasMany Lease
 ├── hasMany MaintenanceRequest
 └── belongsToMany Facility
                │
                ▼
         room_facilities
```

Do NOT infer additional tables or fields unless clearly necessary and explicitly justified.

---

# 6. KNOWN KOSFLY DOMAIN MODEL

The current project context includes these entities.

Treat the repository and ERD as authoritative if they differ.

## Users

```text
users
- id
- name
- email
- password
- phone
- created_at
- updated_at
```

## Tenants

```text
tenants
- id
- user_id (unique, fk -> users.id, cascade)
- ktp_number (unique)
- emergency_name (nullable)
- emergency_contact (nullable)
- job (nullable)
- created_at
- updated_at
```

Relationship:

```text
Tenant belongsTo User
User hasOne Tenant
```

## Rooms

Actual fields (verified against `database/migrations/2026_07_30_015727_create_rooms_table.php`):

```text
rooms
- id
- room_number (unique)
- floor (string, nullable)
- price (decimal 12,2)          <- ERD lama menyebut `price_per_month`
- status (enum: available|occupied|maintenance, default available)
- is_active (boolean, default true)
- description (text, nullable)
- image (string, nullable)
- created_at
- updated_at
```

Relationships:

```text
Room hasMany Lease
Room hasMany MaintenanceRequest
Room belongsToMany Facility
```

## Facilities

```text
facilities
- id
- name
- description
- created_at
- updated_at
```

Relationship:

```text
Facility belongsToMany Room
```

## Room Facilities

Pivot:

```text
room_facilities
- id
- room_id
- facility_id
- created_at
- updated_at
```

Important:

The project uses:

```text
room_facilities
```

Do NOT automatically change it to Laravel's guessed:

```text
facility_room
```

If the pivot is named `room_facilities`, relationships must explicitly reference it when Laravel convention would not resolve it correctly.

## Leases

```text
leases
- id
- tenant_id (nullable, fk -> tenants.id, cascade)
- room_id (fk -> rooms.id, cascade)
- start_date
- end_date (nullable)
- checkout_date (nullable)
- m_price (decimal 12,2)       <- ERD lama menyebut `monthly_price`
- deposit_amount (decimal 12,2, default 0)
- deposit_deduction (decimal 12,2, default 0)
- deposit_refunded (decimal 12,2, default 0)
- status (enum: pending|active|completed|cancelled, default active)
- room_condition (nullable)
- note (text, nullable)
- checkout_notes (text, nullable)
- renewal_count (int, default 0)
- last_renewed_at (datetime, nullable)
- created_at
- updated_at
```

## Payments

```text
payments
- id
- lease_id (fk -> leases.id, cascade)
- invoice_number (unique)
- amount (decimal 12,2, default 0)
- billing_period (date)
- due_date (date)
- payment_date (datetime, nullable)
- payment_method (enum: cash|e_wallet|bank_tf|qris, default cash)
- status (enum: paid|pending|unpaid|overdue, default unpaid)
- proof_img (string, nullable)   <- ERD lama menyebut `proof_image`
- verified_by (nullable, fk -> users.id, null on delete)
- notes (text, nullable)
- created_at
- updated_at
```

## Maintenance Requests

```text
maintenance_requests
- id
- room_id
- tenant_id
- title
- description
- image_path
- priority
- status
- cost
- handled_by
- reported_at
- resolved_at
- created_at
- updated_at
```

## Expenses

```text
expenses
- id
- title
- description (nullable)
- amount (decimal 12,2, default 0)
- expense_date (date, nullable)
- user_id (fk -> users.id, cascade)   <- ERD lama menyebut `created_by`
- created_at
- updated_at
```

---

# 7. EXISTING AUTHENTICATION AND AUTHORIZATION

The project uses:

- Laravel Breeze
- Spatie Laravel Permission

`User` already uses:

```php
use Spatie\Permission\Traits\HasRoles;
```

The agent MUST inspect the actual role and permission setup before adding authorization rules.

Do not invent roles.

Do not invent permissions.

Do not replace Spatie with custom authorization.

Before changing authorization, inspect:

```text
User model
Role/Permission seeders
bootstrap / middleware configuration
Existing route middleware
Existing policies
Existing gates
```

If the project already has a role naming convention, follow it exactly.

## Actual role setup (verified)

- `database/seeders/RoleSeeder.php` creates exactly three roles: **`admin`**, **`owner`**, **`tenant`** (lowercase).
- There are **no permissions, policies, or gates** — authorization is role-based only.
- `App\Http\Middleware\RoleMiddleware` (aliased as `role` in `bootstrap/app.php`) enforces route access.
  Usage: `role:admin` or `role:admin|owner`.
- Registration (`RegisteredUserController`) auto-assigns `tenant`.
- Users with **no role at all** are rejected (403) from every `role:`-guarded route.
- The role **`caretaker`** mentioned in older docs does NOT exist. Do not use it.

---

# 8. EXISTING FRONTEND / DESIGN RULES

The project already contains a visual design system and prototype UI.

The agent MUST inspect and reuse existing:

- layout
- typography
- spacing
- buttons
- cards
- tables
- badges/tags
- inputs
- modals
- navigation
- responsive rules
- CSS variables
- icons
- JavaScript helpers

Do NOT introduce Bootstrap, React, Vue, Livewire, or another frontend system unless it already exists in the project or the project owner explicitly requests it.

Note (verified): the project **already uses Tailwind CSS v3** (via Breeze) and **Alpine.js**.
Tailwind is configured in `tailwind.config.js` and consumed through `@vite`; the KosFly design
system (tokens + components) is inlined in `resources/css/app.css`. Alpine is used by the
Breeze components (`resources/views/components/dropdown.blade.php`, `modal.blade.php`, etc.).
Because they already exist, using them is allowed — but do NOT replace the KosFly design system.

Do not replace the entire design system just to implement one CRUD.

---

# 9. IMPORTANT FRONTEND LEARNING WORKFLOW

The project owner is intentionally learning Laravel by converting frontend prototypes into Blade.

Therefore:

## AI frontend-design tasks

When asked to create UI/design using an external coding agent:

**Prefer HTML/CSS output first.**

The coding agent should NOT automatically write Blade unless explicitly requested.

Preferred flow:

```text
HTML/CSS prototype
        ↓
Project owner converts HTML → Blade
        ↓
Project owner connects routes/forms/data
        ↓
AI reviews/corrects implementation
```

## When working directly in this repository

If the owner is implementing Blade themselves:

- explain the Laravel/Blade concept,
- identify the exact HTML section that needs to change,
- explain the purpose,
- provide focused corrections,
- avoid taking over unrelated parts of the page.

Do not convert an entire frontend file into Blade unless explicitly asked.

---

# 10. NEVER IMPLEMENT THE ENTIRE PROJECT AT ONCE

This is a mandatory rule.

Do NOT receive a broad requirement like:

> "Build all CRUDs"

and then implement everything.

Instead split the work.

Preferred module sequence:

```text
Module
  ↓
Analyze
  ↓
Plan
  ↓
Model / relationship verification
  ↓
Controller
  ↓
Routes
  ↓
One Blade page
  ↓
Test
  ↓
Fix
  ↓
Next page
```

For a CRUD module:

```text
1. READ / INDEX
2. CREATE
3. STORE
4. EDIT
5. UPDATE
6. SHOW / DETAIL
7. DELETE
8. validation/error states
9. authorization
10. related UI improvements
```

Do not implement all modules simultaneously.

---

# 11. CHECKPOINT / STOP RULES

After each meaningful phase, stop and report what changed.

Example:

```text
CHECKPOINT — Facility Index

Completed:
- FacilityController@index
- route
- database query
- index Blade integration

Test:
- GET /facilities
- pagination works

Not yet implemented:
- create
- update
- delete
```

Then continue only with the next clearly defined scope.

If the owner gives a new instruction, prioritize the new instruction over the previous planned next step.

---

# 12. REQUIRED TASK ANALYSIS BEFORE CODING

Before coding any non-trivial task, the agent should briefly identify:

```text
Goal:
What are we trying to change?

Current state:
How does the project work now?

Relevant files:
Which existing files control this behavior?

Data impact:
Which models/tables/relationships are involved?

UI impact:
Which existing views/assets are involved?

Risk:
What could break?

Implementation scope:
What is the smallest safe change?
```

Do not produce a massive plan for a trivial edit.

---

# 13. MINIMAL CHANGE PRINCIPLE

Prefer:

```text
smallest correct change
```

over:

```text
complete rewrite
```

Do not refactor unrelated files while implementing a CRUD.

Do not rename existing project-wide concepts without a direct reason.

Do not introduce abstractions merely for stylistic preference.

Do not rewrite working authentication, dashboard, or unrelated CRUD.

---

# 14. CONTROLLER GUIDELINES

Controllers should:

- validate request input,
- call the appropriate model,
- handle file upload when required,
- handle relationships,
- redirect appropriately,
- return views,
- use clear route names,
- keep business logic understandable.

Prefer validated data:

```php
$validated = $request->validate([...]);
```

Then use:

```php
Model::create($validated);
```

or an explicit array when backend-generated values are needed.

Example:

```php
$room = Room::create([
    'room_number' => $validated['room_number'],
    'floor' => $validated['floor'] ?? null,
    'price' => $validated['price'],
    'status' => $validated['status'],
    'is_active' => true,
    'description' => $validated['description'] ?? null,
    'image' => $imagePath,
]);
```

Use explicit arrays when the backend adds values that do not come directly from the request.

---

# 15. VALIDATION GUIDELINES

Validation must match the database and domain.

Do not invent validation values.

For example, if Room status is restricted by the actual domain, use the actual allowed values.

Be careful with:

```php
unique:table,column
```

Avoid accidental spaces:

```php
// WRONG
'unique:rooms, room_number'

// RIGHT
'unique:rooms,room_number'
```

For update validation, exclude the current model's ID.

Example:

```php
Rule::unique('facilities', 'name')->ignore($facility->id)
```

Prefer `Rule` when it makes update validation clearer.

---

# 16. ELOQUENT RELATIONSHIP GUIDELINES

Always verify:

- related model name,
- foreign key,
- inverse key,
- pivot table,
- pivot foreign keys.

For the current Room ↔ Facility relationship:

```php
// Room
public function facilities()
{
    return $this->belongsToMany(
        Facility::class,
        'room_facilities',
        'room_id',
        'facility_id'
    );
}
```

```php
// Facility
public function rooms()
{
    return $this->belongsToMany(
        Room::class,
        'room_facilities',
        'facility_id',
        'room_id'
    );
}
```

Do not assume Laravel's default pivot name if the database uses a project-specific name.

---

# 17. FILE UPLOAD GUIDELINES

Current Room uses an `image` field.

When handling Room images:

1. Validate the file.
2. Store it using the configured filesystem.
3. Save the resulting path in the database.
4. Display through Laravel's storage link.
5. When replacing, remove the previous file when appropriate.
6. When explicitly removing, delete the old file and clear the database field.
7. Do not store base64 image data in the database.

Typical storage pattern:

```php
$imagePath = $request->file('image')->store('room', 'public');
```

Typical display pattern:

```blade
<img src="{{ asset('storage/' . $room->image) }}">
```

Storage is expected to use:

```bash
php artisan storage:link
```

Do not delete files blindly.

Always distinguish between:

```text
keep existing image
replace image
remove existing image
no image
```

---

# 18. BLADE GUIDELINES

Use Blade naturally and simply.

Common patterns:

```blade
@foreach(...)
@endforeach
```

```blade
@if(...)
@endif
```

```blade
{{ $model->field }}
```

```blade
@csrf
```

```blade
@method('PUT')
```

```blade
@error('field')
@enderror
```

```blade
{{ old('field', $model->field) }}
```

Do not over-engineer Blade templates.

Keep the HTML structure recognizable to the project owner.

---

# 19. ROUTE GUIDELINES

Use Laravel resource routes for standard CRUD when appropriate:

```php
Route::resource('rooms', RoomController::class);
Route::resource('facilities', FacilityController::class);
```

Do not create duplicate index routes such as:

```php
Route::get('/rooms', ...);
Route::resource('rooms', RoomController::class);
```

when the resource route already supplies `rooms.index`.

Always check:

```bash
php artisan route:list
```

after meaningful route changes.

---

# 20. VIEW NAMING CONVENTION

Prefer:

```text
resources/views/rooms/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php
```

and:

```text
resources/views/facilities/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php
```

Keep naming consistent.

---

# 21. FLASH MESSAGE AND VALIDATION UI

CRUD pages should support:

```blade
@if(session('success'))
    ...
@endif
```

and field errors:

```blade
@error('name')
    ...
@enderror
```

Do not implement duplicate notification systems when the existing KosFly UI already has a toast/alert component.

First inspect the existing component and integrate with it.

---

# 22. DATA DISPLAY RULE

Never use dummy data as a substitute for backend data once a module is connected.

Bad:

```javascript
const rooms = [
    { id: 1, room_number: 'A-01' }
];
```

Good:

```blade
@foreach($rooms as $room)
```

The project may temporarily retain static UI placeholders while a module is under construction, but once backend integration begins, clearly separate prototype placeholders from actual application data.

---

# 23. SEARCH / FILTER / PAGINATION

When a page uses server-side pagination, do not silently switch to client-side fake pagination.

Prefer Laravel query parameters when server-side filtering is appropriate.

Example concepts:

```text
?q=
?status=
?floor=
?is_active=
```

Preserve filters during pagination when needed.

Do not implement a complex query system unless the requirement needs it.

---

# 24. DELETE SAFETY

Before implementing deletion, inspect relationships.

For entities referenced by:

```text
leases
payments
maintenance_requests
room_facilities
```

the agent MUST consider whether deleting the parent is safe.

Do not automatically add cascade behavior or force deletion simply to make the button work.

For destructive actions:

- use confirmation UI,
- respect foreign-key behavior,
- explain any integrity risk,
- prefer safe failure over destructive guessing.

---

# 25. DATABASE CHANGE RULE

Do not modify the database casually.

Before changing a migration:

1. Determine whether it has already been run.
2. Determine whether it is development-only or contains important data.
3. Prefer a new migration for an already-deployed schema.
4. Warn before any destructive reset.

Never run or recommend:

```bash
php artisan migrate:fresh
```

without explicitly stating that it deletes existing database tables/data.

---

# 26. TESTING REQUIREMENT

After each implementation step, test the smallest relevant path.

Examples:

## Room Create

```text
GET /rooms/create
POST /rooms
```

Verify:

- validation,
- database row,
- image path,
- room_facilities rows,
- redirect,
- success message.

## Room Index

Verify:

```text
GET /rooms
```

and confirm:

- data loads,
- relationship loads,
- pagination works,
- no unexpected SQL error.

## Facility

Verify:

```text
GET /facilities
GET /facilities/create
POST /facilities
GET /facilities/{facility}/edit
PUT/PATCH /facilities/{facility}
DELETE /facilities/{facility}
```

Use:

```bash
php artisan route:list
```

and project-appropriate tests or manual browser checks.

---

# 27. ERROR DEBUGGING METHOD

When an error occurs, do NOT make random changes.

Read the error from the top.

Identify:

```text
Error type
SQL query if present
File
Line
Request
Route
Database query
```

Then classify it:

```text
Routing
Validation
Database schema
Eloquent relationship
Mass assignment
Blade
Filesystem
Authorization
Frontend JavaScript
```

Fix the root cause.

Example:

```text
Unknown column ' room_number'
```

means inspect:

```php
unique:rooms, room_number
```

for the accidental leading space before immediately modifying the database.

Example:

```text
Table 'facility_room' doesn't exist
```

means inspect the belongsToMany pivot convention before creating another table.

---

# 28. DO NOT HIDE UNCERTAINTY

When the repository does not provide enough information, say so.

Use statements like:

```text
"The current repository does not show the allowed status values."
```

or:

```text
"The ERD and migration disagree on this column."
```

Do not invent a value just because it is common in another Laravel project.

---

# 29. DOCUMENT PROJECT-SPECIFIC DECISIONS

When a significant architectural decision is discovered, maintain a concise project note where appropriate.

Examples:

```text
- Pivot table is named room_facilities.
- Room stores image path in rooms.image.
- Spatie controls authorization.
- Room status uses the project's defined values.
```

Do not create documentation files for trivial changes.

---

# 30. FRONTEND AGENT BEHAVIOR

When asked to generate a new UI with an external coding agent:

Preferred instruction:

```text
HTML + CSS only.
No Blade.
No Laravel.
No backend.
No API.
No dummy backend implementation.
```

The resulting HTML will then be converted to Blade manually by the project owner.

The AI should make the HTML easy to convert by adding useful comments such as:

```html
<!-- DATA WILL LATER COME FROM BLADE -->
<!-- THIS VALUE WILL LATER BECOME $facility->name -->
```

But it should NOT write the Blade syntax unless explicitly asked.

---

# 31. WORKING WITH EXISTING PROTOTYPE JAVASCRIPT

KosFly may contain prototype JavaScript that uses static objects such as:

```text
demoData
```

When converting a module to Laravel:

- identify the prototype data source,
- identify the rendering functions,
- identify event handlers,
- determine which behavior belongs to backend,
- remove only the prototype data logic that is being replaced,
- preserve reusable UI behavior when useful,
- do not break other pages.

Never rewrite all global JavaScript just because one module is being migrated.

---

# 32. MODULE IMPLEMENTATION ORDER

Unless the project owner specifies another order, prefer:

## Master Data

```text
Facility
Room
Tenant
```

## Transactions / Operations

```text
Lease
Payment
Maintenance Request
Expense
```

The exact order may change depending on dependencies.

For example:

```text
Facility
   ↓
Room
   ↓
Tenant
   ↓
Lease
   ↓
Payment
```

is a reasonable dependency-aware sequence.

---

# 33. COMMUNICATION STYLE

The agent should communicate in a practical engineering style.

For each meaningful task:

```text
1. What I found
2. What it means
3. What I will change now
4. Exact files involved
5. What I am NOT changing
6. Test/checkpoint
```

Keep explanations proportional to the task.

Do not bury important implementation decisions in long philosophical explanations.

---

# 34. MANDATORY FIRST RESPONSE IN A NEW PROJECT

When the agent is first activated on the repository, it should NOT begin by editing.

It should first produce a concise project audit:

```text
PROJECT AUDIT

Architecture:
...

Authentication:
...

Authorization:
...

Database:
...

Models:
...

Relationships:
...

Routes:
...

Views:
...

Frontend:
...

Existing completed modules:
...

Incomplete/prototype modules:
...

Important inconsistencies:
...

Risks:
...

Recommended implementation order:
...
```

Then propose the **first smallest implementation scope**.

Do NOT implement that scope in the same step unless the user explicitly requests immediate implementation.

---

# 35. MANDATORY BEHAVIOR WHEN ERD + DESIGN ARE PROVIDED

When the project owner provides:

```text
ERD
+
existing design/UI
```

the agent must first analyze both.

## ERD analysis should answer

```text
What tables exist?
What are their relationships?
What fields are required?
What fields are optional?
What constraints exist?
What should be handled by Eloquent?
```

## UI analysis should answer

```text
What design system exists?
What components are reusable?
Which parts are static?
Which parts are prototype/dummy?
Which fields does the UI expect?
Which fields does the database actually contain?
```

## Reconciliation

The agent MUST create a mapping such as:

```text
UI field             Database field
------------------------------------
Room Number          room_number
Floor                floor
Monthly Price        price            (ERD lama: price_per_month)
Status               status
Active               is_active
Description          description
Image                image
Facilities           room_facilities
Lease Price          m_price          (ERD lama: monthly_price)
Payment Proof        proof_img        (ERD lama: proof_image)
Expense Author       user_id          (ERD lama: created_by)
Emergency Name       emergency_name   (ERD lama: emergency_contact_name)
Emergency Phone      emergency_contact (ERD lama: emergency_contact_phone)
```

If there is a mismatch, flag it before implementation.

---

# 36. NO BIG-BANG IMPLEMENTATION

The following is explicitly prohibited:

```text
Analyze everything
↓
rewrite everything
↓
create every CRUD
↓
change every Blade file
↓
change all JavaScript
↓
change all routes
↓
finish everything in one pass
```

Preferred:

```text
Understand
↓
Plan
↓
One module
↓
One small scope
↓
Test
↓
Checkpoint
↓
Next scope
```

---

# 37. DEFINITION OF DONE FOR A MODULE

A module is not considered complete merely because the page renders.

A CRUD module should eventually satisfy:

```text
[ ] Model correct
[ ] Relationships correct
[ ] Validation correct
[ ] Route correct
[ ] Authorization correct
[ ] Index works
[ ] Create works
[ ] Store works
[ ] Edit works
[ ] Update works
[ ] Show works
[ ] Delete works safely
[ ] Validation errors visible
[ ] Success messages visible
[ ] Existing design preserved
[ ] Responsive UI preserved
[ ] No dummy backend data
[ ] Database verified
[ ] Relevant tests/manual checks completed
```

Do not mark a module complete when these important items remain unverified.

---

# 38. FINAL RULE

The agent must behave as if it is joining a production codebase with an existing owner, existing decisions, and existing constraints.

Therefore:

> **Do not guess. Do not rush. Do not rewrite unnecessarily. Do not implement everything at once.**

The correct workflow is:

```text
READ
↓
UNDERSTAND
↓
ANALYZE
↓
MAP ERD ↔ CODE ↔ UI
↓
PLAN
↓
IMPLEMENT ONE SMALL SCOPE
↓
TEST
↓
REPORT CHECKPOINT
↓
CONTINUE
```

The project owner's learning goal is also part of the project requirements:

```text
HTML
↓
Blade
↓
Route
↓
Controller
↓
Model
↓
Database
```

When frontend work is being generated by an external AI, prioritize **HTML/CSS** so the owner can perform the HTML → Blade conversion themselves and learn from the integration process.
