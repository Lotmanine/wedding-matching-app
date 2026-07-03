<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\Wilaya;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminUsersController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

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

    public function create(): View
    {
        return view('admin.users.create', [
            'wilayas' => Wilaya::active()->get(),
        ]);
    }

    /**
     * Create a back-office user (admin / manager) or a provider account.
     * Admin-created providers are trusted: active + verified immediately;
     * they complete their public profile themselves afterwards.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'min:2', 'max:100'],
            'email'               => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'                => ['required', 'in:admin,manager,provider'],
            'password'            => ['nullable', 'string', 'min:10', 'max:100'],
            'phone'               => ['nullable', 'string', 'regex:/^\+213[567]\d{8}$/'],
            // Provider-only fields
            'business_name'       => ['required_if:role,provider', 'nullable', 'string', 'max:150'],
            'primary_wilaya_code' => ['required_if:role,provider', 'nullable', 'in:' . Wilaya::pluck('code')->implode(',')],
        ]);

        // Auto-generate a strong password when none is provided; it is shown
        // once in the flash message so the admin can pass it on.
        $generated = null;
        $password = $validated['password'] ?? null;
        if (blank($password)) {
            $generated = Str::password(14);
            $password = $generated;
        }

        $user = DB::transaction(function () use ($validated, $password) {
            $user = User::create([
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => Hash::make($password),
                'role'              => $validated['role'],
                'status'            => 'active',
                'phone'             => $validated['phone'] ?? null,
                'whatsapp'          => $validated['phone'] ?? null,
                'email_verified_at' => now(),
            ]);
            $user->assignRole($validated['role']);

            if ($validated['role'] === 'provider') {
                ProviderProfile::create([
                    'user_id'             => $user->id,
                    'business_name'       => $validated['business_name'] ?: $validated['name'],
                    'primary_wilaya_code' => $validated['primary_wilaya_code'],
                    'price_tier'          => 'mid',
                    'verified'            => true,
                    'verified_at'         => now(),
                    'credit_balance'      => 0,
                ]);
            }

            return $user;
        });

        $this->audit->log('user.create', $user, [
            'role'  => $validated['role'],
            'email' => $validated['email'],
        ]);

        $message = "Utilisateur {$user->email} créé ({$validated['role']}).";
        if ($generated !== null) {
            $message .= " Mot de passe généré : {$generated} — transmettez-le maintenant, il ne sera plus affiché.";
        }

        return redirect()->route('admin.users.show', $user->id)->with('status', $message);
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
