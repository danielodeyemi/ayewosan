<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Bills;
use App\Models\LabTests;
use App\Models\LabTestsCategory;
use App\Models\LabTestsGroup;
use App\Models\LabTestsResults;
use App\Models\PatientTransactions;
use App\Models\ReferralTransactions;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        // Get users (created by UsersSeeder)
        $superAdmin = User::where('email', 'danieltheone09@gmail.com')->first() ?? $this->createSuperAdmin();
        $receptionist = User::where('email', 'receptionist@example.com')->first();
        $accountant = User::where('email', 'accountant@example.com')->first();
        $labTech = User::where('email', 'laboratorytechnician@example.com')->first();
        $admin = User::where('email', 'non-techicaladmin@example.com')->first();

        // Demo referrers (users who refer patients)
        $referrer1 = $this->createOrUpdateUser('Dr. Sarah Johnson', 'dr.sarah@example.com', 'referrer1');
        $referrer2 = $this->createOrUpdateUser('Dr. Michael Chen', 'dr.michael@example.com', 'referrer2');
        $referrer3 = $this->createOrUpdateUser('Dr. Emily Brown', 'dr.emily@example.com', 'referrer3');

        // Set referral percentages for referrers
        $referrer1->update(['referral_percentage' => 10.00]);
        $referrer2->update(['referral_percentage' => 15.00]);
        $referrer3->update(['referral_percentage' => 12.00]);

        $this->command->info('Created demo referrers.');

        // Create demo patients referred by different doctors
        $patients = $this->createDemoPatients($referrer1, $referrer2, $referrer3);
        $this->command->info('Created ' . count($patients) . ' demo patients.');

        // Get lab tests (should already exist)
        $labTests = LabTests::all();

        if ($labTests->isEmpty()) {
            $this->command->warn('No lab tests found. Creating basic lab tests...');
            $labTests = $this->createBasicLabTests();
        }

        // Create bills with various scenarios
        $bills = $this->createDemoBills($patients, $labTests, $receptionist, $labTech);
        $this->command->info('Created ' . count($bills) . ' demo bills.');

        // Create payment transactions (various payment scenarios)
        $this->createDemoPayments($bills, $receptionist);
        $this->command->info('Created payment transactions.');

        // Create lab test results (various statuses)
        $this->createDemoLabResults($bills, $labTech);
        $this->command->info('Created lab test results.');

        // Create referral transactions (commission tracking)
        $this->createDemoReferralTransactions($patients, $accountant);
        $this->command->info('Created referral transactions.');

        $this->command->info('Demo data seeding completed successfully!');
        $this->printDemoSummary($patients, $bills);
    }

    /**
     * Create a demo user or update if exists
     */
    private function createOrUpdateUser($name, $email, $password = 'password'): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt($password),
                'referral_percentage' => 0.00,
                'account_balance' => 0.00,
            ]
        );
    }

    /**
     * Create super admin if doesn't exist
     */
    private function createSuperAdmin(): User
    {
        $user = User::updateOrCreate(
            ['email' => 'danieltheone09@gmail.com'],
            [
                'name' => 'Daniel',
                'password' => bcrypt('Daniel'),
                'referral_percentage' => 0.00,
                'account_balance' => 0.00,
            ]
        );

        $user->assignRole('super-admin');
        return $user;
    }

    /**
     * Create demo patients with various demographics
     */
    private function createDemoPatients(User $referrer1, User $referrer2, User $referrer3): array
    {
        $patients = [];
        $referrers = [$referrer1, $referrer2, $referrer3];

        $patientData = [
            // Referred by Dr. Sarah Johnson
            ['name' => 'John David Smith', 'age' => 45, 'gender' => 'Male'],
            ['name' => 'Mary Jennifer Wilson', 'age' => 32, 'gender' => 'Female'],
            ['name' => 'Robert James Anderson', 'age' => 58, 'gender' => 'Male'],

            // Referred by Dr. Michael Chen
            ['name' => 'Patricia Louise Taylor', 'age' => 41, 'gender' => 'Female'],
            ['name' => 'Christopher Paul Moore', 'age' => 36, 'gender' => 'Male'],
            ['name' => 'Linda Marie Jackson', 'age' => 52, 'gender' => 'Female'],

            // Referred by Dr. Emily Brown
            ['name' => 'Richard Thomas White', 'age' => 47, 'gender' => 'Male'],
            ['name' => 'Sandra Michelle Harris', 'age' => 39, 'gender' => 'Female'],
            ['name' => 'Joseph Mark Martin', 'age' => 55, 'gender' => 'Male'],
            ['name' => 'Jessica Anne Thompson', 'age' => 28, 'gender' => 'Female'],
        ];

        foreach ($patientData as $index => $data) {
            $referrerIndex = intval($index / 3) % 3; // Distribute patients among referrers
            $referrer = $referrers[$referrerIndex];

            $birthDate = Carbon::now()
                ->subYears($data['age'])
                ->subDays(rand(0, 365));

            $patient = Patient::create([
                'name' => $data['name'],
                'birth_date' => $birthDate,
                'gender' => $data['gender'],
                'phone_number' => '555-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'patient_email' => strtolower(str_replace(' ', '.', $data['name'])) . '@patient.example.com',
                'patient_address' => rand(100, 9999) . ' ' . ['Main', 'Oak', 'Elm', 'Pine', 'Maple'][rand(0, 4)] . ' Street',
                'referrer_id' => $referrer->id,
            ]);

            $patients[] = $patient;
        }

        return $patients;
    }

    /**
     * Create basic lab tests if none exist
     */
    private function createBasicLabTests()
    {
        $categoryNames = ['Hematology', 'Biochemistry', 'Microbiology'];
        $labTests = [];

        foreach ($categoryNames as $categoryName) {
            $category = LabTestsCategory::create([
                'name' => $categoryName,
            ]);

            $groupName = match ($categoryName) {
                'Hematology' => 'Blood Counts',
                'Biochemistry' => 'Chemistry Panel',
                'Microbiology' => 'Culture & Sensitivity',
            };

            $group = LabTestsGroup::create([
                'name' => $groupName,
                'lab_tests_categories_id' => $category->id,
            ]);

            $tests = match ($categoryName) {
                'Hematology' => [
                    ['name' => 'Complete Blood Count (CBC)', 'code' => 'CBC', 'production_cost' => 50.00, 'patient_price' => 150.00],
                    ['name' => 'Blood Group & RH Type', 'code' => 'BG-RH', 'production_cost' => 30.00, 'patient_price' => 100.00],
                    ['name' => 'Coagulation Profile', 'code' => 'CP', 'production_cost' => 80.00, 'patient_price' => 250.00],
                ],
                'Biochemistry' => [
                    ['name' => 'Fasting Blood Glucose', 'code' => 'FBG', 'production_cost' => 40.00, 'patient_price' => 120.00],
                    ['name' => 'Lipid Profile', 'code' => 'LP', 'production_cost' => 60.00, 'patient_price' => 200.00],
                    ['name' => 'Liver Function Tests', 'code' => 'LFT', 'production_cost' => 70.00, 'patient_price' => 220.00],
                    ['name' => 'Renal Function Tests', 'code' => 'RFT', 'production_cost' => 70.00, 'patient_price' => 220.00],
                ],
                'Microbiology' => [
                    ['name' => 'Blood Culture', 'code' => 'BC', 'production_cost' => 100.00, 'patient_price' => 300.00],
                    ['name' => 'Urinalysis', 'code' => 'UA', 'production_cost' => 45.00, 'patient_price' => 150.00],
                    ['name' => 'Stool Culture', 'code' => 'SC', 'production_cost' => 90.00, 'patient_price' => 280.00],
                ],
            };

            foreach ($tests as $testData) {
                $test = LabTests::create([
                    'lab_tests_groups_id' => $group->id,
                    'name' => $testData['name'],
                    'code' => $testData['code'],
                    'test_description' => "Standard {$testData['name']} laboratory test",
                    'production_cost' => $testData['production_cost'],
                    'patient_price' => $testData['patient_price'],
                ]);
                $labTests[] = $test;
            }
        }

        // Return as collection for consistency with LabTests::all()
        return collect($labTests);
    }

    /**
     * Create demo bills with various lab test combinations
     * Note: Bills are created WITHOUT calculated amounts. Amounts must be calculated via
     * "Update Bill Amounts" button in Nova per the current application workaround.
     */
    private function createDemoBills(array $patients, $labTests, User $receptionist, User $labTech): array
    {
        $bills = [];
        $billScenarios = [
            // Fully paid bill
            [
                'testCount' => 2,
                'discount' => 0,
                'status' => 'Fully Paid',
                'daysAgo' => 10,
            ],
            // Unpaid bill
            [
                'testCount' => 3,
                'discount' => 50,
                'status' => 'Unpaid',
                'daysAgo' => 3,
            ],
            // Partly paid bill
            [
                'testCount' => 4,
                'discount' => 25,
                'status' => 'Partly Paid',
                'daysAgo' => 7,
            ],
            // Another fully paid
            [
                'testCount' => 2,
                'discount' => 0,
                'status' => 'Fully Paid',
                'daysAgo' => 15,
            ],
            // Unpaid
            [
                'testCount' => 3,
                'discount' => 100,
                'status' => 'Unpaid',
                'daysAgo' => 2,
            ],
        ];

        foreach ($patients as $patientIndex => $patient) {
            $scenario = $billScenarios[$patientIndex % count($billScenarios)];

            // Select random lab tests
            $selectedTests = $labTests
                ->random($scenario['testCount'])
                ->pluck('id')
                ->toArray();

            // Calculate total from selected tests for reference
            $totalAmount = LabTests::whereIn('id', $selectedTests)->sum('patient_price');

            // Create bill with initial values
            // Bills are created without calculated amounts per the application workaround
            $bill = new Bills([
                'patient_id' => $patient->id,
                'bill_date' => Carbon::now()->subDays($scenario['daysAgo']),
                'total_amount' => 0, // Will be calculated when "Update Bill Amounts" is clicked
                'discount' => $scenario['discount'],
                'payment_status' => 'Unpaid', // Will be updated when amounts are calculated
                'remarks' => 'Demo bill for testing purposes',
                'processed_by' => $receptionist->id,
                'paid_amount' => 0,
                'due_amount' => 0,
            ]);
            
            // Bypass the booted observer that tries to calculate amounts
            $bill->timestamps = false;
            $bill->save();
            $bill->timestamps = true;

            // Attach lab tests to the bill
            $bill->labTests()->attach($selectedTests);

            $bills[] = $bill;
        }

        return $bills;
    }

    /**
     * Create demo payment transactions with various scenarios
     */
    private function createDemoPayments(array $bills, User $receptionist): void
    {
        foreach ($bills as $index => $bill) {
            if ($index % 3 == 0) {
                // Fully paid scenario
                PatientTransactions::create([
                    'patient_id' => $bill->patient_id,
                    'bills_id' => $bill->id,
                    'paid_on' => $bill->bill_date->addDays(5),
                    'amount_paid' => $bill->total_amount - $bill->discount,
                    'payment_method' => ['Cash', 'P.O.S.', 'Monthly Bill'][rand(0, 2)],
                    'processed_by' => $receptionist->id,
                ]);
            } elseif ($index % 3 == 1) {
                // Unpaid - no transaction
                continue;
            } else {
                // Partly paid scenario (pay 50%)
                $paymentAmount = ($bill->total_amount - $bill->discount) / 2;
                PatientTransactions::create([
                    'patient_id' => $bill->patient_id,
                    'bills_id' => $bill->id,
                    'paid_on' => $bill->bill_date->addDays(3),
                    'amount_paid' => $paymentAmount,
                    'payment_method' => ['Cash', 'P.O.S.', 'Monthly Bill'][rand(0, 2)],
                    'processed_by' => $receptionist->id,
                ]);
            }
        }

        // Note: Bill amounts are NOT recalculated here as per the current workaround
        // where amounts must be manually updated via "Update Bill Amounts" button in Nova
        // The observer on PatientTransactions will call saveWithUpdatedAmounts() on related bills,
        // but since the bill amounts are gated behind the button, we leave them as-is for demo
    }

    /**
     * Create demo lab test results with various statuses
     */
    private function createDemoLabResults(array $bills, User $labTech): void
    {
        $statuses = ['Test Pending', 'Result Recorded', 'Result Delivered'];

        foreach ($bills as $index => $bill) {
            $status = $statuses[$index % 3];

            $result = LabTestsResults::create([
                'bills_id' => $bill->id,
                'result_status' => $status,
                'result_date' => $status !== 'Test Pending' ? $bill->bill_date->addDays(2) : null,
                'delivery_date_time' => $status === 'Result Delivered' ? $bill->bill_date->addDays(3) : null,
                'performed_by' => $labTech->id,
                'delivered_by' => $status === 'Result Delivered' ? $labTech->id : null,
                'result_content' => $this->generateSampleResult($status),
                'report_remarks' => $status === 'Result Recorded' ? 'All tests completed successfully' : null,
            ]);
        }
    }

    /**
     * Generate sample lab result content based on status
     */
    private function generateSampleResult($status): string
    {
        if ($status === 'Test Pending') {
            return 'Test samples received and queued for analysis';
        }

        return <<<HTML
<table style="border-collapse: collapse; width: 100%;">
    <tr>
        <th style="border: 1px solid #ddd; padding: 8px;">Test Name</th>
        <th style="border: 1px solid #ddd; padding: 8px;">Result</th>
        <th style="border: 1px solid #ddd; padding: 8px;">Reference Range</th>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px;">WBC Count</td>
        <td style="border: 1px solid #ddd; padding: 8px;">7.2 K/uL</td>
        <td style="border: 1px solid #ddd; padding: 8px;">4.5-11.0</td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px;">RBC Count</td>
        <td style="border: 1px solid #ddd; padding: 8px;">4.8 M/uL</td>
        <td style="border: 1px solid #ddd; padding: 8px;">4.5-5.5</td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px;">Hemoglobin</td>
        <td style="border: 1px solid #ddd; padding: 8px;">13.5 g/dL</td>
        <td style="border: 1px solid #ddd; padding: 8px;">12.0-16.0</td>
    </tr>
</table>
HTML;
    }

    /**
     * Create demo referral transactions tracking referrer earnings
     */
    private function createDemoReferralTransactions(array $patients, User $accountant): void
    {
        // Group patients by referrer
        $patientsByReferrer = collect($patients)->groupBy('referrer_id');

        foreach ($patientsByReferrer as $referrerId => $referrerPatients) {
            $referrer = User::find($referrerId);

            // Calculate total referral amount based on bills from referred patients
            $totalBillsAmount = Bills::whereIn('patient_id', $referrerPatients->pluck('id'))
                ->sum('total_amount');

            $referralEarnings = ($totalBillsAmount * $referrer->referral_percentage) / 100;

            // Credit transaction (earning)
            if ($referralEarnings > 0) {
                ReferralTransactions::create([
                    'referrer_id' => $referrer->id,
                    'ref_amount' => $referralEarnings,
                    // before_payout is set by observer
                    'type' => 'Credit',
                    'payment_method' => 'Bank Transfer',
                    'processed_by' => $accountant->id,
                    // processed_by is auto-set by observer but we explicitly set it
                ]);
            }

            // Refresh referrer to get updated account_balance from observer
            $referrer->refresh();

            // Simulate a payout (debit transaction) if earnings are substantial
            if ($referralEarnings > 100) {
                $payoutAmount = $referralEarnings * 0.5; // Pay out half

                ReferralTransactions::create([
                    'referrer_id' => $referrer->id,
                    'ref_amount' => $payoutAmount,
                    // before_payout is set by observer
                    'type' => 'Debit',
                    'payment_method' => 'Bank Transfer',
                    'processed_by' => $accountant->id,
                    // Account balance will be updated by observer
                ]);
            }
        }
    }

    /**
     * Print demo data summary
     */
    private function printDemoSummary(array $patients, array $bills): void
    {
        $this->command->info('');
        $this->command->info('========== DEMO DATA SUMMARY ==========');
        $this->command->info('Patients Created: ' . count($patients));
        $this->command->info('Bills Created: ' . count($bills));

        $fullyPaid = Bills::where('payment_status', 'Fully Paid')->count();
        $partlyPaid = Bills::where('payment_status', 'Partly Paid')->count();
        $unpaid = Bills::where('payment_status', 'Unpaid')->count();

        $this->command->info('');
        $this->command->info('Bill Payment Status:');
        $this->command->info('  - Fully Paid: ' . $fullyPaid);
        $this->command->info('  - Partly Paid: ' . $partlyPaid);
        $this->command->info('  - Unpaid: ' . $unpaid);

        $testPending = LabTestsResults::where('result_status', 'Test Pending')->count();
        $recorded = LabTestsResults::where('result_status', 'Result Recorded')->count();
        $delivered = LabTestsResults::where('result_status', 'Result Delivered')->count();

        $this->command->info('');
        $this->command->info('Lab Test Results Status:');
        $this->command->info('  - Test Pending: ' . $testPending);
        $this->command->info('  - Result Recorded: ' . $recorded);
        $this->command->info('  - Result Delivered: ' . $delivered);

        $referrers = User::whereNotNull('referral_percentage')
            ->where('referral_percentage', '>', 0)
            ->get();

        $this->command->info('');
        $this->command->info('Referrer Account Balances:');
        foreach ($referrers as $referrer) {
            $this->command->info('  - ' . $referrer->name . ': $' . number_format($referrer->account_balance, 2));
        }

        $this->command->info('');
        $this->command->info('========== TEST CREDENTIALS ==========');
        $this->command->info('Super Admin: danieltheone09@gmail.com / Daniel');
        $this->command->info('Receptionist: receptionist@example.com / password');
        $this->command->info('Accountant: accountant@example.com / password');
        $this->command->info('Lab Technician: laboratorytechnician@example.com / password');
        $this->command->info('Non-technical Admin: non-techicaladmin@example.com / password');
        $this->command->info('');
        $this->command->info('Demo Referrers:');
        foreach ($referrers as $referrer) {
            $this->command->info('  - ' . $referrer->name . ' (' . $referrer->email . ') / password');
        }
        $this->command->info('========================================');
        $this->command->info('');
    }
}
