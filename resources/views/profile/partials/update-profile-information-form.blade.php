<section>
    <header style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1rem; font-weight: 600; color: #2C2B26; margin: 0 0 0.35rem 0;">
            {{ __('Profile Information') }}
        </h2>
        <p style="font-size: 0.8rem; color: #9E9D97; margin: 0;">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('patch')

        {{-- FULL NAME --}}
        <div>
            <label for="full_name">{{ __('Full Name') }}</label>
            <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $user->full_name) }}" required
                autofocus autocomplete="name" placeholder="Enter your full name">
            @error('full_name')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- USERNAME --}}
        <div>
            <label for="username">{{ __('Username') }}</label>
            <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required
                autocomplete="username" placeholder="Enter your username">
            @error('username')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- EMAIL --}}
        <div>
            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                autocomplete="username" placeholder="Enter your email">
            @error('email')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div style="margin-top: 0.75rem;">
                    <p style="font-size: 0.8rem; color: #6B6A65;">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification"
                            style="background: none; border: none; color: #576238; text-decoration: underline; cursor: pointer; font-size: 0.8rem; padding: 0;">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p style="font-size: 0.8rem; color: #4ADE80; margin-top: 0.5rem;">
                            <i class="fas fa-check-circle"></i>
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- PHONE --}}
        <div>
            <label for="phone">{{ __('Phone Number') }}</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}"
                placeholder="09171234567">
            @error('phone')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- PERSONAL MOTTO --}}
        <div>
            <label for="motto">{{ __('Personal Motto (Optional)') }}</label>
            <input id="motto" name="motto" type="text" value="{{ old('motto', $user->motto) }}"
                placeholder="Example: The sweetness of victory...">
            @error('motto')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- AVATAR --}}
        <div>
            <label for="avatar">{{ __('Profile Picture') }} <span style="color: #9E9D97; font-weight: 400;">(JPG, PNG,
                    max 2MB)</span></label>
            <input id="avatar" name="avatar" type="file" accept="image/*">
            @error('avatar')
                <p style="color: #C5705A; font-size: 0.75rem; margin-top: 0.35rem;">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        {{-- SUBMIT --}}
        <div style="display: flex; align-items: center; gap: 1rem;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p style="font-size: 0.8rem; color: #4ADE80; margin: 0;" x-data="{ show: true }" x-show="show" x-transition
                    x-init="setTimeout(() => show = false, 2000)">
                    <i class="fas fa-check-circle"></i> {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>