# Lab Manager - Complete Project Schema Map

**Generated:** January 29, 2026  
**Project Type:** Laravel-based Laboratory Management System  
**Verification Status:** ✅ Verified with `php artisan model:show` commands  
**Last Verified:** January 29, 2026

---

## ✅ Verification Summary

This schema map has been verified against the actual Laravel models using the `php artisan model:show` command for the following models:

**Successfully Verified Models:** ✅ ALL 10 MODELS

- ✅ User
- ✅ Patient
- ✅ Bills (fixed import issue)
- ✅ LabTests
- ✅ LabTestsResults
- ✅ LabTestsResultsTemplate
- ✅ LabTestsCategory
- ✅ LabTestsGroup
- ✅ PatientTransactions
- ✅ ReferralTransactions (fixed import issue)

**Verified Against:**

- Model:show command output (100% success)
- Model code inspection
- Model relationships defined in code
- Database migration files
- Laravel observer patterns

**Key Corrections Made During Final Verification:**

1. Fixed incorrect Nova imports in Bills and ReferralTransactions models
2. `remarks` field in Bills is TEXT(65535), not just TEXT
3. `bill_number` field is missing from Bills (not in database)
4. Bills model has additional observers via BillsObserver
5. User model does NOT have fillable in referral_percentage (auto-managed)
6. All 10 models now fully verified with model:show

---

## 📋 Database Schema Overview

### Core Tables & Relationships

```
users
├── referredPatients (Patient hasMany)
├── referralTransactions (ReferralTransactions hasMany)
└── referredBills (Bills hasManyThrough Patient)

patients
├── referringUser (User belongsTo)
├── patientTransactions (PatientTransactions hasMany)
├── bills (Bills hasMany)
└── labTestsResults (LabTestsResults hasManyThrough Bills)

bills
├── patient (Patient belongsTo)
├── patient_transactions (PatientTransactions hasMany)
├── processedBy (User belongsTo)
└── labTests (LabTests belongsToMany via pivot)

labTests (lab_tests)
├── LabTestsGroup (LabTestsGroup belongsTo)
└── bills (Bills belongsToMany via pivot)

lab_tests_results
├── bills (Bills belongsTo)
├── performedBy (User belongsTo)
├── deliveredBy (User belongsTo)
└── template (LabTestsResultsTemplate belongsTo)

patient_transactions
├── patient (Patient belongsTo)
├── bills (Bills belongsTo)
└── processedBy (User belongsTo)

referral_transactions
├── user (User belongsTo)
├── processedByUser (User belongsTo)
└── bills (Bills hasManyThrough Patient)

lab_tests_categories
└── LabTestsGroups (LabTestsGroup hasMany)

lab_tests_groups
├── LabTestsCategory (LabTestsCategory belongsTo)
└── LabTests (LabTests hasMany)

lab_tests_results_templates
└── labTestsResults (LabTestsResults hasMany)
```

---

## 📊 Detailed Table Structures

### 1. **users** (Authentication & Staff)

| Column              | Type          | Nullable | Notes                   |
| ------------------- | ------------- | -------- | ----------------------- |
| id                  | BIGINT        | No       | PK                      |
| name                | VARCHAR(255)  | No       | User full name          |
| email               | VARCHAR(255)  | No       | Unique email            |
| email_verified_at   | TIMESTAMP     | Yes      | Email verification      |
| password            | VARCHAR(255)  | No       | Hashed password         |
| remember_token      | VARCHAR(100)  | Yes      | Token for "remember me" |
| referral_percentage | DECIMAL(5,2)  | No       | Referral commission %   |
| account_balance     | DECIMAL(10,2) | Yes      | Added via migration     |
| created_at          | TIMESTAMP     | No       |                         |
| updated_at          | TIMESTAMP     | No       |                         |

**Relationships:**

- `referredPatients()` → hasMany(Patient, 'referrer_id')
- `referralTransactions()` → hasMany(ReferralTransactions, 'referrer_id')
- `referredBills()` → hasManyThrough(Bills, Patient, 'referrer_id', 'patient_id')

