<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /** Consume a recovery code only if its encrypted list has not changed. */
    public function replaceRecoveryCode($code)
    {
        $original = $this->getRawOriginal('two_factor_recovery_codes');
        $codes = $this->recoveryCodes();
        $index = array_search($code, $codes, true);
        if ($index === false) {
            throw \Illuminate\Validation\ValidationException::withMessages(['recovery_code' => 'This recovery code is no longer valid.']);
        }
        $codes[$index] = \Laravel\Fortify\RecoveryCode::generate();
        $replacement = \Laravel\Fortify\Fortify::currentEncrypter()->encrypt(json_encode($codes));
        $updated = static::query()->whereKey($this->getKey())
            ->where('two_factor_recovery_codes', $original)
            ->update(['two_factor_recovery_codes' => $replacement]);
        if ($updated !== 1) {
            throw \Illuminate\Validation\ValidationException::withMessages(['recovery_code' => 'Recovery codes changed. Please sign in again.']);
        }
        $this->forceFill(['two_factor_recovery_codes' => $replacement])->syncOriginalAttribute('two_factor_recovery_codes');
        \Laravel\Fortify\Events\RecoveryCodeReplaced::dispatch($this, $code);
    }

}
