<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\Discipline;
use App\Enums\MemberVerificationStatus;
use App\Models\MemberProfile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class UpdateMemberProfileRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ([
            'pzss_license_number',
            'shooting_patent_number',
            'firearm_permit_number',
            'club_member_number',
        ] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $normalized[$field] = trim($value) ?: null;
            }
        }

        if (isset($normalized['pzss_license_number'])) {
            $normalized['pzss_license_number'] = mb_strtoupper($normalized['pzss_license_number']);
        }

        $this->merge($normalized);
    }

    public function authorize(): bool
    {
        /** @var User|null $editedUser */
        $editedUser = $this->route('user');
        $profile = $editedUser === null
            ? null
            : MemberProfile::query()->where('user_id', $editedUser->getKey())->first();

        return $profile
            ? Gate::allows('update', $profile)
            : Gate::allows('create', MemberProfile::class);
    }

    public function rules(): array
    {
        /** @var User $editedUser */
        $editedUser = $this->route('user');
        $profileId = MemberProfile::query()
            ->where('user_id', $editedUser->getKey())
            ->value('id');

        return [
            'pzss_license_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('member_profiles', 'pzss_license_number')
                    ->ignore($profileId),
            ],
            'pzss_license_expires_at' => ['nullable', 'date'],
            'shooting_patent_number' => ['nullable', 'string', 'max:100'],
            'firearm_permit_number' => ['nullable', 'string', 'max:100'],
            'club_member_number' => ['nullable', 'string', 'max:100'],
            'joined_club_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'disciplines' => ['nullable', 'array', 'max:3'],
            'disciplines.*' => ['required', 'string', 'distinct', Rule::enum(Discipline::class)],
            'verification_status' => ['required', Rule::enum(MemberVerificationStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'pzss_license_number.unique' => 'Ten numer licencji PZSS jest już przypisany do innego profilu.',
            'verification_status.required' => 'Wybierz status weryfikacji danych członkowskich.',
        ];
    }

    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'pzss_license_number' => 'numer licencji PZSS',
            'pzss_license_expires_at' => 'data ważności licencji PZSS',
            'shooting_patent_number' => 'numer patentu strzeleckiego',
            'firearm_permit_number' => 'numer pozwolenia na broń',
            'club_member_number' => 'numer członkowski',
            'joined_club_year' => 'rok wstąpienia do klubu',
            'disciplines' => 'dyscypliny',
            'disciplines.*' => 'dyscyplina',
            'verification_status' => 'status weryfikacji',
        ];
    }

    /** @return list<string> */
    protected function oldInputArrayFields(): array
    {
        return [...parent::oldInputArrayFields(), 'disciplines'];
    }
}
