<?php
// app/Http/Controllers/Api/SensorController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorDevice;
use App\Models\SensorReading;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SensorController extends Controller
{
    /**
     * Ingest sensor data from IoT devices
     */
    public function ingest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'required|string|exists:sensor_devices,api_key',
            'readings' => 'required|array',
            'readings.*.value' => 'required|numeric',
            'readings.*.unit' => 'required|string|max:20',
            'readings.*.reading_time' => 'required|date'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $sensor = SensorDevice::where('api_key', $request->api_key)->first();
        
        if ($sensor->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Sensor is not active'
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $readings = [];
            $alerts = [];
            
            foreach ($request->readings as $readingData) {
                $reading = SensorReading::create([
                    'sensor_device_id' => $sensor->id,
                    'house_id' => $sensor->house_id,
                    'value' => $readingData['value'],
                    'unit' => $readingData['unit'],
                    'reading_time' => $readingData['reading_time'],
                    'is_alert' => false
                ]);
                
                // Check if reading exceeds thresholds
                $isAlert = $this->checkSensorThresholds($sensor, $reading);
                
                if ($isAlert) {
                    $reading->update(['is_alert' => true]);
                    $alerts[] = $reading;
                }
                
                $readings[] = $reading;
            }
            
            // Update sensor last reading time
            $sensor->update([
                'last_reading_at' => now()
            ]);
            
            // Create notifications for alerts
            foreach ($alerts as $alert) {
                Notification::create([
                    'user_id' => $this->getHouseManager($sensor->house_id),
                    'type' => 'sensor_alert',
                    'title' => "Sensor Alert: {$sensor->sensor_type}",
                    'message' => "{$sensor->device_name} reading of {$alert->value} {$alert->unit} exceeds threshold",
                    'severity' => 'warning',
                    'data' => json_encode([
                        'sensor_id' => $sensor->id,
                        'reading_id' => $alert->id,
                        'value' => $alert->value,
                        'unit' => $alert->unit,
                        'house_id' => $sensor->house_id
                    ])
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sensor data ingested successfully',
                'data' => [
                    'readings_count' => count($readings),
                    'alerts_count' => count($alerts),
                    'sensor_status' => $sensor->status
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to ingest sensor data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get latest sensor readings for a house
     */
    public function getLatestReadings(Request $request, $houseId)
    {
        $validator = Validator::make(['house_id' => $houseId], [
            'house_id' => 'required|exists:houses,id'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $sensors = SensorDevice::where('house_id', $houseId)
            ->where('status', 'active')
            ->with(['readings' => function($q) {
                $q->latest('reading_time')->limit(1);
            }])
            ->get();
        
        $readings = [];
        foreach ($sensors as $sensor) {
            $latestReading = $sensor->readings->first();
            $readings[] = [
                'sensor_id' => $sensor->id,
                'sensor_name' => $sensor->device_name,
                'sensor_type' => $sensor->sensor_type,
                'latest_value' => $latestReading ? $latestReading->value : null,
                'unit' => $latestReading ? $latestReading->unit : null,
                'reading_time' => $latestReading ? $latestReading->reading_time->toISOString() : null,
                'status' => $sensor->status
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $readings
        ]);
    }
    
    /**
     * Get sensor history data
     */
    public function getHistory(Request $request, $sensorId)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'interval' => 'nullable|in:hour,day,week'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $sensor = SensorDevice::find($sensorId);
        
        if (!$sensor) {
            return response()->json([
                'success' => false,
                'message' => 'Sensor not found'
            ], 404);
        }
        
        $interval = $request->interval ?? 'hour';
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        $query = SensorReading::where('sensor_device_id', $sensorId)
            ->whereBetween('reading_time', [$startDate, $endDate]);
        
        // Aggregate based on interval
        if ($interval === 'hour') {
            $query->select(
                DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H:00:00") as time'),
                DB::raw('AVG(value) as avg_value'),
                DB::raw('MIN(value) as min_value'),
                DB::raw('MAX(value) as max_value')
            )->groupBy('time');
        } elseif ($interval === 'day') {
            $query->select(
                DB::raw('DATE(reading_time) as time'),
                DB::raw('AVG(value) as avg_value'),
                DB::raw('MIN(value) as min_value'),
                DB::raw('MAX(value) as max_value')
            )->groupBy('time');
        } else {
            $query->select(
                DB::raw('YEARWEEK(reading_time) as time'),
                DB::raw('AVG(value) as avg_value'),
                DB::raw('MIN(value) as min_value'),
                DB::raw('MAX(value) as max_value')
            )->groupBy('time');
        }
        
        $history = $query->orderBy('time', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'sensor' => [
                    'id' => $sensor->id,
                    'name' => $sensor->device_name,
                    'type' => $sensor->sensor_type,
                    'unit' => $sensor->readings()->latest()->first()->unit ?? null
                ],
                'readings' => $history,
                'interval' => $interval,
                'date_range' => [
                    'start' => $startDate->toISOString(),
                    'end' => $endDate->toISOString()
                ]
            ]
        ]);
    }
    
    /**
     * Register a new sensor device
     */
    public function registerSensor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'house_id' => 'required|exists:houses,id',
            'device_name' => 'required|string|max:255',
            'sensor_type' => 'required|in:temperature,humidity,ammonia,weight,feed_level,water_flow',
            'notes' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Generate unique device ID and API key
        $deviceId = 'SEN_' . strtoupper(uniqid());
        $apiKey = 'KEY_' . bin2hex(random_bytes(32));
        
        $sensor = SensorDevice::create([
            'house_id' => $request->house_id,
            'device_id' => $deviceId,
            'device_name' => $request->device_name,
            'sensor_type' => $request->sensor_type,
            'api_key' => $apiKey,
            'status' => 'active',
            'notes' => $request->notes
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Sensor registered successfully',
            'data' => [
                'id' => $sensor->id,
                'device_id' => $sensor->device_id,
                'device_name' => $sensor->device_name,
                'api_key' => $sensor->api_key,
                'sensor_type' => $sensor->sensor_type,
                'status' => $sensor->status
            ]
        ], 201);
    }
    
    /**
     * Check if sensor reading exceeds thresholds
     */
    private function checkSensorThresholds($sensor, $reading)
    {
        // Define thresholds based on sensor type and species
        $thresholds = [
            'temperature' => [
                'min' => 18,
                'max' => 32
            ],
            'humidity' => [
                'min' => 40,
                'max' => 80
            ],
            'ammonia' => [
                'max' => 25
            ]
        ];
        
        $type = $sensor->sensor_type;
        
        if (!isset($thresholds[$type])) {
            return false;
        }
        
        $value = $reading->value;
        $threshold = $thresholds[$type];
        
        if (isset($threshold['min']) && $value < $threshold['min']) {
            return true;
        }
        
        if (isset($threshold['max']) && $value > $threshold['max']) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get house manager user ID
     */
    private function getHouseManager($houseId)
    {
        // This should return the user ID of the house manager
        // For now, return admin user
        return 1;
    }
}