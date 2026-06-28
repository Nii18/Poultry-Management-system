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
    //  GENERAL
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

        return back()->with('success', 'General settings saved.');
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
    //  NOTIFICATIONS
    // ─────────────────────────────────────────────────────────

    public function updateNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notif_email_recipients'  => 'nullable|string',
            'notif_sms_recipients'    => 'nullable|string',
            'notif_severity_filter'   => 'required|in:all,high,critical',
            'notif_quiet_start'       => 'nullable|date_format:H:i',
            'notif_quiet_end'         => 'nullable|date_format:H:i',
            'notif_channels'          => 'nullable|array',
            'notif_channels.*'        => 'in:email,sms,whatsapp',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->saveSetting('notif_email_recipients', $request->notif_email_recipients);
        $this->saveSetting('notif_sms_recipients',   $request->notif_sms_recipients);
        $this->saveSetting('notif_severity_filter',  $request->notif_severity_filter);
        $this->saveSetting('notif_quiet_start',      $request->notif_quiet_start);
        $this->saveSetting('notif_quiet_end',        $request->notif_quiet_end);
        $this->saveSetting('notif_channels',         json_encode($request->notif_channels ?? []));

        return back()->with('success', 'Notification preferences saved.');
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
    //  USER & SECURITY
    // ─────────────────────────────────────────────────────────

    public function updateSecurity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_user_role'       => 'required|in:worker,head_worker,veterinarian,accountant,manager',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
            'max_failed_logins'       => 'required|integer|min:3|max:20',
            'password_expiry_days'    => 'required|integer|min:0|max:365',
            'require_2fa'             => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->saveSetting('default_user_role',       $request->default_user_role);
        $this->saveSetting('session_timeout_minutes', $request->session_timeout_minutes);
        $this->saveSetting('max_failed_logins',       $request->max_failed_logins);
        $this->saveSetting('password_expiry_days',    $request->password_expiry_days);
        $this->saveSetting('require_2fa',             $request->boolean('require_2fa') ? '1' : '0');

        return back()->with('success', 'Security settings saved.');
    }

    // ─────────────────────────────────────────────────────────
    //  REPORTING
    // ─────────────────────────────────────────────────────────

    public function updateReporting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_auto_send'        => 'nullable|boolean',
            'report_frequency'        => 'required|in:daily,weekly,monthly',
            'report_recipients'       => 'nullable|string',
            'report_export_format'    => 'required|in:pdf,excel,both',
            'report_fiscal_month'     => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->saveSetting('report_auto_send',     $request->boolean('report_auto_send') ? '1' : '0');
        $this->saveSetting('report_frequency',     $request->report_frequency);
        $this->saveSetting('report_recipients',    $request->report_recipients);
        $this->saveSetting('report_export_format', $request->report_export_format);
        $this->saveSetting('report_fiscal_month',  $request->report_fiscal_month);

        return back()->with('success', 'Reporting settings saved.');
    }

    // ─────────────────────────────────────────────────────────
    //  INTEGRATIONS
    // ─────────────────────────────────────────────────────────

    public function updateIntegrations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smtp_host'         => 'nullable|string|max:255',
            'smtp_port'         => 'nullable|integer|min:1|max:65535',
            'smtp_username'     => 'nullable|string|max:255',
            'smtp_password'     => 'nullable|string|max:255',
            'smtp_from_name'    => 'nullable|string|max:255',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_encryption'   => 'nullable|in:tls,ssl,none',
            'twilio_sid'        => 'nullable|string|max:255',
            'twilio_token'      => 'nullable|string|max:255',
            'twilio_from'       => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $fields = [
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_from_name', 'smtp_from_address', 'smtp_encryption',
            'twilio_sid', 'twilio_token', 'twilio_from',
        ];

        foreach ($fields as $field) {
            // Don't overwrite passwords with empty string if left blank
            if (in_array($field, ['smtp_password', 'twilio_token']) && $request->$field === null) {
                continue;
            }
            $this->saveSetting($field, $request->$field);
        }

        return back()->with('success', 'Integration settings saved.');
    }

    // ─────────────────────────────────────────────────────────
    //  AUDIT & COMPLIANCE
    // ─────────────────────────────────────────────────────────

    public function updateAudit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audit_log_retention_days'   => 'required|integer|min:30|max:3650',
            'vet_cert_expiry_alert_days'  => 'required|integer|min:1|max:90',
            'roles_can_delete_records'    => 'nullable|array',
            'roles_can_delete_records.*'  => 'in:admin,manager',
            'roles_can_edit_past_records' => 'nullable|array',
            'roles_can_edit_past_records.*' => 'in:admin,manager,head_worker',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->saveSetting('audit_log_retention_days',    $request->audit_log_retention_days);
        $this->saveSetting('vet_cert_expiry_alert_days',  $request->vet_cert_expiry_alert_days);
        $this->saveSetting('roles_can_delete_records',    json_encode($request->roles_can_delete_records ?? []));
        $this->saveSetting('roles_can_edit_past_records', json_encode($request->roles_can_edit_past_records ?? []));

        return back()->with('success', 'Audit & compliance settings saved.');
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
    //  TEST CONNECTIONS
    // ─────────────────────────────────────────────────────────

    public function testEmail(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email from your Poultry Management System.',
                function ($message) use ($request) {
                    $message->to($request->user()->email)
                            ->subject('Test Email — Poultry Management System');
                }
            );
            return back()->with('success', 'Test email sent to ' . $request->user()->email);
        } catch (\Exception $e) {
            return back()->with('error', 'Email test failed: ' . $e->getMessage());
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