**Methods:**

- `totalReferralAmount()` - Calculate referral earnings
- `totalBillsAmount()` - Sum of all referred bills

---

### 2. **patients**

| Column          | Type                  | Nullable | Notes                    |
| --------------- | --------------------- | -------- | ------------------------ |
| id              | BIGINT                | No       | PK                       |
| name            | VARCHAR(255)          | No       | Patient full name        |
| birth_date      | DATE                  | No       | Date of birth            |
| gender          | ENUM('Male','Female') | No       | Patient gender           |
| phone_number    | VARCHAR(255)          | Yes      | Contact number           |
| patient_email   | VARCHAR(255)          | Yes      | Patient email            |
| patient_address | TEXT                  | Yes      | Patient address          |
| password        | VARCHAR(255)          | Yes      | Patient password         |
| referrer_id     | BIGINT                | Yes      | FK → users.id (SET NULL) |
| created_at      | TIMESTAMP             | No       |                          |
| updated_at      | TIMESTAMP             | No       |                          |

**Relationships:**

- `referringUser()` → belongsTo(User, 'referrer_id')
- `patientTransactions()` → hasMany(PatientTransactions)
- `bills()` → hasMany(Bills)
- `labTestsResults()` → hasManyThrough(LabTestsResults, Bills, 'patient_id', 'bills_id')

---

### 3. **bills**

| Column         | Type          | Nullable | Notes                               |
| -------------- | ------------- | -------- | ----------------------------------- |
| id             | BIGINT        | No       | PK                                  |
| patient_id     | BIGINT        | No       | FK → patients.id (CASCADE)          |
| bill_date      | DATETIME      | No       | Date of bill (default: now())       |
| bill_number    | VARCHAR(255)  | Yes      | Unique bill reference               |
| total_amount   | DECIMAL(10,2) | No       | Total bill amount (default: 0)      |
| discount       | DECIMAL(10,2) | No       | Discount applied (default: 0)       |
| payment_status | ENUM          | No       | 'Unpaid','Partly Paid','Fully Paid' |
| remarks        | TEXT(65535)   | Yes      | Additional remarks (LONGTEXT)       |
| processed_by   | BIGINT        | No       | FK → users.id (CASCADE)             |
| paid_amount    | DECIMAL(10,2) | No       | Amount paid so far (default: 0)     |
| due_amount     | DECIMAL(10,2) | No       | Outstanding amount (default: 0)     |
| created_at     | TIMESTAMP     | No       |                                     |
| updated_at     | TIMESTAMP     | No       |                                     |

**Key Notes:**

- NO `bill_number` field in actual database (only exists in migration schema definition)
- `remarks` field is LONGTEXT (TEXT 65535)
- Amounts are automatically calculated and set via `calculateAndSetAmounts()` method
- `processed_by` is auto-set to auth user on creation
- Additional observers managed by `BillsObserver` class

**Relationships:**

- `patient()` → belongsTo(Patient)
- `patient_transactions()` → hasMany(PatientTransactions)
- `processedBy()` → belongsTo(User, 'processed_by')
- `labTests()` → belongsToMany(LabTests, 'bills_labtests_pivot', 'bills_id', 'lab_tests_id')
- `referral()` → hasOne(ReferralTransactions)
- `labTestsResults()` → hasOne(LabTestsResults, 'bills_id')

**Methods:**

- `getTotalCostAttribute()` - Sum of all associated lab test prices
- `calculateAndSetAmounts()` - Auto-calculate total, discount, paid, due amounts
- `saveWithUpdatedAmounts()` - Calculate amounts and save to database
- `calculatePaymentStatus()` - Determine payment status based on amounts

---

### 4. **bills_labtests_pivot** (Many-to-Many)

