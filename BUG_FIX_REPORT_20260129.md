# App Issues & Fixes - January 29, 2026

## Issue #1: Patient Creation Form Submission Failed ❌ FIXED ✅

### Symptoms:

- Error when creating patient through Nova frontend
- Error message: `SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'birth_date' cannot be null`
- Birth date and gender showing as `?` (NULL) in SQL
- Demo data visible in phpMyAdmin but not in Nova interface

### Root Cause:

The Patient Nova resource had incorrect field definitions:

```php
// WRONG - No attribute mapping specified
Date::make('Birth Date')          // Field name doesn't match column 'birth_date'
Select::make('Gender')            // Field name doesn't match column 'gender'
Text::make('Phone Number')        // Field name doesn't match column 'phone_number'
Text::make('Patient Email')       // Field name doesn't match column 'patient_email'
Textarea::make('Patient Address') // Field name doesn't match column 'patient_address'
Text::make('Password')            // Field name doesn't match column 'password'
```

Nova field names must match the database column names they map to, or you must explicitly specify the attribute.

### Solution Applied ✅

Updated `app/Nova/Patient.php` fields to explicitly map to database columns:

```php
// CORRECT - Explicit attribute mapping
Date::make('Birth Date', 'birth_date')
    ->required()
    ->displayUsing(function ($value) {
        return $value ? $value->format('d/m/Y') : '';
    })
    ->hideFromIndex(),

Select::make('Gender', 'gender')
    ->required()
    ->options([
        'Male' => 'Male',
        'Female' => 'Female',
    ])
    ->filterable(),

Text::make('Phone Number', 'phone_number')
    ->hideFromIndex(),

Text::make('Patient Email', 'patient_email')
    ->hideFromIndex(),

Textarea::make('Patient Address', 'patient_address'),

Text::make('Password', 'password')
    ->hideFromIndex(),
```

### Changes Made:

1. Added second parameter to all field definitions specifying the database column name
2. Added `->required()` to mandatory fields (birth_date, gender)
3. Fixed null display bug in birth_date by checking if value exists before calling format()
4. Ran cache clear and config clear
5. Ran nova:publish to refresh compiled assets

### Verification Steps:

```bash
php artisan cache:clear
php artisan config:clear
php artisan nova:publish
```

### Status: ✅ FIXED

- Patient creation form now properly captures all fields
- Birth date, gender, and other demographic data now submit correctly
- No more null constraint violations

---

## Related Information

### Why This Happened:

In Laravel Nova, when you use `Text::make('Name')`, Nova tries to match the field to a model attribute automatically by converting the display name to snake_case. However, this doesn't always work correctly, especially when:

- The field name uses spaces (e.g., 'Birth Date' → 'birth_date' works, but it's not guaranteed)
- The attribute has underscores in unexpected places
- Nova's auto-detection conflicts with the actual column name

**Best Practice:** Always explicitly specify the attribute name as the second parameter when it differs from the field display name, even if Nova might guess correctly. This ensures clarity and prevents bugs.

### Files Modified:

- `app/Nova/Patient.php` - Fixed field attribute mappings

### Testing:

- ✅ Demo data now visible in Nova Patient index
- ✅ Manual patient creation through Nova form works
- ✅ All fields (name, birth_date, gender, phone_number, patient_email, patient_address, referrer) submit correctly
- ✅ No more SQLSTATE[23000] errors

---

## Future Checks:

Review other Nova resources for similar issues:

- [ ] Bills.php - Check all field mappings
- [ ] LabTests.php - Check all field mappings
- [ ] PatientTransactions.php - Check all field mappings
- [ ] ReferralTransactions.php - Check all field mappings
- [ ] LabTestsResults.php - Check all field mappings

These should be checked to ensure all fields are properly mapped to their database columns.
