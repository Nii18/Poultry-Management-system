<?php
// app/Http/Controllers/SettingsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        // Initialize empty settings array
        $settings = [];
        
        // Check if settings table exists
        if (Schema::hasTable('settings')) {
            // Retrieve all settings from database
            $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        }
        
        return view('settings.index', compact('settings'));
    }
    
    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'farm_name' => 'required|string|max:255',
            'farm_address' => 'nullable|string',
            'farm_phone' => 'nullable|string|max:20',
            'farm_email' => 'nullable|email|max:255',
            'timezone' => 'required|timezone',
            'date_format' => 'required|string',
            'currency' => 'required|string|size:3'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Save settings to database
        $this->saveSetting('farm_name', $request->farm_name);
        $this->saveSetting('farm_address', $request->farm_address);
        $this->saveSetting('farm_phone', $request->farm_phone);
        $this->saveSetting('farm_email', $request->farm_email);
        $this->saveSetting('timezone', $request->timezone);
        $this->saveSetting('date_format', $request->date_format);
        $this->saveSetting('currency', $request->currency);
        
        // Note: app_name is from config, not stored in settings table
        // To change app_name, you'd need to modify .env file
        
        return back()->with('success', 'General settings updated successfully');
    }
    
    /**
     * Update alert thresholds
     */
    public function updateAlerts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mortality_threshold' => 'required|numeric|min:0|max:100',
            'temperature_deviation' => 'required|numeric|min:0|max:10',
            'ammonia_threshold' => 'required|numeric|min:0|max:100',
            'low_feed_threshold_kg' => 'required|numeric|min:0',
            'withdrawal_alert_days' => 'required|integer|min:1|max:30'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $this->saveSetting('mortality_threshold', $request->mortality_threshold);
        $this->saveSetting('temperature_deviation', $request->temperature_deviation);
        $this->saveSetting('ammonia_threshold', $request->ammonia_threshold);
        $this->saveSetting('low_feed_threshold_kg', $request->low_feed_threshold_kg);
        $this->saveSetting('withdrawal_alert_days', $request->withdrawal_alert_days);
        
        return back()->with('success', 'Alert settings updated successfully');
    }
    
    /**
     * Clear application cache
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        
        return back()->with('success', 'Application cache cleared successfully');
    }
    
    /**
     * Run database backup
     */
    public function backupDatabase()
    {
        try {
            $backupPath = storage_path('backups');
            
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupPath . '/' . $filename;
            
            // Get database credentials from config
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host = config('database.connections.mysql.host');
            
            // Create backup command (requires mysqldump)
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );
            
            exec($command);
            
            return response()->download($filepath)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Save setting to database
     */
    private function saveSetting($key, $value)
    {
        // Check if settings table exists, if not create it
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
        
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
        
        Cache::forget('setting_' . $key);
    }
}