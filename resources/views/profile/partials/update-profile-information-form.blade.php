<section>
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $user->full_name) }}" required
                autofocus>
            @error('full_name')
            <p class="error-msg">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required>
            @error('username')
            <p class="error-msg">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}"
                placeholder="0917-xxx-xxxx">
            @error('phone')
            <p class="error-msg">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label for="motto">Personal Motto <small>(optional)</small></label>
            <input id="motto" name="motto" type="text" value="{{ old('motto', $user->motto) }}"
                placeholder="Halimbawa: Ang tamis ng tagumpay..." maxlength="255">
            @error('motto')
            <p class="error-msg">{{ $message }}</p> @enderror
        </div>

        <div class="form-group">
            <label for="avatar">Profile Picture <small>(JPG, PNG, max 2MB)</small></label>
            <input id="avatar" name="avatar" type="file" accept="image/*">
            @error('avatar')
            <p class="error-msg">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-green-600">Saved.</p>
            @endif
        </div>
    </form>
</section>