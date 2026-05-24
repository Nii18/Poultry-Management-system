<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceReportExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        
        // Title
        $rows[] = ['PERFORMANCE REPORT'];
        $rows[] = ['Period: ' . $this->data['start_date']->format('Y-m-d') . ' to ' . $this->data['end_date']->format('Y-m-d')];
        $rows[] = [];
        
        // Summary Statistics
        $rows[] = ['SUMMARY STATISTICS'];
        $rows[] = ['Total Flocks', $this->data['summary']['total_flocks']];
        $rows[] = ['Total Animals', $this->data['summary']['total_animals']];
        $rows[] = ['Average Mortality Rate', number_format($this->data['summary']['avg_mortality_rate'], 2) . '%'];
        $rows[] = ['Average FCR', number_format($this->data['summary']['avg_fcr'], 2)];
        $rows[] = ['Average ADG', number_format($this->data['summary']['avg_adg'], 2)];
        $rows[] = ['Total Revenue', 'GHS ' . number_format($this->data['summary']['total_revenue'], 2)];
        $rows[] = ['Total Expenses', 'GHS ' . number_format($this->data['summary']['total_expenses'], 2)];
        $rows[] = ['Net Profit', 'GHS ' . number_format($this->data['summary']['net_profit'], 2)];
        $rows[] = ['Average ROI', number_format($this->data['summary']['avg_roi'], 2) . '%'];
        $rows[] = [];
        
        // Flock Details
        $rows[] = ['FLOCK DETAILS'];
        $rows[] = ['Flock Name', 'Species', 'Start Date', 'End Date', 'Initial Count', 'Mortality Rate', 'FCR', 'ADG', 'Revenue'];
        
        foreach ($this->data['flocks'] as $flock) {
            $rows[] = [
                $flock->name,
                $flock->species->name ?? 'N/A',
                $flock->start_date,
                $flock->end_date,
                $flock->initial_count,
                number_format($flock->mortality_rate, 2) . '%',
                number_format($flock->feed_conversion_ratio, 2),
                number_format($flock->average_daily_gain, 2),
                'GHS ' . number_format($flock->total_revenue, 2)
            ];
        }
        
        $rows[] = [];
        
        // Species Breakdown
        $rows[] = ['SPECIES BREAKDOWN'];
        $rows[] = ['Species', 'Number of Flocks', 'Total Animals', 'Average FCR'];
        
        foreach ($this->data['species_breakdown'] as $species => $data) {
            $rows[] = [
                $species,
                $data['count'],
                $data['total_animals'],
                number_format($data['avg_fcr'], 2)
            ];
        }
        
        $rows[] = [];
        
        // Daily Trends
        $rows[] = ['DAILY TRENDS'];
        $rows[] = ['Date', 'Total Mortality', 'Total Feed (kg)', 'Average Weight (kg)'];
        
        foreach ($this->data['daily_trends'] as $trend) {
            $rows[] = [
                $trend->date,
                $trend->total_mortality,
                number_format($trend->total_feed, 2),
                number_format($trend->avg_weight, 2)
            ];
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
            15 => ['font' => ['bold' => true, 'size' => 12]],
            21 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}