<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralReward;
use App\Models\Referrer;
use App\Models\Vehicle;
use App\Support\Referral;
use Illuminate\Http\Request;

/** Recommendations in the panel: links, what they brought, and what is owed. */
class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $referrers = Referrer::query()
            ->withCount([
                'opens',
                'visitors',
                'visitors as pressed_count' => fn ($q) => $q->whereHas('events', fn ($e) => $e->whereIn('type', ['whatsapp', 'email'])),
                'referredVehicles as sales_count' => fn ($q) => $q->where('status', 'sold'),
            ])
            ->with('vehicle:id,slug,brand,model,year')
            ->latest()
            ->get();

        $rewards = ReferralReward::query()
            ->with(['referrer:id,name,code,phone', 'vehicle:id,slug,brand,model,year,sold_date'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 WHEN 'paid' THEN 2 ELSE 3 END")
            ->latest()
            ->get();

        return view('admin.referrals.index', [
            'referrers' => $referrers,
            'rewards'   => $rewards,
            'fresh'     => (int) $request->get('nuevo'),
        ]);
    }

    /** One link: who opened it, and what each of those people did. */
    public function show(Referrer $referrer)
    {
        $referrer->loadCount([
            'opens',
            'visitors',
            'referredVehicles as sales_count' => fn ($q) => $q->where('status', 'sold'),
        ])->load('vehicle:id,slug,brand,model,year');

        $visitors = $referrer->visitors()
            ->with(['events' => fn ($q) => $q->orderBy('created_at')->orderBy('id')
                ->with(['vehicle:id,slug,brand,model,year', 'referrer:id,name'])])
            ->orderByDesc('last_seen_at')
            ->get();

        $sawCars = $visitors->filter(fn ($v) => $v->events->contains('type', 'car'))->count();
        $pressed = $visitors->filter(fn ($v) => $v->events->contains(fn ($e) => in_array($e->type, ['whatsapp', 'email'], true)))->count();

        $cars = $visitors->flatMap->events
            ->where('type', 'car')
            ->filter(fn ($e) => $e->vehicle)
            ->groupBy('vehicle_id')
            ->map(fn ($g) => [
                'vehicle' => $g->first()->vehicle,
                'views'   => $g->count(),
                'people'  => $g->pluck('visitor_id')->unique()->count(),
            ])
            ->sortByDesc('views')
            ->values();

        return view('admin.referrals.show', compact('referrer', 'visitors', 'sawCars', 'pressed', 'cars'));
    }

    /** The explanation, as a page: where "Cómo funciona" goes when the popup cannot open. */
    public function how()
    {
        return view('admin.referrals.how');
    }

    public function create(Request $request)
    {
        $sold = Vehicle::query()
            ->where('status', 'sold')
            ->orderByDesc('sold_date')->orderByDesc('updated_at')
            ->get(['id', 'slug', 'brand', 'model', 'year', 'buyer_name', 'buyer_phone']);

        return view('admin.referrals.create', [
            'sold' => $sold,
            'pick' => $request->filled('coche') ? $sold->firstWhere('slug', $request->get('coche')) : null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:120'],
            'phone'      => ['required', 'string', 'max:30', 'regex:/^[+\d][\d\s().-]{6,}$/'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
        ], [
            'phone.regex' => 'Ese teléfono no parece un teléfono.',
        ]);

        $phone = Referral::normalizePhone($data['phone']);
        $referrer = Referrer::where('phone', $phone)->first();

        if ($referrer) {
            if (! $referrer->vehicle_id && ! empty($data['vehicle_id'])) {
                $referrer->update(['vehicle_id' => $data['vehicle_id']]);
            }
            $said = 'Ese teléfono ya tenía un enlace; es el mismo.';
        } else {
            $referrer = Referrer::create([
                'code'       => Referral::makeCode($data['name']),
                'name'       => trim($data['name']),
                'phone'      => $phone,
                'source'     => 'admin',
                'vehicle_id' => $data['vehicle_id'] ?? null,
            ]);
            $said = 'Enlace creado.';
        }

        // The buyer's name and phone were never recorded on the 32 cars sold so
        // far. Recording them here also lets the rule "not for your own purchase"
        // see the buyer's phone.
        if (! empty($data['vehicle_id'])) {
            $vehicle = Vehicle::find($data['vehicle_id']);
            if ($vehicle && blank($vehicle->buyer_phone)) {
                $vehicle->buyer_name = $vehicle->buyer_name ?: $data['name'];
                $vehicle->buyer_phone = $data['phone'];
                $vehicle->save();
            }
        }

        return redirect()
            ->route('admin.referrals.index', ['nuevo' => $referrer->id])
            ->with('status', $said . ' Envíaselo por WhatsApp desde su fila.');
    }

    public function reward(Request $request, ReferralReward $reward)
    {
        $to = $request->validate([
            'status' => ['required', 'in:pending,approved,paid,rejected'],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        $patch = ['status' => $to['status']];
        if ($to['status'] === ReferralReward::APPROVED && ! $reward->approved_at) {
            $patch['approved_at'] = now();
        }
        if ($to['status'] === ReferralReward::PAID) {
            $patch['paid_at'] = now();
            $patch['approved_at'] = $reward->approved_at ?? now();
        }
        if (array_key_exists('note', $to) && $to['note'] !== null) {
            $patch['note'] = $to['note'];
        }
        $reward->update($patch);

        return back()->with('status', match ($to['status']) {
            'approved' => 'Premio aprobado.',
            'paid'     => 'Premio marcado como pagado.',
            'rejected' => 'Premio rechazado.',
            default    => 'El premio vuelve a estar pendiente.',
        });
    }

    /** A link with rewards behind it is history, and is not deleted. */
    public function destroy(Referrer $referrer)
    {
        if ($referrer->rewards()->exists()) {
            return back()->with('error', 'Este enlace tiene premios. No se borra, para no perder el registro.');
        }

        $referrer->delete();

        return redirect()->route('admin.referrals.index')->with('status', 'Enlace borrado.');
    }
}
