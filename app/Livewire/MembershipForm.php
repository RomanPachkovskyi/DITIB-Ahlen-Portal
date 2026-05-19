<?php

namespace App\Livewire;

use App\Models\Member;
use App\Support\Iban;
use App\Support\Instagram;
use App\Support\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class MembershipForm extends Component
{
    public int $step = 1;

    // Step 1 — Persönliche Daten
    public string $anrede = '';
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
    public string $instagram = '';

    // PLZ Autocomplete
    public array $plzSuggestions = [];
    public bool $showPlzDropdown = false;

    // Step 3 — Beitrag & Bankverbindung
    public float $monatsbeitrag = 10.00;
    public string $zahlungsart = 'dauerauftrag';
    public string $kontoinhaber = '';
    public string $iban = '';
    public string $bic = '';
    public string $kreditinstitut = '';

    // Step 3 (додатково) — Zustimmung
    // public string $unterschrift = ''; // TODO: Etap 4 — Unterschrift & Foto
    public bool $sepa_zustimmung = false;
    public bool $dsgvo_zustimmung = false;

    public bool $submitted = false;
    public string $member_number = '';
    public array $stepsWithErrors = [];
    public bool $showValidationSummary = false;

    protected function rulesStep1(): array
    {
        return [
            'anrede'               => 'required|in:Frau,Herr',
            'full_name'            => ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'],
            'birth_date'           => ['required', 'date', 'before:today', function ($attr, $value, $fail) {
                if (Carbon::parse($value)->age < 16) {
                    $fail('Eine Registrierung ist erst ab 16 Jahren möglich.');
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
            'email'       => 'required|email', // unique не застосовується: один email може використовуватись для кількох членів (діти реєструють батьків)
            'phone'       => ['required', 'string', 'max:50', function ($attribute, $value, $fail) {
                if (! PhoneNumber::isValid($value)) {
                    $fail(PhoneNumber::validationMessage());
                }
            }],
            'instagram'   => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) {
                if (! Instagram::isValid($value)) {
                    $fail('Bitte geben Sie einen Instagram-Namen oder Instagram-Link ein.');
                }
            }],
        ];
    }

    protected function messages(): array
    {
        return [
            // Pflichtfelder
            'required'                        => 'Dieses Feld ist erforderlich.',
            'accepted'                        => 'Ihre Zustimmung ist erforderlich.',

            // Schritt 1
            'anrede.required'                 => 'Bitte wählen Sie eine Anrede.',
            'anrede.in'                       => 'Ungültige Anrede.',
            'full_name.required'              => 'Bitte geben Sie Ihren Namen ein.',
            'full_name.max'                   => 'Der Name ist zu lang (max. 255 Zeichen).',
            'full_name.regex'                 => 'Der Name darf nur Buchstaben und Bindestriche enthalten.',
            'birth_date.required'             => 'Bitte geben Sie Ihr Geburtsdatum ein.',
            'birth_date.date'                 => 'Ungültiges Datumsformat.',
            'birth_date.before'               => 'Das Geburtsdatum muss in der Vergangenheit liegen.',
            'birth_place.regex'               => 'Nur Buchstaben und Bindestriche erlaubt.',
            'staatsangehoerigkeit.regex'      => 'Nur Buchstaben und Bindestriche erlaubt.',
            'familienangehoerige.required'    => 'Bitte geben Sie die Anzahl der Familienmitglieder ein.',
            'familienangehoerige.integer'     => 'Bitte geben Sie eine ganze Zahl ein.',
            'familienangehoerige.min'         => 'Mindestens 1 Familienmitglied erforderlich.',
            'beruf.regex'                     => 'Nur Buchstaben und Bindestriche erlaubt.',
            'heimatstadt.regex'               => 'Nur Buchstaben und Bindestriche erlaubt.',

            // Schritt 2
            'street.required'                 => 'Bitte geben Sie Ihre Straße und Hausnummer ein.',
            'postal_code.required'            => 'Bitte geben Sie Ihre Postleitzahl ein.',
            'postal_code.regex'               => 'Die Postleitzahl muss aus 5 Ziffern bestehen.',
            'city.required'                   => 'Bitte geben Sie Ihren Ort ein.',
            'city.regex'                      => 'Nur Buchstaben und Bindestriche erlaubt.',
            'state.required'                  => 'Bitte geben Sie das Bundesland ein.',
            'state.regex'                     => 'Nur Buchstaben und Bindestriche erlaubt.',
            'email.required'                  => 'Bitte geben Sie Ihre E-Mail-Adresse ein.',
            'email.email'                     => 'Ungültige E-Mail-Adresse.',
            'phone.required'                  => 'Bitte geben Sie Ihre Telefonnummer ein.',
            'instagram.max'                   => 'Der Instagram-Eintrag ist zu lang.',

            // Schritt 3
            'monatsbeitrag.required'          => 'Bitte geben Sie Ihren Monatsbeitrag ein.',
            'monatsbeitrag.numeric'           => 'Der Beitrag muss eine Zahl sein.',
            'monatsbeitrag.min'               => 'Der Mindestbeitrag beträgt 10,00 €.',
            'zahlungsart.required'            => 'Bitte wählen Sie eine Zahlungsweise.',
            'zahlungsart.in'                  => 'Ungültige Zahlungsweise.',
            'kontoinhaber.required'           => 'Bitte geben Sie den Kontoinhaber ein.',
            'kontoinhaber.regex'              => 'Nur Buchstaben und Bindestriche erlaubt.',
            'iban.required'                   => 'Bitte geben Sie Ihre IBAN ein.',
            'iban.regex'                      => 'Ungültige IBAN.',
            'bic.max'                         => 'Die BIC darf maximal 11 Zeichen haben.',
            'kreditinstitut.regex'            => 'Nur Buchstaben und Bindestriche erlaubt.',
            'sepa_zustimmung.accepted'        => 'Bitte stimmen Sie dem SEPA-Lastschriftmandat zu.',
            'dsgvo_zustimmung.accepted'       => 'Bitte stimmen Sie der Datenschutzerklärung zu.',
        ];
    }

    protected function rulesStep3(): array
    {
        $rules = [
            'monatsbeitrag'    => 'required|numeric|min:10',
            'zahlungsart'      => 'required|in:barzahlung,lastschrift,dauerauftrag',
            'dsgvo_zustimmung' => 'accepted',
        ];

        if ($this->zahlungsart === 'lastschrift') {
            $rules['sepa_zustimmung'] = 'accepted';
            $rules['kontoinhaber']    = ['required', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'];
            $rules['iban']            = ['required', 'string', function ($attribute, $value, $fail) {
                if (! Iban::isValidStructure($value)) {
                    $fail('Ungültige IBAN.');
                }
            }];
            $rules['bic']             = 'nullable|string|max:11';
            $rules['kreditinstitut']  = ['nullable', 'string', 'max:255', 'regex:/^[\pL\s\-]+$/u'];
        }

        return $rules;
    }

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => $this->rulesStep1(),
            2 => $this->rulesStep2(),
            3 => $this->rulesStep3(),
            default => [],
        };
    }

    protected function validateStep(int $step): bool
    {
        if ($step === 2) {
            $this->phone = PhoneNumber::format($this->phone);
            $instagram = Instagram::normalize($this->instagram);
            if ($instagram !== null || trim($this->instagram) === '') {
                $this->instagram = $instagram ?? '';
            }
        }

        if ($step === 3 && $this->zahlungsart === 'lastschrift') {
            $this->iban = Iban::format($this->iban);
        }

        $rules = $this->rulesForStep($step);
        $fields = array_keys($rules);
        $data = [];

        foreach ($fields as $field) {
            $data[$field] = $this->{$field};
        }

        $validator = Validator::make($data, $rules, $this->messages());
        $this->resetValidation($fields);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            $this->stepsWithErrors[$step] = true;
            $this->showValidationSummary = true;

            return false;
        }

        unset($this->stepsWithErrors[$step]);

        if ($this->stepsWithErrors === []) {
            $this->showValidationSummary = false;
        }

        return true;
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
    }

    public function updatedIban(string $value): void
    {
        $this->iban = Iban::format($value);
        $this->resetValidation('iban');
    }

    public function updatedPhone(string $value): void
    {
        $this->phone = PhoneNumber::format($value);
        $this->resetValidation('phone');
    }

    public function updatedInstagram(string $value): void
    {
        $this->instagram = Instagram::normalize($value) ?? $value;
        $this->resetValidation('instagram');
    }

    public function selectMonatsbeitrag(float $amount): void
    {
        $this->monatsbeitrag = $amount;
        $this->resetValidation('monatsbeitrag');
    }

    public function updated($propertyName): void
    {
        $rules = $this->rulesForStep($this->step);

        if (array_key_exists($propertyName, $rules)) {
            $this->validateOnly($propertyName, $rules, $this->messages());
        }
    }

    public function nextStep(): void
    {
        $this->validateStep($this->step);
        $this->step = min(3, $this->step + 1);
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function submit(): void
    {
        $firstInvalidStep = null;

        foreach ([1, 2, 3] as $step) {
            if (! $this->validateStep($step) && $firstInvalidStep === null) {
                $firstInvalidStep = $step;
            }
        }

        if ($firstInvalidStep !== null) {
            $this->step = $firstInvalidStep;
            $this->showValidationSummary = true;

            return;
        }

        $member = Member::create([
            'anrede'               => $this->anrede,
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
            'phone'                => PhoneNumber::normalize($this->phone),
            'instagram'            => Instagram::normalize($this->instagram),
            'monatsbeitrag'        => $this->monatsbeitrag,
            'zahlungsart'          => $this->zahlungsart,
            'kontoinhaber'         => $this->zahlungsart === 'lastschrift' ? $this->kontoinhaber : null,
            'iban'                 => $this->zahlungsart === 'lastschrift' ? Iban::normalize($this->iban) : null,
            'bic'                  => $this->zahlungsart === 'lastschrift' ? ($this->bic ?: null) : null,
            'kreditinstitut'       => $this->zahlungsart === 'lastschrift' ? ($this->kreditinstitut ?: null) : null,
            'unterschrift'         => '', // TODO: Etap 4 — canvas підпис
            'sepa_zustimmung'      => $this->zahlungsart === 'lastschrift',
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