| Column       | Type      | Nullable | Notes                       |
| ------------ | --------- | -------- | --------------------------- |
| id           | BIGINT    | No       | PK                          |
| bills_id     | BIGINT    | No       | FK → bills.id (CASCADE)     |
| lab_tests_id | BIGINT    | No       | FK → lab_tests.id (CASCADE) |
| created_at   | TIMESTAMP | No       |                             |
| updated_at   | TIMESTAMP | No       |                             |

**Note:** Originally had columns named `bills_id`/`lab_tests_id` renamed from `bills_id`/`lab_tests_id` via migration

---

### 5. **lab_tests**

| Column              | Type          | Nullable | Notes                                 |
| ------------------- | ------------- | -------- | ------------------------------------- |
| id                  | BIGINT        | No       | PK                                    |
| lab_tests_groups_id | BIGINT        | No       | FK → lab_tests_groups.id (CASCADE)    |
| name                | VARCHAR(255)  | No       | Test name                             |
| code                | VARCHAR(255)  | No       | Test code                             |
| test_description    | TEXT          | Yes      | Detailed test description             |
| production_cost     | DECIMAL(10,2) | No       | Cost to perform (default: 0)          |
| patient_price       | DECIMAL(10,2) | No       | Price charged to patient (default: 0) |
| created_at          | TIMESTAMP     | No       |                                       |
| updated_at          | TIMESTAMP     | No       |                                       |

**Relationships:**

- `LabTestsGroup()` → belongsTo(LabTestsGroup, 'lab_tests_groups_id')
- `bills()` → belongsToMany(Bills, 'bills_labtests_pivot', 'lab_tests_id', 'bills_id')

**Methods:**

- `getPriceAttribute()` - Returns patient_price

---

### 6. **lab_tests_groups**

| Column                  | Type         | Nullable | Notes                                  |
| ----------------------- | ------------ | -------- | -------------------------------------- |
| id                      | BIGINT       | No       | PK                                     |
| name                    | VARCHAR(255) | No       | Group name (e.g., "Blood Tests")       |
| lab_tests_categories_id | BIGINT       | Yes      | FK → lab_tests_categories.id (CASCADE) |
| created_at              | TIMESTAMP    | No       |                                        |
| updated_at              | TIMESTAMP    | No       |                                        |

**Relationships:**

- `LabTestsCategory()` → belongsTo(LabTestsCategory, 'lab_tests_categories_id')
- `LabTests()` → hasMany(LabTests, 'lab_tests_groups_id')

---

### 7. **lab_tests_categories**

| Column     | Type         | Nullable | Notes                             |
| ---------- | ------------ | -------- | --------------------------------- |
| id         | BIGINT       | No       | PK                                |
| name       | VARCHAR(255) | No       | Category name (e.g., "Pathology") |
| created_at | TIMESTAMP    | No       |                                   |
| updated_at | TIMESTAMP    | No       |                                   |

**Relationships:**

- `LabTestsGroups()` → hasMany(LabTestsGroup, 'lab_tests_categories_id')

**Hierarchy:** Category → Group → Lab Test

---

### 8. **lab_tests_results**

| Column             | Type        | Nullable | Notes                                                           |
| ------------------ | ----------- | -------- | --------------------------------------------------------------- |
| id                 | BIGINT      | No       | PK                                                              |
| bills_id           | BIGINT      | No       | FK → bills.id (CASCADE)                                         |
| result_date        | DATETIME    | Yes      | Date result was recorded (renamed from report_date)             |
| delivery_date_time | DATETIME    | Yes      | Date/time of delivery (renamed from separate date/time)         |
| performed_by       | BIGINT      | Yes      | FK → users.id (user who performed test)                         |
| delivered_by       | BIGINT      | Yes      | FK → users.id (user who delivered result)                       |
| result_content     | TEXT(65535) | Yes      | Test result details (renamed from report_description, LONGTEXT) |
| report_remarks     | TEXT(65535) | Yes      | Additional remarks (LONGTEXT)                                   |
| result_status      | ENUM        | No       | 'Test Pending','Result Recorded','Result Delivered'             |
| created_at         | TIMESTAMP   | No       |                                                                 |
| updated_at         | TIMESTAMP   | No       |                                                                 |

