<?php namespace App\Http\Controllers\Hive;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $payslips = $user->hasAnyRole(['hr_staff', 'admin'])
            ? Payslip::with('user')->latest()->get()
            : $user->payslips()->latest()->get();

        return Inertia::render('Hive/Payslips/Index', ['payslips' => $payslips]);
    }

    public function create()
    {
        $this->authorize('create', Payslip::class);
        $users = User::role(['instructor','hr_staff','admin'])->get(); // staff only
        return Inertia::render('Hive/Payslips/Upload', ['users' => $users]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Payslip::class);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|date_format:Y-m',
            'file' => 'required|file|mimes:pdf|max:2048',
        ]);
        $filePath = $request->file('file')->store('private/payslips');

        Payslip::create([
            'user_id' => $data['user_id'],
            'month' => $data['month'],
            'file_path' => $filePath,
            'uploaded_by' => $request->user()->id,
        ]);
        return redirect()->route('hive.payslips.index')->with('success', 'Payslip uploaded.');
    }

    public function download(Payslip $payslip)
    {
        $this->authorize('view', $payslip);
        return Storage::download($payslip->file_path, 'Payslip-'.$payslip->month.'.pdf');
    }
}