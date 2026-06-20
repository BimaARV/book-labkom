@extends('layouts.admin')

@section('title', 'Pemetaan ' . $laboratory->name . ' | Techub Admin')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <a href="{{ route('admin.lab-mappings.index') }}" class="btn btn-sm bg-light text-dark border mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        <h3 class="fw-bold mb-1">Pemetaan: {{ $laboratory->name }}</h3>
        <p class="text-muted-custom">Atur denah (grid) dan petakan posisi PC di laboratorium ini.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#configModal">
            <i class="bi bi-gear me-2"></i> Konfigurasi Ruangan
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Denah Interaktif</h5>
                <div class="d-flex gap-3 small fw-medium">
                    <span class="d-flex align-items-center gap-1"><i class="bi bi-circle-fill text-secondary"></i> Kosong</span>
                    <span class="d-flex align-items-center gap-1"><i class="bi bi-circle-fill text-primary"></i> Aktif</span>
                    <span class="d-flex align-items-center gap-1"><i class="bi bi-circle-fill text-warning"></i> Maintenance</span>
                    <span class="d-flex align-items-center gap-1"><i class="bi bi-circle-fill text-danger"></i> Rusak</span>
                    <span class="d-flex align-items-center gap-1"><i class="bi bi-circle-fill" style="color: #6c757d;"></i> Tidak Aktif</span>
                </div>
            </div>
            <div class="card-body p-4 text-center overflow-auto">
                @if(!$laboratory->grid_rows || !$laboratory->grid_cols)
                    <div class="alert alert-warning text-start">
                        <i class="bi bi-exclamation-triangle me-2"></i> <strong>Konfigurasi Grid Belum Diatur!</strong>
                        <p class="mb-0 mt-1">Silakan atur jumlah baris dan kolom terlebih dahulu menggunakan tombol <strong>Konfigurasi Ruangan</strong> di atas.</p>
                    </div>
                @else
                    <div class="d-inline-block p-4 rounded bg-light" style="min-width: 100%;">
                        <div class="mb-4 pb-3 border-bottom text-muted fw-bold d-flex align-items-center justify-content-center">
                            <i class="bi bi-easel me-2 fs-4"></i> MEJA ADMIN
                        </div>
                        
                        <div class="grid-container mx-auto" style="display: grid; gap: 15px; grid-template-columns: repeat({{ $laboratory->grid_cols }}, minmax(100px, 1fr));">
                            @php
                                $pcsArray = [];
                                foreach($pcs as $pc) {
                                    $pcsArray[$pc->grid_row][$pc->grid_col] = $pc;
                                }
                                
                                $isRtl = $laboratory->grid_direction === 'rtl';
                            @endphp
                            
                            @for($row = 0; $row < $laboratory->grid_rows; $row++)
                                @for($c = 0; $c < $laboratory->grid_cols; $c++)
                                    @php
                                        // Handle direction
                                        $col = $isRtl ? ($laboratory->grid_cols - 1 - $c) : $c;
                                        $hasPc = isset($pcsArray[$row][$col]);
                                        $pcData = $hasPc ? $pcsArray[$row][$col] : null;
                                        
                                        $bgColor = 'bg-secondary bg-opacity-10'; // empty
                                        $textColor = 'text-muted';
                                        if($hasPc) {
                                            if($pcData->status == 'active') {
                                                $bgColor = 'bg-primary text-white';
                                                $textColor = 'text-white';
                                            } elseif($pcData->status == 'maintenance') {
                                                $bgColor = 'bg-warning text-dark';
                                                $textColor = 'text-dark';
                                            } elseif($pcData->status == 'broken') {
                                                $bgColor = 'bg-danger text-white';
                                                $textColor = 'text-white';
                                            } elseif ($pcData->status == 'inactive') {
                                                $bgColor = 'bg-secondary text-white';
                                                $textColor = 'text-white';
                                            } elseif ($pcData->status == 'kosong') {
                                                $bgColor = 'bg-transparent border border-dashed border-2';
                                                $textColor = 'text-muted';
                                            }
                                        }
                                    @endphp
                                    
                                    <div class="grid-item rounded p-2 border position-relative d-flex flex-column align-items-center justify-content-center {{ $bgColor }}" 
                                         style="aspect-ratio: 1; cursor: pointer; transition: transform 0.2s; user-select: none;"
                                         onclick="handleGridClick({{ $row }}, {{ $col }}, {{ $hasPc ? 'true' : 'false' }}, {{ $hasPc ? $pcData->toJson() : 'null' }})"
                                         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        
                                        @if($hasPc)
                                            @if($pcData->status == 'kosong')
                                                <i class="bi bi-square-dotted fs-1 mb-2 {{ $textColor }}"></i>
                                            @else
                                                <i class="bi bi-pc-display fs-1 mb-2 {{ $textColor }}"></i>
                                            @endif
                                            <div class="fw-bold text-truncate w-100 {{ $textColor }}" style="font-size: 1.1rem;">{{ $pcData->name }}</div>
                                            @if($pcData->ip_address)
                                                <div class="text-truncate w-100 mt-1 {{ $textColor }}" style="font-size: 0.85rem; opacity: 0.9;">{{ $pcData->ip_address }}</div>
                                            @endif
                                            @if($pcData->mac_address)
                                                <div class="text-truncate w-100 {{ $textColor }}" style="font-size: 0.85rem; opacity: 0.9;">{{ $pcData->mac_address }}</div>
                                            @endif
                                        @else
                                            <i class="bi bi-plus-lg fs-2 text-muted opacity-50 mb-1"></i>
                                            <div class="text-muted opacity-50 fw-medium" style="font-size: 1rem;">Tambah</div>
                                        @endif
                                        
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfigurasi -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="configModalLabel">Konfigurasi Ruangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.lab-mappings.config', $laboratory) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Jumlah Baris (Row) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="grid_rows" min="1" max="50" value="{{ old('grid_rows', $laboratory->grid_rows ?? 5) }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Jumlah Kolom (Col) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="grid_cols" min="1" max="50" value="{{ old('grid_cols', $laboratory->grid_cols ?? 6) }}" required>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label fw-medium">Arah Penomoran / Susunan <span class="text-danger">*</span></label>
                            <select class="form-select" name="grid_direction" required>
                                <option value="ltr" {{ (old('grid_direction', $laboratory->grid_direction) == 'ltr') ? 'selected' : '' }}>Kiri ke Kanan (LTR)</option>
                                <option value="rtl" {{ (old('grid_direction', $laboratory->grid_direction) == 'rtl') ? 'selected' : '' }}>Kanan ke Kiri (RTL)</option>
                            </select>
                            <div class="form-text">Mempengaruhi bagaimana letak slot PC diurutkan secara visual.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function handleGridClick(row, col, hasPc, pcData) {
        if(!hasPc) {
            // Tampilkan modal tambah PC
            let autoNumber = (row * {{ $laboratory->grid_cols ?? 1 }}) + col + 1;
            let autoName = "PC-" + autoNumber;
            
            Swal.fire({
                title: '<h4 class="fw-bold mb-0 text-primary">Tambahkan PC</h4>',
                html: `
                    <div class="text-start mb-3">
                        Anda akan menambahkan PC pada Baris ${row+1}, Kolom ${col+1}.
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label fw-medium text-body">Nama / Nomor PC <span class="text-danger">*</span></label>
                        <input type="text" id="pc-name" class="form-control" value="${autoName}" style="color: var(--bs-body-color);">
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label fw-medium text-body">IP Address <span class="text-muted">(Opsional)</span></label>
                        <input type="text" id="pc-ip" class="form-control" placeholder="Misal: 192.168.1.50" style="color: var(--bs-body-color);">
                    </div>
                    <div class="mb-0 text-start">
                        <label class="form-label fw-medium text-body">MAC Address <span class="text-muted">(Opsional)</span></label>
                        <input type="text" id="pc-mac" class="form-control" placeholder="Misal: 00:1B:44:11:3A:B7" style="color: var(--bs-body-color);">
                    </div>
                    <div class="mt-3 text-start">
                        <label class="form-label fw-medium text-body">Status <span class="text-danger">*</span></label>
                        <select id="pc-status" class="form-select" style="color: var(--bs-body-color);" onchange="document.getElementById('pc-desc-container').style.display = ['maintenance', 'broken', 'inactive'].includes(this.value) ? 'block' : 'none'">
                            <option value="active">Aktif</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="broken">Rusak</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="kosong">Kosong (Meja Kosong)</option>
                        </select>
                    </div>
                    <div class="mt-3 text-start" id="pc-desc-container" style="display: none;">
                        <label class="form-label fw-medium text-body">Detail / Keterangan <span class="text-danger">*</span></label>
                        <textarea id="pc-desc" class="form-control" rows="3" style="color: var(--bs-body-color);" placeholder="Tuliskan alasan atau keterangan..."></textarea>
                    </div>
                `,
                customClass: {
                    popup: 'bg-body text-body border',
                    title: 'text-body',
                    htmlContainer: 'text-body'
                },
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#002B5C',
                preConfirm: () => {
                    const name = document.getElementById('pc-name').value;
                    if (!name) {
                        Swal.showValidationMessage('Nama PC wajib diisi');
                    }
                    const status = document.getElementById('pc-status').value;
                    const desc = document.getElementById('pc-desc').value;
                    if (['maintenance', 'broken', 'inactive'].includes(status) && !desc) {
                        Swal.showValidationMessage('Detail kerusakan wajib diisi');
                        return false;
                    }
                    return { 
                        name: name, 
                        ip: document.getElementById('pc-ip').value,
                        mac: document.getElementById('pc-mac').value,
                        status: status,
                        desc: desc
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    savePcData(null, row, col, result.value.name, result.value.ip, result.value.mac, result.value.status, result.value.desc);
                }
            });
        } else {
            // Tampilkan modal aksi PC (Edit, Hapus, Tandai Rusak)
            let isBroken = ['maintenance', 'broken', 'inactive'].includes(pcData.status);
            
            let statusBadge = pcData.status == 'active' ? '<span class="badge bg-primary">Aktif</span>' : 
                              (pcData.status == 'maintenance' ? '<span class="badge bg-warning text-dark">Maintenance</span>' : 
                              (pcData.status == 'broken' ? '<span class="badge bg-danger">Rusak</span>' : 
                              (pcData.status == 'inactive' ? '<span class="badge bg-secondary">Tidak Aktif</span>' : '<span class="badge bg-light border text-dark">Kosong</span>')));
            
            let damageDescHtml = '';
            if (isBroken && pcData.latest_damage) {
                damageDescHtml = `<div class="mb-1"><strong>Keterangan:</strong> <span class="text-danger">${pcData.latest_damage.description}</span></div>`;
            }

            Swal.fire({
                title: 'Detail: ' + pcData.name,
                html: `
                    <div class="text-start mb-3 border-bottom pb-3 text-body">
                        <div class="mb-1"><strong>Status:</strong> ${statusBadge}</div>
                        <div class="mb-1"><strong>IP Address:</strong> ${pcData.ip_address || '-'}</div>
                        <div class="mb-1"><strong>MAC Address:</strong> ${pcData.mac_address || '-'}</div>
                        <div class="mb-1"><strong>Posisi:</strong> Baris ${row+1}, Kolom ${col+1}</div>
                        ${damageDescHtml}
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <button class="btn btn-primary" onclick="editPc(${pcData.id}, '${pcData.name}', '${pcData.ip_address || ''}', '${pcData.mac_address || ''}', '${pcData.status}', ${row}, ${col})">Ubah Informasi PC</button>
                        <button class="btn btn-danger" onclick="deletePc(${pcData.id})">Hapus dari Denah</button>
                        ${!isBroken ? `<button class="btn btn-warning mt-2 text-dark" onclick="reportDamage(${pcData.id})">Ubah Status (dengan Detail)</button>` : `<div class="alert alert-warning mt-2 small">PC ini sedang memiliki laporan status non-aktif/rusak. Buka tab Data Kerusakan untuk update.</div>`}
                    </div>
                `,
                customClass: {
                    popup: 'bg-body text-body border',
                    title: 'text-body',
                    htmlContainer: 'text-body'
                },
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Tutup'
            });
        }
    }

    function savePcData(id, row, col, name, ip, mac, status, damageDesc = null) {
        fetch(`{{ route('admin.lab-mappings.save-pc', $laboratory) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                pc_id: id,
                grid_row: row,
                grid_col: col,
                name: name,
                ip_address: ip,
                mac_address: mac,
                status: status,
                damage_description: damageDesc
            })
        })
        .then(async res => {
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            if(!res.ok) {
                let errorMsg = 'Terjadi kesalahan dari server';
                if (data && data.message) errorMsg = data.message;
                if (data && data.errors) {
                    errorMsg = Object.values(data.errors).map(e => e.join(', ')).join('<br>');
                }
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            if(data && data.success) {
                window.location.reload();
            } else {
                Swal.fire('Error', (data && data.message) ? data.message : 'Gagal menyimpan PC.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                title: 'Error',
                html: err.message || 'Terjadi kesalahan saat menghubungi server.',
                icon: 'error'
            });
        });
    }

    function editPc(id, name, ip, mac, status, row, col) {
        Swal.fire({
            title: 'Ubah PC',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label fw-medium text-body">Nama / Nomor PC <span class="text-danger">*</span></label>
                    <input type="text" id="edit-pc-name" class="form-control" value="${name}" style="color: var(--bs-body-color);">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label fw-medium text-body">IP Address <span class="text-muted">(Opsional)</span></label>
                    <input type="text" id="edit-pc-ip" class="form-control" value="${ip}" style="color: var(--bs-body-color);">
                </div>
                <div class="mb-0 text-start">
                    <label class="form-label fw-medium text-body">MAC Address <span class="text-muted">(Opsional)</span></label>
                    <input type="text" id="edit-pc-mac" class="form-control" value="${mac}" style="color: var(--bs-body-color);">
                </div>
                <div class="mt-3 text-start">
                    <label class="form-label fw-medium text-body">Status <span class="text-danger">*</span></label>
                    <select id="edit-pc-status" class="form-select" style="color: var(--bs-body-color);" onchange="document.getElementById('edit-pc-desc-container').style.display = ['maintenance', 'broken', 'inactive'].includes(this.value) ? 'block' : 'none'">
                        <option value="active" ${status == 'active' ? 'selected' : ''}>Aktif</option>
                        <option value="maintenance" ${status == 'maintenance' ? 'selected' : ''}>Maintenance</option>
                        <option value="broken" ${status == 'broken' ? 'selected' : ''}>Rusak</option>
                        <option value="inactive" ${status == 'inactive' ? 'selected' : ''}>Tidak Aktif</option>
                        <option value="kosong" ${status == 'kosong' ? 'selected' : ''}>Kosong (Meja Kosong)</option>
                    </select>
                </div>
                <div class="mt-3 text-start" id="edit-pc-desc-container" style="display: ${['maintenance', 'broken', 'inactive'].includes(status) ? 'block' : 'none'};">
                    <label class="form-label fw-medium text-body">Detail / Keterangan <span class="text-danger">*</span></label>
                    <textarea id="edit-pc-desc" class="form-control" rows="3" style="color: var(--bs-body-color);" placeholder="Tuliskan alasan atau keterangan..."></textarea>
                </div>
            `,
            customClass: {
                popup: 'bg-body text-body border',
                title: 'text-body',
                htmlContainer: 'text-body'
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#002B5C',
            preConfirm: () => {
                const newName = document.getElementById('edit-pc-name').value;
                if (!newName) {
                    Swal.showValidationMessage('Nama PC wajib diisi');
                }
                const newStatus = document.getElementById('edit-pc-status').value;
                const newDesc = document.getElementById('edit-pc-desc').value;
                if (['maintenance', 'broken', 'inactive'].includes(newStatus) && !newDesc) {
                    Swal.showValidationMessage('Detail atau keterangan wajib diisi');
                    return false;
                }
                return { 
                    name: newName, 
                    ip: document.getElementById('edit-pc-ip').value,
                    mac: document.getElementById('edit-pc-mac').value,
                    status: newStatus,
                    desc: newDesc
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                savePcData(id, row, col, result.value.name, result.value.ip, result.value.mac, result.value.status, result.value.desc);
            }
        });
    }

    function deletePc(id) {
        Swal.fire({
            title: 'Hapus Slot PC?',
            text: "Data PC ini akan dihilangkan dari denah.",
            icon: 'warning',
            customClass: {
                popup: 'bg-body text-body border',
                title: 'text-body',
                htmlContainer: 'text-body'
            },
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('admin/lab-mappings/pc') }}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) window.location.reload();
                });
            }
        });
    }

    function reportDamage(id) {
        Swal.fire({
            title: 'Ubah Status PC',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label fw-medium text-body">Status <span class="text-danger">*</span></label>
                    <select id="report-status" class="form-select" style="color: var(--bs-body-color);">
                        <option value="broken">Rusak (Merah)</option>
                        <option value="maintenance">Maintenance (Kuning)</option>
                        <option value="inactive">Tidak Aktif (Abu-abu)</option>
                    </select>
                </div>
                <div class="mb-0 text-start">
                    <label class="form-label fw-medium text-body">Detail / Keterangan <span class="text-danger">*</span></label>
                    <textarea id="report-desc" class="form-control" rows="4" placeholder="Misal: Monitor tidak menyala, atau sedang dicabut untuk dipinjam..." style="color: var(--bs-body-color);"></textarea>
                </div>
            `,
            customClass: {
                popup: 'bg-body text-body border',
                title: 'text-body',
                htmlContainer: 'text-body'
            },
            showCancelButton: true,
            confirmButtonText: 'Lapor & Sinkronisasi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            preConfirm: () => {
                const status = document.getElementById('report-status').value;
                const desc = document.getElementById('report-desc').value;
                if (!desc) {
                    Swal.showValidationMessage('Deskripsi kerusakan wajib diisi');
                }
                return { status: status, desc: desc }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('admin/pc-damages/report') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: result.value.status,
                        description: result.value.desc
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire({
                            title: 'Tersinkronisasi!',
                            text: 'Status PC telah diubah dan laporan dikirim ke Data Kerusakan.',
                            icon: 'success',
                            customClass: {
                                popup: 'bg-body text-body border',
                                title: 'text-body',
                                htmlContainer: 'text-body'
                            },
                            confirmButtonColor: '#002B5C'
                        }).then(() => window.location.reload());
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
