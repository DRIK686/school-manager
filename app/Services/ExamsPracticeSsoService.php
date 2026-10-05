<?php
namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Str;

class ExamsPracticeSsoService
{
    /**
     * Build a signed, short-lived SSO launch URL for a student.
     * ExamsPractice verifies the signature and expiry, then matches
     * the student by (tenant, student_id) — creating the account
     * on first use if it doesn't exist yet.
     */
    public function launchUrl(Student $student): string
    {
        $payload = [
            'student_id' => $student->admission_no,
            'name'       => $student->full_name,
            'class'      => $student->schoolClass?->name,
            'tenant'     => config('services.exams_practice.tenant'),
            'iat'        => time(),
            'exp'        => time() + 90, // 90 second window
            'nonce'      => Str::random(16),
        ];

        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $payloadEncoded, config('services.exams_practice.sso_secret'));
        $token = $payloadEncoded . '.' . $signature;

        return config('services.exams_practice.sso_url') . '?token=' . urlencode($token);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