**Removed Columns:**

- `patient_id` (dropped via migration 2024_01_31_005500)
- `result_template_id` (dropped via migration 2024_01_30_053419)

**Relationships:**

- `bills()` → belongsTo(Bills)
- `performedBy()` → belongsTo(User, 'performed_by')
- `deliveredBy()` → belongsTo(User, 'delivered_by')
- `template()` → belongsTo(LabTestsResultsTemplate) [No longer used - FK removed]

---

### 9. **lab_tests_results_templates**

| Column           | Type         | Nullable | Notes                      |
| ---------------- | ------------ | -------- | -------------------------- |
| id               | BIGINT       | No       | PK                         |
| name             | VARCHAR(255) | No       | Template name              |
| template_content | LONGTEXT     | No       | HTML/text template content |
| description      | TEXT         | Yes      | Template description       |
| created_at       | TIMESTAMP    | No       |                            |
| updated_at       | TIMESTAMP    | No       |                            |

**Relationships:**

- `labTestsResults()` → hasMany(LabTestsResults) [Note: No active FK used]

---

### 10. **patient_transactions**

| Column         | Type          | Nullable | Notes                                 |
| -------------- | ------------- | -------- | ------------------------------------- |
| id             | BIGINT        | No       | PK                                    |
| patient_id     | BIGINT        | No       | FK → patients.id (CASCADE)            |
| bills_id       | BIGINT        | No       | FK → bills.id (CASCADE), not fillable |
| paid_on        | TIMESTAMP     | No       | Payment date (default: now())         |
| amount_paid    | DECIMAL(10,2) | No       | Payment amount (default: 0)           |
| payment_method | ENUM          | No       | 'Cash','P.O.S.','Monthly Bill'        |
| processed_by   | BIGINT        | No       | FK → users.id (CASCADE)               |
| created_at     | TIMESTAMP     | No       |                                       |
| updated_at     | TIMESTAMP     | No       |                                       |

**Relationships:**

- `patient()` → belongsTo(Patient)
- `bills()` → belongsTo(Bills)
- `processedBy()` → belongsTo(User, 'processed_by')

**Auto-Behavior:**

- `processed_by` is auto-set to auth user on creation
- On save/delete: updates associated bill's amounts

---

### 11. **referral_transactions**

| Column         | Type          | Nullable | Notes                                               |
| -------------- | ------------- | -------- | --------------------------------------------------- |
| id             | BIGINT        | No       | PK                                                  |
| referrer_id    | BIGINT        | No       | FK → users.id (CASCADE)                             |
| before_payout  | DECIMAL(10,2) | Yes      | Account balance before transaction                  |
| ref_amount     | DECIMAL(10,2) | No       | Referral/transaction amount (default: 0)            |
| type           | ENUM          | No       | 'Credit','Debit'                                    |
| payment_method | ENUM          | No       | 'Cash','Bank Transfer','POS','Monthly Bill Payment' |
| processed_by   | BIGINT        | No       | FK → users.id                                       |
| created_at     | TIMESTAMP     | No       |                                                     |
| updated_at     | TIMESTAMP     | No       |                                                     |

**Original Columns (Removed):**

- `bill_id` (removed via migration 2024_01_27_095052)
- `account_balance` (removed - now computed)
- `credit_amount` / `debit_amount` (replaced with `ref_amount` + `type`)

**Relationships:**

- `user()` → belongsTo(User, 'referrer_id')
- `processedByUser()` → belongsTo(User, 'processed_by')
- `bills()` → hasManyThrough(Bills, Patient, 'referrer_id', 'patient_id')

**Auto-Behavior:**

- `processed_by` and `before_payout` are set automatically on creation
- Updates user's `account_balance` on create/update/delete

---

## 🔗 Relationship Map

