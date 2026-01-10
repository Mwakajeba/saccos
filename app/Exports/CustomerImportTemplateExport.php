<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CustomerImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $includeSampleData;

    public function __construct($includeSampleData = true)
    {
        $this->includeSampleData = $includeSampleData;
    }

    public function headings(): array
    {
        return [
            'name',
            'phone1',
            'phone2',
            'sex',
            'dob',
            'region_id',
            'district_id',
            'work',
            'workAddress',
            'idType',
            'idNumber',
            'relation',
            'description',
            'reference',
        ];
    }

    public function array(): array
    {
        $data = [];

        if ($this->includeSampleData) {
            // Generate 100 sample customers
            $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'James', 'Mary', 'Robert', 'Patricia', 'William', 'Jennifer', 'Richard', 'Linda', 'Joseph', 'Elizabeth', 'Thomas', 'Barbara', 'Charles', 'Susan', 'Daniel', 'Jessica', 'Matthew', 'Sarah', 'Anthony', 'Karen', 'Mark', 'Nancy', 'Donald', 'Lisa', 'Steven', 'Betty', 'Paul', 'Margaret', 'Andrew', 'Sandra', 'Joshua', 'Ashley', 'Kenneth', 'Kimberly', 'Kevin', 'Emily', 'Brian', 'Donna', 'George', 'Michelle', 'Edward', 'Carol', 'Ronald', 'Amanda'];
            $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell', 'Carter', 'Roberts', 'Gomez', 'Phillips'];
            $regions = [1, 2, 3, 4, 5]; // Sample region IDs
            $districts = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]; // Sample district IDs
            $idTypes = ['National ID', 'License', 'Voter Registration', 'Other'];
            $jobs = ['Teacher', 'Farmer', 'Business Owner', 'Nurse', 'Engineer', 'Accountant', 'Driver', 'Mechanic', 'Tailor', 'Shopkeeper'];

            for ($i = 1; $i <= 100; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $name = $firstName . ' ' . $lastName;
                $sex = ['M', 'F'][array_rand(['M', 'F'])];
                $phone1 = '712' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $phone2 = rand(0, 1) ? '713' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) : '';
                $dob = date('Y-m-d', strtotime('-' . rand(18, 70) . ' years'));
                $regionId = $regions[array_rand($regions)];
                $districtId = $districts[array_rand($districts)];
                $work = $jobs[array_rand($jobs)];
                $workAddress = 'Address ' . $i;
                $idType = $idTypes[array_rand($idTypes)];
                $idNumber = 'ID' . str_pad($i, 8, '0', STR_PAD_LEFT);
                $relation = rand(0, 1) ? 'Spouse' : '';
                $description = 'Sample customer ' . $i;
                $reference = 'REF' . str_pad($i, 6, '0', STR_PAD_LEFT);

                $data[] = [
                    $name,
                    $phone1,
                    $phone2,
                    $sex,
                    $dob,
                    $regionId,
                    $districtId,
                    $work,
                    $workAddress,
                    $idType,
                    $idNumber,
                    $relation,
                    $description,
                    $reference,
                ];
            }
        }

        return $data;
    }


    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Apply data validation to sex column
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Add data validation for sex column (column D)
                for ($row = 2; $row <= $highestRow; $row++) {
                    $validation = $sheet->getCell("D{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"M,F"');
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setErrorTitle('Invalid Value');
                    $validation->setError('Sex must be either M or F');
                    $validation->setShowDropDown(true);
                    $sheet->getCell("D{$row}")->setDataValidation($validation);
                }
            },
        ];
    }
}

