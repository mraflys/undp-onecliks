<?php
namespace App\Console\Commands;

use App\Http\Controllers\MemberRequestController;
use App\SecUser;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RunStoreMethod extends Command
{
    protected $signature   = 'test:store-method {--users=1 : Number of concurrent users} {--load-test : Enable load testing mode}';
    protected $description = 'Run MemberRequestController store method with test payload';

    public function handle()
    {
        $users    = $this->option('users');
        $loadTest = $this->option('load-test');

        if ($loadTest) {
            return $this->runLoadTest($users);
        }

        $this->info('=== MENJALANKAN METHOD STORE ===');

        // Create fake uploaded files untuk testing
        $uploadedFile1 = UploadedFile::fake()->create('Siti_Kurniasih_CV_hitam.pdf', 136, 'application/pdf');
        $uploadedFile2 = UploadedFile::fake()->create('CV_an_Siti_Kurniasih.docx', 53, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $uploadedFile3 = UploadedFile::fake()->create('CV_an_Siti_Kurniasih_2.docx', 53, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $uploadedFile4 = UploadedFile::fake()->create('CV_an_Siti_Kurniasih_2_copy.docx', 53, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // Data payload berdasarkan yang Anda berikan
        $payloadData = [
            '_method'                => 'POST',
            '_token'                 => 'kz7hUv9rkRVHti9RrJItloZg7ljy4uHqMkA1RozW',
            'id_user_buyer'          => '980',
            'id_agency_unit_buyer'   => '5',
            'agency'                 => '1',
            'id_agency_unit_service' => '5',
            'id_service'             => '131',
            'description'            => 'dawda',
            'supervisor_mail'        => 'tetewt95@gmail.com',
            'id_currency'            => '1',
            'currency'               => 'USD',
            'service_price'          => '112.34',
            'qty'                    => '1',
            'total_price'            => '112.34',
            'payment_method'         => 'transfer_cash',
            'required_infos'         => [
                461 => 'dawdaw',
                43  => 'dawdaw',
                44  => 'dawdaw',
                479 => 'dawda',
                480 => null,
            ],
            'workflows'              => [
                210 => '112.34',
                211 => '0',
                214 => '0',
                750 => '0',
                215 => '0',
            ],
            'checkbox_price_input'   => [
                68 => '112.34',
            ],
            'tnc_confirmation'       => '1',
            'submit'                 => 'tr_service',
            'required_docs'          => [
                182 => $uploadedFile1,
                73  => $uploadedFile2,
                178 => $uploadedFile3,
                180 => $uploadedFile4,
            ],
        ];

        $this->info("Payload Data:");
        $this->info("- ID User Buyer: " . $payloadData['id_user_buyer']);
        $this->info("- ID Service: " . $payloadData['id_service']);
        $this->info("- Description: " . $payloadData['description']);
        $this->info("- Supervisor Mail: " . $payloadData['supervisor_mail']);
        $this->info("- Service Price: " . $payloadData['service_price']);
        $this->info("- Payment Method: " . $payloadData['payment_method']);
        $this->info("- Submit Type: " . $payloadData['submit']);
        $this->info("- Required Files: " . count($payloadData['required_docs']) . " files");
        $this->info("- Workflows: " . count($payloadData['workflows']) . " workflows");
        $this->info("- Required Infos: " . count($payloadData['required_infos']) . " infos");
        $this->info("");

        try {
            // Cari user yang ada di database untuk authentication
            $user = SecUser::where('is_active', 1)->whereNull('date_deleted')->first();
            if (! $user) {
                $this->error("User aktif tidak ditemukan. Pastikan ada user aktif di database.");
                return;
            }

            $this->info("Using user: {$user->user_name} (ID: {$user->id_user})");

            // Mock authentication tanpa login sebenarnya (karena kita dalam console)
            // Set session data
            session(['user_id' => $user->id_user]);
            session(['user_agency_unit_id' => $user->id_agency_unit]);

            // Override payload data dengan user yang sebenarnya ada
            $payloadData['id_user_buyer']        = $user->id_user;
            $payloadData['id_agency_unit_buyer'] = $user->id_agency_unit;

            // Create Request object dengan payload
            $request = new Request($payloadData);
            $request->files->add(['required_docs' => $payloadData['required_docs']]);

            // Set server variables untuk request dan $_SERVER global
            $request->server->set('REMOTE_ADDR', '127.0.0.1');
            $request->server->set('HTTP_USER_AGENT', 'PHPScript/1.0');
            $request->server->set('HTTP_HOST', 'localhost');
            $request->server->set('REQUEST_URI', '/test-store-method');

            // Set $_SERVER global variables juga untuk GeneralHelper
            $_SERVER['REMOTE_ADDR']     = '127.0.0.1';
            $_SERVER['HTTP_USER_AGENT'] = 'PHPScript/1.0';
            $_SERVER['HTTP_HOST']       = 'localhost';
            $_SERVER['REQUEST_URI']     = '/test-store-method';

            // Simulate login instead of mocking
            Auth::login($user);

            // Instantiate controller
            $controller = new MemberRequestController();

            $this->info("Menjalankan method store...");

            // Count records before
            $countBefore = DB::table('tr_service')->count();

            // Jalankan method store dengan request payload
            $result = $controller->store($request);

            // Count records after
            $countAfter = DB::table('tr_service')->count();

            $this->info("Method store berhasil dijalankan!");
            $this->info("Result type: " . get_class($result));
            $this->info("Records before: $countBefore");
            $this->info("Records after: $countAfter");

            // Cek apakah ada data yang tersimpan di database
            $lastTransaction = DB::table('tr_service')
                ->orderBy('id_transaction', 'desc')
                ->first();

            if ($lastTransaction && $countAfter > $countBefore) {
                $this->info("\n=== DATA TERSIMPAN DI DATABASE ===");
                $this->info("ID Transaction: " . $lastTransaction->id_transaction);
                $this->info("Transaction Code: " . ($lastTransaction->transaction_code ?? 'NULL'));
                $this->info("Service ID: " . ($lastTransaction->id_service ?? 'NULL'));
                $this->info("User Buyer ID: " . ($lastTransaction->id_user_buyer ?? 'NULL'));
                $this->info("Description: " . ($lastTransaction->description ?? 'NULL'));
                $this->info("Service Price: " . ($lastTransaction->service_price ?? 'NULL'));
                $this->info("Payment Method: " . ($lastTransaction->payment_method ?? 'NULL'));
                $this->info("Date Created: " . ($lastTransaction->date_created ?? 'NULL'));

                // Cek workflow data
                $workflowCount = DB::table('tr_service_workflow')
                    ->where('id_transaction', $lastTransaction->id_transaction)
                    ->count();
                $this->info("Workflow records: $workflowCount");

            } else {
                $this->info("\nTidak ada data baru yang tersimpan di tr_service table.");
            }

        } catch (\Exception $e) {
            $this->error("ERROR: " . $e->getMessage());
            $this->error("File: " . $e->getFile());
            $this->error("Line: " . $e->getLine());
            $this->error("\nStack Trace:");
            $this->error($e->getTraceAsString());
        }

        $this->info("\n=== SELESAI ===");
    }

    private function runLoadTest($userCount = 15)
    {
        $this->info("=== ASYNCHRONOUS LOAD TESTING MODE ===");
        $this->info("Concurrent Users: $userCount");
        $this->info("Starting TRULY concurrent load test...\n");

        $startTime   = microtime(true);
        $countBefore = DB::table('tr_service')->count();

        // Buat temporary directory untuk hasil
        $tempDir = storage_path('app/temp');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Buat file batch script untuk menjalankan semua user secara bersamaan
        $batchScript = $tempDir . '/run_concurrent_users.bat';
        $resultFiles = [];

        // Generate script untuk menjalankan semua user secara bersamaan
        $batchContent = "@echo off\n";
        $batchContent .= "cd /d \"" . base_path() . "\"\n";

        for ($i = 1; $i <= $userCount; $i++) {
            $resultFile    = str_replace('/', '\\', $tempDir . "\\result_{$i}.json");
            $resultFiles[] = $resultFile;

            // Start setiap proses secara asynchronous menggunakan PowerShell
            $batchContent .= "start /B powershell -WindowStyle Hidden -Command \"php artisan test:single-user-store --user-id={$i} --result-file='{$resultFile}'\"\n";
        }

        file_put_contents($batchScript, $batchContent);

        $this->info("Launching {$userCount} concurrent users...");

        // Jalankan batch script untuk start semua proses bersamaan
        exec($batchScript);

        $this->info("All {$userCount} users launched simultaneously!");
        $this->info("Waiting for completion...\n");

                                   // Monitor progress
        $maxWaitTime        = 120; // 2 menit max
        $startWait          = time();
        $lastCompletedCount = 0;

        while (time() - $startWait < $maxWaitTime) {
            $completedCount  = 0;
            $inProgressCount = 0;

            foreach ($resultFiles as $file) {
                if (file_exists($file)) {
                    $completedCount++;
                } else {
                    $inProgressCount++;
                }
            }

            if ($completedCount > $lastCompletedCount) {
                $this->info("Progress: {$completedCount}/{$userCount} completed ({$inProgressCount} in progress)");
                $lastCompletedCount = $completedCount;
            }

            if ($completedCount == $userCount) {
                $this->info("All users completed!");
                break;
            }

            sleep(1); // Check every second
        }

        $endTime   = microtime(true);
        $totalTime = $endTime - $startTime;

        // Collect all results
        $allResults         = [];
        $successCount       = 0;
        $errorCount         = 0;
        $totalExecutionTime = 0;
        $minTime            = PHP_FLOAT_MAX;
        $maxTime            = 0;

        for ($i = 1; $i <= $userCount; $i++) {
            $resultFile = $resultFiles[$i - 1];

            if (file_exists($resultFile)) {
                $result       = json_decode(file_get_contents($resultFile), true);
                $allResults[] = $result;

                if ($result['success']) {
                    $successCount++;
                    $execTime = $result['execution_time'];
                    $totalExecutionTime += $execTime;
                    $minTime = min($minTime, $execTime);
                    $maxTime = max($maxTime, $execTime);
                } else {
                    $errorCount++;
                }

                // Cleanup file
                unlink($resultFile);
            } else {
                $errorCount++;
                $allResults[] = [
                    'success'        => false,
                    'user_id'        => $i,
                    'error'          => 'Process timeout or failed to complete',
                    'execution_time' => 0,
                ];
            }
        }

        // Cleanup batch script
        if (file_exists($batchScript)) {
            unlink($batchScript);
        }

        $countAfter = DB::table('tr_service')->count();
        $newRecords = $countAfter - $countBefore;

        // Display comprehensive results
        $this->info("\n" . str_repeat("=", 60));
        $this->info("=== ASYNCHRONOUS LOAD TEST RESULTS ===");
        $this->info(str_repeat("=", 60));
        $this->info("TIMING METRICS:");
        $this->info("  • Total Wall Time: " . number_format($totalTime, 2) . " seconds");
        $this->info("  • Concurrent Users: {$userCount}");

        if ($successCount > 0) {
            $avgExecTime = $totalExecutionTime / $successCount;
            $this->info("  • Average Execution Time: " . number_format($avgExecTime, 2) . " seconds");
            $this->info("  • Fastest Response: " . number_format($minTime, 2) . " seconds");
            $this->info("  • Slowest Response: " . number_format($maxTime, 2) . " seconds");
            $this->info("  • Throughput: " . number_format($successCount / $totalTime, 2) . " req/sec");

            // Concurrency achieved calculation
            $concurrencyAchieved = ($totalExecutionTime / $totalTime);
            $this->info("  • Concurrency Achieved: " . number_format($concurrencyAchieved, 1) . "x");
        }

        $this->info("\nRESULT SUMMARY:");
        $this->info("  • Successful Requests: {$successCount}");
        $this->info("  • Failed Requests: {$errorCount}");
        $this->info("  • Success Rate: " . number_format(($successCount / $userCount) * 100, 1) . "%");

        $this->info("\nDATABASE IMPACT:");
        $this->info("  • Records Before: {$countBefore}");
        $this->info("  • Records After: {$countAfter}");
        $this->info("  • New Records Created: {$newRecords}");
        $this->info("  • Expected Records: " . ($successCount * 2) . " (tr_service + workflows)");

        // Show individual results with color coding
        $this->info("\n=== INDIVIDUAL USER RESULTS ===");
        foreach ($allResults as $result) {
            $userId = $result['user_id'] ?? 'N/A';
            $status = $result['success'] ? '✓ SUCCESS' : '✗ FAILED';
            $time   = isset($result['execution_time']) ? number_format($result['execution_time'], 2) . 's' : 'N/A';

            if ($result['success']) {
                $this->info("User {$userId}: {$status} (Time: {$time})");
                if (isset($result['service_id']) && isset($result['price'])) {
                    $this->info("  └─ Service: {$result['service_id']}, Price: \${$result['price']}");
                }
            } else {
                $this->error("User {$userId}: {$status} (Time: {$time})");
                if (isset($result['error'])) {
                    $this->error("  └─ Error: " . substr($result['error'], 0, 100) . "...");
                }
            }
        }

        $this->info("\n" . str_repeat("=", 60));
        $this->info("=== ASYNCHRONOUS LOAD TEST COMPLETED ===");
        $this->info(str_repeat("=", 60));
    }

    private function executeSingleUser($userId)
    {
        // Set $_SERVER global variables
        $_SERVER['REMOTE_ADDR']     = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'LoadTest/1.0';
        $_SERVER['HTTP_HOST']       = 'localhost';
        $_SERVER['REQUEST_URI']     = "/load-test-user-{$userId}";

        // Create fake uploaded files
        $uploadedFile1 = UploadedFile::fake()->create("CV_User_{$userId}_1.pdf", 136, 'application/pdf');
        $uploadedFile2 = UploadedFile::fake()->create("CV_User_{$userId}_2.docx", 53, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $uploadedFile3 = UploadedFile::fake()->create("CV_User_{$userId}_3.docx", 53, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $uploadedFile4 = UploadedFile::fake()->create("CV_User_{$userId}_4.docx", 53, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // Get active users and rotate
        $users = SecUser::where('is_active', 1)->whereNull('date_deleted')->get();
        if ($users->isEmpty()) {
            throw new \Exception("No active users found");
        }

        $user = $users[($userId - 1) % $users->count()];

                                 // Randomize payload data untuk variasi
        $serviceIds = [68, 131]; // Gunakan service IDs yang ada
        $currencies = [1, 2];    // Currency IDs yang ada
        $prices     = [50.00, 75.50, 100.00, 112.34, 150.75, 200.00];

        $selectedServiceId  = $serviceIds[array_rand($serviceIds)];
        $selectedCurrencyId = $currencies[array_rand($currencies)];
        $selectedPrice      = $prices[array_rand($prices)];

        $payloadData = [
            '_method'                => 'POST',
            '_token'                 => 'loadtest_' . $userId . '_' . time(),
            'id_user_buyer'          => $user->id_user,
            'id_agency_unit_buyer'   => $user->id_agency_unit,
            'agency'                 => '1',
            'id_agency_unit_service' => $user->id_agency_unit,
            'id_service'             => $selectedServiceId,
            'description'            => "Load Test Request from User {$userId} - " . date('Y-m-d H:i:s'),
            'supervisor_mail' => "loadtest{$userId}@example.com",
            'id_currency' => $selectedCurrencyId,
            'currency' => 'USD',
            'service_price' => $selectedPrice,
            'qty' => rand(1, 3),
            'total_price' => $selectedPrice,
            'payment_method' => 'transfer_cash',
            'required_infos' => [
                461 => "Info 1 from User {$userId}",
                43 => "Info 2 from User {$userId}",
                44 => "Info 3 from User {$userId}",
                479 => "Info 4 from User {$userId}",
                480 => null,
            ],
            'workflows' => [
                210 => $selectedPrice,
                211 => '0',
                214 => '0',
                750 => '0',
                215 => '0',
            ],
            'checkbox_price_input' => [
                68 => $selectedPrice,
            ],
            'tnc_confirmation' => '1',
            'submit' => 'tr_service',
            'required_docs' => [
                182 => $uploadedFile1,
                73  => $uploadedFile2,
                178 => $uploadedFile3,
                180 => $uploadedFile4,
            ],
        ];

        // Set session data
        session(['user_id' => $user->id_user]);
        session(['user_agency_unit_id' => $user->id_agency_unit]);

        // Create Request object
        $request = new Request($payloadData);
        $request->files->add(['required_docs' => $payloadData['required_docs']]);

        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $request->server->set('HTTP_USER_AGENT', 'LoadTest/1.0');
        $request->server->set('HTTP_HOST', 'localhost');
        $request->server->set('REQUEST_URI', "/load-test-user-{$userId}");

        // Simulate login instead of mocking
        Auth::login($user);

        // Execute store method
        $controller  = new MemberRequestController();
        $storeResult = $controller->store($request);

        return [
            'user_id'     => $userId,
            'service_id'  => $selectedServiceId,
            'price'       => $selectedPrice,
            'user_name'   => $user->user_name,
            'description' => $payloadData['description'],
        ];
    }
}
