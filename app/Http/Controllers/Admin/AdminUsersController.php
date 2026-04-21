<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminUsersController extends Controller
{
    public function index(Request $request): View
    {
        $role = (string) $request->query('role', 'all');
        $search = (string) $request->query('q', '');

        $query = User::with('roles');
        if ($role !== 'all') {
            $query->role($role);
        }
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $users = $query->latest('id')->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users', 'role', 'search'));
    }

    public function show(int $id): View
    {
        $user = User::with(['roles', 'providerProfile'])->find($id);
        if (! $user) {
            throw new NotFoundHttpException();
        }

        return view('admin.users.show', compact('user'));
    }
}
