@extends('layouts.admin')

@section('content')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<div class="page-header" style="margin-bottom: 25px;">
    <h1>🗂️ Laporan Per Staff</h1>
    <p style="color: #64748b;">Rekam berkas individu karyawan untuk periode: <strong>{{ $label }}</strong></p>
</div>

<!-- BLOCK PANEL CONTROL FILTER -->
<div class="card" style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
    <form action="{{ route('admin.reports.staff') }}" method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        
        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Pilih Karyawan</label>
            <select name="employee_id" style="padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; min-width: 200px;" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->division }})</option>
                @endforeach
            </select>
        </div>

        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Rentang Waktu</label>
            <select name="type" style="padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" id="filter-type">
                <option value="day" {{ $type === 'day' ? 'selected' : '' }}>Harian</option>
                <option value="week" {{ $type === 'week' ? 'selected' : '' }}>Mingguan</option>
                <option value="month" {{ $type === 'month' ? 'selected' : '' }}>Bulanan</option>
            </select>
        </div>

        <div style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-size: 13px; font-weight: 600; color: #475569;">Pilih Tanggal Acuan</label>
            <input type="date" name="date" value="{{ $targetDate }}" style="padding: 9px; border-radius: 6px; border: 1px solid #cbd5e1;">
        </div>

        <button type="submit" style="background: #4f46e5; color: white; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
            🔍 Buka Berkas
        </button>

        @if($selectedEmployee && $attendances->count() > 0)
            <button type="button" onclick="exportStaffPDF()" style="background: #ef4444; color: white; border: none; padding: 11px 20px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                📄 Download PDF
            </button>
        @endif
    </form>
</div>

<!-- AREA VIEW FOLDER HASIL REPORT -->
@if($selectedEmployee)
<div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 25px;">
    <div style="border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px;">
        <h3 id="pdf-emp-name" style="margin: 0; color: #1e293b; font-size: 18px;">{{ $selectedEmployee->name }}</h3>
        <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">NIP: <span id="pdf-emp-nip">{{ $selectedEmployee->nip }}</span> | Divisi/Status: <span id="pdf-emp-div">{{ $selectedEmployee->division }}</span></p>
    </div>

    <table id="staff-table-data" style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <th style="padding: 12px;">Tanggal</th>
                <th style="padding: 12px;">Jam Masuk</th>
                <th style="padding: 12px;">Jam Pulang</th>
                <th style="padding: 12px;">Status Hadir</th>
                <th style="padding: 12px;">Keterangan Alasan Telat / Izin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $row)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 12px;">{{ $row->date->format('d M Y') }}</td>
                <td style="padding: 12px;">{{ $row->clock_in ? $row->clock_in->format('H:i') : '--:--' }}</td>
                <td style="padding: 12px;">{{ $row->clock_out ? $row->clock_out->format('H:i') : '--:--' }}</td>
                <td style="padding: 12px;">
                    @if($row->status === 'masuk')
                        <span style="color: {{ $row->is_late ? '#ef4444' : '#10b981' }}; font-weight: bold;">
                            {{ $row->is_late ? '🔴 TELAT' : '🟢 HADIR' }}
                        </span>
                    @else
                        <span style="color: #f59e0b; font-weight: bold;">🟡 {{ strtoupper($row->status) }}</span>
                    @endif
                </td>
                <td style="padding: 12px; color: #64748b; font-size: 13px;">
                    {{ $row->late_reason ?? ($row->notes ?? '-') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8; font-style: italic;">
                    Tidak ditemukan rekam data absensi untuk rentang waktu ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    function exportStaffPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        const name = document.getElementById('pdf-emp-name').innerText;
        const nip = document.getElementById('pdf-emp-nip').innerText;
        const division = document.getElementById('pdf-emp-div').innerText;
        
        doc.setFont("Helvetica", "bold");
        doc.setFontSize(16);
        doc.text("BERKAS LAPORAN INDIVIDU STAFF", 14, 20);
        
        doc.setFontSize(11);
        doc.setFont("Helvetica", "normal");
        doc.text(`Nama Karyawan : ${name}`, 14, 28);
        doc.text(`NIP            : ${nip}`, 14, 34);
        doc.text(`Divisi / Peran : ${division}`, 14, 40);
        doc.text(`Periode Rekap  : {{ $label }}`, 14, 46);

        doc.autoTable({
            startY: 52,
            html: '#staff-table-data',
            theme: 'striped',
            headStyles: { fillColor: [79, 70, 229] },
            styles: { fontSize: 9 }
        });

        doc.save(`Laporan_Staff_${name.replace(/ /g, '_')}.pdf`);
    }
</script>
@endif
@endsection