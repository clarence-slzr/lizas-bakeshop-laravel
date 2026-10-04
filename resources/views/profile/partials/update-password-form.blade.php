<section>
    <header style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1rem; font-weight: 600; color: #2C2B26; margin: 0 0 0.35rem 0;">
            {{ __('Update Password') }}
        </h2>
        <p style="font-size: 0.8rem; color: #9E9D97; margin: 0;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        {{-- CURRENT PASSWORD --}}
        <div>
            <label for="update_password_current_password">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password"
                autocomplete="current-password" placeholder="Enter current password">
            @error('current_password', 'updatePassword')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- NEW PASSWORD --}}
        <div>
            <label for="update_password_password">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                placeholder="Enter new password">
            @error('password', 'updatePassword')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- CONFIRM PASSWORD --}}
        <div>
            <label for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password" placeholder="Confirm new password">
            @error('password_confirmation', 'updatePassword')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- SUBMIT --}}
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-lock"></i> {{ __('Update Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p style="font-size: 0.8rem; color: #4ADE80; margin: 0;">
                    <i class="fas fa-check-circle"></i> {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>