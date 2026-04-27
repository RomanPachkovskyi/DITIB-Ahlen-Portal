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
            'full_name'            => 'required|string|max:255',
            'birth_date'           => ['required', 'date', 'before:today', function ($attr, $value, $fail) {
                if (Carbon::parse($value)->age < 16) {
                    $fail('Eine Registrierung ist erst ab 16 Jahren möglich. / Kayıt yalnızca 16 yaş ve üzeri için mümkündür.');
                }
            }],
            'birth_place'          => 'nullable|string|max:255',
            'staatsangehoerigkeit' => 'nullable|string|max:100',
            'familienangehoerige'  => 'required|integer|min:1',
            'beruf'                => 'nullable|string|max:255',
            'heimatstadt'          => 'nullable|string|max:255',
        ];
    }

    protected function rulesStep2(): array
    {
        return [
            'street'      => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'city'        => 'required|string|max:100',
            'state'       => 'required|string|max:100',
            'email'       => 'required|email|unique:members,email',
            'phone'       => 'required|string|max:30',
        ];
    }

    protected function rulesStep3(): array
    {
        $rules = [
            'monatsbeitrag' => 'required|numeric|min:25',
            'zahlungsart'   => 'required|in:barzahlung,lastschrift,dauerauftrag',
        ];

        if (in_array($this->zahlungsart, ['lastschrift', 'dauerauftrag'])) {
            $rules['kontoinhaber']  = 'required|string|max:255';
            $rules['iban']          = 'required|string|max:34';
            $rules['bic']           = 'nullable|string|max:11';
            $rules['kreditinstitut'] = 'nullable|string|max:255';
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

    public function nextStep(): void
    {
        match ($this->step) {
            1 => $this->validate($this->rulesStep1()),
            2 => $this->validate($this->rulesStep2()),
            3 => $this->validate($this->rulesStep3()),
            default => null,
        };

        $this->step++;
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
            'phone'                => $this->phone,
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
    }

    public function render()
    {
        return view('livewire.membership-form')
            ->layout('layouts.public');
    }
}
