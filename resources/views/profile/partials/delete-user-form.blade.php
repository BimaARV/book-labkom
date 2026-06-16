<section class="space-y-6">
    <header>
        <h2 class="h5 text-danger">
            {{ __('Delete Account') }}
        </h2>

        <p class="text-muted small mb-4">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        {{ __('Delete Account') }}
    </button>

    <!-- Modal -->
    <div class="modal fade @if($errors->userDeletion->isNotEmpty()) show @endif" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true" @if($errors->userDeletion->isNotEmpty()) style="display: block;" @endif>
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionModalLabel">{{ __('Are you sure you want to delete your account?') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted small">
                            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                        </p>

                        <div class="mb-3">
                            <label for="password" class="form-label sr-only">{{ __('Password') }}</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                                placeholder="{{ __('Password') }}"
                            >
                            @if($errors->userDeletion->has('password'))
                                <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
