<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    // ─────────────────────────────────────────────────────────
    //  DISPLAY
    // ─────────────────────────────────────────────────────────

    public function index()
    {
        $settings = [];

        if (Schema::hasTable('settings')) {
            $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        }

        return view('settings.index', compact('settings'));
    }

    // ─────────────────────────────────────────────────────────
    //  GENERAL / FARM DETAILS
    // ─────────────────────────────────────────────────────────

    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_name'    => 'required|string|max:255',
            'farm_address' => 'nullable|string',
            'farm_phone'   => 'nullable|string|max:20',
            'farm_email'   => 'nullable|email|max:255',
            'timezone'     => 'required|timezone',
            'date_format'  => 'required|string',
            'currency'     => 'required|string|size:3',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $fields = ['farm_name', 'farm_address', 'farm_phone', 'farm_email',
                   'timezone', 'date_format', 'currency'];

        foreach ($fields as $field) {
            $this->saveSetting($field, $request->$field);
        }

        return back()->with('success', 'Farm details saved.');
    }

    // ─────────────────────────────────────────────────────────
    //  ALERT THRESHOLDS
    // ─────────────────────────────────────────────────────────

    public function updateAlerts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mortality_threshold'    => 'required|numeric|min:0|max:100',
            'temperature_deviation'  => 'required|numeric|min:0|max:10',
            'ammonia_threshold'      => 'required|numeric|min:0|max:100',
            'low_feed_threshold_kg'  => 'required|numeric|min:0',
            'withdrawal_alert_days'  => 'required|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $fields = ['mortality_threshold', 'temperature_deviation',
                   'ammonia_threshold', 'low_feed_threshold_kg', 'withdrawal_alert_days'];

        foreach ($fields as $field) {
            $this->saveSetting($field, $request->$field);
        }

        return back()->with('success', 'Alert thresholds saved.');
    }

    // ─────────────────────────────────────────────────────────
    //  PRODUCTION STANDARDS
    // ─────────────────────────────────────────────────────────

    public function updateProduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'target_fcr'                => 'required|numeric|min:0|max:10',
            'target_weight_gain_g'      => 'required|numeric|min:0',
            'target_egg_production_pct' => 'required|numeric|min:0|max:100',
            'target_flock_density'      => 'required|numeric|min:0',
            'target_water_feed_ratio'   => 'required|numeric|min:0|max:10',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $fields = ['target_fcr', 'target_weight_gain_g', 'target_egg_production_pct',
                   'target_flock_density', 'target_water_feed_ratio'];

        foreach ($fields as $field) {
            $this->saveSetting($field, $request->$field);
        }

        return back()->with('success', 'Production standards saved.');
    }

    // ─────────────────────────────────────────────────────────
    //  SYSTEM ACTIONS
    // ─────────────────────────────────────────────────────────

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return back()->with('success', 'Application cache cleared.');
    }

    public function backupDatabase()
    {
        try {
            $backupPath = storage_path('backups');

            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $host     = config('database.connections.mysql.host');

            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
                escapeshellarg($host),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($filepath)) {
                return back()->with('error', 'Backup failed. Check server permissions and mysqldump availability.');
            }

            return response()->download($filepath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────
    //  PRIVATE HELPER
    // ─────────────────────────────────────────────────────────

    private function saveSetting(string $key, $value): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        DB::table('settings')->updateOrInsert(
            ['key'  => $key],
            ['value' => $value, 'updated_at' => now()]
        );

        Cache::forget('setting_' . $key);
    }
}