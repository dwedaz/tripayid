<?php

namespace Tripay\PPOB\Console\Commands;

use Illuminate\Console\Command;
use Tripay\PPOB\Facades\Tripay;

class TestConnectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tripay:test-connection';

    /**
     * The console command description.
     */
    protected $description = 'Test connection to Tripay API and verify credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Testing connection to Tripay API...');
        $this->newLine();

        try {
            // Test API connection
            $this->info('1. Testing API connectivity...');
            $isConnected = Tripay::testConnection();
            
            if ($isConnected) {
                $this->info('   ✅ API connection successful');
            } else {
                $this->error('   ❌ API connection failed');
                return Command::FAILURE;
            }

            // Test server health
            $this->info('2. Checking server health...');
            $serverResponse = Tripay::server()->checkServer();
            
            if ($serverResponse->success) {
                $this->info('   ✅ Server health check passed');
                $this->info('   📝 Message: ' . $serverResponse->message);
            } else {
                $this->error('   ❌ Server health check failed');
                $this->error('   📝 Message: ' . $serverResponse->message);
            }

            // Test balance retrieval
            $this->info('3. Retrieving account balance...');
            try {
                $balance = Tripay::getBalance();
                $this->info('   ✅ Balance retrieved successfully');
                $this->info('   💰 Current Balance: Rp ' . number_format($balance, 0, ',', '.'));
            } catch (\Exception $e) {
                $this->error('   ❌ Failed to retrieve balance: ' . $e->getMessage());
            }

            // Test service accessibility
            $this->info('4. Testing service accessibility...');
            $services = [
                'Server' => Tripay::server(),
                'Balance' => Tripay::balance(),
                'Prepaid' => Tripay::prepaid(),
                'Postpaid' => Tripay::postpaid(),
                'Transaction' => Tripay::transaction(),
            ];

            foreach ($services as $name => $service) {
                if ($service) {
                    $this->info("   ✅ {$name} service accessible");
                } else {
                    $this->error("   ❌ {$name} service not accessible");
                }
            }

            // Test sample API calls
            $this->info('5. Testing sample API calls...');
            
            try {
                $this->info('   📱 Testing prepaid categories...');
                $categories = Tripay::prepaid()->getCategories();
                if ($categories->success) {
                    $this->info('   ✅ Retrieved ' . count($categories->data) . ' prepaid categories');
                } else {
                    $this->error('   ❌ Failed to retrieve prepaid categories');
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error testing prepaid categories: ' . $e->getMessage());
            }

            try {
                $this->info('   📱 Testing prepaid operators...');
                $operators = Tripay::prepaid()->getOperators();
                if ($operators->success) {
                    $this->info('   ✅ Retrieved ' . count($operators->data) . ' prepaid operators');
                } else {
                    $this->error('   ❌ Failed to retrieve prepaid operators');
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error testing prepaid operators: ' . $e->getMessage());
            }

            // Display configuration info
            $this->newLine();
            $this->info('📋 Configuration Summary:');
            $this->table(['Setting', 'Value'], [
                ['Mode', config('tripay.mode')],
                ['API Key', substr(config('tripay.api_key'), 0, 10) . '...'],
                ['Base URI', config('tripay.mode') === 'sandbox' 
                    ? config('tripay.sandbox_base_uri') 
                    : config('tripay.production_base_uri')],
                ['Timeout', config('tripay.timeout') . 's'],
                ['Retry Attempts', config('tripay.retry')],
                ['Caching Enabled', config('tripay.cache.enabled') ? 'Yes' : 'No'],
                ['Logging Enabled', config('tripay.logging.enabled') ? 'Yes' : 'No'],
            ]);

            $this->newLine();
            $this->info('✅ Connection test completed successfully!');
            $this->info('🚀 Your Tripay integration is ready to use.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Connection test failed: ' . $e->getMessage());
            $this->newLine();
            
            // Provide troubleshooting tips
            $this->warn('💡 Troubleshooting tips:');
            $this->line('   • Check your API credentials in .env file');
            $this->line('   • Verify your internet connection');
            $this->line('   • Ensure Tripay API is accessible');
            $this->line('   • Check if you have sufficient balance for API calls');
            
            return Command::FAILURE;
        }
    }
}