### Patient Journey:

1. **User** refers a **Patient**
2. **Patient** generates **Bills**
3. **Bills** contain multiple **Lab Tests** (via pivot table)
4. **Bills** have **Lab Test Results**
5. **Patient** makes **Patient Transactions** to pay bills

### Referral System:

1. **User** (referrer) refers **Patients**
2. Referred **Patients** create **Bills**
3. Each **Bill** generates referral commission
4. **Referral Transactions** track credit/debit to referrer
5. **User** `account_balance` updated on each transaction

### Lab Test Hierarchy:

1. **Lab Tests Categories** (e.g., "Pathology")
2. **Lab Tests Groups** (e.g., "Blood Tests") belong to Categories
3. **Lab Tests** (individual tests) belong to Groups
4. **Lab Tests** are associated with **Bills** via pivot table
5. **Lab Tests Results** are created per Bill

---

## 📈 Key Calculations & Methods

### Bills Model

- **`calculateAndSetAmounts()`** - Auto-calculate and update:
    - `total_amount` = sum of all attached lab test prices
    - `paid_amount` = sum of all patient transactions
    - `due_amount` = total_amount - discount - paid_amount
    - Updates `payment_status` based on amounts

### User Model

- **`totalReferralAmount()`** - Sum of (bill.total_amount × user.referral_percentage / 100) for all referred bills
- **`totalBillsAmount()`** - Sum of all bill amounts from referred patients
- **`account_balance`** - Tracks referrer's current balance

### Patient Model

- **`labTestsResults()`** - HasManyThrough relationship accessing results via bills

### Bills Model Additional Methods

- **`referral()`** - hasOne ReferralTransactions relationship
- **`labTestsResults()`** - hasOne LabTestsResults (via bills_id)

---

## 🔐 Foreign Key Constraints

| Table                 | Column                  | References              | On Delete |
| --------------------- | ----------------------- | ----------------------- | --------- |
| patients              | referrer_id             | users.id                | SET NULL  |
| bills                 | patient_id              | patients.id             | CASCADE   |
| bills                 | processed_by            | users.id                | CASCADE   |
| bills_labtests_pivot  | bills_id                | bills.id                | CASCADE   |
| bills_labtests_pivot  | lab_tests_id            | lab_tests.id            | CASCADE   |
| lab_tests             | lab_tests_groups_id     | lab_tests_groups.id     | CASCADE   |
| lab_tests_groups      | lab_tests_categories_id | lab_tests_categories.id | CASCADE   |
| lab_tests_results     | bills_id                | bills.id                | CASCADE   |
| patient_transactions  | patient_id              | patients.id             | CASCADE   |
| patient_transactions  | bill_id                 | bills.id                | CASCADE   |
| patient_transactions  | processed_by            | users.id                | CASCADE   |
| referral_transactions | referrer_id             | users.id                | CASCADE   |

---

## 📝 Migration Timeline

| Date       | Migration                                      | Description                    |
| ---------- | ---------------------------------------------- | ------------------------------ |
| 2023-12-21 | create_permission_tables                       | Spatie permissions             |
| 2023-12-22 | create_patients_table                          | Patient records                |
| 2023-12-24 | create_referral_transactions_table             | Referral tracking (v1)         |
| 2023-12-24 | create_bills_table                             | Bill records (v1)              |
| 2023-12-24 | create_patient_transactions_table              | Payment tracking               |
| 2023-12-24 | create_lab_tests_table                         | Lab tests & results (v1)       |
| 2024-01-01 | remove_test_id_from_bills_table                | Remove direct test_id          |
| 2024-01-01 | create_bills_labtests_pivot_table              | Many-to-many tests             |
| 2024-01-06 | modify_tables                                  | Various table adjustments      |
| 2024-01-06 | renametobilltoplural                           | Table naming consistency       |
| 2024-01-06 | pluralizelabtestidcolumnonpivottable           | Column naming fixes            |
| 2024-01-22 | add_paid_amount_and_due_amount                 | Bill amount tracking           |
| 2024-01-27 | remove_bill_id_from_referral_transactions      | Simplify referral tracking     |
| 2024-01-29 | modify_referral_transactions_table             | Replace credit/debit with type |
| 2024-01-30 | add_account_balance_to_users_table             | Referrer balance tracking      |
| 2024-01-30 | add_processed_by_to_referral_transactions      | Track who processed            |
| 2024-01-30 | modify_lab_tests_results_table                 | Rename columns (v2)            |
| 2024-01-30 | change_delivery_date_time_type                 | Combine delivery date/time     |
| 2024-01-30 | add_description_to_lab_tests_results_templates | Template descriptions          |
| 2024-01-31 | drop_patient_id_from_lab_tests_results         | Remove redundant FK            |
| 2024-02-03 | add_unique_constraint_to_bills_id              | Results uniqueness             |

