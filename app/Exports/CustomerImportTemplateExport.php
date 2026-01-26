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
use App\Models\Region;
use App\Models\District;

class CustomerImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $includeSampleData;
    protected $regions;
    protected $districts;

    public function __construct($includeSampleData = true)
    {
        $this->includeSampleData = $includeSampleData;
        $this->regions = Region::orderBy('name')->get();
        $this->districts = District::orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'name',
            'phone1',
            'phone2',
            'email',
            'sex',
            'dob',
            'marital_status',
            'region',
            'district',
            'street',
            'employment_status',
            'work',
            'workAddress',
            'idType',
            'idNumber',
            'category',
            'relation',
            'number_of_spouse',
            'number_of_children',
            'monthly_income',
            'monthly_expenses',
            'bank_name',
            'bank_account',
            'bank_account_name',
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
            
            $maritalStatuses = ['Single', 'Married', 'Divorced', 'Widowed'];
            $employmentStatuses = ['Employed', 'Self Employed', 'Unemployed', 'Student', 'Retired'];
            $idTypes = ['National ID', 'License', 'Voter Registration', 'Passport', 'Other'];
            $categories = ['Member', 'Borrower', 'Guarantor'];
            $banks = ['CRDB Bank', 'NMB Bank', 'NBC Bank', 'Equity Bank'];
            $jobs = ['Teacher', 'Farmer', 'Business Owner', 'Nurse', 'Engineer', 'Accountant', 'Driver', 'Mechanic', 'Tailor', 'Shopkeeper'];

            for ($i = 1; $i <= 100; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $name = $firstName . ' ' . $lastName;
                $sex = ['M', 'F'][array_rand(['M', 'F'])];
                $phone1 = '712' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $phone2 = rand(0, 1) ? '713' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT) : '';
                $email = strtolower($firstName . '.' . $lastName . '@example.com');
                $dob = date('Y-m-d', strtotime('-' . rand(18, 70) . ' years'));
                $maritalStatus = $maritalStatuses[array_rand($maritalStatuses)];
                
                $region = $this->regions->random();
                $regionDistricts = $this->districts->where('region_id', $region->id);
                $district = $regionDistricts->isNotEmpty() ? $regionDistricts->random() : $this->districts->first();
                
                $street = 'Street ' . $i . ', Block ' . chr(65 + ($i % 26));
                $employmentStatus = $employmentStatuses[array_rand($employmentStatuses)];
                $work = $jobs[array_rand($jobs)];
                $workAddress = 'Work Address ' . $i;
                $idType = $idTypes[array_rand($idTypes)];
                $idNumber = 'ID' . str_pad($i, 8, '0', STR_PAD_LEFT);
                $category = $categories[array_rand($categories)];
                $relation = rand(0, 1) ? 'Spouse' : '';
                $numberOfSpouse = $maritalStatus === 'Married' ? rand(0, 1) : 0;
                $numberOfChildren = rand(0, 5);
                $monthlyIncome = rand(500000, 5000000);
                $monthlyExpenses = rand(300000, $monthlyIncome);
                $bankName = rand(0, 1) ? $banks[array_rand($banks)] : '';
                $bankAccount = $bankName ? str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT) : '';
                $bankAccountName = $bankName ? $name : '';
                $description = 'Sample customer ' . $i;
                $reference = 'REF' . str_pad($i, 6, '0', STR_PAD_LEFT);

                $data[] = [
                    $name,
                    $phone1,
                    $phone2,
                    $email,
                    $sex,
                    $dob,
                    $maritalStatus,
                    $region->name,
                    $district->name,
                    $street,
                    $employmentStatus,
                    $work,
                    $workAddress,
                    $idType,
                    $idNumber,
                    $category,
                    $relation,
                    $numberOfSpouse,
                    $numberOfChildren,
                    $monthlyIncome,
                    $monthlyExpenses,
                    $bankName,
                    $bankAccount,
                    $bankAccountName,
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
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Create hidden sheets for dropdowns
                $workbook = $sheet->getParent();
                
                // Create Regions sheet
                $regionsSheet = new Worksheet($workbook, 'Regions');
                $workbook->addSheet($regionsSheet);
                $regionNames = $this->regions->pluck('name')->toArray();
                foreach ($regionNames as $index => $regionName) {
                    $regionsSheet->setCellValue('A' . ($index + 1), $regionName);
                }
                $regionsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
                
                // Create Districts sheet with region mapping
                $districtsSheet = new Worksheet($workbook, 'Districts');
                $workbook->addSheet($districtsSheet);
                $districtNames = $this->districts->pluck('name')->unique()->values()->toArray();
                foreach ($districtNames as $index => $districtName) {
                    $districtsSheet->setCellValue('A' . ($index + 1), $districtName);
                }
                $districtsSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
                
                for ($row = 2; $row <= max($highestRow, 102); $row++) {
                    // Sex validation (Column E)
                    $validation = $sheet->getCell("E{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"M,F"');
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Invalid Value');
                    $validation->setError('Sex must be either M or F');
                    $validation->setShowDropDown(true);
                    
                    // Marital Status validation (Column G)
                    $validation = $sheet->getCell("G{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"Single,Married,Divorced,Widowed"');
                    $validation->setShowDropDown(true);
                    
                    // Region validation (Column H)
                    $validation = $sheet->getCell("H{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('=Regions!$A$1:$A$' . count($regionNames));
                    $validation->setShowDropDown(true);
                    
                    // District validation (Column I)
                    $validation = $sheet->getCell("I{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('=Districts!$A$1:$A$' . count($districtNames));
                    $validation->setShowDropDown(true);
                    
                    // Employment Status validation (Column K)
                    $validation = $sheet->getCell("K{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"Employed,Self Employed,Unemployed,Student,Retired"');
                    $validation->setShowDropDown(true);
                    
                    // ID Type validation (Column N)
                    $validation = $sheet->getCell("N{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"National ID,License,Voter Registration,Passport,Other"');
                    $validation->setShowDropDown(true);
                    
                    // Category validation (Column P)
                    $validation = $sheet->getCell("P{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"Member,Borrower,Guarantor"');
                    $validation->setShowDropDown(true);
                    
                    // Bank Name validation (Column V)
                    $validation = $sheet->getCell("V{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"CRDB Bank,NMB Bank,NBC Bank,TPB Bank,Equity Bank,Exim Bank,Stanbic Bank,Standard Chartered Bank,Bank of Africa,DTB Bank,Access Bank,Azania Bank,Bank of Baroda,Bank of India,Citibank,Ecobank,GTBank,I&M Bank,KCB Bank,Mwalimu Commercial Bank,PBZ Bank,UBA Bank,Absa Bank,Amana Bank,Other"');
                    $validation->setShowDropDown(true);
                }
            },
        ];
    }
}

