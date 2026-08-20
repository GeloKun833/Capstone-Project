<?php

namespace App\Http\Controllers;

use App\Services\StudentSisService;
use Illuminate\Http\Request;

class SisHubController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin|Registrar|Teacher']);
    }

    public function index(Request $request, StudentSisService $sisService)
    {
        $query = trim((string) $request->get('q', ''));
        $type = $request->get('type', 'students');

        $students = collect();
        $teachers = collect();

        if ($query !== '') {
            if ($type === 'teachers') {
                $teachers = $sisService->searchTeachers($query);
            } else {
                $students = $sisService->searchStudents($query);
            }
        }

        return view('sis.hub', compact('query', 'type', 'students', 'teachers'));
    }
}