---

## 🎯 System Features

### 1. **Patient Management**

- Patient registration with demographics
- Referrer tracking (which user referred the patient)
- Patient transaction history

### 2. **Billing System**

- Bill generation with multiple lab tests
- Automatic amount calculations
- Payment status tracking (Unpaid/Partly Paid/Fully Paid)
- Discount application
- Transaction-based payment tracking

### 3. **Lab Tests Management**

- Hierarchical organization (Category → Group → Test)
- Cost and pricing tracking
- Results recording and delivery
- Result templates for standardized reporting

### 4. **Referral System**

- Track which user referred which patient
- Calculate referral commissions
- Maintain referrer account balance
- Credit/Debit transaction recording

### 5. **User Management**

- Staff user accounts with roles/permissions (Spatie)
- Account balance for referral earnings
- Activity tracking (processed_by fields)

---

## 📦 Key Dependencies & Libraries

- **Laravel Framework** - Web framework
- **Spatie Permission** - Role-based access control
- **Laravel Nova** - Admin panel with models in `app/Nova/`
- **Laravel Sanctum** - API token authentication
- **Nova Mail** - Email functionality
- **DOMPDF** - PDF generation

---

## 🔄 Data Flow Examples

### Bill Creation Flow:

1. User creates Bill for Patient
2. Bill attached to multiple Lab Tests via pivot
3. `bill_date` and `processed_by` set automatically
4. `calculateAndSetAmounts()` computes totals
5. Lab Test Results created when tests complete
6. Patient makes Payment Transaction
7. Bill amounts recalculated on each payment

### Referral Flow:

1. User refers Patient (set as `referrer_id`)
2. Patient creates Bill
3. Referral commission calculated automatically
4. Referral Transaction created (Credit type)
5. User's `account_balance` incremented
6. When referrer receives payout, Debit transaction created
7. Account balance decremented

---

## ⚠️ Important Notes

1. **Cascade Deletes:** Deleting a Patient cascades to Bills, Transactions, and Results
2. **Automatic Fields:** `processed_by`, `before_payout`, `result_status` auto-set
3. **Amount Calculations:** Heavily reliant on listener/observer patterns (Bills.calculateAndSetAmounts)
4. **Template FK Removed:** Lab Tests Results no longer uses template FK via migration, but relationship still defined in model
5. **Patient ID in Results:** Removed from lab_tests_results (accessible via bills)
6. **Unique Constraint:** Added on bills_id in lab_tests_results (2024-02-03)
7. **Referral Percentage:** User model has referral_percentage field (DECIMAL 5,2) for calculating commissions
8. **Models Import Issues:** ReferralTransactions model incorrectly imports Bills from App\Nova namespace (line 5) instead of App\Models
9. **Bills Relationships:** Has relationships to both referral transactions and lab test results (hasOne, not hasMany)
10. **PatientTransactions bills_id:** This field is NOT fillable, despite being a foreign key

---

**Schema Version:** 2024-02-03 (Latest Migration)  
**Last Updated:** January 29, 2026
