<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnlockPack;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPacksController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(): View
    {
        $packs = UnlockPack::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.packs.index', compact('packs'));
    }

    public function create(): View
    {
        return view('admin.packs.form', ['pack' => new UnlockPack()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateForm($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));

        $pack = UnlockPack::create($data);
        $this->audit->log('pack.create', $pack, $data);

        return redirect()->route('admin.packs.index')->with('status', __('admin.packs.created_ok'));
    }

    public function edit(int $id): View
    {
        $pack = UnlockPack::findOrFail($id);

        return view('admin.packs.form', ['pack' => $pack]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $pack = UnlockPack::findOrFail($id);
        $data = $this->validateForm($request, $pack);

        $hasPurchases = \App\Models\Purchase::where('unlock_pack_id', $pack->id)->exists();
        if ($hasPurchases && ($request->input('slug') && $request->input('slug') !== $pack->slug)) {
            return back()->withErrors(['slug' => __('admin.packs.slug_locked')]);
        }

        $pack->update($data);
        $this->audit->log('pack.update', $pack, $data);

        return redirect()->route('admin.packs.index')->with('status', __('admin.packs.updated_ok'));
    }

    public function toggle(int $id): RedirectResponse
    {
        $pack = UnlockPack::findOrFail($id);
        $pack->forceFill(['active' => ! $pack->active])->save();

        $this->audit->log('pack.toggle', $pack, ['active' => $pack->active]);

        return back()->with('status', $pack->active ? __('admin.packs.activated_ok') : __('admin.packs.deactivated_ok'));
    }

    private function validateForm(Request $request, ?UnlockPack $existing = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'min:2', 'max:100'],
            'unlocks_count' => ['required', 'integer', 'min:1'],
            'price'     => ['required', 'integer', 'min:0'],
            'is_popular'    => ['nullable', 'boolean'],
            'sort_order'    => ['nullable', 'integer'],
            'active'        => ['nullable', 'boolean'],
        ]) + [
            'is_popular' => $request->boolean('is_popular'),
            'active'     => $request->boolean('active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
        ];
    }
}
