<?php
namespace App\Console\Commands;

use App\Http\Controllers\MemberRequestController;
use App\SecUser;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class SingleUserStoreMethod extends Command
{
    protected $signature   = 'test:single-user-store {--user-id=1} {--result-file=}';
    protected $description = 'Single user store method execution for load testing';

    public function handle()
    {
        $userId     = $this->option('user-id');
        $resultFile = $this->option('result-file');

        $startTime = microtime(true);
        $result    = ['success' => false, 'user_id' => $userId, 'execution_time' => 0];

        try {
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

            // Get active user
            $users = SecUser::where('is_active', 1)->whereNull('date_deleted')->get();
            if ($users->isEmpty()) {
                throw new \Exception("No active users found");
            }

            // Rotate users for different load test users
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
                'qty' => rand(1, 5),
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

            // Mock Auth::user()
            Auth::shouldReceive('user')->andReturn($user);

            // Execute store method
            $controller  = new MemberRequestController();
            $storeResult = $controller->store($request);

            $endTime                  = microtime(true);
            $result['success']        = true;
            $result['execution_time'] = $endTime - $startTime;
            $result['service_id']     = $selectedServiceId;
            $result['price']          = $selectedPrice;
            $result['user_name']      = $user->user_name;

        } catch (\Exception $e) {
            $endTime                  = microtime(true);
            $result['success']        = false;
            $result['execution_time'] = $endTime - $startTime;
            $result['error']          = $e->getMessage();
            $result['file']           = $e->getFile();
            $result['line']           = $e->getLine();
        }

        // Save result to file
        if ($resultFile) {
            file_put_contents($resultFile, json_encode($result, JSON_PRETTY_PRINT));
        }

        return $result['success'] ? 0 : 1;
    }
}
