<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College\Course;
use App\Models\College\Student;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use App\Models\College\CourseRegistration;
use App\Models\College\CourseAssessment;
use App\Models\College\AssessmentType;

class AssessmentScoreTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $student = Student::first();
        $course = Course::first();
        $academicYear = AcademicYear::first();
        $semester = Semester::first();
        
        if (!$student || !$course || !$academicYear || !$semester) {
            $this->command->error('Missing required data: Student, Course, AcademicYear, or Semester');
            return;
        }

        // Get or create assessment type
        $assessmentType = AssessmentType::first();
        if (!$assessmentType) {
            $assessmentType = AssessmentType::create([
                'name' => 'Quiz',
                'code' => 'QUIZ',
                'description' => 'Regular quiz assessment',
                'default_weight' => 10,
                'max_score' => 20,
                'is_active' => true,
                'company_id' => $student->company_id,
            ]);
            $this->command->info('Created Assessment Type: Quiz');
        }

        // Create Course Registration (register student to course)
        $registration = CourseRegistration::firstOrCreate(
            [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ],
            [
                'program_id' => $course->program_id,
                'status' => 'registered',
                'registration_date' => now(),
                'company_id' => $student->company_id,
                'branch_id' => $student->branch_id,
            ]
        );
        
        $this->command->info("Course Registration created/found: ID {$registration->id}");

        // Create Course Assessment
        $assessment = CourseAssessment::firstOrCreate(
            [
                'course_id' => $course->id,
                'assessment_type_id' => $assessmentType->id,
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
            ],
            [
                'title' => 'Quiz 1 - ' . $course->name,
                'max_marks' => 20,
                'weight_percentage' => 10,
                'assessment_date' => now(),
                'description' => 'First quiz for ' . $course->name,
                'status' => 'published',
                'company_id' => $student->company_id,
                'branch_id' => $student->branch_id,
            ]
        );
        
        $this->command->info("Course Assessment created/found: ID {$assessment->id}");
        
        $this->command->info('Test data seeding complete!');
        $this->command->info("Student: {$student->first_name} {$student->last_name} (ID: {$student->id})");
        $this->command->info("Course: {$course->code} - {$course->name} (ID: {$course->id})");
    }
}
