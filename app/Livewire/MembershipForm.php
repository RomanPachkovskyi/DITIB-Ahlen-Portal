<?php

namespace App\Livewire;

use App\Models\Member;
use Carbon\Carbon;
use Livewire\Component;

class MembershipForm extends Component
{
    public int $step = 1;

    // Step 1 — Persönliche Daten
    public string $full_name = '';
    public string $birth_date = '';
    public string $birth_place = '';
    public string $staatsangehoerigkeit = '';
    public int $familienangehoerige = 1;
    public bool $cenaze_fonu = false;
    public string $cenaze_fonu_nr = '';
    public bool $gemeinderegister = false;
    public string $beruf = '';
    public string $heimatstadt = '';

    // Step 2 — Adresse & Kontakt
    public string $street = '';
    public string $postal_code = '';
    public string $city = '';
    public string $state = '';
    public string $email = '';
    public string $phone = '';

    // PLZ Autocomplete
    public array $plzSuggestions = [];
    public bool $showPlzDropdown = false;

    // Step 3 — Beitrag & Bankverbindung
    public float $monatsbeitrag = 25.00;
    public string $zahlungsart = 'barzahlung';
    public string $kontoinhaber = '';
    public string $iban = '';
    public string $bic = '';
    public string $kreditinstitut = '';

    // Step 4 — Unterschrift & Zustimmung
    public string $unterschrift = '';
    public bool $sepa_zustimmung = false;
    public bool $dsgvo_zustimmung = false;

    public bool $submitted = false;
    public string $member_number = '';

    protected function rulesStep1(): array
    {
        return [
            'full_name'            => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'birth_date'           => ['required', 'date', 'before:today', function ($attr, $value, $fail) {
                if (Carbon::parse($value)->age < 16) {
                    $fail('Eine Registrierung ist erst ab 16 Jahren möglich. / Kayıt yalnızca 16 yaş ve üzeri için mümkündür.');
                }
            }],
            'birth_place'          => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'staatsangehoerigkeit' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'familienangehoerige'  => 'required|integer|min:1',
            'beruf'                => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'heimatstadt'          => ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
        ];
    }

    protected function rulesStep2(): array
    {
        return [
            'street'      => 'required|string|max:255',
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'city'        => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'state'       => ['required', 'string', 'max:100', 'regex:/^[\pL\s\-]+$/u'],
            'email'       => 'required|email|unique:members,email',
            'phone'       => ['required', 'string', 'max:30', 'regex:/^[+\(\)\-\s0-9]+$/'],
        ];
    }

    protected function rulesStep3(): array
    {
        $rules = [
            'monatsbeitrag' => 'required|numeric|min:25',
            'zahlungsart'   => 'required|in:barzahlung,lastschrift,dauerauftrag',
        ];

        if (in_array($this->zahlungsart, ['lastschrift', 'dauerauftrag'])) {
            $rules['kontoinhaber']  = ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'];
            $rules['iban']          = ['required', 'string', 'regex:/^[A-Za-z]{2}[0-9]{2}[A-Za-z0-9]{11,30}$/'];
            $rules['bic']           = 'nullable|string|max:11';
            $rules['kreditinstitut'] = ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'];
        }

        return $rules;
    }

    protected function rulesStep4(): array
    {
        return [
            'unterschrift'     => 'required|string',
            'dsgvo_zustimmung' => 'accepted',
        ];
    }

    public function updatedPostalCode(string $value): void
    {
        $value = preg_replace('/[^0-9]/', '', $value);

        if (strlen($value) >= 2) {
            // Load all matches, then group by PLZ and pick the shortest ort
            // (company names like "Sparkasse Münsterland Ost Hauptstelle Ahlen" are always longer than city names)
            $all = \App\Models\PostalCode::where('plz', 'like', $value . '%')
                ->orderBy('plz')
                ->get(['plz', 'ort', 'bundesland']);

            $grouped = [];
            foreach ($all as $row) {
                $plz = $row->plz;
                if (!isset($grouped[$plz]) || mb_strlen($row->ort) < mb_strlen($grouped[$plz]['ort'])) {
                    $grouped[$plz] = [
                        'plz'        => $plz,
                        'ort'        => $row->ort,
                        'bundesland' => $row->bundesland,
                    ];
                }
            }

            $this->plzSuggestions = array_values(array_slice($grouped, 0, 10));
            $this->showPlzDropdown = count($this->plzSuggestions) > 0;
        } else {
            $this->plzSuggestions = [];
            $this->showPlzDropdown = false;
        }

        // Auto-fill on exact 5-digit match
        if (strlen($value) === 5) {
            $all = \App\Models\PostalCode::where('plz', $value)->get(['ort', 'bundesland']);
            if ($all->isNotEmpty()) {
                // Pick shortest ort = most likely city name
                $best = $all->sortBy(fn($r) => mb_strlen($r->ort))->first();
                $this->city  = $best->ort;
                $this->state = $best->bundesland;
                $this->showPlzDropdown = false;
                $this->plzSuggestions = [];
            }
        }
    }

