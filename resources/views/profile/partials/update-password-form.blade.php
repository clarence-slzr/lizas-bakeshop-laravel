<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div class="form-group">
            <label for="update_password_current_password">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password"
                autocomplete="current-password">
            @if($errors->updatePassword->has('current_password'))
                <p class="error-msg">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div class="form-group">
            <label for="update_password_password">New Password</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password">
            @if($errors->updatePassword->has('password'))
                <p class="error-msg">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password">
            @if($errors->updatePassword->has('password_confirmation'))
                <p class="error-msg">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary">
                <i class="fas fa-key"></i> Update Password
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-sm text-green-600">Saved.</p>
            @endif
        </div>
    </form>
</section>