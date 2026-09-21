@extends('layouts.app')

@section('content')
    @php
        $page_title = 'Edit User';
        $hide_page_title = true;

        $currentUserId = auth()->id();
        $isSelf = ($user->id == $currentUserId);
        $isAdminUser = ($user->role === 'admin');

        $error = session('error') ?? '';
        if ($error) {
            session()->forget('error');
        }
    @endphp

    <div class="form-container">
        <div class="form-header">
            <div class="form-header-left">
                <a href="{{ route('admin.users.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
                <h1>Edit User</h1>
                <p class="form-description">Update user account information</p>
            </div>
        </div>

        @if($error)
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                {{ $error }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="user-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $user->id }}">

            <div class="form-grid">
                <div class="form-section">
                    <h3 class="section-title">Account Information</h3>

                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
                            required>
                        <small class="form-hint">Used for logging in</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                            required>
                        <small class="form-hint">Complete name of the user</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password">
                            <small class="form-hint">Leave blank to keep current password</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                            <small class="form-hint">Re-enter new password</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Role & Permissions</h3>

                    <div class="form-group">
                        <label for="role">User Role <span class="required">*</span></label>
                        <select id="role" name="role" {{ $isSelf ? 'disabled' : '' }}>
                            <option value="cashier" {{ $user->role == 'cashier' ? 'selected' : '' }}>Cashier</option>
                            <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrator</option>
                        </select>
                        @if($isSelf)
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <small class="form-hint warning-hint">
                                <i class="fas fa-lock"></i> You cannot change your own role.
                            </small>
                        @else
                            <small class="form-hint">
                                <strong>Cashier:</strong> Can process orders, view inventory<br>
                                <strong>Admin:</strong> Full access to all features
                            </small>
                        @endif
                    </div>

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <div class="info-content">
                            <strong>Note:</strong> Changes will take effect immediately after saving.
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fa-regular fa-floppy-disk"></i> Save Changes
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>

        <!-- ============================================
             DANGER ZONE - DELETE USER
             ============================================ -->
        @if(!$isSelf && !$isAdminUser)
            <div class="danger-zone">
                <div class="danger-header">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <h3>Danger Zone</h3>
                        <p>Once you delete this user, there is no going back.</p>
                    </div>
                </div>
                <div class="danger-content">
                    <div class="danger-info">
                        <strong>Delete this user account</strong>
                        <p>
                            This will permanently remove <strong>{{ $user->username }}</strong>
                            from the system. All associated data will be removed.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger" onclick="return confirmDeleteUser('{{ $user->username }}')">
                            <i class="fas fa-trash-alt"></i> Delete User
                        </button>
                    </form>
                </div>
            </div>
        @elseif($isAdminUser && !$isSelf)
            <div class="info-protected">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Administrator Account Protected</strong>
                    <p>Administrator accounts cannot be deleted from the system.</p>
                </div>
            </div>
        @elseif($isSelf)
            <div class="info-protected">
                <i class="fas fa-user-shield"></i>
                <div>
                    <strong>Your Own Account</strong>
                    <p>You cannot delete your own account. Ask another administrator if needed.</p>
                </div>
            </div>
        @endif
    </div>

    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .form-header-left {
            flex: 1;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #576238;
            text-decoration: none;
            font-size: 0.8rem;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .back-link:hover {
            color: #3E4A28;
            transform: translateX(-2px);
        }

        .form-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .form-description {
            color: #9E9D97;
            font-size: 0.85rem;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
        }

        .alert-error {
            background: #FEF0ED;
            border: 1px solid #C5705A;
            color: #C5705A;
        }

        .user-form {
            background: white;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .form-grid {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }

        .form-section {
            border-bottom: 1px solid #F0EADC;
            padding-bottom: 1.5rem;
        }

        .form-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2C2B26;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #576238;
            display: inline-block;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            font-size: 0.8rem;
            color: #2C2B26;
        }

        .required {
            color: #C5705A;
            margin-left: 0.25rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #576238;
            box-shadow: 0 0 0 3px rgba(87, 98, 56, 0.1);
        }

        .form-group select:disabled {
            background: #F0EADC;
            color: #9E9D97;
            cursor: not-allowed;
        }

        .form-hint {
            display: block;
            font-size: 0.7rem;
            color: #9E9D97;
            margin-top: 0.375rem;
            line-height: 1.4;
        }

        .warning-hint {
            color: #D4A054;
            font-weight: 500;
        }

        .info-box {
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
            border-radius: 0.5rem;
            padding: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .info-box i {
            color: #576238;
            font-size: 1rem;
            margin-top: 0.125rem;
        }

        .info-content {
            font-size: 0.8rem;
            color: #6B6A65;
            line-height: 1.5;
        }

        .info-content strong {
            color: #2C2B26;
        }

        .form-actions {
            background: #FDF8F0;
            padding: 1rem 1.5rem;
            border-top: 1px solid #E3DCD0;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: #576238;
            color: white;
        }

        .btn-primary:hover {
            background: #3E4A28;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: white;
            color: #6B6A65;
            border: 1px solid #E3DCD0;
        }

        .btn-secondary:hover {
            background: #F0EADC;
            border-color: #576238;
            color: #576238;
        }

        /* ========== DANGER ZONE ========== */
        .danger-zone {
            background: white;
            border: 1px solid #F5C7BC;
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .danger-header {
            background: #FEF0ED;
            padding: 0.875rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid #F5C7BC;
        }

        .danger-header i {
            color: #C5705A;
            font-size: 1.15rem;
        }

        .danger-header h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #C5705A;
            margin: 0;
        }

        .danger-header p {
            font-size: 0.75rem;
            color: #A85844;
            margin: 0.15rem 0 0 0;
        }

        .danger-content {
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .danger-info {
            flex: 1;
            min-width: 200px;
        }

        .danger-info strong {
            display: block;
            font-size: 0.85rem;
            color: #2C2B26;
            margin-bottom: 0.25rem;
        }

        .danger-info p {
            font-size: 0.78rem;
            color: #7A7A75;
            line-height: 1.5;
        }

        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #C5705A;
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
            border: none;
            cursor: pointer;
        }

        .btn-danger:hover {
            background: #A85844;
            transform: translateY(-1px);
        }

        /* ========== PROTECTED INFO ========== */
        .info-protected {
            background: #FDF8F0;
            border: 1px solid #E3DCD0;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .info-protected i {
            color: #9E9D97;
            font-size: 1.25rem;
        }

        .info-protected strong {
            display: block;
            font-size: 0.85rem;
            color: #2C2B26;
            margin-bottom: 0.15rem;
        }

        .info-protected p {
            font-size: 0.78rem;
            color: #7A7A75;
            margin: 0;
        }
    </style>

    <script>
        document.querySelector('.user-form')?.addEventListener('submit', function (e) {
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirm_password');

            if (password.value !== '' || confirm.value !== '') {
                if (password.value !== confirm.value) {
                    e.preventDefault();
                    alert('Password and Confirm Password do not match!');
                    confirm.focus();
                    return false;
                }
                if (password.value.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters.');
                    password.focus();
                    return false;
                }
            }
        });

        function confirmDeleteUser(username) {
            const input = prompt(
                '⚠️ WARNING: This action cannot be undone.\n\n' +
                'Type the username "' + username + '" to confirm deletion:'
            );

            if (input === null) return false;
            if (input !== username) {
                alert('Username does not match. Deletion cancelled.');
                return false;
            }

            return confirm('Are you absolutely sure you want to delete this user?');
        }
    </script>
@endsection