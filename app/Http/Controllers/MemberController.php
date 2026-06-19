<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('participant', 'like', "%{$search}%")
                  ->orWhere('email_address', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($filter = $request->filter) {
            if ($filter === 'executive') $query->where('is_executive', true);
            elseif ($filter === 'member') $query->where('status', 'Member');
            elseif ($filter === 'non_member') $query->where('status', '!=', 'Member');
            elseif ($filter === 'no_password') $query->where('password_set', false);
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(10);
        $totalMembers = Member::count();
        $executiveCount = Member::where('is_executive', true)->count();
        $memberCount = Member::where('status', 'Member')->count();
        $nonMemberCount = Member::where('status', '!=', 'Member')->count();

        return view('admin.members.index', compact(
            'members', 'totalMembers', 'executiveCount', 'memberCount', 'nonMemberCount'
        ));
    }

    public function create()
    {
        return view('admin.members.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'nullable|string|max:50|unique:members,member_id',
            'participant' => 'required|string|max:255',
            'email_address' => 'required|email|max:255|unique:members,email_address',
            'phone_number' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'status' => 'sometimes|string|in:Member,Non-Member',
            'is_executive' => 'sometimes|boolean',
            'credit' => 'nullable|numeric|min:0',
            'debt' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Member::create([
            'member_id' => $request->member_id,
            'participant' => $request->participant,
            'email_address' => $request->email_address,
            'phone_number' => $request->phone_number,
            'company_name' => $request->company_name,
            'status' => $request->status ?? 'Member',
            'is_executive' => $request->boolean('is_executive'),
            'credit' => $request->credit ?? 0,
            'debt' => $request->debt ?? 0,
            'password_set' => false,
        ]);

        return redirect()->route('admin.members.index')
            ->with('success', 'Member added successfully.');
    }

    public function edit(Member $member)
    {
        return view('admin.members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'nullable|string|max:50|unique:members,member_id,' . $member->id,
            'participant' => 'required|string|max:255',
            'email_address' => 'required|email|max:255|unique:members,email_address,' . $member->id,
            'phone_number' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'status' => 'sometimes|string|in:Member,Non-Member',
            'is_executive' => 'sometimes|boolean',
            'credit' => 'nullable|numeric|min:0',
            'debt' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $member->update([
            'member_id' => $request->member_id,
            'participant' => $request->participant,
            'email_address' => $request->email_address,
            'phone_number' => $request->phone_number,
            'company_name' => $request->company_name,
            'status' => $request->status ?? $member->status,
            'is_executive' => $request->boolean('is_executive'),
            'credit' => $request->credit ?? 0,
            'debt' => $request->debt ?? 0,
        ]);

        return redirect()->route('admin.members.index')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('admin.members.index')
            ->with('success', 'Member deleted successfully.');
    }

    public function importForm()
    {
        return view('admin.members.import');
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xls,xlsx,csv',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $imported = 0;
            $skipped = 0;
            $memberIdErrors = [];
            $validationErrors = [];
            $emailErrors = [];
            $rows = Excel::toArray([], $request->file('excel_file'));

            if (empty($rows) || empty($rows[0])) {
                return back()->with('error', 'Excel file is empty.');
            }

            $headers = array_map('strtolower', $rows[0][0]);
            $expectedHeaders = ['member_id', 'name', 'email', 'phone', 'company', 'is_executive'];

            $headerMap = [];
            foreach ($expectedHeaders as $expected) {
                $key = array_search($expected, $headers);
                if ($key !== false) {
                    $headerMap[$expected] = $key;
                }
            }

            if (!isset($headerMap['name']) || !isset($headerMap['email'])) {
                return back()->with('error', 'Excel must have at least "name" and "email" columns.');
            }

            $seenMemberIds = [];
            foreach ($rows[0] as $rowIndex => $row) {
                if ($rowIndex === 0) continue;

                $name = trim($row[$headerMap['name']] ?? '');
                $email = trim($row[$headerMap['email']] ?? '');

                if (empty($name) || empty($email)) {
                    $validationErrors[] = "Row " . ($rowIndex + 1) . ": name and email are required.";
                    continue;
                }

                $memberId = isset($headerMap['member_id']) ? trim($row[$headerMap['member_id']] ?? '') : null;
                $phone = isset($headerMap['phone']) ? trim($row[$headerMap['phone']] ?? '') : null;
                $company = isset($headerMap['company']) ? trim($row[$headerMap['company']] ?? '') : null;
                $isExec = isset($headerMap['is_executive']) ? strtolower(trim($row[$headerMap['is_executive']] ?? '')) : '';

                if (!empty($memberId)) {
                    if (in_array($memberId, $seenMemberIds)) {
                        $skipped++;
                        $memberIdErrors[] = "Row " . ($rowIndex + 1) . ": member_id '{$memberId}' appears more than once in the file — skipped.";
                        continue;
                    }
                    $seenMemberIds[] = $memberId;

                    $memberIdExists = Member::where('member_id', $memberId)->whereNotNull('member_id')->first();
                    if ($memberIdExists) {
                        $skipped++;
                        $memberIdErrors[] = "Row " . ($rowIndex + 1) . ": member_id '{$memberId}' already exists — skipped.";
                        continue;
                    }
                }

                $exists = Member::where('email_address', $email)->first();
                if ($exists) {
                    $skipped++;
                    $emailErrors[] = "Row " . ($rowIndex + 1) . ": email '{$email}' already exists — skipped.";
                    continue;
                }

                Member::create([
                    'member_id' => $memberId,
                    'participant' => $name,
                    'email_address' => $email,
                    'phone_number' => $phone,
                    'company_name' => $company,
                    'status' => 'Member',
                    'is_executive' => in_array($isExec, ['yes', 'true', '1', 'on']),
                    'password_set' => false,
                ]);
                $imported++;
            }

            $errors = array_merge($memberIdErrors, $validationErrors, $emailErrors);
            $message = "Imported {$imported} new members.";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} duplicate(s).";
            }
            if (!empty($errors)) {
                $message .= " Details: " . implode('; ', $errors);
            }

            return redirect()->route('admin.members.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = ['member_id', 'name', 'email', 'phone', 'company', 'is_executive'];
        $sampleData = [
            ['IIA-001', 'John Doe', 'john@example.com', '0999123456', 'ACME Corp', 'No'],
            ['IIA-002', 'Jane Smith', 'jane@example.com', '0999123457', '', 'Yes'],
        ];

        return Excel::download(
            new \App\Exports\ArrayExport($headers, $sampleData),
            'member_import_template.xlsx'
        );
    }
}