    public function selectPlz(string $plz, string $ort, string $bundesland): void
    {
        $this->postal_code      = $plz;
        $this->city             = $ort;
        $this->state            = $bundesland;
        $this->plzSuggestions   = [];
        $this->showPlzDropdown  = false;

        // Clear any validation errors that appeared while typing (partial PLZ)
        $this->resetValidation(['postal_code', 'city', 'state']);
    }

    public function closePlzDropdown(): void
    {
        $this->showPlzDropdown = false;
        $this->plzSuggestions  = [];
        $this->resetValidation(['postal_code', 'city', 'state']);
    }

    public function updated($propertyName): void
    {
        $rules = match ($this->step) {
            1 => $this->rulesStep1(),
            2 => $this->rulesStep2(),
            3 => $this->rulesStep3(),
            4 => $this->rulesStep4(),
            default => [],
        };

        if (array_key_exists($propertyName, $rules)) {
            $this->validateOnly($propertyName, $rules);
        }
    }

    public function nextStep(): void
    {
        match ($this->step) {
            1 => $this->validate($this->rulesStep1()),
            2 => $this->validate($this->rulesStep2()),
            3 => $this->validate($this->rulesStep3()),
            default => null,
        };

        $this->step++;
        $this->resetValidation();
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        $this->validate($this->rulesStep4());

        $member = Member::create([
            'full_name'            => $this->full_name,
            'birth_date'           => $this->birth_date,
            'birth_place'          => $this->birth_place ?: null,
            'staatsangehoerigkeit' => $this->staatsangehoerigkeit ?: null,
            'familienangehoerige'  => $this->familienangehoerige,
            'cenaze_fonu'          => $this->cenaze_fonu,
            'cenaze_fonu_nr'       => $this->cenaze_fonu ? ($this->cenaze_fonu_nr ?: null) : null,
            'gemeinderegister'     => $this->gemeinderegister,
            'beruf'                => $this->beruf ?: null,
            'heimatstadt'          => $this->heimatstadt ?: null,
            'street'               => $this->street,
            'postal_code'          => $this->postal_code,
            'city'                 => $this->city,
            'state'                => $this->state,
            'email'                => $this->email,
            'phone'                => preg_replace('/[^\+0-9]/', '', $this->phone),
            'monatsbeitrag'        => $this->monatsbeitrag,
            'zahlungsart'          => $this->zahlungsart,
            'kontoinhaber'         => in_array($this->zahlungsart, ['lastschrift', 'dauerauftrag']) ? $this->kontoinhaber : null,
            'iban'                 => in_array($this->zahlungsart, ['lastschrift', 'dauerauftrag']) ? $this->iban : null,
            'bic'                  => in_array($this->zahlungsart, ['lastschrift', 'dauerauftrag']) ? ($this->bic ?: null) : null,
            'kreditinstitut'       => in_array($this->zahlungsart, ['lastschrift', 'dauerauftrag']) ? ($this->kreditinstitut ?: null) : null,
            'unterschrift'         => $this->unterschrift,
            'sepa_zustimmung'      => in_array($this->zahlungsart, ['lastschrift', 'dauerauftrag']) ? true : false,
            'dsgvo_zustimmung'     => $this->dsgvo_zustimmung,
            'zustimmung_at'        => now(),
            'status'               => 'pending',
        ]);

        $this->member_number = $member->member_number;
        $this->submitted = true;

        \App\Events\MemberRegistered::dispatch($member);
    }

    public function render()
    {
        return view('livewire.membership-form')
            ->layout('layouts.public');
    }
}
