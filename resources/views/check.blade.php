@extends('layouts.public')

@section('title', 'Cek Ketersediaan - Labkom Booking System | Techub')

@section('content')
<main class="container py-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <h3 class="fw-bold mb-0">Ketersediaan Labkom</h3>
                <a href="{{ url('/') }}" class="btn bg-light text-dark border"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <form action="{{ route('check') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="dateFilter" class="form-label fw-medium">Pilih Tanggal</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="date" class="form-control" id="dateFilter" value="{{ $date }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tampilkan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>

            <h5 class="fw-bold mb-3" id="scheduleTitle">Jadwal Pemakaian: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h5>

            <div class="row g-4">
                @foreach($laboratories as $lab)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm interactive-card">
                        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold text-primary mb-0"><i class="bi bi-pc-display me-2"></i>{{ $lab->name }}</h5>
                                @php
                                    $totalMapped = $lab->labPcs->count();
                                    $available = $totalMapped > 0 ? $lab->labPcs->where('status', 'active')->count() : $lab->capacity;
                                @endphp
                                <span class="badge bg-secondary rounded-pill">{{ $available }} PC Tersedia</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                @if($lab->bookings->isEmpty())
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Tersedia Sepanjang Hari</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 py-2 rounded-pill"><i class="bi bi-exclamation-circle-fill me-1"></i> Ada Jadwal Terisi</span>
                                @endif
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted-custom text-uppercase fw-bold mb-2 d-block">Slot Terpakai:</small>
                                @forelse($lab->bookings as $booking)
                                    @php
                                        $endWithBuffer = \Carbon\Carbon::parse($booking->end_time)->addHour();
                                    @endphp
                                    <div class="p-2 bg-light text-dark rounded mb-2 border-start border-4 border-danger">
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ $endWithBuffer->format('H:i') }}</div>
                                        <div class="small mt-1">{{ $booking->businessUnit->name }}{{ $booking->subBusinessUnit ? ' / ' . $booking->subBusinessUnit->name : '' }} - {{ $booking->pic_name }}</div>
                                        @if($booking->group_id)
                                            @php
                                                $recurringEnd = \App\Models\Booking::where('group_id', $booking->group_id)->max('date');
                                            @endphp
                                            @if($recurringEnd && $recurringEnd != $booking->date)
                                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-arrow-repeat text-secondary"></i> Rutin s/d {{ \Carbon\Carbon::parse($recurringEnd)->format('d M Y') }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted small">Belum ada booking pada tanggal ini.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pb-4">
                            <a href="{{ url('/') }}?lab_id={{ $lab->id }}&date={{ $date }}#bookingForm" class="btn btn-primary w-100">Booking Lab Ini</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>
</main>
@endsection
