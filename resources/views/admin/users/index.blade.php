@extends('layouts.admin')
@section('title', 'Manajemen Admin | Techub Admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Manajemen Admin</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-person-plus me-1"></i> Tambah Admin</button>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama Admin</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td class="fw-semibold">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0A4B91&color=fff" alt="Admin" class="rounded-circle me-2" width="32" height="32">
                            {{ $user->name }}
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2 align-items-center">
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $user->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="m-0 p-0">
                                    @csrf @method('DELETE')
                                    @if($user->is_active)
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Nonaktifkan" onclick="return confirm('Yakin ingin menonaktifkan admin ini?')"><i class="bi bi-person-x"></i></button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan" onclick="return confirm('Yakin ingin mengaktifkan admin ini?')"><i class="bi bi-person-check"></i></button>
                                    @endif
                                </form>
                                @else
                                <span class="badge bg-secondary">Current</span>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">Edit Admin</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body text-start">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Admin</label>
                                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password Baru (Opsional)</label>
                                            <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Konfirmasi Password Baru</label>
                                            <input type="password" name="password_confirmation" class="form-control">
                                        </div>
                                        <div class="form-check mt-3 border rounded p-2 bg-light">
                                            <input class="form-check-input ms-1" type="checkbox" name="send_notification" value="1" id="notifCheck{{ $user->id }}">
                                            <label class="form-check-label small ms-2 fw-medium" for="notifCheck{{ $user->id }}">
                                                Kirim Notifikasi Perubahan (Email & WA)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr><td colspan="4" class="text-center py-4">Belum ada data admin</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tambah Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="form-check mt-3 border rounded p-2 bg-light">
                        <input class="form-check-input ms-1" type="checkbox" name="send_notification" value="1" id="addNotifCheck" checked>
                        <label class="form-check-label small ms-2 fw-medium" for="addNotifCheck">
                            Kirim Notifikasi Perubahan (Email & WA)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
