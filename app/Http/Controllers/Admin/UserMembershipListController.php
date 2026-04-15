<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UserMembershipListController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
               if (! Schema::hasTable('user_memberships')) {
            $memberships = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25,1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

            return view('admin.user-memberships.index', compact('memberships'))
                ->with('error', 'جدول اشتراكات العضوية غير متوفر.');
        }

        $memberships = UserMembership::query()
            ->with(['user', 'membershipType'])
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.user-memberships.index', compact('memberships'));
    }
}
